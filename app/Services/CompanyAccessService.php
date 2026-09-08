<?php

namespace App\Services;

use App\Models\Company;
use App\Models\CompanyDomain;
use App\Models\CompanyEmployee;
use App\Models\CompanyEmployeeAccess;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CompanyAccessService
{
    public function activeAccessForUser($userId)
    {
        return CompanyEmployeeAccess::with('company')
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->where('access_ends_at', '>=', now())
            ->orderByDesc('access_ends_at')
            ->first();
    }

    public function claimForUser(User $user)
    {
        $email = $this->normalizeEmail($user->email);
        if (!$email) {
            return [
                'status' => false,
                'message' => 'A valid email address is required for company access.',
                'access' => null,
                'company' => null,
            ];
        }

        $activeAccess = $this->activeAccessForUser($user->id);
        if ($activeAccess) {
            return [
                'status' => true,
                'message' => 'Company access is already active.',
                'access' => $activeAccess,
                'company' => $activeAccess->company,
            ];
        }

        $eligibility = $this->findEligibleCompanyForEmail($email);
        if (!$eligibility) {
            return [
                'status' => false,
                'message' => 'Your email is not eligible for company access.',
                'access' => null,
                'company' => null,
            ];
        }

        if ($this->requiresEmailVerification($user, $eligibility)) {
            return [
                'status' => false,
                'message' => 'Please verify your work email before claiming company access.',
                'access' => null,
                'company' => $eligibility['company'],
            ];
        }

        $company = $eligibility['company'];
        $existingCompanyAccess = CompanyEmployeeAccess::where('company_id', $company->id)
            ->where(function ($query) use ($user, $email) {
                $query->where('user_id', $user->id)->orWhere('email', $email);
            })
            ->first();

        if ($existingCompanyAccess) {
            $existingCompanyAccess->load('company');

            return [
                'status' => $existingCompanyAccess->isActive(),
                'message' => $existingCompanyAccess->isActive()
                    ? 'Company access is already active.'
                    : 'Company access has already been used for this email.',
                'access' => $existingCompanyAccess,
                'company' => $existingCompanyAccess->company,
            ];
        }

        if ($company->max_employees) {
            $usedSeats = CompanyEmployeeAccess::where('company_id', $company->id)->count();
            if ($usedSeats >= $company->max_employees) {
                return [
                    'status' => false,
                    'message' => 'Company employee access limit has been reached.',
                    'access' => null,
                    'company' => $company,
                ];
            }
        }

        $access = DB::transaction(function () use ($company, $user, $email, $eligibility) {
            $now = now();
            $access = CompanyEmployeeAccess::create([
                'company_id' => $company->id,
                'user_id' => $user->id,
                'email' => $email,
                'access_starts_at' => $now,
                'access_ends_at' => $now->copy()->addDays((int) $company->free_access_days),
                'verified_at' => $user->email_verified_at ?: $now,
                'source' => $eligibility['source'],
                'status' => 'active',
                'metadata' => [
                    'matched_by' => $eligibility['matched_by'],
                    'email_verified' => !empty($user->email_verified_at),
                ],
            ]);

            CompanyEmployee::where('company_id', $company->id)
                ->where('email', $email)
                ->update(['user_id' => $user->id]);

            return $access->load('company');
        });

        return [
            'status' => true,
            'message' => 'Company access activated successfully.',
            'access' => $access,
            'company' => $company,
        ];
    }

    public function findEligibleCompanyForEmail($email)
    {
        $email = $this->normalizeEmail($email);
        if (!$email) {
            return null;
        }

        $employee = CompanyEmployee::with('company')
            ->where('email', $email)
            ->where('status', 'active')
            ->first();

        if ($employee && $employee->company && $employee->company->isActiveNow()) {
            return [
                'company' => $employee->company,
                'source' => 'employee_email',
                'matched_by' => $email,
            ];
        }

        $domain = $this->domainFromEmail($email);
        if (!$domain) {
            return null;
        }

        $companyDomain = CompanyDomain::with('company')
            ->where('domain', $domain)
            ->where('status', 'active')
            ->first();

        if ($companyDomain && $companyDomain->company && $companyDomain->company->isActiveNow()) {
            return [
                'company' => $companyDomain->company,
                'source' => 'domain',
                'matched_by' => $domain,
            ];
        }

        return null;
    }

    public function requiresEmailVerification(User $user, $eligibility)
    {
        return empty($user->email_verified_at)
            && $eligibility
            && ($eligibility['source'] ?? null) !== 'employee_email';
    }

    public function normalizeEmail($email)
    {
        $email = strtolower(trim((string) $email));

        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }

    public function domainFromEmail($email)
    {
        $email = $this->normalizeEmail($email);
        if (!$email || strpos($email, '@') === false) {
            return null;
        }

        return substr(strrchr($email, '@'), 1);
    }
}
