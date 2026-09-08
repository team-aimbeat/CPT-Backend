<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\CompanyAccessService;
use App\Traits\SubscriptionTrait;
use Illuminate\Http\Request;

class CompanyAccessController extends Controller
{
    use SubscriptionTrait;

    public function status(Request $request, CompanyAccessService $companyAccessService)
    {
        $user = auth('sanctum')->user();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized access',
            ], 401);
        }

        $access = $companyAccessService->activeAccessForUser($user->id);
        $eligibility = $companyAccessService->findEligibleCompanyForEmail($user->email);
        $requiresEmailVerification = $companyAccessService->requiresEmailVerification($user, $eligibility);

        return response()->json([
            'status' => true,
            'is_eligible' => (bool) $eligibility,
            'can_claim_company_access' => (bool) $eligibility && !$access && !$requiresEmailVerification,
            'requires_email_verification' => $requiresEmailVerification,
            'has_company_access' => (bool) $access,
            'company_access' => $this->formatCompanyAccess($access),
            'eligible_company' => $eligibility ? $this->formatCompany($eligibility['company']) : null,
            'subscription_detail' => $this->subscriptionPlanDetail($user->id),
        ]);
    }

    public function claim(Request $request, CompanyAccessService $companyAccessService)
    {
        $user = auth('sanctum')->user();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized access',
            ], 401);
        }

        $result = $companyAccessService->claimForUser($user);
        $statusCode = $result['status'] ? 200 : 422;

        return response()->json([
            'status' => $result['status'],
            'message' => $result['message'],
            'company_access' => $this->formatCompanyAccess($result['access']),
            'company' => $result['company'] ? $this->formatCompany($result['company']) : null,
            'subscription_detail' => $this->subscriptionPlanDetail($user->id),
        ], $statusCode);
    }

    private function formatCompanyAccess($access)
    {
        if (!$access) {
            return null;
        }

        return [
            'id' => $access->id,
            'company_id' => $access->company_id,
            'company_name' => $access->company ? $access->company->name : null,
            'email' => $access->email,
            'access_starts_at' => $access->access_starts_at ? $access->access_starts_at->toDateTimeString() : null,
            'access_ends_at' => $access->access_ends_at ? $access->access_ends_at->toDateTimeString() : null,
            'status' => $access->status,
            'source' => $access->source,
        ];
    }

    private function formatCompany($company)
    {
        return [
            'id' => $company->id,
            'name' => $company->name,
            'code' => $company->code,
            'free_access_days' => $company->free_access_days,
        ];
    }
}
