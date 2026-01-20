<!-- Modal -->
{{ Form::open(['route' => 'save.assigndiet','method' => 'post','data-toggle' => 'validator' ]) }}
    <div class="row">
    {{ Form::hidden('user_id',$user_id) }}
        <div class="form-group col-md-12">
            {{ Form::label('diet_id', __('message.diet').' <span class="text-danger">*</span>',[ 'class' => 'form-control-label' ], false) }} 
            {{ Form::select('diet_id', [], old('diet_id'), [
                    'class' => 'select2js form-group diet',
                    'data-placeholder' => __('message.select_name',[ 'select' => __('message.diet') ]),
                    'data-ajax--url' => route('ajax-list', ['type' => 'diet']),
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
    $('#diet_id').select2({
        dropdownParent: $('#formModal'),
        width: '100%',
        placeholder: "{{ __('message.select_name',['select' => __('message.parent_permission')]) }}",
    });
</script>

