@push('scripts')
<script>

    (function ($) {
        $(function () {
            // 1. TinyMCE Initialization
            tinymceEditor('.tinymce-instruction', ' ', function (ed) {}, 450);
            tinymceEditor('.tinymce-tips', ' ', function (ed) {}, 450);

            // 2. Select2 Initialization
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
                            <div class="form-group col-md-4">
                                {{ Form::label('title', __('message.title').' *',[ 'class' => 'form-control-label' ], false ) }}
                                {{ Form::text('title', old('title'),[ 'placeholder' => __('message.title'),'class' =>'form-control','required']) }}
                            </div>
                            
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
                            
                             <div class="form-group col-md-4">
                                <label class="form-control-label" for="primary_video">Gif Video</label>
                                <div><input class="form-control" type="file" name="primary_video" accept="video/*" id="primary_video" /></div>
                            </div>
                            
                                @php
                                    $gifMaster = $data->exercise_gif_hls_master_url ?? null;
                                    $gifPoster = $data->exercise_gif_poster_url ?? null;
                                @endphp
                                @if(!empty($gifMaster) || !empty($data->exercise_gif))
                                    <div class="mt-2">
                                        <label class="text-muted small d-block mb-1">Current Gif Video:</label>
                                        <a href="{{ cloudfrontUrl($gifMaster ?: $data->exercise_gif) }}" target="_blank" rel="noopener">
                                            View current gif video
                                        </a>
                                        @if(!empty($gifPoster))
                                            <div class="mt-2">
                                                <img width="320" height="240" src="{{ cloudfrontUrl($gifPoster) }}">
                                            </div>
                                        @endif
                                    </div>
                                @endif
                                

                            <div class="form-group col-md-4">
                                <label class="form-control-label" for="image">{{ __('message.image') }} </label>
                                <div><input class="form-control file-input" type="file" name="exercise_image" accept="image/*"></div>
                            </div>
                            
                            @if($is_update && getMediaFileExit($data, 'exercise_image'))
                            <div class="col-md-2 mb-2 position-relative">
                                <img id="exercise_image_preview" src="{{ cloudfrontUrl($data->exercise_image) }}" alt="exercise-image" class="avatar-100 mt-1">
                                {!! $file_remove_macro($data->id, 'exercise_image', __('message.image')) !!}
                            </div>
                            @endif
                            
                            
                        </div>
                        
                          @if(!empty($data->exercise_image))
                            <div class="mt-2">
                                <label class="text-muted small d-block mb-1">thumbnail:</label>
                                    <img width="320" height="240" src="{{ cloudfrontUrl($data->exercise_image) }}">
                            </div>
                        @endif

                        <hr>

                        <div class="form-group col-md-12">
                            {{ Form::label('instruction',__('message.instruction'), ['class' => 'form-control-label']) }}
                            {{ Form::textarea('instruction', null, ['class'=> 'form-control tinymce-instruction' , 'placeholder'=> __('message.instruction') ]) }}
                        </div>
                        <div class="form-group col-md-12">
                            {{ Form::label('tips',__('message.tips'), ['class' => 'form-control-label']) }}
                            {{ Form::textarea('tips', null, ['class'=> 'form-control tinymce-tips' , 'placeholder'=> __('message.tips') ]) }}
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
