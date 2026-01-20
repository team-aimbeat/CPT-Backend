<!-- Modal -->
{{ Form::open(['route' => 'save.assignworkout','method' => 'post','data-toggle' => 'validator' ]) }}
    <div class="row">
    {{ Form::hidden('user_id',$user_id) }}
        <div class="form-group col-md-12">
            {{ Form::label('workout_id', __('message.workout').' <span class="text-danger">*</span>',[ 'class' => 'form-control-label' ], false) }} 
            {{ Form::select('workout_id', [], old('workout_id'), [
                    'class' => 'select2js form-group workout',
                    'data-placeholder' => __('message.select_name',[ 'select' => __('message.workout') ]),
                    'data-ajax--url' => route('ajax-list', ['type' => 'workout']),
                    'required'
                ])
            }}
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-md btn-secondary" data-bs-dismiss="modal">{{ __('message.close') }}</button>
        <button type="submit" class="btn btn-md btn-primary" id="btn_submit" data-form="ajax" >{{ __('message.save') }}</button>
    </div>
{{ Form::close() }} 
<script>
    $('#workout_id').select2({
        dropdownParent: $('#formModal'),
        width: '100%',
        placeholder: "{{ __('message.select_name',['select' => __('message.parent_permission')]) }}",
    });
</script>

