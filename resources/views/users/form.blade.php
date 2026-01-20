@push('scripts')
<script>
    (function ($) {
        

        $(document).on('click', '#equipment_clear', function () {
            $('.equipment_ids').val(null).trigger('change');
        });

        $(document).on('click', '#injury_clear', function () {
            $('.injury_ids').val(null).trigger('change');
        });
        
        changeInjuryInfo();

        $('.equipment_ids').select2({
            ajax: {
                url: "{{ route('ajax-list', ['type' => 'equipment']) }}",
                data: function (params) {
                    return {
                        q: params.term, // Search term
                        workout_mode: $('select[name="user_profile[workout_mode]"]').val()
                    };
                }
            }
        });

        $( 'select[name="user_profile[workout_mode]"]' ).on( "change", function() {
            $('.equipment_ids').val(null).trigger('change');

            $('.equipment_ids').select2('destroy');
            $('.equipment_ids').select2({
                ajax: {
                    url: "{{ route('ajax-list', ['type' => 'equipment']) }}",
                    data: function (params) {
                        return {
                            q: params.term, // Search term
                            workout_mode: $('select[name="user_profile[workout_mode]"]').val()
                        };
                    }
                }
            });
        });

    })(jQuery);
    function changeInjuryInfo() {
            var has_injury = $(".has_injury").val();
            if (has_injury == 1) {
                $('.injury_info').removeClass('d-none');
            } else {
                $('.injury_info').addClass('d-none');
            }
        }
