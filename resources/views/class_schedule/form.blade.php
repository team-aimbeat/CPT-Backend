@push('scripts')
    <script>
        (function($) {
            $(document).ready(function(){
                changeValues();

                $('input[name="is_paid"]').change(function () {
                    changeValues();
                });

                $(document).on('change', '#workout_id', function () {                    
                    changeValues();
                });

                function changeValues() {
                    var is_paid_val = $('input[name="is_paid"]:checked').val();
                    if ( is_paid_val == 1 ) {
                        $('.is_paid_price').show();
                        $('#price').prop('required', true);
                    }else{
                        $('.is_paid_price').hide();
                        $('#price').prop('required', false);
                    }

                    var class_id = $('#workout_id').val();
                    if ( class_id == 'other' ) {
                        $('.workout_title').show();
                        $('#workout_title').prop('required', true);
                    }else{
                        $('.workout_title').hide();
                        $('#workout_title').prop('required', false);
                    }
                }

                var video_type = $('select[name=video_type]').val();
                changeUploadFile(video_type);

                $(".video_type").change(function () {
                    changeUploadFile(this.value)
                });

                function changeUploadFile(type) {
                    if (jQuery.inArray(type, ['url']) !== -1) {
                        $('.video_url').removeClass('d-none');
                        $('.video_upload').addClass('d-none');
                    } else {
                        $('.video_upload').removeClass('d-none');
                        $('.video_url').addClass('d-none');
                    }
                }
            });
        })(jQuery);
    </script>
