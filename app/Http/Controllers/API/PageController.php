<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function termsCondition(Request $request)
    {
        return $this->show($request, 'terms_condition');
    }

    public function privacyPolicy(Request $request)
    {
        return $this->show($request, 'privacy_policy');
    }

    public function show(Request $request, string $type)
    {
        $type = str_replace('-', '_', $type);

        if (!in_array($type, ['terms_condition', 'privacy_policy'], true)) {
            return json_custom_response([
                'message' => __('message.not_found_entry', ['name' => 'Page']),
            ], 404);
        }

        $page = Setting::where('type', $type)->where('key', $type)->first();

        $response = [
            'data' => [
                'type' => $type,
                'title' => $type === 'terms_condition' ? __('message.terms_condition') : __('message.privacy_policy'),
                'content' => optional($page)->value,
                'updated_at' => optional($page)->updated_at,
            ],
        ];

        return json_custom_response($response);
    }
}
