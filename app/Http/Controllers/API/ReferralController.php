<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ReferralCode;
use App\Models\ReferralRedemption;
use Illuminate\Http\Request;

class ReferralController extends Controller
{
    public function getInfo(Request $request)
    {
        $user = auth('sanctum')->user();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized access',
            ], 401);
        }

        $code = ReferralCode::where('user_id', $user->id)
            ->where('status', 'active')
            ->orderByDesc('id')
            ->first();

        $redemptions = ReferralRedemption::where('referrer_id', $user->id)
            ->orderByDesc('id')
            ->get()
            ->map(function ($row) {
                return [
                    'id' => $row->id,
                    'referred_user_id' => $row->referred_user_id,
                    'subscription_id' => $row->subscription_id,
                    'reward_amount' => $row->reward_amount,
                    'redeemed_at' => $row->redeemed_at ? $row->redeemed_at->toDateTimeString() : null,
                ];
            });

        return response()->json([
            'status' => true,
            'data' => [
                'referral_code' => $code?->code,
                'referral_status' => $code?->status,
                'referral_credit_balance' => (float) $user->referral_credit_balance,
                'total_referrals' => $redemptions->count(),
                'redemptions' => $redemptions,
            ],
        ]);
    }
}
