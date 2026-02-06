<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaymentGateway;
use App\Http\Resources\PaymentGatewayResource;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Coupon;
use App\Models\ReferralCode;
use App\Models\ReferralRedemption;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;

class PaymentGatewayController extends Controller
{

    public function getList(Request $request)
    {
        $gateways = PaymentGateway::where('status',1);

        $gateways = $gateways->where('type','!=', 'cash' )->orderBy('title','asc')->paginate(10);
        $items = PaymentGatewayResource::collection($gateways);

        $response = [
            'pagination' => json_pagination_response($items),
            'data' => $items,
        ];
        
        return json_custom_response($response);
    }
    
    public function completePayment(Request $request)
    {
        $request->validate([
            'subscription_id'     => 'required|exists:subscriptions,id',
            'razorpay_payment_id' => 'required',
        ]);
    
        try {
            // 1. Subscription fetch karein package details ke saath
            $subscription = Subscription::with('package')->findOrFail($request->subscription_id);
            $user = auth()->user();
    
            // 2. Payment table mein data store karein
            $payment = Payment::create([
                'user_id'             => $user->id,
                'subscription_id'     => $subscription->id,
                'package_id'          => $subscription->package_id,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'amount'              => $subscription->total_amount,
                'status'              => 'success',
                'currency'            => 'INR',
            ]);
    
            // 3. Subscription status update karein
            $subscription->update([
                'payment_status' => 'paid',
                'status'         => config('constant.SUBSCRIPTION_STATUS.ACTIVE')
            ]);
    
            // 4. User table update
            $user->update(['is_subscribe' => 1]);

            if (!empty($subscription->referral_credit_used)) {
                $newBalance = max(0, (float) $user->referral_credit_balance - (float) $subscription->referral_credit_used);
                $user->update(['referral_credit_balance' => $newBalance]);
            }

            if (!empty($subscription->referral_code_id) && !empty($subscription->referral_referrer_id)) {
                $alreadyRedeemed = ReferralRedemption::where('subscription_id', $subscription->id)->exists();
                if (!$alreadyRedeemed) {
                    ReferralRedemption::create([
                        'referral_code_id' => $subscription->referral_code_id,
                        'referrer_id' => $subscription->referral_referrer_id,
                        'referred_user_id' => $user->id,
                        'subscription_id' => $subscription->id,
                        'reward_amount' => 200,
                        'redeemed_at' => now(),
                    ]);

                    $referrer = User::find($subscription->referral_referrer_id);
                    if ($referrer) {
                        $referrer->update([
                            'referral_credit_balance' => (float) $referrer->referral_credit_balance + 200,
                        ]);
                    }
                }
            }

            $existingReferral = ReferralCode::where('user_id', $user->id)
                ->where('status', 'active')
                ->first();

            if (!$existingReferral) {
                $code = 'REF-' . strtoupper(substr(md5($user->id . now()->timestamp), 0, 8));
                ReferralCode::create([
                    'user_id' => $user->id,
                    'code' => $code,
                    'status' => 'active',
                ]);
            }

            $offerCoupon = null;
            $package = $subscription->package;
            if ($package && $package->offer_enabled) {
                $offerType = $package->offer_type ?: 'free_access';
                $freeAccessDays = $package->offer_access_days;
                $sameCount = (int) ($package->offer_same_access_count ?? 0);
                $freeCount = (int) ($package->offer_free_access_count ?? 0);

                $sameAccessDays = null;
                if (!empty($subscription->subscription_end_date)) {
                    $sameAccessDays = max(1, now()->diffInDays($subscription->subscription_end_date, false));
                } else {
                    $months = (int) $package->duration;
                    if ($package->duration_unit === 'yearly') {
                        $months = $months * 12;
                    }
                    $sameAccessDays = max(1, $months * 30);
                }

                $generated = [];
                for ($i = 0; $i < $sameCount; $i++) {
                    $code = 'SAME-' . strtoupper(substr(md5($subscription->id . '|same|' . $i . '|' . now()->timestamp), 0, 8));
                    $generated[] = Coupon::create([
                        'code' => $code,
                        'type' => 'same_access',
                        'access_days' => $sameAccessDays,
                        'max_redemptions' => 1,
                        'per_user_limit' => 1,
                        'status' => 'active',
                        'is_auto_generated' => true,
                        'source_subscription_id' => $subscription->id,
                        'description' => 'Auto-generated SAME access coupon for subscription #' . $subscription->id,
                    ]);
                }

                for ($i = 0; $i < $freeCount; $i++) {
                    $code = 'FREE-' . strtoupper(substr(md5($subscription->id . '|free|' . $i . '|' . now()->timestamp), 0, 8));
                    $generated[] = Coupon::create([
                        'code' => $code,
                        'type' => $offerType,
                        'access_days' => $freeAccessDays,
                        'max_redemptions' => 1,
                        'per_user_limit' => 1,
                        'status' => 'active',
                        'is_auto_generated' => true,
                        'source_subscription_id' => $subscription->id,
                        'description' => 'Auto-generated FREE access coupon for subscription #' . $subscription->id,
                    ]);
                }

                $offerCoupon = $generated;

                if (!empty($user->email)) {
                    try {
                        $lines = ['Your offer coupons have been generated:'];
                        foreach ($generated as $c) {
                            $lines[] = 'Code: ' . $c->code . ' | Type: ' . $c->type . ' | Days: ' . ($c->access_days ?? 'N/A');
                        }
                        $lines[] = 'Share these coupons with your extra members.';
                        Mail::raw(implode("\n", $lines), function ($message) use ($user) {
                            $message->to($user->email)
                                ->subject('Your Offer Coupon Codes');
                        });
                    } catch (\Exception $e) {
                        // Ignore mail failure; payment should still succeed.
                    }
                }
            }

            // 5. PDF Invoice Generate karein
            // $data = [
            //     'payment'      => $payment,
            //     'subscription' => $subscription,
            //     'user'         => $user,
            //     'package'      => $subscription->package_data,
            //     'date'         => now()->format('d-m-Y')
            // ];
    
            // $pdf = Pdf::loadView('invoice.pdf', $data);
    
            // $fileName = 'invoices/inv_' . $payment->razorpay_payment_id . '.pdf';
            // Storage::disk('public')->put($fileName, $pdf->output());
    
            // $payment->update(['invoice_path' => $fileName]);
    
            return response()->json([
                'status' => true,
                'message' => 'Payment successful and invoice generated',
                'data' => $payment,
                'referral_credit_balance' => (float) $user->fresh()->referral_credit_balance,
                'referral_credit_used' => (float) $subscription->referral_credit_used,
                'offer_coupon' => is_array($offerCoupon)
                    ? collect($offerCoupon)->map(fn ($c) => [
                        'code' => $c->code,
                        'type' => $c->type,
                        'access_days' => $c->access_days,
                        'max_redemptions' => $c->max_redemptions,
                    ])->values()
                    : null,
                // 'invoice_url' => asset('storage/' . $fileName)
            ]);
    
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
}
