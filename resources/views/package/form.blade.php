@push('scripts')
    <script>
        (function($) {
            $(document).ready(function(){
                tinymceEditor('.tinymce-description',' ',function (ed) {
                }, 450)

                $('#offer_preset').on('change', function () {
                    const preset = $(this).val();
                    if (!preset) return;

                    const presets = {
                        '2p_yearly': { enabled: 1, type: 'free_access', days: 180, max: 2 },
                        '4p_yearly': { enabled: 1, type: 'free_access', days: 365, max: 4 },
                        '2p_24m': { enabled: 1, type: 'free_access', days: 365, max: 2 },
                        '4p_24m': { enabled: 1, type: 'free_access', days: 730, max: 4 },
                    };

                    const cfg = presets[preset];
                    if (!cfg) return;

                    $('input[name="offer_enabled"]').prop('checked', cfg.enabled === 1);
                    $('select[name="offer_type"]').val(cfg.type).trigger('change');
                    $('input[name="offer_access_days"]').val(cfg.days);
                    $('input[name="offer_max_redemptions"]').val(cfg.max);
                });
            });
        })(jQuery);
    </script>
@endpush

<x-app-layout :assets="$assets ?? []">
    <div>
        <?php $id = $id ?? null;?>
        @if(isset($id))
            {!! Form::model($data, [ 'route' => ['packages.update', $id], 'method' => 'patch', 'enctype' => 'multipart/form-data' ]) !!}
        @else
            {!! Form::open(['route' => ['packages.store'], 'method' => 'package', 'enctype' => 'multipart/form-data' ]) !!}
        @endif
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <div class="header-title">
                            <h4 class="card-title">{{ $pageTitle }}</h4>
                        </div>
                        <div class="card-action">
                            <a href="{{ route('packages.index') }} " class="btn btn-sm btn-primary" role="button">{{ __('message.back') }}</a>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="form-group col-md-4">
                                {{ Form::label('name', __('message.name').' <span class="text-danger">*</span>',[ 'class' => 'form-control-label' ], false ) }}
                                {{ Form::text('name', old('name'),[ 'placeholder' => __('message.name'),'class' =>'form-control','required']) }}
                            </div>

                            <div class="form-group col-md-4">
                                {{ Form::label('duration_unit',__('message.duration_unit'), ['class' => 'form-control-label']) }}
                                {{ Form::select('duration_unit',[ 'monthly' => __('message.monthly'), 'yearly' => __('message.yearly') ], old('duration_unit'),[ 'class' =>'form-control select2js','required']) }}
                            </div>

                            <div class="form-group col-md-4">
                                {{ Form::label('duration',trans('message.duration').' <span class="text-danger">*</span>', ['class'=>'form-control-label'],false) }}
                                {{ Form::select('duration',['1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6', '7' => '7', '8' => '8', '9' => '9', '10' => '10', '11' => '11', '12' => '12', '24' => '24' ],old('duration'),[ 'id' => 'duration' ,'class' =>'form-control select2js','required']) }}
                            </div>

                            <div class="form-group col-md-4">
                                {{ Form::label('price', __('message.price').' <span class="text-danger">(₹)*</span>',['class'=>'form-control-label'], false ) }}
                                {{ Form::number('price', old('price'), ['class' => 'form-control',  'min' => 0, 'step' => 'any', 'required', 'placeholder' => __('message.price') ]) }}
                            </div>
                            <div class="form-group col-md-4">
                                {{ Form::label('package_type',__('message.type').' <span class="text-danger">*</span>',['class'=>'form-control-label'],false) }}
                                {{ Form::select('package_type',[ 'workout' => __('message.workout'), 'diet' => __('message.diet') , 'both' => __('message.both') ], old('package_type'), [ 'class' =>'form-control select2js','required']) }}
                            </div>
                            <div class="form-group col-md-4">
                                {{ Form::label('status',__('message.status').' <span class="text-danger">*</span>',['class'=>'form-control-label'],false) }}
                                {{ Form::select('status',[ 'active' => __('message.active'), 'inactive' => __('message.inactive') ], old('status'), [ 'class' =>'form-control select2js','required']) }}
                            </div>
                            <div class="form-group col-md-4">
                                {{ Form::label('offer_enabled','Offer Enabled',[ 'class' => 'form-control-label' ]) }}
                                <div class="mt-2">
                                    {!! Form::hidden('offer_enabled',0) !!}
                                    {!! Form::checkbox('offer_enabled',1, old('offer_enabled', $data->offer_enabled ?? false), ['class' => 'form-check-input']) !!}
                                </div>
                            </div>
                            <div class="form-group col-md-4">
                                <label class="form-control-label" for="offer_preset">Offer Preset</label>
                                <select id="offer_preset" class="form-control">
                                    <option value="">-- Select Preset --</option>
                                    <option value="2p_yearly">2 Person Yearly (6 months free)</option>
                                    <option value="4p_yearly">4 Person Yearly (1 year free)</option>
                                    <option value="2p_24m">2 Person 24 Month (1 year free)</option>
                                    <option value="4p_24m">4 Person 24 Month (2 years free)</option>
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                {{ Form::label('offer_type','Offer Type',[ 'class' => 'form-control-label' ]) }}
                                {{ Form::select('offer_type', [
                                    '' => '-- None --',
                                    'free_access' => 'Free Access',
                                    'free_months' => 'Free Months'
                                ], old('offer_type', $data->offer_type ?? null), [ 'class' =>'form-control select2js']) }}
                            </div>
                            <div class="form-group col-md-4">
                                {{ Form::label('offer_access_days','Offer Access Days',[ 'class' => 'form-control-label' ]) }}
                                {{ Form::number('offer_access_days', old('offer_access_days', $data->offer_access_days ?? null), [ 'class' => 'form-control', 'min' => 1 ]) }}
                            </div>
                            <div class="form-group col-md-4">
                                {{ Form::label('offer_max_redemptions','Offer Max Redemptions',[ 'class' => 'form-control-label' ]) }}
                                {{ Form::number('offer_max_redemptions', old('offer_max_redemptions', $data->offer_max_redemptions ?? null), [ 'class' => 'form-control', 'min' => 1 ]) }}
                            </div>
                            <div class="form-group col-md-4">
                                <label class="form-control-label" for="image">{{ __('message.image') }} </label>
                                <div class="">
                                    <input class="form-control file-input" type="file" name="package_image" accept="image/*">
                                </div>
                            </div>
                            @if( isset($id) && getMediaFileExit($data, 'package_image'))
                            <div class="col-md-2 mb-2 position-relative">
                                <img id="package_image_preview" src="{{ getSingleMedia($data,'package_image') }}" alt="package-image" class="avatar-100 mt-1">
                                <a class="text-danger remove-file"
                                    href="{{ route('remove.file', ['id' => $data->id, 'type' => 'package_image']) }}"
                                    data--submit='confirm_form' data--confirmation='true' data--ajax='true'
                                    data-toggle='tooltip'
                                    title='{{ __("message.remove_file_title" , ["name" =>  __("message.image") ]) }}'
                                    data-title='{{ __("message.remove_file_title" , ["name" =>  __("message.image") ]) }}'
                                    data-message='{{ __("message.remove_file_msg") }}'>
                                    <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path opacity="0.4" d="M16.34 1.99976H7.67C4.28 1.99976 2 4.37976 2 7.91976V16.0898C2 19.6198 4.28 21.9998 7.67 21.9998H16.34C19.73 21.9998 22 19.6198 22 16.0898V7.91976C22 4.37976 19.73 1.99976 16.34 1.99976Z" fill="currentColor"></path>
                                        <path d="M15.0158 13.7703L13.2368 11.9923L15.0148 10.2143C15.3568 9.87326 15.3568 9.31826 15.0148 8.97726C14.6728 8.63326 14.1198 8.63426 13.7778 8.97626L11.9988 10.7543L10.2198 8.97426C9.87782 8.63226 9.32382 8.63426 8.98182 8.97426C8.64082 9.31626 8.64082 9.87126 8.98182 10.2123L10.7618 11.9923L8.98582 13.7673C8.64382 14.1093 8.64382 14.6643 8.98582 15.0043C9.15682 15.1763 9.37982 15.2613 9.60382 15.2613C9.82882 15.2613 10.0518 15.1763 10.2228 15.0053L11.9988 13.2293L13.7788 15.0083C13.9498 15.1793 14.1728 15.2643 14.3968 15.2643C14.6208 15.2643 14.8448 15.1783 15.0158 15.0083C15.3578 14.6663 15.3578 14.1123 15.0158 13.7703Z" fill="currentColor"></path>
                                    </svg>
                                </a>
                            </div>
                            @endif
                            <div class="form-group col-md-12">
                                {{ Form::label('description',__('message.description'), ['class' => 'form-control-label']) }}
                                {{ Form::textarea('description', null, ['class'=> 'form-control tinymce-description' , 'placeholder'=> __('message.description') ]) }}
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
