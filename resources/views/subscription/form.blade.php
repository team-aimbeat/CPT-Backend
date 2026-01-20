<!-- Modal -->
{{ Form::open(['route' => 'subscription.store','method' => 'post','data-toggle' => 'validator' ]) }}
    <div class="row">
        <div class="form-group col-md-12">
            {{ Form::label('user_id', __('message.user').' <span class="text-danger">*</span>',[ 'class' => 'form-control-label' ], false) }} 
            {{ Form::select('user_id[]', $users ?? [], old('user_id'), [
                    'class' => 'select2js form-group user',
                    'multiple' => 'multiple',
                    'data-placeholder' => __('message.select_name',[ 'select' => __('message.user') ]),
                    'required'
                ])
            }}
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('package_id', __('message.package').' <span class="text-danger">*</span>',[ 'class' => 'form-control-label' ], false) }} 
            {{ Form::select('package_id', [], old('package_id'), [
                    'class' => 'select2js form-group package',
                    'data-placeholder' => __('message.select_name',[ 'select' => __('message.package') ]),
                    'data-ajax--url' => route('ajax-list', ['type' => 'package']),
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
    $('.select2js').select2({
        dropdownParent: $('#formModal'),
        width: '100%',
    });
</script>

