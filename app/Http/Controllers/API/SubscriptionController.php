<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Subscription;
use App\Models\Package;
use App\Models\User;
use App\Models\ReferralCode;
use App\Models\ReferralRedemption;
use App\Models\PaymentGateway;
use App\Http\Resources\SubscriptionResource;
use App\Traits\SubscriptionTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

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
        $request->validate([
            'package_id' => 'required|exists:packages,id',
            'payment_type' => 'nullable|string',
            'trial_autopay' => 'nullable|boolean',
        ]);

        $data = $request->all();

        $user_id = auth()->id();
        $user = User::where('id', $user_id)->first();
        $package_data = Package::where('id',$data['package_id'])->first();
        $referralCodeInput = $request->input('referral_code');
        $requestedPaymentType = $request->input('payment_type');
        $isIosIap = $package_data->platform === 'ios'
            && (in_array($requestedPaymentType, ['ios_iap', 'apple_iap'], true) || $request->boolean('trial_autopay'));
        $isTrialAutopay = $package_data->platform === 'android'
            && (in_array($requestedPaymentType, ['razorpay_autopay', 'android_autopay'], true) || $request->boolean('trial_autopay'));

        if ($request->boolean('trial_autopay') && !$isTrialAutopay && !$isIosIap) {
            return response()->json([
                'status' => false,
                'message' => 'Trial autopay is available only for Android or iOS subscription packages.',
            ], 422);
        }

        if ($isTrialAutopay) {
            $existingTrial = Subscription::where('user_id', $user_id)
                ->where('status', config('constant.SUBSCRIPTION_STATUS.TRIALING'))
                ->where('trial_ends_at', '>=', now())
                ->first();

            if ($existingTrial) {
                return response()->json([
                    'status' => true,
                    'message' => 'Trial subscription already exists.',
                    'data' => new SubscriptionResource($existingTrial),
                    'requires_autopay_authorization' => empty($existingTrial->gateway_subscription_id),
                ]);
            }

            $hasPreviousPaidSubscription = Subscription::where('user_id', $user_id)
                ->where('payment_status', 'paid')
                ->exists();

            if ($hasPreviousPaidSubscription) {
                return response()->json([
                    'status' => false,
                    'message' => 'Free trial is available only before the first paid subscription.',
                ], 422);
            }
        }
        
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

        // An Apple purchase is not complete until StoreKit receipt verification succeeds.
        // Do not deactivate an existing plan merely because the iOS checkout was opened.
        if($get_existing_plan && !$isIosIap)
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
        if ($isTrialAutopay) {
            $trialStartAt = Carbon::now();
            $trialEndsAt = $trialStartAt->copy()->addDays((int) config('constant.FREE_TRIAL_DAYS', 3));

            $data['payment_type'] = 'razorpay_autopay';
            $data['payment_status'] = 'pending';
            $data['status'] = config('constant.SUBSCRIPTION_STATUS.TRIALING');
            $data['autopay_status'] = config('constant.AUTOPAY_STATUS.PENDING');
            $data['trial_start_at'] = $trialStartAt->format('Y-m-d H:i:s');
            $data['trial_ends_at'] = $trialEndsAt->format('Y-m-d H:i:s');
            $data['billing_starts_at'] = $trialEndsAt->format('Y-m-d H:i:s');
            $data['subscription_start_date'] = $trialEndsAt->format('Y-m-d H:i:s');
        }

        if ($isIosIap) {
            // Apple determines trial eligibility and the exact trial end date. Those values
            // are written only after a verified StoreKit purchase is received.
            $data['payment_type'] = 'ios_iap';
            $data['payment_status'] = 'pending';
            $data['status'] = config('constant.SUBSCRIPTION_STATUS.PENDING');
            $data['autopay_status'] = config('constant.AUTOPAY_STATUS.PENDING');
            $data['referral_credit_used'] = 0;
            $data['total_amount'] = $package_data->price;
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
            'data' => ($isTrialAutopay || $isIosIap) ? new SubscriptionResource($subscription) : $subscription,
            'referral_credit_balance' => (float) $user->referral_credit_balance,
            'referral_credit_used' => (float) $subscription->referral_credit_used,
            'requires_autopay_authorization' => $isTrialAutopay,
            'requires_storekit_purchase' => $isIosIap,
            'trial_remaining_days' => $isTrialAutopay ? (int) config('constant.FREE_TRIAL_DAYS', 3) : 0,
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
            if ($user_subscription->payment_type === 'ios_iap') {
                return response()->json([
                    'status' => false,
                    'message' => 'Cancel this subscription from Apple ID Subscriptions. Access remains available until Apple confirms expiry.',
                    'manage_subscription_url' => 'https://apps.apple.com/account/subscriptions',
                ], 422);
            }

            $gatewayCancelResponse = null;
            if ($user_subscription->payment_type === 'razorpay_autopay' && !empty($user_subscription->gateway_subscription_id)) {
                $gatewayCancelResponse = $this->cancelRazorpaySubscription($user_subscription->gateway_subscription_id);
            }

            $user_subscription->status = config('constant.SUBSCRIPTION_STATUS.INACTIVE');
            $user_subscription->autopay_status = config('constant.AUTOPAY_STATUS.CANCELLED');
            $user_subscription->autopay_cancelled_at = now();
            if ($gatewayCancelResponse) {
                $user_subscription->transaction_detail = array_merge($user_subscription->transaction_detail ?: [], [
                    'razorpay_cancel_response' => $gatewayCancelResponse,
                ]);
            }
            $user_subscription->save();
            $user->is_subscribe = 0;
            $user->save();
            $message = __('message.subscription_cancelled');
        }
        return json_message_response($message);
    }

    private function cancelRazorpaySubscription(string $gatewaySubscriptionId): ?array
    {
        $credentials = $this->razorpayCredentials();
        if (!$credentials) {
            return null;
        }

        try {
            return Http::withBasicAuth($credentials['key_id'], $credentials['secret_id'])
                ->timeout(20)
                ->asJson()
                ->post('https://api.razorpay.com/v1/subscriptions/' . $gatewaySubscriptionId . '/cancel', [
                    'cancel_at_cycle_end' => false,
                ])
                ->json();
        } catch (\Throwable $e) {
            return [
                'error' => $e->getMessage(),
            ];
        }
    }

    private function razorpayCredentials(): ?array
    {
        $gateway = PaymentGateway::where('type', 'razorpay')
            ->where('status', 1)
            ->first();

        if (!$gateway) {
            return null;
        }

        $values = ((int) $gateway->is_test === 1) ? $gateway->test_value : $gateway->live_value;
        $keyId = $values['key_id'] ?? null;
        $secretId = $values['secret_id'] ?? null;

        if (empty($keyId) || empty($secretId)) {
            return null;
        }

        return [
            'key_id' => $keyId,
            'secret_id' => $secretId,
        ];
    }
}
