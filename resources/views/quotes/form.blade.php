@push('scripts')
@endpush
<x-app-layout :assets="$assets ?? []">
    <div>
        <?php $id = $id ?? null;?>
        @if(isset($id))
            {!! Form::model($data, ['route' => ['quotes.update', $id], 'method' => 'patch' ]) !!}
        @else
            {!! Form::open(['route' => ['quotes.store'], 'method' => 'post' ]) !!}
        @endif
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <div class="header-title">
                            <h4 class="card-title">{{ $pageTitle }}</h4>
                        </div>
                        <div class="card-action">
                            <a href="{{ route('quotes.index') }} " class="btn btn-sm btn-primary" role="button">{{ __('message.back') }}</a>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">

                            <div class="form-group col-md-6">
                                {{ Form::label('title', __('message.title').' <span class="text-danger">*</span>',['class' => 'form-control-label'], false ) }}
                                {{ Form::text('title', old('title'),[ 'placeholder' => __('message.title'),'class' =>'form-control','required']) }}
                            </div>
                            <div class="form-group col-md-6">
                                {{ Form::label('date', __('message.date').' <span class="text-danger">*</span>',[ 'class' => 'form-control-label' ], false ) }}
                                {{ Form::text('date', old('date'),[ 'placeholder' => __('message.date'), 'class' =>'datepicker form-control', 'required']) }}
                            </div>
                            <div class="form-group col-md-12">
                                {{ Form::label('message',__('message.message').' <span class="text-danger">*</span>',['class' => 'form-control-label'], false ) }}
                                {{ Form::textarea('message', null, [ 'class' => 'form-control textarea', 'rows' => 3, 'required', 'placeholder' => __('message.message') ]) }}
                            </div>
                        </div>
                        <hr>
                        {{ Form::submit( __('message.save'), ['class'=>'btn btn-md btn-primary float-end']) }}
                    </div>
                </div>
            </div>
        </div>
        {!! Form::close() !!}
    </div>
</x-app-layout>
