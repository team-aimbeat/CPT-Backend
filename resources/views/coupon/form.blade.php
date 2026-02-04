<x-app-layout :assets="$assets ?? []">
    <div>
        @php
            $id = $coupon->id ?? null;
        @endphp

        @if($id)
            {!! Form::model($coupon, ['route' => ['coupons.update', $id], 'method' => 'patch']) !!}
        @else
            {!! Form::open(['route' => ['coupons.store'], 'method' => 'post']) !!}
        @endif

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <div class="header-title">
                            <h4 class="card-title">{{ $pageTitle }}</h4>
                        </div>
                        <div class="card-action">
                            <a href="{{ route('coupons.index') }} " class="btn btn-sm btn-primary" role="button">Back</a>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="form-group col-md-4">
                                {{ Form::label('code', 'Coupon Code *', [ 'class' => 'form-control-label' ], false) }}
                                {{ Form::text('code', old('code', request('code', $coupon->code ?? null)), [ 'class' => 'form-control', 'required' ]) }}
                            </div>

                            <div class="form-group col-md-4">
                                {{ Form::label('type', 'Coupon Type *', [ 'class' => 'form-control-label' ], false) }}
                                {{ Form::select('type', [
                                    'free_access' => 'Free Access',
                                    'discount' => 'Discount',
                                    'free_months' => 'Free Months',
                                    'same_access' => 'Same Access'
                                ], old('type', request('type', $coupon->type ?? null)), [ 'class' => 'form-control', 'required' ]) }}
                            </div>

                            <div class="form-group col-md-4">
                                {{ Form::label('status', 'Status *', [ 'class' => 'form-control-label' ], false) }}
                                {{ Form::select('status', [ 'active' => 'Active', 'inactive' => 'Inactive' ], old('status', request('status', $coupon->status ?? null)), [ 'class' => 'form-control', 'required' ]) }}
                            </div>

                            <div class="form-group col-md-4">
                                {{ Form::label('value', 'Value', [ 'class' => 'form-control-label' ]) }}
                                {{ Form::number('value', old('value', request('value', $coupon->value ?? null)), [ 'class' => 'form-control', 'min' => 0, 'step' => 'any' ]) }}
                            </div>

                            <div class="form-group col-md-4">
                                {{ Form::label('access_days', 'Access Days', [ 'class' => 'form-control-label' ]) }}
                                {{ Form::number('access_days', old('access_days', request('access_days', $coupon->access_days ?? null)), [ 'class' => 'form-control', 'min' => 1 ]) }}
                            </div>

                            <div class="form-group col-md-4">
                                {{ Form::label('max_redemptions', 'Max Redemptions', [ 'class' => 'form-control-label' ]) }}
                                {{ Form::number('max_redemptions', old('max_redemptions', request('max_redemptions', $coupon->max_redemptions ?? null)), [ 'class' => 'form-control', 'min' => 1 ]) }}
                            </div>

                            <div class="form-group col-md-4">
                                {{ Form::label('per_user_limit', 'Per User Limit', [ 'class' => 'form-control-label' ]) }}
                                {{ Form::number('per_user_limit', old('per_user_limit', request('per_user_limit', $coupon->per_user_limit ?? null)), [ 'class' => 'form-control', 'min' => 1 ]) }}
                            </div>

                            <div class="form-group col-md-4">
                                {{ Form::label('valid_from', 'Valid From', [ 'class' => 'form-control-label' ]) }}
                                {{ Form::date('valid_from', old('valid_from', request('valid_from', $coupon->valid_from ?? null)), [ 'class' => 'form-control' ]) }}
                            </div>

                            <div class="form-group col-md-4">
                                {{ Form::label('valid_to', 'Valid To', [ 'class' => 'form-control-label' ]) }}
                                {{ Form::date('valid_to', old('valid_to', request('valid_to', $coupon->valid_to ?? null)), [ 'class' => 'form-control' ]) }}
                            </div>

                            <div class="form-group col-md-4">
                                {{ Form::label('first_purchase_only', 'First Purchase Only', [ 'class' => 'form-control-label' ]) }}
                                <div class="mt-2">
                                    {!! Form::hidden('first_purchase_only',0) !!}
                                    {!! Form::checkbox('first_purchase_only',1, old('first_purchase_only', request('first_purchase_only', $coupon->first_purchase_only ?? false)), ['class' => 'form-check-input']) !!}
                                </div>
                            </div>

                            <div class="form-group col-md-12">
                                {{ Form::label('description', 'Description', [ 'class' => 'form-control-label' ]) }}
                                {{ Form::textarea('description', old('description', request('description', $coupon->description ?? null)), [ 'class' => 'form-control', 'rows' => 4 ]) }}
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
