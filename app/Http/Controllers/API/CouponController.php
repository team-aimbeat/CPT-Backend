<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\CouponRedemption;
use App\Models\Subscription;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function apply(Request $request)
    {
        $user = auth('sanctum')->user();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized access',
            ], 401);
        }

        $data = $request->validate([
            'code' => 'required|string',
        ]);

        $coupon = Coupon::where('code', $data['code'])
            ->where('status', 'active')
            ->first();

        if (!$coupon) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid or inactive coupon.',
            ], 422);
        }

        $today = now()->toDateString();

        if ($coupon->valid_from && $today < $coupon->valid_from) {
            return response()->json([
                'status' => false,
                'message' => 'Coupon is not active yet.',
            ], 422);
        }

        if ($coupon->valid_to && $today > $coupon->valid_to) {
            return response()->json([
                'status' => false,
                'message' => 'Coupon has expired.',
            ], 422);
        }

        if (!empty($coupon->max_redemptions)) {
            $totalRedemptions = CouponRedemption::where('coupon_id', $coupon->id)->count();
            if ($totalRedemptions >= $coupon->max_redemptions) {
                return response()->json([
                    'status' => false,
                    'message' => 'Coupon redemption limit reached.',
                ], 422);
            }
        }

        $userRedemptionCount = CouponRedemption::where('coupon_id', $coupon->id)
            ->where('user_id', $user->id)
            ->count();

        if (!empty($coupon->per_user_limit) && $userRedemptionCount >= $coupon->per_user_limit) {
            return response()->json([
                'status' => false,
                'message' => 'You have already used this coupon.',
            ], 422);
        }

        if ($coupon->first_purchase_only) {
            $hasPaidSubscription = Subscription::where('user_id', $user->id)
                ->where('payment_status', 'paid')
                ->exists();

            if ($hasPaidSubscription) {
                return response()->json([
                    'status' => false,
                    'message' => 'Coupon valid only for first purchase.',
                ], 422);
            }
        }

        if ($userRedemptionCount === 0) {
            CouponRedemption::create([
                'coupon_id' => $coupon->id,
                'user_id' => $user->id,
                'redeemed_at' => now(),
            ]);
        }

        $accessEndsAt = null;
        $hasCouponAccess = false;

        if (in_array($coupon->type, ['free_access', 'free_months'], true)) {
            $hasCouponAccess = true;
            if (!empty($coupon->access_days)) {
                $accessEndsAt = now()->addDays((int) $coupon->access_days);
            }

            $currentEndsAt = $user->coupon_access_ends_at;
            if ($accessEndsAt && $currentEndsAt && $currentEndsAt->gt($accessEndsAt)) {
                $accessEndsAt = $currentEndsAt;
            }

            $user->update([
                'has_coupon_access' => 1,
                'coupon_access_ends_at' => $accessEndsAt,
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Coupon applied successfully.',
            'data' => [
                'coupon_code' => $coupon->code,
                'coupon_status' => $coupon->status,
                'coupon_type' => $coupon->type,
                'coupon_value' => $coupon->value,
                'access_days' => $coupon->access_days,
                'access_ends_at' => $accessEndsAt ? $accessEndsAt->toDateTimeString() : null,
                'has_coupon_access' => $hasCouponAccess,
            ],
        ]);
    }
}