</script>
@endpush
<x-app-layout :assets="$assets ?? []">
    <div>
        <?php
            $id = $id ?? null;
        ?>
        @if(isset($id))
            {!! Form::model($data, ['route' => ['users.update', $id], 'method' => 'patch' , 'enctype' => 'multipart/form-data']) !!}
        @else
            {!! Form::open(['route' => ['users.store'], 'method' => 'post', 'enctype' => 'multipart/form-data']) !!}
        @endif
        <div class="row">
            <div class="col-xl-3 col-lg-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <div class="header-title">
                            <h4 class="card-title">{{ $pageTitle }}</h4>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <div class="profile-img-edit position-relative">
                                <img src="{{ $profileImage ?? asset('images/avatars/01.png')}}" alt="User-Profile" class="profile-pic rounded avatar-100">
                                <div class="upload-icone bg-primary">
                                    <svg class="upload-button" width="14" height="14" viewBox="0 0 24 24">
                                        <path fill="#ffffff" d="M14.06,9L15,9.94L5.92,19H5V18.08L14.06,9M17.66,3C17.41,3 17.15,3.1 16.96,3.29L15.13,5.12L18.88,8.87L20.71,7.04C21.1,6.65 21.1,6 20.71,5.63L18.37,3.29C18.17,3.09 17.92,3 17.66,3M14.06,6.19L3,17.25V21H6.75L17.81,9.94L14.06,6.19Z" />
                                    </svg>
                                    <input class="file-upload" type="file" accept="image/*" name="profile_image">
                                </div>
                            </div>
                            
                            <div class="img-extension mt-3">
                                <div class="d-inline-block align-items-center">
                                    <span>{{ __('message.only') }}</span>

                                    @foreach(config('constant.IMAGE_EXTENTIONS') as $extention)
                                        <a href="javascript:void();">.{{  __('message.'.$extention) }}</a>
                                    @endforeach
                                    <span>{{ __('message.allowed') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">{{ __('message.status') }}</label>
                            <div class="grid" style="--bs-gap: 1rem">
                                <div class="form-check g-col-6">
                                    {{ Form::radio('status', 'active', old('status') || true, ['class' => 'form-check-input', 'id' => 'status-active' ]) }}
                                    {{ Form::label('status-active', __('message.active'), ['class' => 'form-check-label' ]) }}
                                </div>
                                <div class="form-check g-col-6">
                                    {{ Form::radio('status', 'inactive', old('status') , ['class' => 'form-check-input', 'id' => 'status-inactive' ]) }}
                                    {{ Form::label('status-inactive', __('message.inactive'), ['class' => 'form-check-label' ]) }}
                                </div>
                                <div class="form-check g-col-6">
                                    {{ Form::radio('status', 'pending', old('status') , ['class' => 'form-check-input', 'id' => 'status-pending' ]) }}
                                    {{ Form::label('status-pending', __('message.pending'), ['class' => 'form-check-label' ]) }}
                                </div>
                                <div class="form-check g-col-6">
                                    {{ Form::radio('status', 'banned', old('status') , ['class' => 'form-check-input', 'id' => 'status-banned' ]) }}
                                    {{ Form::label('status-banned', __('message.banned'), ['class' => 'form-check-label' ]) }}
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            {{ Form::label('role', __('message.role').' <span class="text-danger">*</span>',[ 'class' => 'form-control-label' ], false) }} 
                            {{ Form::select('user_type', $roles, old('user_type'), [
                                    'class' => 'select2js form-group role',
                                    'data-placeholder' => __('message.select_name',[ 'select' => __('message.role') ]),
                                    'required'
                                ])
                            }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-9 col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <div class="header-title">
                            <h4 class="card-title">{{ $pageTitle }} {{ __('message.information') }}</h4>
                        </div>
                        <div class="card-action">
                            <a href="{{ route('users.index') }} " class="btn btn-sm btn-primary" role="button">{{ __('message.back') }}</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="new-user-info">
                            <div class="row">
                                <div class="form-group col-md-6">
                                    {{ Form::label('first_name',__('message.first_name').' <span class="text-danger">*</span>',['class'=>'form-control-label'], false ) }}
                                    {{ Form::text('first_name',old('first_name'),['placeholder' => __('message.first_name'),'class' =>'form-control','required']) }}
                                </div>

                                <div class="form-group col-md-6">
                                    {{ Form::label('last_name',__('message.last_name').' <span class="text-danger">*</span>',['class'=>'form-control-label'], false ) }}
                                    {{ Form::text('last_name',old('last_name'),['placeholder' => __('message.last_name'),'class' =>'form-control','required']) }}
                                </div>
                                
                                <div class="form-group col-md-6">
                                    {{ Form::label('email',__('message.email').' <span class="text-danger">*</span>',['class'=>'form-control-label'], false ) }}
                                    {{ Form::email('email', old('email'), [ 'placeholder' => __('message.email'), 'class' => 'form-control', 'required' ]) }}
                                </div>

                                <div class="form-group col-md-6">
                                    {{ Form::label('username',__('message.username').' <span class="text-danger">*</span>',['class'=>'form-control-label'], false ) }}
                                    {{ Form::text('username', old('username'), ['class' => 'form-control', 'required', 'placeholder' => __('message.username') ]) }}
                                </div>

                                @if(isset($id))
                                    <div class="form-group col-md-6">
                                        {{ Form::label('password',__('message.password'),['class'=>'form-control-label'], false ) }}
                                        {{ Form::password('password', ['class' => 'form-control', 'placeholder' =>  __('message.password') ]) }}
                                    </div>
                                @endif
                                @if(!isset($id))
                                    <div class="form-group col-md-6">
                                        {{ Form::label('password',__('message.password').' <span class="text-danger">*</span>',['class'=>'form-control-label'], false ) }}
                                        {{ Form::password('password', ['class' => 'form-control', 'placeholder' =>  __('message.password') ]) }}
                                    </div>
                                @endif

                                <div class="form-group col-md-6">
                                    {{ Form::label('phone_number',__('message.phone_number'),['class'=>'form-control-label'] ) }}
                                    {{ Form::text('phone_number', old('phone_number'), [ 'placeholder' => __('message.phone_number'), 'class' => 'form-control']) }}
                                </div>

                                <div class="form-group col-md-6">
                                    {{ Form::label('gender',__('message.gender').' <span class="text-danger">*</span>',['class'=>'form-control-label'],false) }}
                                    {{ Form::select('gender',[ 'male' => __('message.male') ,'female' => __('message.female') , 'other' => __('message.other') ], old('gender') ,[ 'class' =>'form-control select2js','required']) }}
                                </div>

                                <div class="form-group col-md-6">
                                    {{ Form::label('age',__('message.age').' <span class="text-danger">*</span>',['class'=>'form-control-label'], false ) }}
                                    {{ Form::text('user_profile[age]',old('age'),['placeholder' => __('message.age'),'class' =>'form-control','required']) }}
                                </div>
                                <div class="form-group col-md-6">
                                    {{ Form::label('weight_unit', __('message.weight_unit').' <span class="text-danger">*</span>',[ 'class' => 'form-control-label' ], false) }} 
                                    {{ Form::select('user_profile[weight_unit]',[ 'lbs' => 'lbs' ,'kg' => 'kg' ], old('user_profile[weight_unit]') ,[ 'class' =>'form-control select2js','required']) }}
                                </div>
                                <div class="form-group col-md-6">
                                    {{ Form::label('weight',__('message.weight').' <span class="text-danger">*</span>',['class'=>'form-control-label'], false ) }}
                                    {{ Form::text('user_profile[weight]',old('user_profile[weight]'),['placeholder' => __('message.weight'),'class' =>'form-control','required']) }}
                                </div>
                                <div class="form-group col-md-6">
                                    {{ Form::label('height_unit', __('message.height_unit').' <span class="text-danger">*</span>',[ 'class' => 'form-control-label' ], false) }} 
                                    {{ Form::select('user_profile[height_unit]',[ 'feet' => 'feet' ,'cm' => 'cm' ], old('user_profile[height_unit]') ,[ 'class' =>'form-control select2js','required']) }}
                                </div>
                                <div class="form-group col-md-6">
                                    {{ Form::label('height',__('message.height').' <span class="text-danger">*</span>',['class'=>'form-control-label'], false ) }}
                                    {{ Form::text('user_profile[height]',old('user_profile[height]'),['placeholder' => __('message.height'),'class' =>'form-control','required']) }}
                                </div>

                                
                                <div class="form-group col-md-6">
                                    {{ Form::label('goal',__('message.goal').' <span class="text-danger">*</span>',['class'=>'form-control-label'], false ) }}
                                    {{ Form::text('user_profile[goal]',old('user_profile[goal]'),['placeholder' => __('message.goal'),'class' =>'form-control','required']) }}
                                </div>
                                <div class="form-group col-md-6">
                                    {{ Form::label('workout_mode', __('message.workout_mode').' <span class="text-danger">*</span>',[ 'class' => 'form-control-label' ], false) }} 
                                    {{ Form::select('user_profile[workout_mode]',[ 
                                        'gym' => 'Gym workout' ,
                                        'home' => 'Home exercise',
                                        // 'no_equipment' => 'No equipment exercise'
                                        ], old('user_profile[workout_mode]') ,[ 'class' =>'form-control select2js','required']) }}
                                </div>
                                <div class="form-group col-md-6">
                                    {{ Form::label('workout_level', __('message.workout_level').' <span class="text-danger">*</span>',[ 'class' => 'form-control-label' ], false) }} 
                                    {{ Form::select('user_profile[workout_level]',[ 
                                        'beginner' => 'Beginner' ,
                                        'intermediate' => 'Intermediate',
                                        'advance' => 'Advance'
                                        ], old('user_profile[workout_level]') ,[ 'class' =>'form-control select2js','required']) }}
                                </div>

                                {{-- <div class="form-group col-md-6">
                                    {{ Form::label('workout_days',__('message.workout_days').' <span class="text-danger">*</span>',['class'=>'form-control-label'], false ) }}
                                    {{ Form::number('user_profile[workout_days]',old('user_profile[workout_days]'),['placeholder' => __('message.workout_days'),'class' =>'form-control','required']) }}
                                </div> --}}

                                <div class="form-group col-md-6">
                                    {{ Form::label('workout_days', __('message.workout_days').' <span class="text-danger">*</span>',[ 'class' => 'form-control-label' ], false) }} 
                                    {{ Form::select('user_profile[workout_days][]',[ 
                                        'monday' => 'Monday' ,
                                        'tuesday' => 'Tuesday',
                                        'wednesday' => 'Wednesday',
                                        'thursday' => 'Thursday',
                                        'friday' => 'Friday',
                                        'saturday' => 'Saturday',
                                        'sunday' => 'Sunday'
                                        ], 
                                        old('user_profile[workout_days][]') ,
                                        [ 
                                            'class' =>'form-control workout_days select2js','required',
                                            'data-placeholder' => __('message.select_name',[ 'select' => __('message.workout_days') ]),
                                            'multiple' => true,
                                        ],
                                        
                                        ) }}
                                </div>

                                {{-- <div class="form-group col-md-6">
                                    {{ Form::label('workout_time', __('message.workout_time').' <span class="text-danger">*</span>',[ 'class' => 'form-control-label' ], false) }} 
                                    {{ Form::select('user_profile[workout_time]',[ 
                                        '15' => '15 Min' ,
                                        '30' => '30 Min',
                                        '45' => '45 Min',
                                        '60' => '1 Hours',
                                        '120' => 'Above 1 hour'
                                        ], old('user_profile[workout_time]') ,[ 'class' =>'form-control select2js','required']) }}
                                </div> --}}
                                <div class="form-group col-md-6">
                                    {{ Form::label('equipment_ids', __('message.equipment'),[ 'class' => 'form-control-label' ]) }}
                                    <a id="equipment_clear" class="float-end" href="javascript:void(0)">{{ __('message.l_clear') }}</a>
                                    {{ Form::select('user_profile[equipment_ids][]', $selected_equipment ?? null, old('equipment_ids'), [
                                            'class' => 'select2js form-group equipment_ids w-100',
                                            'data-placeholder' => __('message.select_name',[ 'select' => __('message.equipment') ]),
                                            'data-ajax--url' => route('ajax-list', ['type' => 'equipment']),
                                            'multiple' => true,
                                        ])
                                    }}
                                </div>
                                
                                <div class="form-group col-md-6">
                                    {{ Form::label('has_injury', __('message.injury').' <span class="text-danger">*</span>',[ 'class' => 'form-control-label' ], false) }} 
                                    {{ Form::select('user_profile[has_injury]',[ 
                                        0 => 'No' ,
                                        1 => 'Yes'
                                        ], old('user_profile[has_injury]') ,
                                        [ 
                                            'class' =>'form-control select2js has_injury',
                                            'required',
                                            'onchange'=>'changeInjuryInfo()',
                                        ]) }}
                                </div>
                                
                                <div class="form-group col-md-6 injury_info d-none">
                                    {{ Form::label('injury_ids', __('message.injury_type'),[ 'class' => 'form-control-label' ]) }}
                                    <a id="injury_clear" class="float-end" href="javascript:void(0)">{{ __('message.l_clear') }}</a>
                                    {{ Form::select('user_profile[injury_ids][]', $selected_injury ?? null, old('injury_ids'), [
                                            'class' => 'select2js form-group injury_ids',
                                            'data-placeholder' => __('message.select_name',[ 'select' => __('message.injury_type') ]),
                                            'data-ajax--url' => route('ajax-list', ['type' => 'injury']),
                                            'multiple' => true,
                                        ])
                                    }}
                                </div>
                                <div class="form-group col-md-6 injury_info d-none">
                                    {{ Form::label('injury_info',__('message.injury_info').' <span class="text-danger">*</span>',['class'=>'form-control-label'], false ) }}
                                    {{ Form::text('user_profile[injury_info]',old('user_profile[injury_info]'),['placeholder' => __('message.injury_info'),'class' =>'form-control']) }}
                                </div>

                            </div>
                            <hr>
                            {{ Form::submit( __('message.save'), ['class'=>'btn btn-md btn-primary float-end']) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {!! Form::close() !!}
    </div>
</x-app-layout>
