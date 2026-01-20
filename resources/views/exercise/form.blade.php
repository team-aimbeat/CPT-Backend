@push('scripts')
<script>

    (function ($) {
        $(function () {
            // 1. TinyMCE Initialization
            tinymceEditor('.tinymce-instruction', ' ', function (ed) {}, 450);
            tinymceEditor('.tinymce-tips', ' ', function (ed) {}, 450);

            // 2. Video Type and File Upload Toggle Logic
            const videoTypeSelect = $('select[name=video_type]');
            changeUploadFile(videoTypeSelect.val());
            videoTypeSelect.on('change', function () {
                changeUploadFile(this.value);
            });

            // 3. Select2 Initialization
            $(".select2tagsjs").select2({ width: "100%", tags: true });

            // 4. Sets Table - Add Row Logic
            let rowCounter = {{ isset($id) && $data->sets ? count($data->sets) : 0 }};
            $('#add_button').on('click', function () {
                const trLast = $('#table_list').find("tbody tr:last");
                const trNew = trLast.clone(true); 

                rowCounter++;
                trNew.attr({
                    'id': 'row_' + rowCounter,
                    'data-id': 0,
                    'row': rowCounter
                });
                trNew.find('[type="hidden"]').val(0).attr('data-id', 0);
                trNew.find('.removebtn').attr({
                    'id': "remove_" + rowCounter,
                    'row': rowCounter
                });
                trNew.find('input[type="number"]').val(''); // Clear inputs

                trLast.after(trNew);
            });

            // 5. Sets Table - Remove Row Logic
            $(document).on('click', '.removebtn', function () {
                const currentRow = $(this).attr('row');
                const totalRows = $('#table_list tbody tr').length;

                if (!confirm("{{ __('message.delete_msg') }}")) return false;

                if (totalRows === 1) {
                    $('#add_button').trigger('click'); // Add new row if deleting the last one
                }

                $('#row_' + currentRow).remove();
            });

            // 6. Tab Logic (Sets/Duration)
            const initialType = "{{ $id ?? false ? $data->type : 'sets' }}";
            changeTabValue(initialType);
            
            $('#exercise-pills-tab').on('show.bs.tab', function (e) { 
                changeTabValue($(e.target).attr('data-type'));
            });

            // 7. Clear Buttons Logic
            $(document).on('click', '#equipment_clear', function () {
                $('.equipment').val(null).trigger('change');
                clearDurationSet(); // Clears both duration and sets
            });
            
            $(document).on('click', '#sets_clear', function () {
                clearDurationSet('set');
            });

            $(document).on('click', '#duration_clear', function () {
                clearDurationSet('duration');
            });
        });

        // ** Global Utility Functions **

        function changeTabValue(type) {
            $('input[name=type]').val(type);
        }
        
        function changeUploadFile(type) {
            const isUrl = (type === 'url');
            $('.video_url').toggleClass('d-none', !isUrl);
            $('.video_upload').toggleClass('d-none', isUrl);
        }

        // Initialize Flatpickr for timepicker
        if ($('.timepicker').length) {
            flatpickr('.timepicker', {
                enableTime: true,
                noCalendar: true,
                dateFormat: "H:i"
            });
        }

        function clearDurationSet(type = null) {
            switch (type) {
                case 'duration':
                    $('#hours, #minute, #second').val(null).trigger('change');
                    break;
                case 'set':
                    $('.normal_row').find('input[type="number"]').val('');
                    break;
                default:
                    $('#hours, #minute, #second').val(null).trigger('change');
                    $('.normal_row').find('input[type="number"]').val('');
                    break;
            }
        }
    })(jQuery);
</script>
@endpush

