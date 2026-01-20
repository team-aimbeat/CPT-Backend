@push('scripts')
    <script>
        (function($) {
            $(document).ready(function(){
                tinymceEditor('.tinymce-terms_condition',' ',function (ed) {

                }, 450)
            
            });

        })(jQuery);
    </script>
@endpush
<x-app-layout :assets="$assets ?? []">
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-block card-stretch">
                <div class="card-body p-0">
                    <div class="d-flex justify-content-between align-items-center p-3">
                        <h5 class="font-weight-bold">{{ $pageTitle ?? __('message.list') }}</h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    {{ Form::model($setting_data,['method' => 'POST', 'route' => 'pages.term_condition_save', 'data-toggle' => 'validator' ] ) }}
                        {{ Form::hidden('id') }}
                        <div class="row">
                            <div class="form-group col-md-12">
                                {{ Form::label('terms_condition',__('message.terms_condition'), ['class' => 'form-control-label']) }}
                                {{ Form::textarea('value', null, ['class'=> 'form-control tinymce-terms_condition', 'placeholder'=> __('message.terms_condition') ]) }}
                            </div>
                        </div>
                        {{ Form::submit( __('message.save'), ['class' => 'btn btn-md btn-primary float-end']) }}
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>