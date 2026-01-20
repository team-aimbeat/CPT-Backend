@push('scripts')
    <script>
           (function($) {
               $(document).ready(function()
               {
                   var resetSequenceNumbers = function() {
                       $("#table_list tbody tr").each(function(i) {
                           $(this).find('td:first').text(i + 1);
                       });
                   };
                   resetSequenceNumbers();
                   $(".select2tagsjs").select2({
                       width: "100%",
                       tags: true
                   });
                   tinymceEditor('.tinymce-description',' ',function (ed) {
   
                   }, 450)
                   var row = 0;
                   $('#add_button').on('click', function ()
                   {
                       
                       $(".select2tagsjs").select2("destroy");
                       var tableBody = $('#table_list').find("tbody");
                       var trLast = tableBody.find("tr:last");
                       
                       trLast.find(".removebtn").show().fadeIn(300);
   
                       var trNew = trLast.clone();
                       row = parseInt(trNew.attr('row')); 
                       row++;
   
                       trNew.attr('id','row_'+row).attr('data-id',0).attr('row',row);
                       trNew.find('[type="hidden"]').val(0).attr('data-id',0);
   
                      
                       trNew.find('[id^="week_"]').attr('name', "week[" + row + "]").attr('id', "week_" + row).val('1');
   
                      
                       trNew.find('[id^="day_"]').attr('name', "day[" + row + "]").attr('id', "day_" + row).val('');
   
                       trNew.find('[id^="workout_days_id_"]').attr('name',"workout_days_id["+row+"]").attr('id',"workout_days_id_"+row).val('');
                       trNew.find('[id^="exercise_ids_"]').attr('name',"exercise_ids["+row+"][]").attr('id',"exercise_ids_"+row).val('');
                       trNew.find('[id^="is_rest_no_"]').attr('name',"is_rest["+row+"]").attr('id',"is_rest_no_"+row).val('0');
                       trNew.find('[id^="is_rest_yes_"]').attr('name',"is_rest["+row+"]").attr('id',"is_rest_yes_"+row).val('1').prop('checked', false);
   
                       trNew.find('[id^="remove_"]').attr('id',"remove_"+row).attr('row',row);
   
                       trLast.after(trNew);
                       // Select2 को Re-initialize करें
                       $(".select2tagsjs").select2({
                           width: "100%",
                           tags: true
                       });
                       resetSequenceNumbers();
                   });
   
                   $(document).on('click','.removebtn', function()
                   {
                       var row = $(this).attr('row');
                       var delete_row  = $('#row_'+row);
                       // console.log(delete_row);
                       var check_exists_id = delete_row.attr('data-id');
                       var total_row = $('#table_list tbody tr').length;
                       var user_response = confirm("{{ __('message.delete_msg') }}");
                       if(!user_response) {
                           return false;
                       }
   
                       if(total_row == 1){
                           $(document).find('#add_button').trigger('click');
                       }
                       // console.log(check_exists_id);
                       if(check_exists_id != 0 ) {
                           $.ajax({
                               url: "{{ route('workoutdays.exercise.delete')}}",
                               type: 'post',
                               data: {'id': check_exists_id, '_token': $('input[name=_token]').val()},
                               dataType: 'json',
                               success: function (response) {
                                   if(response['status']) {
                                       delete_row.remove();
                                       showMessage(response.message);
                                   } else {
                                       errorMessage(response.message);
                                   }
                               }
                           });
                       } else {
                           delete_row.remove();
                       }
                       
                       resetSequenceNumbers();
                   })
   
                   var video_type = $('select[name=video_type]').val();
                   changeUploadFile(video_type);
   
                   $(".video_type").change(function () {
                       changeUploadFile(this.value)
                   });
               });
               function showMessage(message) {
                   Swal.fire({
                       icon: 'success',
                       title: "{{ __('message.done') }}",
                       text: message,
                       confirmButtonColor: "var(--bs-primary)",
                       confirmButtonText: "{{ __('message.ok') }}"
                   });
               }
   
               function errorMessage(message) {
                   Swal.fire({
                       icon: 'error',
                       title: "{{ __('message.opps') }}",
                       text: message,
                       confirmButtonColor: "var(--bs-primary)",
                       confirmButtonText: "{{ __('message.ok') }}"
                   });
               }
               function changeUploadFile(type) {
                   if (jQuery.inArray(type, ['url']) !== -1) {
                       $('.video_url').removeClass('d-none');
                       $('.video_upload').addClass('d-none');
                   } else {
                       $('.video_upload').removeClass('d-none');
                       $('.video_url').addClass('d-none');
                   }
               }
           })(jQuery);
       
