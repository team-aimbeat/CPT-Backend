<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Subscription;
use App\Models\Package;
use App\Models\User;
use App\Models\ReferralCode;
use App\Models\ReferralRedemption;
use App\Http\Resources\SubscriptionResource;
use App\Traits\SubscriptionTrait;

class SubscriptionController extends Controller
{
    use SubscriptionTrait;
    public function getList(Request $request)
    {
        $subscription = Subscription::mySubscription();

        $subscription->when(request('id'), function ($q) {
            return $q->where('id', 'LIKE', '%' . request('id') . '%');
        });
                
        $per_page = config('constant.PER_PAGE_LIMIT');
        if( $request->has('per_page') && !empty($request->per_page)){
            if(is_numeric($request->per_page))
            {
                $per_page = $request->per_page;
            }
            if($request->per_page == -1 ){
                $per_page = $subscription->count();
            }
        }

        $subscription = $subscription->orderBy('id', 'asc')->paginate($per_page);

        $items = SubscriptionResource::collection($subscription);

        $response = [
            'pagination'    => json_pagination_response($items),
            'data'          => $items,
        ];
        
        return json_custom_response($response);
    }

    public function subscriptionSave(Request $request)
    {
        $data = $request->all();

        $user_id = auth()->id();
        $user = User::where('id', $user_id)->first();
        $package_data = Package::where('id',$data['package_id'])->first();
        $referralCodeInput = $request->input('referral_code');
        
        $get_existing_plan = $this->get_user_active_subscription_plan($user_id);
        
        $active_plan_left_days = 0;
        
        $data['user_id'] = $user_id;
        $data['status'] = config('constant.SUBSCRIPTION_STATUS.PENDING');
        $data['subscription_start_date'] = date('Y-m-d H:i:s');
        $data['total_amount'] = $package_data->price;

        if (!empty($referralCodeInput)) {
            $referral = ReferralCode::where('code', $referralCodeInput)
                ->where('status', 'active')
                ->first();

            if (!$referral) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid referral code.',
                ], 422);
            }

            if ($referral->user_id == $user_id) {
                return response()->json([
                    'status' => false,
                    'message' => 'You cannot use your own referral code.',
                ], 422);
            }

            $hasPaidSubscription = Subscription::where('user_id', $user_id)
                ->where('payment_status', 'paid')
                ->exists();

            if ($hasPaidSubscription) {
                return response()->json([
                    'status' => false,
                    'message' => 'Referral code valid only for first purchase.',
                ], 422);
            }

            if (!empty($referral->max_redemptions)) {
                $redemptionCount = ReferralRedemption::where('referral_code_id', $referral->id)->count();
                if ($redemptionCount >= $referral->max_redemptions) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Referral code redemption limit reached.',
                    ], 422);
                }
            }

            $data['referral_code_id'] = $referral->id;
            $data['referral_referrer_id'] = $referral->user_id;
        }

        $creditToUse = 0;
        if (!empty($user->referral_credit_balance)) {
            $creditToUse = min((float) $user->referral_credit_balance, (float) $package_data->price);
        }
        $data['referral_credit_used'] = $creditToUse;
        $data['total_amount'] = max(0, (float) $package_data->price - $creditToUse);

        if($get_existing_plan)
        {
            $active_plan_left_days = $this->check_days_left_plan($get_existing_plan, $data);
            if($package_data->id != $get_existing_plan->package_id)
            {
                $get_existing_plan->update([
                    'status' => config('constant.SUBSCRIPTION_STATUS.INACTIVE')
                ]);
                $get_existing_plan->save();
            }
        }
        $data['subscription_end_date'] = $this->get_plan_expiration_date( $data['subscription_start_date'], $package_data->duration_unit, $active_plan_left_days, $package_data->duration );

        $data['package_data'] = $package_data ?? null;

        $subscription = Subscription::create($data);

        if( $subscription->payment_status == 'paid' ) {
            $subscription->status = config('constant.SUBSCRIPTION_STATUS.ACTIVE');
            $subscription->save();
            $user->update([ 'is_subscribe' => 1 ]);
        }

        $message = __('message.save_form', ['form' => __('message.subscription')]);
        
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $subscription,
            'referral_credit_balance' => (float) $user->referral_credit_balance,
            'referral_credit_used' => (float) $subscription->referral_credit_used,
        ]);
    }

    public function cancelSubscription(Request $request)
    {
        $user_id = auth()->id();
        $id = $request->id;
        $user_subscription = Subscription::where('id', $id )->where('user_id', $user_id)->first();
        $user = User::where('id', $user_id)->first();

        $message = __('message.not_found_entry',['name' => __('message.subscription')] );
        if($user_subscription)
        {
            $user_subscription->status = config('constant.SUBSCRIPTION_STATUS.INACTIVE');
            $user_subscription->save();
            $user->is_subscribe = 0;
            $user->save();
            $message = __('message.subscription_cancelled');
        }
        return json_message_response($message);
    }
}
