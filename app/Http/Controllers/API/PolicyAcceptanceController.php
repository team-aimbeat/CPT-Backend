<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\UserPolicyAcceptance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PolicyAcceptanceController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'policy_type' => 'required|in:terms_condition,privacy_policy',
            'accepted' => 'required|accepted',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => true,
                'message' => $validator->errors()->first(),
                'all_message' => $validator->errors(),
            ], 422);
        }

        $acceptance = self::recordAcceptance($request->user(), $request, $request->policy_type);

        return json_custom_response([
            'message' => 'Policy accepted successfully',
            'data' => $acceptance,
        ]);
    }

    public function status(Request $request)
    {
        $accepted = UserPolicyAcceptance::where('user_id', $request->user()->id)
            ->latest('accepted_at')
            ->get()
            ->unique('policy_type')
            ->values();

        return json_custom_response([
            'data' => $accepted,
        ]);
    }

    public static function recordAcceptance($user, Request $request, string $policyType)
    {
        $setting = Setting::where('type', $policyType)->where('key', $policyType)->first();
        $content = optional($setting)->value;
        $hash = hash('sha256', (string) $content);

        return UserPolicyAcceptance::firstOrCreate(
            [
                'user_id' => $user->id,
                'policy_type' => $policyType,
                'policy_content_hash' => $hash,
            ],
            [
                'setting_id' => optional($setting)->id,
                'policy_title' => $policyType === 'terms_condition' ? __('message.terms_condition') : __('message.privacy_policy'),
                'policy_content' => $content,
                'accepted_at' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]
        );
    }
}