@endpush
<x-app-layout :assets="$assets ?? []">
    <div>
        <?php $id = $id ?? null;?>
        @if(isset($id))
            {!! Form::model($data, [ 'route' => ['classschedule.update', $id], 'method' => 'patch', 'enctype' => 'multipart/form-data']) !!}
        @else
            {!! Form::open(['route' => ['classschedule.store'], 'method' => 'class_schedule', 'enctype' => 'multipart/form-data']) !!}
        @endif
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <div class="header-title">
                            <h4 class="card-title">{{ $pageTitle }}</h4>
                        </div>
                        <div class="card-action">
                            <a href="{{ route('classschedule.index') }} " class="btn btn-sm btn-primary" role="button">{{ __('message.back') }}</a>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="form-group col-md-4">
                                {{ Form::label('class_name', __('message.class_name').' <span class="text-danger">*</span>',[ 'class' => 'form-control-label' ], false ) }}
                                {{ Form::text('class_name', old('class_name'),[ 'placeholder' => __('message.class_name'),'class' =>'form-control','required']) }}
                            </div>
                            <div class="form-group col-md-4">
                                {{ Form::label('workout_id', __('message.workout').' <span class="text-danger">*</span>',[ 'class' => 'form-control-label' ], false) }} 
                                {{ Form::select('workout_id',isset($id) ? $workout_id : [], old('workout_id'), [
                                        'class' => 'select2js form-group workout',
                                        'data-placeholder' => __('message.select_name',[ 'select' => __('message.workout') ]),
                                        'data-ajax--url' => route('ajax-list', ['type' => 'workout' , 'sub_type' => 'class_schedule_workout']),
                                    ])
                                }}
                            </div>
                            <div class="form-group col-md-4 workout_title">
                                {{ Form::label('workout_title', __('message.workout_title').' <span class="text-danger">*</span>',[ 'class' => 'form-control-label' ], false ) }}
                                {{ Form::text('workout_title', old('workout_title'),[ 'placeholder' => __('message.workout_title'),'class' =>'form-control','required']) }}
                            </div>
                            <div class="form-group col-md-6">
                                {{ Form::label('start_date', __('message.start_date').' <span class="text-danger">*</span>',[ 'class' => 'form-control-label' ], false ) }}
                                {{ Form::text('start_date', old('start_date'),[ 'placeholder' => __('message.start_date'), 'class' =>'maxdatepicker form-control', 'required']) }}
                            </div>
                            <div class="form-group col-md-6">
                                {{ Form::label('end_date', __('message.end_date').' <span class="text-danger">*</span>',[ 'class' => 'form-control-label' ], false ) }}
                                {{ Form::text('end_date', old('end_date'),[ 'placeholder' => __('message.end_date'), 'class' =>'maxdatepicker form-control', 'required']) }}
                            </div>

                            <div class="form-group col-md-6">
                                {{ Form::label('start_time', __('message.start_time').' <span class="text-danger">*</span>',[ 'class' => 'form-control-label' ], false ) }}
                                {{ Form::text('start_time', old('start_time'),[ 'placeholder' => __('message.start_time'), 'class' =>'timepicker24 form-control', 'required']) }}
                            </div>
                            <div class="form-group col-md-6">
                                {{ Form::label('end_time', __('message.end_time').' <span class="text-danger">*</span>',[ 'class' => 'form-control-label' ], false ) }}
                                {{ Form::text('end_time', old('end_time'),[ 'placeholder' => __('message.end_time'), 'class' =>'timepicker24 form-control', 'required']) }}
                            </div>
                            <div class="form-group col-md-4">
                                {{ Form::label('name', __('message.name'),[ 'class' => 'form-control-label' ], false ) }}
                                {{ Form::text('name', old('name'),[ 'placeholder' => __('message.name'),'class' =>'form-control']) }}
                            </div>

                           
                            <div class="form-group col-md-4">
                                {{ Form::label('video_type', __('message.video_type').' <span class="text-danger">*</span>',['class'=>'form-control-label'], false) }}
                                {{ Form::select('video_type', [ 'url' => __('message.url'), 'upload_video' => __('message.upload_video') ], old('video_type'), [ 'class' => 'form-control select2js video_type','required']) }}
                            </div>
                            <div class="form-group col-md-4 video_url">
                                {{ Form::label('video_url', __('message.video_url'), [ 'class' => 'form-control-label' ] ) }}
                                {{ Form::url('link', old('link'),[ 'placeholder' => __('message.video_url'),'class' =>'form-control']) }}
                            </div>

                            <div class="form-group col-md-4 video_upload">
                                <label class="form-control-label" for="class_schedule_video">{{ __('message.video') }}
                                </label>
                                <div class="">
                                    <input class="form-control file-input" type="file" name="class_schedule_video"
                                        accept="video/*" id="class_schedule_video" />
                                </div>
                            </div>
                            
                            
                            
                            <div class="form-group col-md-6">
                                {{ Form::label('is_paid', __('message.is_paid'), ['class' => 'form-control-label']) }}
                                <div class="form-check">
                                    <div class="custom-control custom-radio d-inline-block col-4">
                                        <label class="form-check-label" for="is_paid-free"> {{__('message.free')}} </label>
                                        {{ Form::radio('is_paid', '0', old('is_paid') || true, [ 'class' => 'form-check-input', 'id' => 'is_paid-free']) }}
                                    </div>
                                    <div class="custom-control custom-radio d-inline-block col-4">
                                        <label class="form-check-label" for="is_paid-paid"> {{__('message.paid')}} </label>
                                        {{ Form::radio('is_paid', '1', old('is_paid'), [ 'class' => 'form-check-input', 'id' => 'is_paid-paid']) }}
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-md-6 is_paid_price">
                                {{ Form::label('price', __('message.price').' <span class="text-danger">(₹)*</span>',['class'=>'form-control-label'], false ) }}
                                {{ Form::number('price', old('price'), ['class' => 'form-control',  'min' => 0, 'step' => 'any', 'required', 'placeholder' => __('message.price') ]) }}
                            </div>

                            @if( isset($id) && getMediaFileExit($data, 'class_schedule_video'))
                            <div class="col-md-12 mb-2 position-relative">
                                <?php
                                        $file_extention = config('constant.IMAGE_EXTENTIONS');
                                        $image = getSingleMedia($data, 'class_schedule_video');
                                        
                                        $extention = in_array(strtolower(imageExtention($image)), $file_extention);
                                    ?>
                                @if($extention)
                                <img id="class_schedule_video_preview" src="{{ $image}}" alt="equipment-video"
                                    class="avatar-100 mt-1" />
                                @else
                                    <img id="class_schedule_video_preview" src="{{ asset('images/file.png') }}" class="avatar-100" />
                                    <a href="{{ $image }}" download>{{ __('message.download') }}</a>
                                @endif
                                <a class="text-danger remove-file"
                                    href="{{ route('remove.file', ['id' => $data->id, 'type' => 'class_schedule_video']) }}"
                                    data--submit='confirm_form' data--confirmation='true' data--ajax='true'
                                    data-toggle='tooltip'
                                    title='{{ __("message.remove_file_title" , ["name" =>  __("message.video") ]) }}'
                                    data-title='{{ __("message.remove_file_title" , ["name" =>  __("message.video") ]) }}'
                                    data-message='{{ __("message.remove_file_msg") }}'>
                                    <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path opacity="0.4"
                                            d="M16.34 1.99976H7.67C4.28 1.99976 2 4.37976 2 7.91976V16.0898C2 19.6198 4.28 21.9998 7.67 21.9998H16.34C19.73 21.9998 22 19.6198 22 16.0898V7.91976C22 4.37976 19.73 1.99976 16.34 1.99976Z"
                                            fill="currentColor"></path>
                                        <path
                                            d="M15.0158 13.7703L13.2368 11.9923L15.0148 10.2143C15.3568 9.87326 15.3568 9.31826 15.0148 8.97726C14.6728 8.63326 14.1198 8.63426 13.7778 8.97626L11.9988 10.7543L10.2198 8.97426C9.87782 8.63226 9.32382 8.63426 8.98182 8.97426C8.64082 9.31626 8.64082 9.87126 8.98182 10.2123L10.7618 11.9923L8.98582 13.7673C8.64382 14.1093 8.64382 14.6643 8.98582 15.0043C9.15682 15.1763 9.37982 15.2613 9.60382 15.2613C9.82882 15.2613 10.0518 15.1763 10.2228 15.0053L11.9988 13.2293L13.7788 15.0083C13.9498 15.1793 14.1728 15.2643 14.3968 15.2643C14.6208 15.2643 14.8448 15.1783 15.0158 15.0083C15.3578 14.6663 15.3578 14.1123 15.0158 13.7703Z"
                                            fill="currentColor"></path>
                                    </svg>
                                </a>
                            </div>
                            @endif
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
