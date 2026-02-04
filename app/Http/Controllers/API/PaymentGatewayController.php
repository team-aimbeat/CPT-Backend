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

            $offerCoupon = null;
            $package = $subscription->package;
            if ($package && $package->offer_enabled) {
                $offerType = $package->offer_type ?: 'free_access';
                $accessDays = $package->offer_access_days;
                $maxRedemptions = $package->offer_max_redemptions;

                $code = 'OFFER-' . strtoupper(substr(md5($subscription->id . '|' . now()->timestamp), 0, 8));

                $offerCoupon = Coupon::create([
                    'code' => $code,
                    'type' => $offerType,
                    'access_days' => $accessDays,
                    'max_redemptions' => $maxRedemptions,
                    'per_user_limit' => 1,
                    'status' => 'active',
                    'is_auto_generated' => true,
                    'source_subscription_id' => $subscription->id,
                    'description' => 'Auto-generated offer coupon for subscription #' . $subscription->id,
                ]);

                if (!empty($user->email)) {
                    try {
                        $lines = [
                            'Your offer coupon has been generated.',
                            'Coupon Code: ' . $offerCoupon->code,
                            'Type: ' . $offerCoupon->type,
                            'Access Days: ' . ($offerCoupon->access_days ?? 'N/A'),
                            'Max Redemptions: ' . ($offerCoupon->max_redemptions ?? 'N/A'),
                            'Use this coupon to give free access to your extra member.',
                        ];
                        Mail::raw(implode("\n", $lines), function ($message) use ($user) {
                            $message->to($user->email)
                                ->subject('Your Offer Coupon Code');
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
                'offer_coupon' => $offerCoupon ? [
                    'code' => $offerCoupon->code,
                    'type' => $offerCoupon->type,
                    'access_days' => $offerCoupon->access_days,
                    'max_redemptions' => $offerCoupon->max_redemptions,
                ] : null,
                // 'invoice_url' => asset('storage/' . $fileName)
            ]);
    
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
}
