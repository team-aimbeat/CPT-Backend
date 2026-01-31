<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\CouponRedemption;
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

        $alreadyRedeemed = CouponRedemption::where('coupon_id', $coupon->id)
            ->where('user_id', $user->id)
            ->exists();

        if (!$alreadyRedeemed) {
            CouponRedemption::create([
                'coupon_id' => $coupon->id,
                'user_id' => $user->id,
                'redeemed_at' => now(),
            ]);
        }

        $user->update(['has_coupon_access' => 1]);

        return response()->json([
            'status' => true,
            'message' => 'Coupon applied successfully.',
            'data' => [
                'coupon_code' => $coupon->code,
                'coupon_status' => $coupon->status,
                'has_coupon_access' => true,
            ],
        ]);
    }
}
