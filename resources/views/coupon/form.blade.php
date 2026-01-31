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
                                {{ Form::text('code', old('code'), [ 'class' => 'form-control', 'required' ]) }}
                            </div>

                            <div class="form-group col-md-4">
                                {{ Form::label('status', 'Status *', [ 'class' => 'form-control-label' ], false) }}
                                {{ Form::select('status', [ 'active' => 'Active', 'inactive' => 'Inactive' ], old('status'), [ 'class' => 'form-control', 'required' ]) }}
                            </div>

                            <div class="form-group col-md-12">
                                {{ Form::label('description', 'Description', [ 'class' => 'form-control-label' ]) }}
                                {{ Form::textarea('description', null, [ 'class' => 'form-control', 'rows' => 4 ]) }}
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
