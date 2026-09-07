<x-app-layout :assets="$assets ?? []">
    <div>
        @php
            $id = $company->id ?? null;
            $domains = isset($company) ? $company->domains->pluck('domain')->implode("\n") : '';
            $employeeEmails = isset($company) ? $company->employees->pluck('email')->implode("\n") : '';
        @endphp

        @if($id)
            {!! Form::model($company, ['route' => ['companies.update', $id], 'method' => 'patch']) !!}
        @else
            {!! Form::open(['route' => ['companies.store'], 'method' => 'post']) !!}
        @endif

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <div class="header-title">
                            <h4 class="card-title">{{ $pageTitle }}</h4>
                        </div>
                        <div class="card-action">
                            <a href="{{ route('companies.index') }} " class="btn btn-sm btn-primary" role="button">Back</a>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="form-group col-md-4">
                                {{ Form::label('name', 'Company Name *', [ 'class' => 'form-control-label' ], false) }}
                                {{ Form::text('name', old('name', $company->name ?? null), [ 'class' => 'form-control', 'required' ]) }}
                            </div>

                            <div class="form-group col-md-4">
                                {{ Form::label('code', 'Company Code', [ 'class' => 'form-control-label' ]) }}
                                {{ Form::text('code', old('code', $company->code ?? null), [ 'class' => 'form-control', 'placeholder' => 'Auto generated if blank' ]) }}
                            </div>

                            <div class="form-group col-md-4">
                                {{ Form::label('status', 'Status *', [ 'class' => 'form-control-label' ], false) }}
                                {{ Form::select('status', [ 'active' => 'Active', 'inactive' => 'Inactive' ], old('status', $company->status ?? 'active'), [ 'class' => 'form-control', 'required' ]) }}
                            </div>

                            <div class="form-group col-md-4">
                                {{ Form::label('free_access_days', 'Free Access Days *', [ 'class' => 'form-control-label' ], false) }}
                                {{ Form::number('free_access_days', old('free_access_days', $company->free_access_days ?? 60), [ 'class' => 'form-control', 'min' => 1, 'required' ]) }}
                            </div>

                            <div class="form-group col-md-4">
                                {{ Form::label('max_employees', 'Max Employees', [ 'class' => 'form-control-label' ]) }}
                                {{ Form::number('max_employees', old('max_employees', $company->max_employees ?? null), [ 'class' => 'form-control', 'min' => 1, 'placeholder' => 'Unlimited if blank' ]) }}
                            </div>

                            <div class="form-group col-md-4">
                                {{ Form::label('valid_from', 'Valid From', [ 'class' => 'form-control-label' ]) }}
                                {{ Form::date('valid_from', old('valid_from', isset($company) && $company->valid_from ? $company->valid_from->format('Y-m-d') : null), [ 'class' => 'form-control' ]) }}
                            </div>

                            <div class="form-group col-md-4">
                                {{ Form::label('valid_to', 'Valid To', [ 'class' => 'form-control-label' ]) }}
                                {{ Form::date('valid_to', old('valid_to', isset($company) && $company->valid_to ? $company->valid_to->format('Y-m-d') : null), [ 'class' => 'form-control' ]) }}
                            </div>

                            <div class="form-group col-md-6">
                                {{ Form::label('domains', 'Allowed Domains', [ 'class' => 'form-control-label' ]) }}
                                {{ Form::textarea('domains', old('domains', $domains), [ 'class' => 'form-control', 'rows' => 5, 'placeholder' => "company.com\ncompany.co.in" ]) }}
                            </div>

                            <div class="form-group col-md-6">
                                {{ Form::label('employee_emails', 'Allowed Employee Emails', [ 'class' => 'form-control-label' ]) }}
                                {{ Form::textarea('employee_emails', old('employee_emails', $employeeEmails), [ 'class' => 'form-control', 'rows' => 5, 'placeholder' => "employee1@company.com\nemployee2@gmail.com" ]) }}
                            </div>

                            <div class="form-group col-md-12">
                                {{ Form::label('notes', 'Notes', [ 'class' => 'form-control-label' ]) }}
                                {{ Form::textarea('notes', old('notes', $company->notes ?? null), [ 'class' => 'form-control', 'rows' => 4 ]) }}
                            </div>
                        </div>
                        <hr>
                        {{ Form::submit(__('message.save'), ['class' => 'btn btn-md btn-primary float-end']) }}
                    </div>
                </div>
            </div>
        </div>

        {!! Form::close() !!}
    </div>
</x-app-layout>
