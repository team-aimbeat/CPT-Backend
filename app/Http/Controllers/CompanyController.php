<?php

namespace App\Http\Controllers;

use App\Helpers\AuthHelper;
use App\Models\Company;
use App\Models\CompanyDomain;
use App\Models\CompanyEmployee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CompanyController extends Controller
{
    public function index()
    {
        $auth_user = AuthHelper::authSession();
        if (!$auth_user->can('package-list')) {
            $message = __('message.permission_denied_for_account');
            return redirect()->back()->withErrors($message);
        }

        $pageTitle = 'Companies';
        $companies = Company::withCount(['domains', 'employees', 'employeeAccesses'])
            ->orderByDesc('id')
            ->paginate(20);

        return view('company.index', compact('pageTitle', 'companies'));
    }

    public function create()
    {
        if (!auth()->user()->can('package-add')) {
            $message = __('message.permission_denied_for_account');
            return redirect()->back()->withErrors($message);
        }

        $pageTitle = 'Add Company';

        return view('company.form', compact('pageTitle'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->can('package-add')) {
            $message = __('message.permission_denied_for_account');
            return redirect()->back()->withErrors($message);
        }

        $data = $this->validatedData($request);

        DB::transaction(function () use ($data, $request) {
            $company = Company::create($data);
            $this->syncCompanyLists($company, $request);
        });

        return redirect()->route('companies.index')->withSuccess('Company saved successfully.');
    }

    public function edit($id)
    {
        if (!auth()->user()->can('package-edit')) {
            $message = __('message.permission_denied_for_account');
            return redirect()->back()->withErrors($message);
        }

        $company = Company::with(['domains', 'employees'])->findOrFail($id);
        $pageTitle = 'Edit Company';

        return view('company.form', compact('company', 'pageTitle'));
    }

    public function show($id)
    {
        return redirect()->route('companies.edit', $id);
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('package-edit')) {
            $message = __('message.permission_denied_for_account');
            return redirect()->back()->withErrors($message);
        }

        $company = Company::findOrFail($id);
        $data = $this->validatedData($request, $company->id);

        DB::transaction(function () use ($company, $data, $request) {
            $company->update($data);
            $this->syncCompanyLists($company, $request);
        });

        return redirect()->route('companies.index')->withSuccess('Company updated successfully.');
    }

    public function destroy($id)
    {
        if (env('APP_DEMO')) {
            $message = __('message.demo_permission_denied');
            return redirect()->route('companies.index')->withErrors($message);
        }

        if (!auth()->user()->can('package-delete')) {
            $message = __('message.permission_denied_for_account');
            return redirect()->back()->withErrors($message);
        }

        Company::findOrFail($id)->delete();

        return redirect()->route('companies.index')->withSuccess('Company deleted successfully.');
    }

    private function validatedData(Request $request, $companyId = null)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('companies', 'code')->ignore($companyId),
            ],
            'free_access_days' => 'required|integer|min:1|max:3650',
            'max_employees' => 'nullable|integer|min:1',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string',
            'domains' => 'nullable|string',
            'employee_emails' => 'nullable|string',
        ]);

        $validator->after(function ($validator) use ($request, $companyId) {
            foreach ($this->parseDomains($request->input('domains')) as $domain) {
                $exists = CompanyDomain::where('domain', $domain)
                    ->when($companyId, function ($query) use ($companyId) {
                        $query->where('company_id', '!=', $companyId);
                    })
                    ->exists();

                if ($exists) {
                    $validator->errors()->add('domains', 'Domain already belongs to another company: ' . $domain);
                }
            }

            foreach ($this->parseEmails($request->input('employee_emails')) as $email) {
                $exists = CompanyEmployee::where('email', $email)
                    ->when($companyId, function ($query) use ($companyId) {
                        $query->where('company_id', '!=', $companyId);
                    })
                    ->exists();

                if ($exists) {
                    $validator->errors()->add('employee_emails', 'Employee email already belongs to another company: ' . $email);
                }
            }
        });

        $data = $validator->validate();
        $data['code'] = $data['code'] ?: $this->uniqueCodeFromName($data['name'], $companyId);

        unset($data['domains'], $data['employee_emails']);

        return $data;
    }

    private function syncCompanyLists(Company $company, Request $request)
    {
        $company->domains()->delete();
        foreach ($this->parseDomains($request->input('domains')) as $domain) {
            $company->domains()->create([
                'domain' => $domain,
                'status' => 'active',
            ]);
        }

        $company->employees()->delete();
        foreach ($this->parseEmails($request->input('employee_emails')) as $email) {
            $company->employees()->create([
                'email' => $email,
                'status' => 'active',
            ]);
        }
    }

    private function parseDomains($value)
    {
        $domains = [];
        foreach ($this->parseLines($value) as $item) {
            $domain = $this->normalizeDomain($item);
            if ($domain) {
                $domains[$domain] = $domain;
            }
        }

        return array_values($domains);
    }

    private function parseEmails($value)
    {
        $emails = [];
        foreach ($this->parseLines($value) as $item) {
            $email = strtolower(trim($item));
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $emails[$email] = $email;
            }
        }

        return array_values($emails);
    }

    private function parseLines($value)
    {
        if (!$value) {
            return [];
        }

        return array_filter(array_map('trim', preg_split('/[\r\n,]+/', $value)));
    }

    private function normalizeDomain($domain)
    {
        $domain = strtolower(trim($domain));
        $domain = preg_replace('/^https?:\/\//', '', $domain);
        $domain = trim($domain, " \t\n\r\0\x0B/@");
        $domain = explode('/', $domain)[0];

        if (!$domain || strpos($domain, '.') === false || strpos($domain, '@') !== false) {
            return null;
        }

        return $domain;
    }

    private function uniqueCodeFromName($name, $companyId = null)
    {
        $base = Str::slug($name);
        $base = $base ?: 'company';
        $code = $base;
        $counter = 2;

        while (Company::where('code', $code)
            ->when($companyId, function ($query) use ($companyId) {
                $query->where('id', '!=', $companyId);
            })
            ->exists()) {
            $code = $base . '-' . $counter;
            $counter++;
        }

        return $code;
    }
}
