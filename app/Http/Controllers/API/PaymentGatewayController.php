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
use App\Traits\SubscriptionTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PaymentGatewayController extends Controller
{
    use SubscriptionTrait;

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
            $subscription = Subscription::with('package')
                ->where('id', $request->subscription_id)
                ->where('user_id', auth()->id())
                ->firstOrFail();
            $user = auth()->user();

            $result = DB::transaction(function () use ($request, $subscription, $user) {
                $payment = Payment::create([
                    'user_id'             => $user->id,
                    'subscription_id'     => $subscription->id,
                    'package_id'          => $subscription->package_id,
                    'razorpay_payment_id' => $request->razorpay_payment_id,
                    'gateway'             => 'razorpay',
                    'transaction_id'      => $request->razorpay_payment_id,
                    'gateway_response'    => $request->all(),
                    'amount'              => $subscription->total_amount,
                    'status'              => 'success',
                    'currency'            => 'INR',
                ]);

                $subscription->update([
                    'payment_type' => 'razorpay',
                    'txn_id' => $request->razorpay_payment_id,
                    'transaction_detail' => $request->all(),
                ]);

                $offerCoupon = $this->finalizePaidSubscription($subscription, $user);

                return compact('payment', 'offerCoupon');
            });

            $payment = $result['payment'];
            $offerCoupon = $result['offerCoupon'];
            $invoiceUrl = $this->generateAndSendInvoice($payment, $subscription, $user);
    
            return response()->json([
                'status' => true,
                'message' => 'Payment successful.',
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
                'invoice_url' => $invoiceUrl,
            ]);
    
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function createRazorpayAutopaySubscription(Request $request)
    {
        $request->validate([
            'subscription_id' => 'required|exists:subscriptions,id',
            'plan_id' => 'nullable|string',
            'total_count' => 'nullable|integer|min:1',
            'quantity' => 'nullable|integer|min:1',
        ]);

        try {
            $user = auth()->user();
            $subscription = Subscription::with('package')
                ->where('id', $request->subscription_id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            $package = $subscription->package;
            if (!$package || $package->platform !== 'android') {
                return response()->json([
                    'status' => false,
                    'message' => 'Selected package is not an Android package.',
                ], 422);
            }

            $planId = $request->plan_id ?: $package->product_id;
            if (empty($planId)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Razorpay plan_id is missing. Add it in package Product ID or send plan_id.',
                ], 422);
            }

            $credentials = $this->razorpayCredentials();
            if (!$credentials) {
                return response()->json([
                    'status' => false,
                    'message' => 'Active Razorpay credentials are missing.',
                ], 422);
            }

            $payload = [
                'plan_id' => $planId,
                'total_count' => (int) $request->input('total_count', $this->defaultRazorpayTotalCount($package)),
                'quantity' => (int) $request->input('quantity', 1),
                'customer_notify' => true,
                'notes' => [
                    'local_subscription_id' => (string) $subscription->id,
                    'user_id' => (string) $user->id,
                    'package_id' => (string) $package->id,
                ],
            ];

            $billingStartsAt = $subscription->billing_starts_at ?: $subscription->trial_ends_at;
            if ($billingStartsAt && Carbon::parse($billingStartsAt)->isFuture()) {
                $payload['start_at'] = Carbon::parse($billingStartsAt)->timestamp;
            }

            $razorpaySubscription = Http::withBasicAuth($credentials['key_id'], $credentials['secret_id'])
                ->timeout(20)
                ->asJson()
                ->post('https://api.razorpay.com/v1/subscriptions', $payload)
                ->json();

            if (empty($razorpaySubscription['id'])) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unable to create Razorpay autopay subscription.',
                    'data' => $razorpaySubscription,
                ], 422);
            }

            $subscription->update([
                'payment_type' => 'razorpay_autopay',
                'gateway_subscription_id' => $razorpaySubscription['id'],
                'autopay_status' => $razorpaySubscription['status'] ?? 'created',
                'billing_starts_at' => !empty($payload['start_at'])
                    ? Carbon::createFromTimestamp($payload['start_at'])->format('Y-m-d H:i:s')
                    : $subscription->billing_starts_at,
                'transaction_detail' => array_merge($subscription->transaction_detail ?: [], [
                    'razorpay_subscription' => $razorpaySubscription,
                    'razorpay_create_payload' => $payload,
                ]),
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Razorpay autopay subscription created.',
                'data' => [
                    'key_id' => $credentials['key_id'],
                    'local_subscription_id' => $subscription->id,
                    'subscription_id' => $razorpaySubscription['id'],
                    'short_url' => $razorpaySubscription['short_url'] ?? null,
                    'status' => $razorpaySubscription['status'] ?? null,
                    'trial_ends_at' => optional($subscription->trial_ends_at)->toDateTimeString(),
                    'billing_starts_at' => optional($subscription->fresh()->billing_starts_at)->toDateTimeString(),
                    'razorpay_subscription' => $razorpaySubscription,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function completeRazorpayAutopayPayment(Request $request)
    {
        $request->validate([
            'subscription_id' => 'required|exists:subscriptions,id',
            'razorpay_subscription_id' => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ]);

        try {
            $user = auth()->user();
            $subscription = Subscription::with('package')
                ->where('id', $request->subscription_id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            if (!empty($subscription->gateway_subscription_id) && $subscription->gateway_subscription_id !== $request->razorpay_subscription_id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Razorpay subscription ID does not match.',
                ], 422);
            }

            $credentials = $this->razorpayCredentials();
            if (!$credentials || !$this->verifyRazorpaySubscriptionSignature($request->razorpay_payment_id, $request->razorpay_subscription_id, $request->razorpay_signature, $credentials['secret_id'])) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid Razorpay autopay signature.',
                ], 422);
            }

            $result = DB::transaction(function () use ($request, $subscription, $user) {
                $isTrialSubscription = $subscription->status === config('constant.SUBSCRIPTION_STATUS.TRIALING')
                    || !empty($subscription->trial_ends_at);

                $payment = Payment::create([
                    'user_id' => $user->id,
                    'subscription_id' => $subscription->id,
                    'package_id' => $subscription->package_id,
                    'razorpay_payment_id' => $request->razorpay_payment_id,
                    'gateway' => 'razorpay_autopay',
                    'transaction_id' => $request->razorpay_payment_id,
                    'gateway_subscription_id' => $request->razorpay_subscription_id,
                    'gateway_response' => $request->all(),
                    'amount' => $isTrialSubscription ? 0 : $subscription->total_amount,
                    'status' => $isTrialSubscription ? 'authorized' : 'success',
                    'method' => $isTrialSubscription ? 'mandate_authorization' : null,
                    'currency' => 'INR',
                ]);

                $subscriptionUpdates = [
                    'payment_type' => 'razorpay_autopay',
                    'txn_id' => $request->razorpay_payment_id,
                    'gateway_subscription_id' => $request->razorpay_subscription_id,
                    'autopay_status' => config('constant.AUTOPAY_STATUS.AUTHENTICATED'),
                    'mandate_authorized_at' => now(),
                    'transaction_detail' => array_merge($subscription->transaction_detail ?: [], [
                        'razorpay_autopay_complete' => $request->all(),
                    ]),
                ];

                if ($isTrialSubscription) {
                    $subscriptionUpdates['payment_status'] = 'pending';
                    $subscriptionUpdates['status'] = config('constant.SUBSCRIPTION_STATUS.TRIALING');
                    $offerCoupon = null;
                } else {
                    $offerCoupon = $this->finalizePaidSubscription($subscription, $user);
                    $subscriptionUpdates['autopay_status'] = config('constant.AUTOPAY_STATUS.ACTIVE');
                }

                $subscription->update($subscriptionUpdates);

                return compact('payment', 'offerCoupon');
            });
            $invoiceUrl = $result['payment']->status === 'success'
                ? $this->generateAndSendInvoice($result['payment'], $subscription, $user)
                : null;

            return response()->json([
                'status' => true,
                'message' => 'Razorpay autopay verified successfully.',
                'data' => $result['payment'],
                'subscription' => $subscription->fresh(),
                'offer_coupon' => $this->formatOfferCoupons($result['offerCoupon']),
                'invoice_url' => $invoiceUrl,
                'access_type' => $subscription->fresh()->status === config('constant.SUBSCRIPTION_STATUS.TRIALING') ? 'trial' : 'paid',
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function razorpayWebhook(Request $request)
    {
        $webhookSecret = config('services.razorpay.webhook_secret');
        if (!empty($webhookSecret)) {
            $signature = $request->header('X-Razorpay-Signature');
            $expected = hash_hmac('sha256', $request->getContent(), $webhookSecret);
            if (!hash_equals($expected, (string) $signature)) {
                return response()->json(['status' => false, 'message' => 'Invalid webhook signature.'], 400);
            }
        }

        $payload = $request->all();
        $event = $payload['event'] ?? null;
        $paymentEntity = $payload['payload']['payment']['entity'] ?? null;
        $subscriptionEntity = $payload['payload']['subscription']['entity'] ?? null;
        $gatewaySubscriptionId = $subscriptionEntity['id'] ?? $paymentEntity['subscription_id'] ?? null;

        if (empty($gatewaySubscriptionId)) {
            return response()->json(['status' => true, 'message' => 'Webhook ignored.']);
        }

        $subscription = Subscription::with('package')
            ->where('gateway_subscription_id', $gatewaySubscriptionId)
            ->first();

        if (!$subscription) {
            return response()->json(['status' => true, 'message' => 'Local subscription not found.']);
        }

        DB::transaction(function () use ($event, $payload, $paymentEntity, $subscriptionEntity, $subscription, $gatewaySubscriptionId) {
            if ($subscriptionEntity) {
                $gatewayStatus = $subscriptionEntity['status'] ?? $subscription->autopay_status;
                $subscription->autopay_status = $gatewayStatus;

                if (!empty($subscriptionEntity['current_start'])) {
                    $subscription->subscription_start_date = Carbon::createFromTimestamp($subscriptionEntity['current_start'])->format('Y-m-d H:i:s');
                }

                if (!empty($subscriptionEntity['current_end'])) {
                    $subscription->subscription_end_date = Carbon::createFromTimestamp($subscriptionEntity['current_end'])->format('Y-m-d H:i:s');
                }

                if (in_array($gatewayStatus, ['authenticated', 'active'], true) && empty($subscription->mandate_authorized_at)) {
                    $subscription->mandate_authorized_at = now();
                }

                if (in_array($subscriptionEntity['status'] ?? null, ['cancelled', 'completed', 'expired'])) {
                    $subscription->status = config('constant.SUBSCRIPTION_STATUS.INACTIVE');
                    $subscription->autopay_cancelled_at = now();
                    optional($subscription->user)->update(['is_subscribe' => 0]);
                }

                if (($subscriptionEntity['status'] ?? null) === 'halted') {
                    $subscription->status = config('constant.SUBSCRIPTION_STATUS.PAST_DUE');
                    $subscription->payment_status = 'failed';
                    $subscription->last_payment_failed_at = now();
                    $subscription->failure_reason = 'Razorpay subscription halted.';
                    optional($subscription->user)->update(['is_subscribe' => 0]);
                }

                $subscription->transaction_detail = array_merge($subscription->transaction_detail ?: [], [
                    'last_razorpay_webhook' => $payload,
                ]);
                $subscription->save();
            }

            if ($event === 'subscription.authenticated') {
                $subscription->status = !empty($subscription->trial_ends_at) && Carbon::parse($subscription->trial_ends_at)->isFuture()
                    ? config('constant.SUBSCRIPTION_STATUS.TRIALING')
                    : config('constant.SUBSCRIPTION_STATUS.PENDING');
                $subscription->payment_status = 'pending';
                $subscription->autopay_status = config('constant.AUTOPAY_STATUS.AUTHENTICATED');
                $subscription->mandate_authorized_at = $subscription->mandate_authorized_at ?: now();
                $subscription->save();
            }

            if ($event === 'subscription.charged' && $paymentEntity && in_array($paymentEntity['status'] ?? null, ['captured', 'authorized'])) {
                $paymentId = $paymentEntity['id'] ?? null;
                $paymentExists = $paymentId
                    ? Payment::where('gateway', 'razorpay_autopay')->where('transaction_id', $paymentId)->exists()
                    : false;

                if (!$paymentExists) {
                    $payment = Payment::create([
                        'user_id' => $subscription->user_id,
                        'subscription_id' => $subscription->id,
                        'package_id' => $subscription->package_id,
                        'razorpay_payment_id' => $paymentId,
                        'gateway' => 'razorpay_autopay',
                        'transaction_id' => $paymentId,
                        'gateway_subscription_id' => $gatewaySubscriptionId,
                        'gateway_response' => $payload,
                        'amount' => isset($paymentEntity['amount']) ? ((float) $paymentEntity['amount'] / 100) : $subscription->total_amount,
                        'status' => 'success',
                        'currency' => $paymentEntity['currency'] ?? 'INR',
                    ]);

                    $this->activateAutopaySubscriptionAfterCharge($subscription, $subscriptionEntity);
                    $this->generateAndSendInvoice($payment, $subscription->fresh(['user', 'package']), $subscription->user);
                }
            }

            if ($event === 'subscription.charged') {
                $subscription->payment_status = 'paid';
                $subscription->status = config('constant.SUBSCRIPTION_STATUS.ACTIVE');
                $subscription->autopay_status = config('constant.AUTOPAY_STATUS.ACTIVE');
                $subscription->last_payment_failed_at = null;
                $subscription->failure_reason = null;
                $subscription->save();

                optional($subscription->user)->update(['is_subscribe' => 1]);
            }

            if ($paymentEntity && in_array($paymentEntity['status'] ?? null, ['failed'], true)) {
                $subscription->payment_status = 'failed';
                $subscription->status = config('constant.SUBSCRIPTION_STATUS.PAST_DUE');
                $subscription->autopay_status = config('constant.AUTOPAY_STATUS.FAILED');
                $subscription->last_payment_failed_at = now();
                $subscription->failure_reason = $paymentEntity['error_description'] ?? $paymentEntity['error_reason'] ?? 'Razorpay payment failed.';
                $subscription->save();
                optional($subscription->user)->update(['is_subscribe' => 0]);
            }
        });

        return response()->json(['status' => true, 'message' => 'Webhook processed.']);
    }

    public function completeIosPayment(Request $request)
    {
        $request->validate([
            'subscription_id' => 'required|exists:subscriptions,id',
            'product_id' => 'required|string',
            'transaction_id' => 'required|string',
            'receipt_data' => 'nullable|required_without:signed_transaction|string',
            'signed_transaction' => 'nullable|string',
            'original_transaction_id' => 'nullable|string',
        ]);

        try {
            $user = auth()->user();
            $subscription = Subscription::with('package')
                ->where('id', $request->subscription_id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            $package = $subscription->package;
            if (!$package || $package->platform !== 'ios') {
                return response()->json([
                    'status' => false,
                    'message' => 'Selected package is not an iOS package.',
                ], 422);
            }

            if (!empty($package->product_id) && $package->product_id !== $request->product_id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Product ID does not match selected package.',
                ], 422);
            }

            $alreadyUsed = Payment::where('gateway', 'ios_iap')
                ->where('transaction_id', $request->transaction_id)
                ->where('subscription_id', '!=', $subscription->id)
                ->exists();

            if ($alreadyUsed) {
                return response()->json([
                    'status' => false,
                    'message' => 'This Apple transaction has already been used.',
                ], 422);
            }

            $purchase = null;
            $verification = [];
            $jwsVerificationError = null;
            if ($request->filled('signed_transaction')) {
                $purchase = $this->verifyAndDecodeAppleJws($request->signed_transaction, $jwsVerificationError);
                $verification = [
                    'status' => $purchase ? 0 : null,
                    'environment' => $purchase['environment'] ?? null,
                    'source' => 'storekit_signed_transaction',
                    'error' => $jwsVerificationError,
                ];

                // Some StoreKit integrations send both values. If JWS validation cannot
                // complete, use Apple's receipt endpoint instead of rejecting a valid sale.
                if (!$purchase && $request->filled('receipt_data')) {
                    $verification = $this->verifyAppleReceipt($request->receipt_data);
                    if (($verification['status'] ?? null) === 0) {
                        $purchase = $this->findApplePurchase($verification, $request->product_id, $request->transaction_id, $request->original_transaction_id);
                    }
                }
            } else {
                $verification = $this->verifyAppleReceipt($request->receipt_data);
                if (($verification['status'] ?? null) === 0) {
                    $purchase = $this->findApplePurchase($verification, $request->product_id, $request->transaction_id, $request->original_transaction_id);
                }
            }

            if (($verification['status'] ?? null) !== 0) {
                Log::warning('Apple IAP purchase verification failed.', [
                    'user_id' => $user->id,
                    'subscription_id' => $subscription->id,
                    'product_id' => $request->product_id,
                    'transaction_id' => $request->transaction_id,
                    'source' => $verification['source'] ?? ($request->filled('receipt_data') ? 'receipt_data' : 'unknown'),
                    'apple_status' => $verification['status'] ?? null,
                    'environment' => $verification['environment'] ?? null,
                    'verification_error' => $verification['error'] ?? $verification['message'] ?? $jwsVerificationError,
                ]);

                return response()->json([
                    'status' => false,
                    'message' => 'Apple purchase verification failed.',
                    'apple_status' => $verification['status'] ?? null,
                    'environment' => $verification['environment'] ?? null,
                ], 422);
            }

            $configuredBundleId = config('services.apple_iap.bundle_id');
            if (!empty($configuredBundleId)
                && !empty($purchase['bundleId'])
                && $purchase['bundleId'] !== $configuredBundleId) {
                return response()->json([
                    'status' => false,
                    'message' => 'Apple transaction bundle does not match this app.',
                ], 422);
            }

            if ($purchase && ((string) ($purchase['product_id'] ?? $purchase['productId'] ?? '') !== (string) $request->product_id
                || (string) ($purchase['transaction_id'] ?? $purchase['transactionId'] ?? '') !== (string) $request->transaction_id)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Apple transaction does not match the selected product.',
                ], 422);
            }

            if (!$purchase) {
                return response()->json([
                    'status' => false,
                    'message' => 'No matching Apple purchase found for this product and transaction.',
                ], 422);
            }

            if ($this->isApplePurchaseExpired($purchase)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Apple purchase is expired.',
                ], 422);
            }

            $result = DB::transaction(function () use ($request, $subscription, $user, $verification, $purchase) {
                $isTrialPurchase = $this->isAppleTrialPurchase($purchase, $subscription);
                [$payment, $paymentCreated] = $this->createApplePayment($subscription, $purchase, $verification, $isTrialPurchase);

                $this->updateSubscriptionFromApplePurchase($subscription, $purchase, [
                    'product_id' => $request->product_id,
                    'transaction_id' => $request->transaction_id,
                    'original_transaction_id' => $request->original_transaction_id,
                    'environment' => $verification['environment'] ?? null,
                    'receipt_verification' => $verification,
                ], $isTrialPurchase);

                // A free trial does not consume referral credit or generate paid benefits.
                $offerCoupon = (!$isTrialPurchase && $paymentCreated)
                    ? $this->finalizePaidSubscription($subscription, $user)
                    : null;

                return compact('payment', 'paymentCreated', 'isTrialPurchase', 'offerCoupon');
            });
            $invoiceUrl = $result['paymentCreated'] && !$result['isTrialPurchase']
                ? $this->generateAndSendInvoice($result['payment'], $subscription->fresh(['package']), $user)
                : null;
            $subscriptionDetail = $this->subscriptionPlanDetail($user->id);

            return response()->json([
                'status' => true,
                'message' => 'iOS purchase verified successfully.',
                'data' => $result['payment'],
                'subscription' => $subscription->fresh(),
                'referral_credit_balance' => (float) $user->fresh()->referral_credit_balance,
                'referral_credit_used' => (float) $subscription->referral_credit_used,
                'offer_coupon' => $this->formatOfferCoupons($result['offerCoupon']),
                'invoice_url' => $invoiceUrl,
                'access_type' => $result['isTrialPurchase'] ? 'trial' : 'paid',
                'subscription_detail' => $subscriptionDetail,
                'has_subscription_access' => $subscriptionDetail['has_access'],
            ]);
        } catch (\Exception $e) {
            Log::error('iOS payment completion failed.', [
                'user_id' => auth()->id(),
                'subscription_id' => $request->input('subscription_id'),
                'product_id' => $request->input('product_id'),
                'transaction_id' => $request->input('transaction_id'),
                'exception' => $e->getMessage(),
            ]);

            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function iosSubscriptionStatus(Request $request)
    {
        $request->validate([
            'subscription_id' => 'required|exists:subscriptions,id',
            'receipt_data' => 'nullable|required_without:signed_transaction|string',
            'signed_transaction' => 'nullable|string',
            'product_id' => 'nullable|string',
        ]);

        try {
            $user = auth()->user();
            $subscription = Subscription::with('package')
                ->where('id', $request->subscription_id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            $productId = $request->product_id ?: optional($subscription->package)->product_id;
            if (empty($productId)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Product ID is required to check iOS subscription status.',
                ], 422);
            }

            $purchase = null;
            $verification = [];
            $jwsVerificationError = null;
            if ($request->filled('signed_transaction')) {
                $purchase = $this->verifyAndDecodeAppleJws($request->signed_transaction, $jwsVerificationError);
                $verification = [
                    'status' => $purchase ? 0 : null,
                    'environment' => $purchase['environment'] ?? null,
                    'source' => 'storekit_signed_transaction',
                    'error' => $jwsVerificationError,
                ];

                if (!$purchase && $request->filled('receipt_data')) {
                    $verification = $this->verifyAppleReceipt($request->receipt_data);
                    if (($verification['status'] ?? null) === 0) {
                        $purchase = $this->latestApplePurchaseForProduct($verification, $productId);
                    }
                }
            } else {
                $verification = $this->verifyAppleReceipt($request->receipt_data);
                if (($verification['status'] ?? null) === 0) {
                    $purchase = $this->latestApplePurchaseForProduct($verification, $productId);
                }
            }

            if (($verification['status'] ?? null) !== 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'Apple subscription verification failed.',
                    'apple_status' => $verification['status'] ?? null,
                    'environment' => $verification['environment'] ?? null,
                ], 422);
            }

            if ($purchase && (($purchase['product_id'] ?? $purchase['productId'] ?? null) !== $productId)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Apple transaction does not match the selected product.',
                ], 422);
            }

            if (!$purchase) {
                return response()->json([
                    'status' => false,
                    'message' => 'No Apple purchase found for this product.',
                ], 422);
            }

            $result = DB::transaction(function () use ($subscription, $purchase, $verification) {
                $isTrialPurchase = $this->isAppleTrialPurchase($purchase, $subscription);
                [$payment, $paymentCreated] = $this->createApplePayment($subscription, $purchase, $verification, $isTrialPurchase);
                $this->updateSubscriptionFromApplePurchase($subscription, $purchase, $verification, $isTrialPurchase);

                return compact('payment', 'paymentCreated', 'isTrialPurchase');
            });

            $invoiceUrl = $result['paymentCreated'] && !$result['isTrialPurchase']
                ? $this->generateAndSendInvoice($result['payment'], $subscription->fresh(['package']), $user)
                : null;

            return response()->json([
                'status' => true,
                'message' => 'iOS subscription status refreshed.',
                'is_active' => !$this->isApplePurchaseExpired($purchase),
                'subscription' => $subscription->fresh(),
                'apple_purchase' => $purchase,
                'invoice_url' => $invoiceUrl,
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function appleIapNotification(Request $request)
    {
        $payload = $request->all();
        $notification = $this->decodeAppleNotificationPayload($payload);
        $purchase = $notification['purchase'] ?? null;

        if (strtoupper((string) ($notification['notification_type'] ?? '')) === 'TEST') {
            return response()->json(['status' => true, 'message' => 'Apple test notification verified.']);
        }

        if (!$purchase) {
            return response()->json(['status' => false, 'message' => 'Invalid Apple server notification.'], 400);
        }

        $bundleId = config('services.apple_iap.bundle_id');
        if (!empty($bundleId) && ($purchase['bundleId'] ?? null) !== $bundleId) {
            return response()->json(['status' => false, 'message' => 'Apple notification bundle does not match.'], 400);
        }

        $gatewaySubscriptionId = $purchase['original_transaction_id']
            ?? $purchase['originalTransactionId']
            ?? null;

        $transactionId = $purchase['transaction_id']
            ?? $purchase['transactionId']
            ?? null;

        $subscription = Subscription::with('package')
            ->where(function ($query) use ($gatewaySubscriptionId, $transactionId) {
                if (!empty($gatewaySubscriptionId)) {
                    $query->orWhere('gateway_subscription_id', $gatewaySubscriptionId);
                }
                if (!empty($transactionId)) {
                    $query->orWhere('txn_id', $transactionId);
                }
            })
            ->first();

        if (!$subscription) {
            return response()->json(['status' => true, 'message' => 'Local subscription not found.']);
        }

        DB::transaction(function () use ($subscription, $purchase, $payload, $notification) {
            $notificationType = strtoupper((string) ($notification['notification_type'] ?? ''));
            $notificationSubtype = strtoupper((string) ($notification['subtype'] ?? ''));
            $isTrialPurchase = $this->isAppleTrialPurchase($purchase, $subscription);

            $this->updateSubscriptionFromApplePurchase($subscription, $purchase, [
                'environment' => $notification['environment'] ?? null,
                'notification' => $payload,
            ], $isTrialPurchase);

            if (in_array($notificationType, ['DID_FAIL_TO_RENEW', 'GRACE_PERIOD_EXPIRED'], true)) {
                $subscription->update([
                    'status' => config('constant.SUBSCRIPTION_STATUS.PAST_DUE'),
                    'payment_status' => 'failed',
                    'autopay_status' => config('constant.AUTOPAY_STATUS.FAILED'),
                    'last_payment_failed_at' => now(),
                    'failure_reason' => 'Apple could not renew this subscription.',
                ]);
                optional($subscription->user)->update(['is_subscribe' => 0]);
                return;
            }

            if (in_array($notificationType, ['EXPIRED', 'REVOKE'], true)) {
                $subscription->update([
                    'status' => config('constant.SUBSCRIPTION_STATUS.INACTIVE'),
                    'payment_status' => 'failed',
                    'autopay_status' => config('constant.AUTOPAY_STATUS.EXPIRED'),
                    'autopay_cancelled_at' => now(),
                ]);
                optional($subscription->user)->update(['is_subscribe' => 0]);
                return;
            }

            if ($notificationType === 'DID_CHANGE_RENEWAL_STATUS' && $notificationSubtype === 'AUTO_RENEW_DISABLED') {
                $subscription->update([
                    'autopay_status' => config('constant.AUTOPAY_STATUS.CANCELLED'),
                    'autopay_cancelled_at' => now(),
                ]);
                return;
            }

            [$payment, $paymentCreated] = $this->createApplePayment($subscription, $purchase, $payload, $isTrialPurchase);
            if ($paymentCreated && !$isTrialPurchase) {
                $this->generateAndSendInvoice($payment, $subscription->fresh(['user', 'package']), $subscription->user);
            }
        });

        return response()->json(['status' => true, 'message' => 'Notification processed.']);
    }

    private function verifyAppleReceipt(string $receiptData): array
    {
        $payload = [
            'receipt-data' => $receiptData,
            'exclude-old-transactions' => true,
        ];

        $sharedSecret = config('services.apple_iap.shared_secret');
        if (!empty($sharedSecret)) {
            $payload['password'] = $sharedSecret;
        }

        $productionUrl = config('services.apple_iap.production_url');
        $sandboxUrl = config('services.apple_iap.sandbox_url');

        $response = Http::timeout(20)->asJson()->post($productionUrl, $payload)->json() ?: [
            'status' => null,
            'message' => 'Empty response from Apple production receipt verification.',
        ];
        if (($response['status'] ?? null) === 21007) {
            $response = Http::timeout(20)->asJson()->post($sandboxUrl, $payload)->json() ?: [
                'status' => null,
                'message' => 'Empty response from Apple sandbox receipt verification.',
            ];
            $response['environment'] = $response['environment'] ?? 'Sandbox';
        } else {
            $response['environment'] = $response['environment'] ?? 'Production';
        }

        return $response;
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

    private function defaultRazorpayTotalCount($package): int
    {
        if (($package->duration_unit ?? null) === 'yearly') {
            return 10;
        }

        return 120;
    }

    private function verifyRazorpaySubscriptionSignature(string $paymentId, string $subscriptionId, string $signature, string $secret): bool
    {
        $expected = hash_hmac('sha256', $paymentId . '|' . $subscriptionId, $secret);
        return hash_equals($expected, $signature);
    }

    private function findApplePurchase(array $verification, string $productId, string $transactionId, ?string $originalTransactionId): ?array
    {
        $items = $verification['latest_receipt_info'] ?? $verification['receipt']['in_app'] ?? [];
        if (!is_array($items)) {
            return null;
        }

        usort($items, function ($a, $b) {
            return (int) ($b['expires_date_ms'] ?? $b['purchase_date_ms'] ?? 0) <=> (int) ($a['expires_date_ms'] ?? $a['purchase_date_ms'] ?? 0);
        });

        foreach ($items as $item) {
            if (($item['product_id'] ?? null) !== $productId) {
                continue;
            }

            $matchesTransaction = ($item['transaction_id'] ?? null) === $transactionId;
            $matchesOriginal = !empty($originalTransactionId) && ($item['original_transaction_id'] ?? null) === $originalTransactionId;

            if ($matchesTransaction || $matchesOriginal) {
                return $item;
            }
        }

        return null;
    }

    private function latestApplePurchaseForProduct(array $verification, string $productId): ?array
    {
        $items = $verification['latest_receipt_info'] ?? $verification['receipt']['in_app'] ?? [];
        if (!is_array($items)) {
            return null;
        }

        $matches = array_values(array_filter($items, fn ($item) => ($item['product_id'] ?? null) === $productId));
        if (empty($matches)) {
            return null;
        }

        usort($matches, function ($a, $b) {
            return (int) ($b['expires_date_ms'] ?? $b['purchase_date_ms'] ?? 0) <=> (int) ($a['expires_date_ms'] ?? $a['purchase_date_ms'] ?? 0);
        });

        return $matches[0];
    }

    private function isApplePurchaseExpired(array $purchase): bool
    {
        $expiresMs = $purchase['expires_date_ms'] ?? $purchase['expiresDate'] ?? null;
        if (empty($expiresMs)) {
            return false;
        }

        return Carbon::createFromTimestamp(((int) $expiresMs) / 1000)->isPast();
    }

    private function isAppleTrialPurchase(array $purchase, ?Subscription $subscription = null): bool
    {
        $trialFlag = $purchase['is_trial_period'] ?? $purchase['isTrialPeriod'] ?? false;
        if (filter_var($trialFlag, FILTER_VALIDATE_BOOLEAN)) {
            return true;
        }

        $transactionId = $purchase['transaction_id'] ?? $purchase['transactionId'] ?? null;
        return $subscription
            && $subscription->status === config('constant.SUBSCRIPTION_STATUS.TRIALING')
            && $subscription->trial_ends_at
            && Carbon::parse($subscription->trial_ends_at)->isFuture()
            && !empty($transactionId)
            && $transactionId === $subscription->txn_id;
    }

    private function createApplePayment(Subscription $subscription, array $purchase, array $gatewayResponse, bool $isTrialPurchase): array
    {
        $transactionId = $purchase['transaction_id'] ?? $purchase['transactionId'] ?? null;
        if (empty($transactionId)) {
            throw new \RuntimeException('Apple transaction ID is missing.');
        }

        $existingPayment = Payment::where('gateway', 'ios_iap')
            ->where('transaction_id', $transactionId)
            ->first();
        if ($existingPayment) {
            return [$existingPayment, false];
        }

        $originalTransactionId = $purchase['original_transaction_id']
            ?? $purchase['originalTransactionId']
            ?? $subscription->gateway_subscription_id;

        $payment = Payment::create([
            'user_id' => $subscription->user_id,
            'subscription_id' => $subscription->id,
            'package_id' => $subscription->package_id,
            'razorpay_payment_id' => $transactionId,
            'gateway' => 'ios_iap',
            'transaction_id' => $transactionId,
            'gateway_subscription_id' => $originalTransactionId,
            'gateway_response' => $gatewayResponse,
            'amount' => $isTrialPurchase ? 0 : $subscription->total_amount,
            'status' => $isTrialPurchase ? 'authorized' : 'success',
            'method' => $isTrialPurchase ? 'apple_free_trial' : 'apple_iap',
            'currency' => 'INR',
        ]);

        return [$payment, true];
    }

    private function updateSubscriptionFromApplePurchase(Subscription $subscription, array $purchase, array $context = [], ?bool $isTrialPurchase = null): void
    {
        $transactionId = $purchase['transaction_id'] ?? $purchase['transactionId'] ?? null;
        $originalTransactionId = $purchase['original_transaction_id'] ?? $purchase['originalTransactionId'] ?? $transactionId;
        $expiresMs = $purchase['expires_date_ms'] ?? $purchase['expiresDate'] ?? null;
        $purchaseMs = $purchase['purchase_date_ms'] ?? $purchase['purchaseDate'] ?? null;
        $isExpired = $this->isApplePurchaseExpired($purchase);
        $isTrialPurchase = $isTrialPurchase ?? $this->isAppleTrialPurchase($purchase, $subscription);

        if (!empty($purchaseMs)) {
            $subscription->subscription_start_date = Carbon::createFromTimestamp(((int) $purchaseMs) / 1000)->format('Y-m-d H:i:s');
        }

        if (!empty($expiresMs)) {
            $subscription->subscription_end_date = Carbon::createFromTimestamp(((int) $expiresMs) / 1000)->format('Y-m-d H:i:s');
        }

        $subscription->payment_type = 'ios_iap';
        $subscription->txn_id = $transactionId ?: $subscription->txn_id;
        $subscription->gateway_subscription_id = $originalTransactionId ?: $subscription->gateway_subscription_id;
        $subscription->payment_status = $isExpired || $isTrialPurchase ? ($isExpired ? 'failed' : 'pending') : 'paid';
        $subscription->status = $isExpired
            ? config('constant.SUBSCRIPTION_STATUS.INACTIVE')
            : ($isTrialPurchase ? config('constant.SUBSCRIPTION_STATUS.TRIALING') : config('constant.SUBSCRIPTION_STATUS.ACTIVE'));
        $subscription->autopay_status = $isExpired
            ? config('constant.AUTOPAY_STATUS.EXPIRED')
            : config('constant.AUTOPAY_STATUS.ACTIVE');

        if ($isTrialPurchase) {
            $subscription->trial_start_at = !empty($purchaseMs)
                ? Carbon::createFromTimestamp(((int) $purchaseMs) / 1000)->format('Y-m-d H:i:s')
                : ($subscription->trial_start_at ?: now());
            $subscription->trial_ends_at = !empty($expiresMs)
                ? Carbon::createFromTimestamp(((int) $expiresMs) / 1000)->format('Y-m-d H:i:s')
                : $subscription->trial_ends_at;
            $subscription->billing_starts_at = $subscription->trial_ends_at;
            $subscription->mandate_authorized_at = $subscription->mandate_authorized_at ?: now();
        }

        if (!$isExpired) {
            $subscription->last_payment_failed_at = null;
            $subscription->failure_reason = null;
        }
        $subscription->transaction_detail = array_merge($subscription->transaction_detail ?: [], [
            'apple_purchase' => $purchase,
            'apple_context' => $context,
        ]);
        $subscription->save();

        optional($subscription->user)->update(['is_subscribe' => ($isExpired || $isTrialPurchase) ? 0 : 1]);
    }

    private function decodeAppleNotificationPayload(array $payload): array
    {
        if (empty($payload['signedPayload'])) {
            return [];
        }

        $decoded = $this->verifyAndDecodeAppleJws($payload['signedPayload']);
        if (!$decoded) {
            return [];
        }

        $data = $decoded['data'] ?? [];
        $signedTransactionInfo = $data['signedTransactionInfo'] ?? null;
        $purchase = $signedTransactionInfo ? $this->verifyAndDecodeAppleJws($signedTransactionInfo) : null;

        return [
            'environment' => $data['environment'] ?? null,
            'notification_type' => $decoded['notificationType'] ?? null,
            'subtype' => $decoded['subtype'] ?? null,
            'purchase' => is_array($purchase) ? $purchase : null,
        ];
    }

    private function verifyAndDecodeAppleJws(string $jws, ?string &$verificationError = null): ?array
    {
        $parts = explode('.', $jws);
        if (count($parts) !== 3) {
            $verificationError = 'The signed transaction is not a three-part JWS.';
            return null;
        }

        $header = json_decode($this->decodeBase64Url($parts[0]), true);
        if (!is_array($header) || ($header['alg'] ?? null) !== 'ES256' || empty($header['x5c']) || !is_array($header['x5c'])) {
            $verificationError = 'The signed transaction has an invalid ES256/x5c header.';
            return null;
        }

        $leafCertificate = $this->verifiedAppleLeafCertificate($header['x5c'], $verificationError);
        if (!$leafCertificate) {
            return null;
        }

        try {
            $decoded = \Firebase\JWT\JWT::decode($jws, new \Firebase\JWT\Key($leafCertificate, 'ES256'));
            return json_decode(json_encode($decoded), true);
        } catch (\Throwable $e) {
            $verificationError = 'JWS signature validation failed: ' . $e->getMessage();
            return null;
        }
    }

    private function verifiedAppleLeafCertificate(array $certificateChain, ?string &$verificationError = null): ?string
    {
        if (count($certificateChain) < 2) {
            $verificationError = 'Apple certificate chain is incomplete.';
            return null;
        }

        $rootCertificatePath = config('services.apple_iap.root_certificate_path');
        $bundledRootCertificatePath = resource_path('certificates/AppleRootCA-G3.pem');
        if ((empty($rootCertificatePath) || !is_readable($rootCertificatePath))
            && is_readable($bundledRootCertificatePath)) {
            $rootCertificatePath = $bundledRootCertificatePath;
        }

        if (empty($rootCertificatePath) || !is_readable($rootCertificatePath)) {
            $verificationError = 'Configured Apple root certificate is missing or unreadable.';
            return null;
        }

        $certificates = array_map(fn ($certificate) => $this->appleCertificatePem($certificate), $certificateChain);
        $leafCertificate = $certificates[0];
        $rootCertificate = file_get_contents($rootCertificatePath);

        $rootPublicKey = openssl_pkey_get_public($rootCertificate);
        if (!$rootPublicKey) {
            $verificationError = 'Configured Apple root certificate is invalid.';
            return null;
        }

        foreach ($certificates as $index => $certificate) {
            $certificateInfo = openssl_x509_parse($certificate);
            $now = time();
            if (!$certificateInfo
                || ($certificateInfo['validFrom_time_t'] ?? PHP_INT_MAX) > $now
                || ($certificateInfo['validTo_time_t'] ?? 0) < $now) {
                $verificationError = 'Apple certificate chain contains an invalid or expired certificate.';
                return null;
            }

            if (isset($certificates[$index + 1])) {
                $issuerPublicKey = openssl_pkey_get_public($certificates[$index + 1]);
                if (!$issuerPublicKey || openssl_x509_verify($certificate, $issuerPublicKey) !== 1) {
                    $verificationError = 'Apple certificate chain signature validation failed.';
                    return null;
                }
            }
        }

        $lastCertificate = $certificates[count($certificates) - 1];
        if (openssl_x509_verify($lastCertificate, $rootPublicKey) !== 1) {
            $verificationError = 'Apple certificate chain is not rooted in the configured Apple root CA.';
            return null;
        }

        return $leafCertificate;
    }

    private function appleCertificatePem(string $certificate): string
    {
        return "-----BEGIN CERTIFICATE-----\n"
            . chunk_split($certificate, 64, "\n")
            . "-----END CERTIFICATE-----\n";
    }

    private function decodeBase64Url(string $value): string
    {
        $value = strtr($value, '-_', '+/');
        $padding = strlen($value) % 4;
        if ($padding > 0) {
            $value .= str_repeat('=', 4 - $padding);
        }

        return (string) base64_decode($value, true);
    }

    private function activateAutopaySubscriptionAfterCharge(Subscription $subscription, ?array $subscriptionEntity = null): void
    {
        $package = $subscription->package;
        $start = !empty($subscriptionEntity['current_start'] ?? null)
            ? Carbon::createFromTimestamp($subscriptionEntity['current_start'])
            : Carbon::now();

        if (!empty($subscriptionEntity['current_end'] ?? null)) {
            $end = Carbon::createFromTimestamp($subscriptionEntity['current_end']);
        } elseif ($package && $package->duration_unit === 'yearly') {
            $end = $start->copy()->addYears((int) $package->duration);
        } elseif ($package) {
            $end = $start->copy()->addMonths((int) $package->duration);
        } else {
            $end = $subscription->subscription_end_date
                ? Carbon::parse($subscription->subscription_end_date)
                : $start->copy();
        }

        $subscription->subscription_start_date = $start->format('Y-m-d H:i:s');
        $subscription->subscription_end_date = $end->format('Y-m-d H:i:s');
        $subscription->billing_starts_at = $subscription->billing_starts_at ?: $start->format('Y-m-d H:i:s');
        $subscription->payment_status = 'paid';
        $subscription->status = config('constant.SUBSCRIPTION_STATUS.ACTIVE');
        $subscription->autopay_status = config('constant.AUTOPAY_STATUS.ACTIVE');
        $subscription->last_payment_failed_at = null;
        $subscription->failure_reason = null;
        $subscription->save();

        optional($subscription->user)->update(['is_subscribe' => 1]);
    }

    private function generateAndSendInvoice(Payment $payment, Subscription $subscription, ?User $user): ?string
    {
        if (!$user || empty($user->email)) {
            return null;
        }

        try {
            $subscription->loadMissing('package');
            $package = $subscription->package;
            $transactionId = $payment->transaction_id ?: $payment->razorpay_payment_id ?: ('payment_' . $payment->id);
            $fileName = 'invoices/inv_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $transactionId) . '.pdf';

            $pdf = $this->loadInvoicePdf([
                'payment' => $payment,
                'subscription' => $subscription,
                'user' => $user,
                'package' => $package,
                'date' => now()->format('d-m-Y'),
            ]);

            if (!$pdf) {
                return null;
            }

            Storage::disk('public')->put($fileName, $pdf->output());
            $payment->update(['invoice_path' => $fileName]);

            Mail::raw('Thank you for your payment. Your subscription invoice is attached with this email.', function ($message) use ($user, $fileName) {
                $message->to($user->email)
                    ->subject('Your Subscription Invoice')
                    ->attachData(Storage::disk('public')->get($fileName), basename($fileName), [
                        'mime' => 'application/pdf',
                    ]);
            });

            return asset('storage/' . $fileName);
        } catch (\Exception $e) {
            return null;
        }
    }

    private function loadInvoicePdf(array $data)
    {
        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return \Barryvdh\DomPDF\Facade\Pdf::loadView('invoice.pdf', $data);
        }

        if (class_exists(\Barryvdh\DomPDF\Facade::class)) {
            return \Barryvdh\DomPDF\Facade::loadView('invoice.pdf', $data);
        }

        if (class_exists(\PDF::class)) {
            return \PDF::loadView('invoice.pdf', $data);
        }

        return null;
    }

    private function finalizePaidSubscription(Subscription $subscription, User $user): ?array
    {
        $subscription->update([
            'payment_status' => 'paid',
            'status' => config('constant.SUBSCRIPTION_STATUS.ACTIVE'),
        ]);

        $user->update(['is_subscribe' => 1]);

        if (!empty($subscription->referral_credit_used)) {
            $newBalance = max(0, (float) $user->referral_credit_balance - (float) $subscription->referral_credit_used);
            $user->update(['referral_credit_balance' => $newBalance]);
        }

        $this->rewardReferralIfNeeded($subscription, $user);
        $this->ensureReferralCode($user);

        return $this->generateOfferCouponsIfNeeded($subscription, $user);
    }

    private function rewardReferralIfNeeded(Subscription $subscription, User $user): void
    {
        if (empty($subscription->referral_code_id) || empty($subscription->referral_referrer_id)) {
            return;
        }

        $alreadyRedeemed = ReferralRedemption::where('subscription_id', $subscription->id)->exists();
        if ($alreadyRedeemed) {
            return;
        }

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

    private function ensureReferralCode(User $user): void
    {
        $existingReferral = ReferralCode::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if ($existingReferral) {
            return;
        }

        $code = 'REF-' . strtoupper(substr(md5($user->id . now()->timestamp), 0, 8));
        ReferralCode::create([
            'user_id' => $user->id,
            'code' => $code,
            'status' => 'active',
        ]);
    }

    private function generateOfferCouponsIfNeeded(Subscription $subscription, User $user): ?array
    {
        $package = $subscription->package;
        if (!$package || !$package->offer_enabled) {
            return null;
        }

        $offerType = $package->offer_type ?: 'free_access';
        $freeAccessDays = $package->offer_access_days;
        $sameCount = (int) ($package->offer_same_access_count ?? 0);
        $freeCount = (int) ($package->offer_free_access_count ?? 0);

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

        if (!empty($user->email) && !empty($generated)) {
            try {
                $lines = ['Your offer coupons have been generated:'];
                foreach ($generated as $coupon) {
                    $lines[] = 'Code: ' . $coupon->code . ' | Type: ' . $coupon->type . ' | Days: ' . ($coupon->access_days ?? 'N/A');
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

        return $generated;
    }

    private function formatOfferCoupons($offerCoupon)
    {
        return is_array($offerCoupon)
            ? collect($offerCoupon)->map(fn ($coupon) => [
                'code' => $coupon->code,
                'type' => $coupon->type,
                'access_days' => $coupon->access_days,
                'max_redemptions' => $coupon->max_redemptions,
            ])->values()
            : null;
    }
    
}