<x-app-layout :assets="$assets ?? []">
    <div>
        {{-- PHP Variable Setup --}}
        @php
            $id = $id ?? null;
            $is_update = isset($id);
            $form_route = $is_update ? ['exercise.update', $id] : ['exercise.store'];
            $form_method = $is_update ? 'patch' : 'post';
            $data = $data ?? new stdClass();
            
            $duration = $is_update && $data->duration ? explode(':', $data->duration) : [null, null, null];
            $type = $is_update && $data->type ? $data->type : 'sets';
            
            $sets_data = $is_update && $data->sets && count($data->sets) > 0 ? $data->sets : [[
                'set' => null,'reps' => null, 'weight' => null,'note' => null
            ]];
            $based_value = old('based', $is_update ? ($data->based ?? 'reps') : 'reps');

            // File removal macro data setup
            $file_remove_macro = function($file_id, $file_type, $file_name) {
                return "<a class='text-danger remove-file'
                    href='".route('remove.file', ['id' => $file_id, 'type' => $file_type])."'
                    data--submit='confirm_form' data--confirmation='true' data--ajax='true'
                    data-toggle='tooltip'
                    title='".__("message.remove_file_title" , ["name" =>  $file_name ])."'
                    data-title='".__("message.remove_file_title" , ["name" =>  $file_name ])."'
                    data-message='".__("message.remove_file_msg")."'>
                    <svg width='20' viewBox='0 0 24 24' fill='none' xmlns='http://www.w3.org/2000/svg'><path opacity='0.4' d='M16.34 1.99976H7.67C4.28 1.99976 2 4.37976 2 7.91976V16.0898C2 19.6198 4.28 21.9998 7.67 21.9998H16.34C19.73 21.9998 22 19.6198 22 16.0898V7.91976C22 4.37976 19.73 1.99976 16.34 1.99976Z' fill='currentColor'></path><path d='M15.0158 13.7703L13.2368 11.9923L15.0148 10.2143C15.3568 9.87326 15.3568 9.31826 15.0148 8.97726C14.6728 8.63326 14.1198 8.63426 13.7778 8.97626L11.9988 10.7543L10.2198 8.97426C9.87782 8.63226 9.32382 8.63426 8.98182 8.97426C8.64082 9.31626 8.64082 9.87126 8.98182 10.2123L10.7618 11.9923L8.98582 13.7673C8.64382 14.1093 8.64382 14.6643 8.98582 15.0043C9.15682 15.1763 9.37982 15.2613 9.60382 15.2613C9.82882 15.2613 10.0518 15.1763 10.2228 15.0053L11.9988 13.2293L13.7788 15.0083C13.9498 15.1793 14.1728 15.2643 14.3968 15.2643C14.6208 15.2643 14.8448 15.1783 15.0158 15.0083C15.3578 14.6663 15.3578 14.1123 15.0158 13.7703Z' fill='currentColor'></path></svg>
                </a>";
            };
        @endphp

        {{-- Form Open/Model Logic --}}
        @if($is_update)
            {!! Form::model($data, [ 'route' => $form_route, 'method' => $form_method, 'enctype' => 'multipart/form-data' ]) !!}
        @else
            {!! Form::open(['route' => $form_route, 'method' => $form_method, 'enctype' => 'multipart/form-data' ]) !!}
        @endif
        {{ Form::hidden('type', $data->type ?? null ) }}

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <div class="header-title"><h4 class="card-title">{{ $pageTitle }}</h4></div>
                        <div class="card-action">
                            <a href="{{ route('exercise.index') }}" class="btn btn-sm btn-primary" role="button">{{ __('message.back') }}</a>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            {{-- Row 1: Basic Details --}}
                            <div class="form-group col-md-4">
                                {{ Form::label('title', __('message.title').' *',[ 'class' => 'form-control-label' ], false ) }}
                                {{ Form::text('title', old('title'),[ 'placeholder' => __('message.title'),'class' =>'form-control','required']) }}
                            </div>
                            
                            
                            <div class="form-group col-md-4">
                                {{ Form::label('equipment_id', __('message.equipment'),[ 'class' => 'form-control-label' ]) }}
                                <a id="equipment_clear" class="float-end" href="javascript:void(0)">{{ __('message.l_clear') }}</a>
                                {{ Form::select('equipment_id', $is_update && $data->equipment ? [ $data->equipment->id => $data->equipment->title ] : [], old('equipment_id'), [
                                        'class' => 'select2js form-group equipment',
                                        'data-placeholder' => __('message.select_name',['select' => __('message.equipment')]),
                                        'data-ajax--url' => route('ajax-list', ['type' => 'equipment'])
                                    ])
                                }}
                            </div>
                           
                            
                             {{-- Row 2: alternate exercise --}}
                            <div class="form-group col-md-4">
                                {{ Form::label('alternate_exercise_id', __('message.alternate_exercise'), [ 'class' => 'form-control-label' ]) }}
                                <a id="alternate_exercise_clear" class="float-end" href="javascript:void(0)">{{ __('message.l_clear') }}</a>
                              {{ Form::select(
                                'exercise_id',
                                $is_update && $data->alternate_exercise 
                                    ? [ $data->alternate_exercise->id => $data->alternate_exercise->title ] 
                                    : [],
                                old('exercise_id', $is_update ? $data->alternate_exercise_id : null), 
                                [
                                    'class' => 'select2js form-group alternate-exercise',
                                    'data-placeholder' => __('message.select_name',['select' => __('message.alternate_exercise')]),
                                    'data-ajax--url' => route('ajax-list', ['type' => 'exercise'])
                                ]
                            ) }}
                            </div>


                            
                            <div class="form-group col-md-4">
                                {{ Form::label('status',__('message.status').' *',['class'=>'form-control-label'],false) }}
                                {{ Form::select('status',[ 'active' => __('message.active'), 'inactive' => __('message.inactive') ], old('status'), [ 'class' => 'form-control select2js','required']) }}
                            </div>
                            <div class="form-group col-md-4">
                                {{ Form::label('is_premium', __('message.is_premium'), ['class' => 'form-control-label']) }}
                                <div class="mt-2">
                                    {!! Form::hidden('is_premium',0, null, ['class' => 'form-check-input' ]) !!}
                                    {!! Form::checkbox('is_premium',1, null, ['class' => 'form-check-input' ]) !!}
                                    <label class="custom-control-label" for="is_premium"></label>
                                </div>
                            </div>

                            {{-- Row 3: Video Type and Input Fields --}}
                            <div class="form-group col-md-4">
                                {{ Form::label('video_type', __('message.video_type').' *',['class'=>'form-control-label'], false) }}
                                {{ Form::select('video_type', [ 'url' => __('message.url'), 'upload_video' => __('message.upload_video') ], old('video_type'), [ 'class' => 'form-control select2js video_type','required']) }}
                            </div>
                            
                            <!--<div class="form-group col-md-4 video_upload d-none">-->
                            <!--    <label class="form-control-label" for="exercise_video">Video url</label>-->
                            <!--    <div><input class="form-control file-input" type="file" name="exercise_video" accept="video/*" id="exercise_video" /></div>-->
                            <!--</div>-->
                            
                             <div class="form-group col-md-4 video_upload d-none">
                                <label class="form-control-label" for="exercise_video">Video url</label>
                                <div><input class="form-control file-input" type="text" name="exercise_video"  id="exercise_video" /></div>
                            </div>
                            
                            
                             @if(!empty($data->video_url))
                                    <div class="mt-2">
                                        <label class="text-muted small d-block mb-1">Current Primary Video:</label>
                                        <div class="embed-responsive embed-responsive-16by9">
                                    <iframe 
                                        class="embed-responsive-item" 
                                        src="https://www.youtube.com/embed/{{ $data->video_url }}?rel=0&modestbranding=1&controls=0&loop=1&playlist={{ $data->video_url }}" 
                                        frameborder="0"
                                        allow="autoplay; encrypted-media" 
                                        allowfullscreen>
                                    </iframe>
                                    </div>
                                    <a href="https://www.youtube.com/watch?v={{ $data->video_url }}" target="_blank" class="d-block mt-2 text-primary">
                                        View on YouTube
                                    </a>
                                    </div>
                                @endif
                            
                             <!--@if(!empty($data->video_url))-->
                             <!--       <div class="mt-2">-->
                             <!--           <label class="text-muted small d-block mb-1">Current Primary Video:</label>-->
                             <!--           <video width="320" height="240" controls>-->
                             <!--               <source src="{{ asset('storage/' . $data->video_url) }}" type="video/mp4">-->
                             <!--               Your browser does not support the video tag.-->
                             <!--           </video>-->
                             <!--           <a href="{{ asset('storage/' . $data->exercise_gif) }}" target="_blank" class="d-block mt-2 text-primary">-->
                             <!--               View / Download Video-->
                             <!--           </a>-->
                             <!--       </div>-->
                             <!--   @endif-->

                            
                            
                             <div class="form-group col-md-4">
                                <label class="form-control-label" for="primary_video">Primary Video</label>
                                <div><input class="form-control" type="file" name="primary_video" accept="image/*" id="primary_video" /></div>
                            </div>
                            
                            {{-- ✅ Show existing uploaded video if available --}}
                            
                                   @if(!empty($data->exercise_gif))
                                    <div class="mt-2">
                                        <label class="text-muted small d-block mb-1">Current Gif Video:</label>
                                        
                                         <img width="320" height="240" src="{{ asset('storage/' . $data->exercise_gif) }}">
                                        <!--<video width="320" height="240" controls>-->
                                        <!--    <source src="{{ asset('storage/' . $data->exercise_gif) }}" type="video/mp4">-->
                                        <!--    Your browser does not support the video tag.-->
                                        <!--</video>-->
                                        <a href="{{ asset('storage/' . $data->exercise_gif) }}" target="_blank" class="d-block mt-2 text-primary">
                                            View / Download Video
                                        </a>
                                    </div>
                                @endif
                                
                                
                                
                            

                            {{-- Row 4: Existing Video/Image Display --}}
                            @if($is_update && getMediaFileExit($data, 'exercise_video'))
                            <div class="col-md-2 mb-2 position-relative">
                                @php
                                    $image = getSingleMedia($data, 'exercise_video');
                                    $is_image = in_array(strtolower(imageExtention($image)), config('constant.IMAGE_EXTENTIONS'));
                                @endphp
                                @if($is_image)
                                <img id="exercise_video_preview" src="{{ $image}}" alt="equipment-video" class="avatar-100 mt-1" />
                                @else
                                    <img id="exercise_video_preview" src="{{ asset('images/file.png') }}" class="avatar-100" />
                                    <a href="{{ $image }}" download>{{ __('message.download') }}</a>
                                @endif
                                {!! $file_remove_macro($data->id, 'exercise_video', __('message.video')) !!}
                            </div>
                            @endif

                            <div class="form-group col-md-4">
                                <label class="form-control-label" for="image">{{ __('message.image') }} </label>
                                <div><input class="form-control file-input" type="file" name="exercise_image" accept="image/*"></div>
                            </div>
                            
                            @if($is_update && getMediaFileExit($data, 'exercise_image'))
                            <div class="col-md-2 mb-2 position-relative">
                                <img id="exercise_image_preview" src="{{ getSingleMedia($data,'exercise_image') }}" alt="exercise-image" class="avatar-100 mt-1">
                                {!! $file_remove_macro($data->id, 'exercise_image', __('message.image')) !!}
                            </div>
                            @endif
                            
                            
                        </div>
                        
                          @if(!empty($data->exercise_image))
                            <div class="mt-2">
                                <label class="text-muted small d-block mb-1">thumbnail:</label>
                              
                                    <img width="320" height="240" src="{{ asset('storage/' . $data->exercise_image) }}">
                                   
                               
                                <a href="{{ asset('storage/' . $data->exercise_image) }}" target="_blank" class="d-block mt-2 text-primary">
                                    View / Download Video
                                </a>
                            </div>
                        @endif

                        <!--<h5 class="text-danger mt-3"> <i><u>{{ __('message.notes')}}:</u></i> {{ __('message.exercise_info') }}</h5>-->
                        <hr>
                        
                        {{-- ** Sets/Duration Tab Logic ** --}}
                        <!--<ul class="nav nav-pills nav-fill mb-3 text-center exercise-tab" id="exercise-pills-tab" role="tablist">-->
                        <!--    <li class="nav-item" role="presentation">-->
                        <!--        <a class="nav-link {{ $type == 'sets' ? 'active show' : '' }}" data-bs-toggle="tab" href="#exercise-sets" data-type="sets" role="tab">{{ __('message.sets') }}</a>-->
                        <!--    </li>-->
                        <!--    <li class="nav-item" role="presentation">-->
                        <!--        <a class="nav-link {{ $type == 'duration' ? 'active show' : '' }}" data-bs-toggle="tab" href="#exercise-duration" data-type="duration" role="tab" tabindex="-1">{{ __('message.duration') }}</a>-->
                        <!--    </li>-->
                        <!--</ul>-->

                        <!--<div class="exercise-content tab-content">-->
                        <!--    {{-- Sets Tab Content --}}-->
                        <!--    <div id="exercise-sets" class="tab-pane fade {{ $type == 'sets' ? 'active show' : '' }}" role="tabpanel">-->
                        <!--        <div class="row normal_row">-->
                        <!--            <div class="col-md-4">-->
                        <!--                <h5 class="mb-3">{{__('message.sets')}} -->
                        <!--                    <span class="text-danger" data-bs-toggle="tooltip" title="{{ __('message.exercise_sets_based_info') }}">-->
                        <!--                        <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M16.334 2.75H7.665C4.644 2.75 2.75 4.889 2.75 7.916V16.084C2.75 19.111 4.635 21.25 7.665 21.25H16.333C19.364 21.25 21.25 19.111 21.25 16.084V7.916C21.25 4.889 19.364 2.75 16.334 2.75Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M11.9946 16V12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M11.9896 8.2041H11.9996" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>-->
                        <!--                    </span>-->
                        <!--                </h5>-->
                        <!--            </div>-->
                        <!--            <div class="col-md-4">-->
                        <!--                <div class="form-check">-->
                        <!--                    <div class="custom-control custom-radio d-inline-block col-4">-->
                        <!--                        <label class="form-check-label" for="based-reps"> {{__('message.reps')}}(x)</label>-->
                        <!--                        {{ Form::radio('based', 'reps', $based_value == 'reps', [ 'class' => 'form-check-input', 'id' => 'based-reps']) }}-->
                        <!--                    </div>-->
                        <!--                    <div class="custom-control custom-radio d-inline-block col-4">-->
                        <!--                        <label class="form-check-label" for="based-time"> {{__('message.time')}}(s)</label>-->
                        <!--                        {{ Form::radio('based', 'time', $based_value == 'time', [ 'class' => 'form-check-input', 'id' => 'based-time']) }}-->
                        <!--                    </div>-->
                        <!--                </div>-->
                        <!--            </div>-->
                        <!--            <div class="col-md-4">-->
                        <!--                <button type="button" id="add_button" class="btn btn-sm btn-primary float-end me-2">{{ __('message.add',['name' => '']) }}</button>-->
                        <!--                <a id="sets_clear" class="float-end me-2" href="javascript:void(0)" title="{{ __('message.clear_sets') }}">{{ __('message.l_clear') }}</a> -->
                        <!--            </div>-->
                        <!--            <div class="col-md-12">-->
                        <!--                <table id="table_list" class="table table-responsive">-->
                        <!--                    <thead>-->
                        <!--                        <tr>-->
                        <!--                             <th class="col-md-3">{{ __('message.set') }}<span>(s)</span></th>-->
                        <!--                            <th class="col-md-3">{{ __('message.reps') }}<span>(x)</span></th>-->
                                                   
                        <!--                            <th class="col-md-3 weight">{{ __('message.weight') }}<span>(kg)</span></th>-->
                                                   
                        <!--                             <th class="col-md-3">{{ __('message.note') }}<span>(s)</span></th>-->
                        <!--                            <th class="col-md-1">{{ __('message.action') }}</th>-->
                        <!--                        </tr>-->
                        <!--                    </thead>-->
                        <!--                    <tbody>-->
                        <!--                        @foreach($sets_data as $key => $field)-->
                        <!--                        <tr id="row_{{ $key }}" row="{{ $key }}" data-id="{{ $key }}">-->
                        <!--                             <td><div class="form-group">{{ Form::text('set[]', $field['set'] ?? null,[ 'placeholder' => __('message.set'), 'class' =>'form-control']) }}</div></td>-->
                        <!--                            <td><div class="form-group">{{ Form::text('reps[]', $field['reps'] ?? null,[ 'placeholder' => __('message.reps'), 'class' =>'form-control', 'min' => 0 ]) }}</div></td>-->
                                       
                        <!--                            <td class="weight"><div class="form-group">{{ Form::text('weight[]', $field['weight'] ?? null,[ 'placeholder' => __('message.weight'), 'class' =>'form-control', 'min' => 0 ]) }}</div></td>-->
                                                   
                                                    
                        <!--                            <td><div class="form-group">{{ Form::text('note[]', $field['note'] ?? null,[ 'placeholder' => __('message.note'), 'class' =>'form-control' ]) }}</div></td>-->
                        <!--                            <td>-->
                        <!--                                {{-- Set Row Remove Button --}}-->
                        <!--                                <a href="javascript:void(0)" id="remove_{{ $key }}"-->
                        <!--                                    class="removebtn btn btn-sm btn-icon btn-danger" row="{{ $key }}">-->
                        <!--                                    <span class="btn-inner">-->
                        <!--                                        <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="currentColor"><path d="M19.3248 9.46826C19.3248 9.46826 18.7818 16.2033 18.4668 19.0403C18.3168 20.3953 17.4798 21.1893 16.1088 21.2143C13.4998 21.2613 10.8878 21.2643 8.27979 21.2093C6.96079 21.1823 6.13779 20.3783 5.99079 19.0473C5.67379 16.1853 5.13379 9.46826 5.13379 9.46826" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M20.708 6.23975H3.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M17.4406 6.23973C16.6556 6.23973 15.9796 5.68473 15.8256 4.91573L15.5826 3.69973C15.4326 3.13873 14.9246 2.75073 14.3456 2.75073H10.1126C9.53358 2.75073 9.02558 3.13873 8.87558 3.69973L8.63258 4.91573C8.47858 5.68473 7.80258 6.23973 7.01758 6.23973" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>-->
                        <!--                                    </span>-->
                        <!--                                </a>-->
                        <!--                            </td>-->
                        <!--                        </tr>-->
                        <!--                        @endforeach-->
                        <!--                    </tbody>-->
                        <!--                </table>-->
                        <!--            </div>-->
                        <!--        </div>-->
                        <!--    </div>-->

                        <!--    {{-- Duration Tab Content --}}-->
                        <!--    <div id="exercise-duration" class="tab-pane fade {{ $type == 'duration' ? 'active show' : '' }}" role="tabpanel">-->
                        <!--        <div class="row duration_row">-->
                        <!--            <h5 class="mb-3 col-md-12">{{__('message.duration')}}-->
                        <!--                <a id="duration_clear" class="float-end" href="javascript:void(0)" title="{{ __('message.clear_duration') }}">{{ __('message.l_clear') }}</a> -->
                        <!--            </h5>-->
                        <!--            {{-- Time Selects --}}-->
                        <!--            <div class="form-group col-md-2">-->
                        <!--                {{ Form::label('hours',__('message.hours').' *',['class'=>'form-control-label'],false) }}-->
                        <!--                {{ Form::select('hours', $duration[0] ? [ $duration[0] => $duration[0] ] : [], old('hours', $duration[0]), [ 'class' => 'form-control select2js', 'id' => 'hours', 'data-placeholder' => __('message.select_name',['select' => __('message.hours')]), 'data-ajax--url' => route('ajax-list', ['type' => 'hours'])]) }}-->
                        <!--            </div>-->
                        <!--            <div class="form-group col-md-2">-->
                        <!--                {{ Form::label('minute',__('message.minute').' *',['class'=>'form-control-label'],false) }}-->
                        <!--                {{ Form::select('minute', $duration[1] ? [$duration[1] => $duration[1] ] : [], old('minute', $duration[1]), [ 'class' => 'form-control select2js', 'id' => 'minute', 'data-placeholder' => __('message.select_name',['select' => __('message.minute')]), 'data-ajax--url' => route('ajax-list', ['type' => 'minute'])]) }}-->
                        <!--            </div>-->
                        <!--            <div class="form-group col-md-2">-->
                        <!--                {{ Form::label('second',__('message.second').' *',['class'=>'form-control-label'],false) }}-->
                        <!--                {{ Form::select('second', $duration[2] ? [$duration[2] => $duration[2] ] : [], old('second', $duration[2]), [ 'class' => 'form-control select2js', 'id' => 'second', 'data-placeholder' => __('message.select_name',['select' => __('message.second')]), 'data-ajax--url' => route('ajax-list', ['type' => 'second'])]) }}-->
                        <!--            </div>-->
                        <!--        </div>-->
                        <!--    </div>-->
                        <!--</div>-->
                       
                        
                        {{-- Instructions and Tips (TinyMCE) --}}
                        <div class="form-group col-md-12">
                            {{ Form::label('instruction',__('message.instruction'), ['class' => 'form-control-label']) }}
                            {{ Form::textarea('instruction', null, ['class'=> 'form-control tinymce-instruction' , 'placeholder'=> __('message.instruction') ]) }}
                        </div>
                        <div class="form-group col-md-12">
                            {{ Form::label('tips',__('message.tips'), ['class' => 'form-control-label']) }}
                            {{ Form::textarea('tips', null, ['class'=> 'form-control tinymce-tips' , 'placeholder'=> __('message.tips') ]) }}
                        </div>                        
                        <hr>
                        
                        {{-- Submit Button --}}
                        {{ Form::submit( __('message.save'), ['class'=>'btn btn-md btn-primary float-end']) }}
                    </div>
                </div>
            </div>
        </div>
        {!! Form::close() !!}
    </div>
</x-app-layout>