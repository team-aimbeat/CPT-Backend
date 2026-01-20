<!-- Modal -->

       {{ Form::open(['route' => 'permission.save','method' => 'post','data-toggle' => 'validator' ]) }}

           {{ Form::hidden('type',$type) }}
           {{ Form::hidden('id',-1) }}
            <div class="row">
                <div class="col-md-12 form-group">
                   {{ Form::label('name',__('message.name').' <span class="text-danger">*</span>', ['class' => 'form-control-label'],false) }}
                   {{ Form::text('name', null, [ 'placeholder' => __('message.name') ,'class' => 'form-control' ,'required']) }}
                </div>
            </div>
            @if( $type == 'permission' )
                <div class="row">
                    <div class="col-md-12 form-group">
                    {{ Form::label('parent_id',__('message.parent_permission'), ['class' => 'form-control-label']) }}
                    <select name="parent_id" id="parent_id" class="select2js form-control" data-ajax--url="{{ route('ajax-list', ['type' => 'permission']) }}" data-ajax--cache = "true">
                       
                    </select>
                    </div>
                </div>
            @endif
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-md btn-secondary" data-bs-dismiss="modal">{{ __('message.close') }}</button>
            <button type="submit" class="btn btn-md btn-primary" id="btn_submit" data-form="ajax" >{{ __('message.save') }}</button>
        </div>
        {{ Form::close() }}
<script>
    $('#parent_id').select2({
        dropdownParent: $('#formModal'),
        width: '100%',
        placeholder: "{{ __('message.select_name',['select' => __('message.parent_permission')]) }}",
    });
</script>

