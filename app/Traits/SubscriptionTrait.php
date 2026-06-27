<?php

namespace App\Traits;

use App\Models\Subscription;
use App\Models\User;
use App\Http\Resources\SubscriptionResource;
use Carbon\Carbon;

trait SubscriptionTrait {

    public function get_user_active_subscription_plan($user_id)
    {
        $get_user_plan = Subscription::where('user_id', $user_id)->where('status', config('constant.SUBSCRIPTION_STATUS.ACTIVE'))->first();
        $activeplan = null; 
        if(!empty($get_user_plan)){
            $activeplan = new SubscriptionResource($get_user_plan);
        }
        return $activeplan;
    }

    public function is_subscribed_users($user_id)
    {
        return Subscription::where('user_id', $user_id)
            ->where('status', config('constant.SUBSCRIPTION_STATUS.ACTIVE'))
            ->where('payment_status', 'paid')
            ->exists();
    }

    public function get_user_trial_subscription($user_id)
    {
        return Subscription::where('user_id', $user_id)
            ->where('status', config('constant.SUBSCRIPTION_STATUS.TRIALING'))
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '>=', now())
            ->where(function ($query) {
                $query->whereNotNull('mandate_authorized_at')
                    ->orWhereIn('autopay_status', [
                        config('constant.AUTOPAY_STATUS.AUTHENTICATED'),
                        config('constant.AUTOPAY_STATUS.ACTIVE'),
                    ]);
            })
            ->orderByDesc('trial_ends_at')
            ->first();
    }

    public function is_trial_active($user_id)
    {
        return (bool) $this->get_user_trial_subscription($user_id);
    }

    public function has_subscription_or_trial_access($user_id)
    {
        return $this->is_subscribed_users($user_id) || $this->is_trial_active($user_id);
    }

    public function get_plan_expiration_date($plan_start_date = '', $duration_unit = '', $left_days = 0, $plan_duration = 1)
    {
        $start_at = new Carbon($plan_start_date);
        $end_date = '';
        
        if($duration_unit === 'monthly'){
            $end_date =  $start_at->addMonths($plan_duration)->addDays($left_days);
        }
        if($duration_unit === 'yearly'){
            $end_date =  $start_at->addYears($plan_duration)->addDays($left_days);
        }
        return $end_date->format('Y-m-d H:i:s');
    }

    public function check_days_left_plan($previous_plan, $new_plan)
    {
        $previous_plan_end = new Carbon($previous_plan->subscription_end_date);
        $new_plan_start = new Carbon(date('Y-m-d H:i:s'));
        $left_days = $previous_plan_end->diffInDays($new_plan_start);
        return $left_days;
    }

    public function user_last_plan($user_id)
    {
        $user_subscribed = Subscription::where('user_id',$user_id)->where('status',config('constant.SUBSCRIPTION_STATUS.INACTIVE'))->orderBy('id','desc')->first();
        $inactivePlan = null;

        if(!empty($user_subscribed)) {
            $inactivePlan = new SubscriptionResource($user_subscribed);
        }
        return $inactivePlan;
    }

    public function is_plan_active($user_id){
        $is_subscribe = Subscription::where('user_id', $user_id)
            ->where('status', config('constant.SUBSCRIPTION_STATUS.ACTIVE'))
            ->where('payment_status', 'paid')
            ->exists();
        return $is_subscribe;
    }

    public function subscriptionPlanDetail($user_id)
    {
        $subscription_plan = $this->get_user_active_subscription_plan($user_id);
        $is_subscribed_users = $this->is_subscribed_users($user_id);
        $trialPlan = null;
        if(!$this->is_plan_active($user_id) && !$is_subscribed_users) {
            $trialPlan = $this->get_user_trial_subscription($user_id);
            $subscription_plan = $trialPlan ? new SubscriptionResource($trialPlan) : $this->user_last_plan($user_id);
        }

        $isTrialActive = (bool) $trialPlan;
        if (!$isTrialActive && !$is_subscribed_users) {
            $isTrialActive = $this->is_trial_active($user_id);
        }

        $user = User::find($user_id);
        $hasCouponAccess = $user ? $user->hasActiveCouponAccess() : false;
        $hasAccess = $is_subscribed_users || $isTrialActive || $hasCouponAccess;
        
        return [
            'is_subscribe' => (int) $is_subscribed_users,
            'is_trial_active' => (int) $isTrialActive,
            'has_coupon_access' => (int) $hasCouponAccess,
            'coupon_access_ends_at' => $hasCouponAccess && $user->coupon_access_ends_at
                ? $user->coupon_access_ends_at->toDateTimeString()
                : null,
            'has_access' => (int) $hasAccess,
            'access_type' => $is_subscribed_users
                ? 'paid'
                : ($isTrialActive ? 'trial' : ($hasCouponAccess ? 'coupon' : 'none')),
            'subscription_plan' => $subscription_plan,
        ];
        
        return null;
    }
}