</script>
@endpush
<x-app-layout :assets="$assets ?? []">
       
   <div>
              <?php $id = $id ?? null;?>
              @if(isset($id))
                  {!! Form::model($data, [ 'route' => [ 'workout.update', $id], 'method' => 'patch', 'enctype' => 'multipart/form-data' ]) !!}
              @else
                  {!! Form::open(['route' => ['workout.store'], 'method' => 'post', 'enctype' => 'multipart/form-data' ]) !!}
              @endif
              
      <div class="row">
                     
         <div class="col-lg-12">
                            
            <div class="card">
                                   
               <div class="card-header d-flex justify-content-between">
                                          
                  <div class="header-title">
                                                 
                     <h4 class="card-title">{{ $pageTitle }}</h4>
                                             
                  </div>
                                          
                  <div class="card-action">
                                                 <a href="{{ route('workout.index') }} " class="btn btn-sm btn-primary" role="button">{{ __('message.back') }}</a>
                                             
                  </div>
                                      
               </div>
                                   
               <div class="card-body">
                                          
                  <div class="row">
                                                 
                     <div class="form-group col-md-4">
                                                        {{ Form::label('title', __('message.title').' <span class="text-danger">*</span>',[ 'class' => 'form-control-label' ], false ) }}
                                                        {{ Form::text('title', old('title'),[ 'placeholder' => __('message.title'),'class' =>'form-control','required']) }}
                                                    
                     </div>
                     
                     
                     
                            
                           
                            
                            
                            <div class="form-group col-md-4">
                                                        {{ Form::label('goal_id', __('message.bodypart').' <span class="text-danger">*</span>',[ 'class' => 'form-control-label' ], false) }} 
                                                        {{ Form::select('goal_id', isset($id) ? [ optional($data->goal)->id => optional($data->goal)->title ] : [], old('goal_id'), [
                                                                'class' => 'select2js form-group bodypart',
                                                                'data-placeholder' => __('message.select_name',[ 'select' => __('message.bodypart') ]),
                                                                'data-ajax--url' => route('ajax-list', ['type' => 'bodypart']),
                                                                'required'
                                                            ])
                                                        }}
                                                    
                     </div>
                     
                     
                                                 
                     <div class="form-group col-md-4">
                                                        {{ Form::label('level_id', __('message.level').' <span class="text-danger">*</span>',[ 'class' => 'form-control-label' ], false) }} 
                                                        {{ Form::select('level_id', isset($id) ? [ optional($data->level)->id => optional($data->level)->title ] : [], old('level_id'), [
                                                                'class' => 'select2js form-group level',
                                                                'data-placeholder' => __('message.select_name',[ 'select' => __('message.level') ]),
                                                                'data-ajax--url' => route('ajax-list', ['type' => 'level']),
                                                                'required'
                                                            ])
                                                        }}
                                                    
                     </div>
                                                 
                     <div class="form-group col-md-4">
                                                        {{ Form::label('workout_type_id', __('message.workouttype').' <span class="text-danger">*</span>',[ 'class' => 'form-control-label' ], false) }} 
                                                        {{ Form::select('workout_type_id', isset($id) ? [ optional($data->workouttype)->id => optional($data->workouttype)->title ] : [], old('workout_type_id'), [
                                                                'class' => 'select2js form-group workouttype',
                                                                'data-placeholder' => __('message.select_name',[ 'select' => __('message.workouttype') ]),
                                                                'data-ajax--url' => route('ajax-list', ['type' => 'workout_type']),
                                                                'required'
                                                            ])
                                                        }}
                                                    
                     </div>
                                                 
                     <div class="form-group col-md-4">
                                                        {{ Form::label('status',__('message.status').' <span class="text-danger">*</span>',['class'=>'form-control-label'],false) }}
                                                        {{ Form::select('status',[ 'active' => __('message.active'), 'inactive' => __('message.inactive') ], old('status'), [ 'class' => 'form-control select2js', 'required']) }}
                                                    
                     </div>
                                                 
                     
                                         
                                                 
                                                 
                     <div class="form-group col-md-4">
                                                        {{ Form::label('is_premium', __('message.is_premium'), ['class' => 'form-control-label']) }}
                                                        
                        <div class="">
                                                               {!! Form::hidden('is_premium',0, null, ['class' => 'form-check-input' ]) !!}
                                                               {!! Form::checkbox('is_premium',1, null, ['class' => 'form-check-input' ]) !!}
                                                               <label class="custom-control-label" for="is_premium"></label>
                                                           
                        </div>
                                                    
                     </div>
                     
                      <div class="form-group col-md-4">
                                <label class="form-control-label" for="video_url">Warmup Video</label>
                                <div><input class="form-control" type="text" name="video_url" id="video_url" value="{{ old('video_url', $data->video_url ?? '') }}" /></div>
                            </div>
                            
                            @if(!empty($data->video_url))
                                    <div class="mt-2">
                                        <label class="text-muted small d-block mb-1">Warmup Video:</label>
                                        <div class="embed-responsive embed-responsive-16by9">
                                    <iframe 
                                        class="embed-responsive-item" 
                                        src="https://www.youtube.com/embed/{{ $data->video_url }}?rel=0&modestbranding=1&controls=0&loop=1&playlist={{ $data->video_url }}" 
                                        frameborder="0"
                                        allow="autoplay; encrypted-media" 
                                        allowfullscreen>
                                    </iframe>
                                    </div>
                                    </div>
                                @endif
                                
                                
                                
                                <div class="form-group col-md-4">
                                <label class="form-control-label" for="stetch_video">Stretching Video</label>
                                <div><input class="form-control" type="text" name="stetch_video" id="stetch_video" value="{{ old('stetch_video', $data->stetch_video ?? '') }}" /></div>
                            </div>
                            
                            
                             @if(!empty($data->stetch_video))
                                    <div class="mt-2">
                                        <label class="text-muted small d-block mb-1">Stretching Video:</label>
                                        <div class="embed-responsive embed-responsive-16by9">
                                    <iframe 
                                        class="embed-responsive-item" 
                                        src="https://www.youtube.com/embed/{{ $data->stetch_video }}?rel=0&modestbranding=1&controls=0&loop=1&playlist={{ $data->stetch_video }}" 
                                        frameborder="0"
                                        allow="autoplay; encrypted-media" 
                                        allowfullscreen>
                                    </iframe>
                                    </div>
                                    </div>
                                @endif
                     
                                                 
                     <!--<div class="form-group col-md-4">-->
                     <!--                                   {{ Form::label('video_type', __('message.video_type').' <span class="text-danger">*</span>',['class'=>'form-control-label'], false) }}-->
                     <!--                                   {{ Form::select('video_type', [ 'url' => __('message.url'), 'upload_video' => __('message.upload_video') ], old('video_type'), [ 'class' => 'form-control select2js video_type','required']) }}-->
                                                    
                     <!--</div>-->
                                                 
                     <!--<div class="form-group col-md-4 video_url">-->
                     <!--                                   {{ Form::label('video_url', __('message.video_url'), [ 'class' => 'form-control-label' ] ) }}-->
                     <!--                                   {{ Form::url('video_url', old('title'),[ 'placeholder' => __('message.video_url'), 'class' => 'form-control' ]) }}-->
                                                    
                     <!--</div>-->
                                                 
                     <!--<div class="form-group col-md-4 video_upload">-->
                     <!--                                   <label class="form-control-label" for="workout_video">{{ __('message.video') }}-->
                     <!--                                   </label>-->
                                                        
                     <!--   <div class="">-->
                     <!--                                          <input class="form-control file-input" type="file" name="workout_video"-->
                     <!--                                              accept="video/*" id="workout_video" />-->
                                                           
                     <!--   </div>-->
                                                    
                     <!--</div>-->
                                                 
                     <div class="form-group col-md-12">
                                                        {{ Form::label('description',__('message.description'), ['class' => 'form-control-label']) }}
                                                        {{ Form::textarea('description', null, ['class'=> 'form-control tinymce-description' , 'placeholder'=> __('message.description') ]) }}
                                                    
                     </div>
                                             
                  </div>
                                          
                  <hr>
                                          
                  <h5 class="mb-3">{{__('message.workout_days')}} <button type="button" id="add_button" class="btn btn-sm btn-primary float-end">{{ __('message.add',['name' => '']) }}</button></h5>
                                          
                  <div class="row">
                                                 
                     <div class="col-md-12">
                                                        
                        <table id="table_list" class="table workout_days_table table-responsive">
                                                               
                           <thead>
                                                                      
                              <tr>
                                                                             
                                 <th class="col-md-1">#</th>
                                 <th class="col-md-2">{{ __('message.week') }}</th>
                                 <th class="col-md-2">{{ __('message.day') }}</th>
                                                                             
                                 <th class="col-md-3">{{ __('message.exercise') }}</th>
                                                                             
                                 <th class="col-md-1">{{ __('message.is_rest') }}</th>
                                                                             
                                 <th class="col-md-1">{{ __('message.action') }}</th>
                                                                         
                              </tr>
                                                                  
                           </thead>
                                                               
                           <tbody>
                                                                  @if(isset($id) && count($data->workoutDay) > 0)
                                                                      @foreach($data->workoutDay as $key => $field)
                                                                          
                              <tr id="row_{{ $key }}" row="{{ $key }}" data-id="{{ $field->id }}">
                                                                                 
                                 <td></td>
                                 
                                 <td>
                                    <div class="form-group">
                                       {{-- Week 1 से 12 तक का विकल्प (डिफ़ॉल्ट 12 वीक का वर्कआउट प्लान) --}}
                                       {{ Form::select('week['.$key.']', array_combine(range(1, 12), range(1, 12)), $field->week ?? 1, [ 'class' => 'form-control', 'id' => 'week_'.$key, 'required' => 'required' ]) }}
                                    </div>
                                 </td>
                               
                                 <td>
                                    <div class="form-group">
                                       {{-- Day Select Field (0 = Sunday, 1 = Monday, etc.) --}}
                                       {{ Form::select('day['.$key.']', ['0' => 'Sunday', '1' => 'Monday', '2' => 'Tuesday', '3' => 'Wednesday', '4' => 'Thursday', '5' => 'Friday', '6' => 'Saturday'], $field->day  ?? null, [ 'class' => 'form-control', 'id' => 'day_'.$key, 'required' => 'required' ]) }}
                                    </div>
                                 </td>
                                                                                 
                                 <td>
                                                                                        
                                    <div class="form-group" id="exercise_ids_{{$key}}">
                                                                                               <input type="hidden" name="workout_days_id[{{$key}}]" class="form-control" value="{{ $field->id }}" id="workout_days_id_{{$key}}" />
                                                                                               {{ Form::select('exercise_ids['.$key.'][]', $field->exercise_data ?? [], $field->exercise_ids ?? old('exercise_ids'), [
                                                                                                       'class' => 'select2tagsjs form-group exercise',
                                                                                                       'multiple' => 'multiple',
                                                                                                       'id' => 'exercise_ids_'.$key,
                                                                                                       'data-placeholder' => __('message.select_name',[ 'select' => __('message.exercise') ]),
                                                                                                       'data-ajax--url' => route('ajax-list', ['type' => 'exercise']),
                                                                                                   ])
                                                                                               }}
                                                                                           
                                    </div>
                                                                                    
                                 </td>
                                                                                 
                                 <td>
                                                                                        
                                    <div class="form-group">
                                                                                               <input type="hidden" name="is_rest[{{$key}}]" value="0" id="is_rest_no_{{$key}}">
                                                                                               {!! Form::checkbox('is_rest['.$key.']', 1, $field->is_rest ?? null, ['class' => 'form-check-input' , 'id' => 'is_rest_yes_'.$key ]) !!}
                                                                                           
                                    </div>
                                                                                    
                                 </td>
                                                                                 
                                 <td>
                                                                                        
                                    <a href="javascript:void(0)" id="remove_{{$key}}" class="removebtn btn btn-sm btn-icon btn-danger" row="{{$key}}">
                                                                                               
                                       <span class="btn-inner">
                                                                                                      
                                          <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="currentColor">
                                                                                                             
                                             <path d="M19.3248 9.46826C19.3248 9.46826 18.7818 16.2033 18.4668 19.0403C18.3168 20.3953 17.4798 21.1893 16.1088 21.2143C13.4998 21.2613 10.8878 21.2643 8.27979 21.2093C6.96079 21.1823 6.13779 20.3783 5.99079 19.0473C5.67379 16.1853 5.13379 9.46826 5.13379 9.46826" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                                                                                             
                                             <path d="M20.708 6.23975H3.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                                                                                             
                                             <path d="M17.4406 6.23973C16.6556 6.23973 15.9796 5.68473 15.8256 4.91573L15.5826 3.69973C15.4326 3.13873 14.9246 2.75073 14.3456 2.75073H10.1126C9.53358 2.75073 9.02558 3.13873 8.87558 3.69973L8.63258 4.91573C8.47858 5.68473 7.80258 6.23973 7.01758 6.23973" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                                                                                         
                                          </svg>
                                                                                                  
                                       </span>
                                                                                           
                                    </a>
                                                                                    
                                 </td>
                                                                             
                              </tr>
                                                                      @endforeach
                                                                  @else
                                                                      
                              <tr id="row_0" row="0" data-id="0">
                                                                             
                                 <td></td>
                                 {{-- ✅ NEW: Week Select Field --}}
                                 <td>
                                    <div class="form-group">
                                       {{ Form::select('week[0]', array_combine(range(1, 12), range(1, 12)), 1, [ 'class' => 'form-control', 'id' => 'week_0', 'required' => 'required' ]) }}
                                    </div>
                                 </td>
                                 {{-- ✅ NEW: Day Select Field --}}
                                 <td>
                                    <div class="form-group">
                                       {{ Form::select('day[0]', ['0' => 'Sunday', '1' => 'Monday', '2' => 'Tuesday', '3' => 'Wednesday', '4' => 'Thursday', '5' => 'Friday', '6' => 'Saturday'], null, [ 'class' => 'form-control', 'id' => 'day_0', 'required' => 'required' ]) }}
                                    </div>
                                 </td>
                                                                             
                                 <td>
                                                                                    
                                    <div class="form-group" id="exercise_ids_0">
                                                                                           <input type="hidden" name="workout_days_id[0]" class="form-control" value="0" id="workout_days_id_0" />
                                                                                           {{ Form::select('exercise_ids[0][]', [], old('exercise_ids'), [
                                                                                                   'class' => 'select2tagsjs form-group exercise',
                                                                                                   'multiple' => 'multiple',
                                                                                                   'id' => 'exercise_ids_0',
                                                                                                   'data-placeholder' => __('message.select_name',[ 'select' => __('message.exercise') ]),
                                                                                                   'data-ajax--url' => route('ajax-list', ['type' => 'exercise']),
                                                                                               ])
                                                                                           }}
                                                                                       
                                    </div>
                                                                                
                                 </td>
                                                                             
                                 <td>
                                                                                    
                                    <div class="form-group">
                                                                                           <input type="hidden" name="is_rest[0]" value="0" id="is_rest_no_0">
                                                                                           {!! Form::checkbox('is_rest[0]', 1, old('is_rest'), ['class' => 'form-check-input', 'id' => 'is_rest_yes_1' ]) !!}
                                                                                       
                                    </div>
                                                                                
                                 </td>
                                                                             
                                 <td>
                                                                                    
                                    <a href="javascript:void(0)" id="remove_0" class="removebtn btn btn-sm btn-icon btn-danger" row="0">
                                                                                           
                                       <span class="btn-inner">
                                                                                                  
                                          <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="currentColor">
                                                                                                         
                                             <path d="M19.3248 9.46826C19.3248 9.46826 18.7818 16.2033 18.4668 19.0403C18.3168 20.3953 17.4798 21.1893 16.1088 21.2143C13.4998 21.2613 10.8878 21.2643 8.27979 21.2093C6.96079 21.1823 6.13779 20.3783 5.99079 19.0473C5.67379 16.1853 5.13379 9.46826 5.13379 9.46826" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                                                                                         
                                             <path d="M20.708 6.23975H3.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                                                                                         
                                             <path d="M17.4406 6.23973C16.6556 6.23973 15.9796 5.68473 15.8256 4.91573L15.5826 3.69973C15.4326 3.13873 14.9246 2.75073 14.3456 2.75073H10.1126C9.53358 2.75073 9.02558 3.13873 8.87558 3.69973L8.63258 4.91573C8.47858 5.68473 7.80258 6.23973 7.01758 6.23973" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                                                                                     
                                          </svg>
                                                                                              
                                       </span>
                                                                                       
                                    </a>
                                                                                
                                 </td>
                                                                         
                              </tr>
                                                                  @endif
                                                                  
                           </tbody>
                                                           
                        </table>
                                                    
                     </div>
                                             
                  </div>
                                          
                  <hr>
                                          {{ Form::submit( __('message.save'), ['class' => 'btn btn-md btn-primary float-end']) }}
                                      
               </div>
                               
            </div>
                        
         </div>
                 
      </div>
              {!! Form::close() !!}
          
   </div>
</x-app-layout>