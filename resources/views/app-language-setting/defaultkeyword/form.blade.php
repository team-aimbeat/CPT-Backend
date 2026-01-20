@if (isset($id))
    {!! Form::model($data, ['route' => ['defaultkeyword.update', $id], 'method' => 'patch']) !!}
@else
    {!! Form::open(['route' => ['defaultkeyword.store'], 'method' => 'post']) !!}
@endif
<div class="row">
    <div class="form-group col-md-12">
        {{ Form::label('keyword_id', __('message.keyword_id') . ' <span class="text-danger">*</span>', ['class' => 'form-control-label'], false) }}
        {{ Form::number('keyword_id', isset($id) ? $data->keyword_id : $lastKeywordId, ['placeholder' => __('message.keyword_id'), 'class' => 'form-control', 'required' => true, 'readonly' => true]) }}
    </div>
    <div class="form-group col-md-12">
        {{ Form::label('keyword_name', __('message.keyword_title') . ' <span class="text-danger">*</span>', ['class' => 'form-control-label'], false) }}
        {{ Form::text('keyword_name', old('keyword_name'), ['placeholder' => __('message.keyword_title'), 'class' => 'form-control', 'required', 'readonly' => true]) }}
    </div>
    <div class="form-group col-md-12">
        {{ Form::label('keyword_value', __('message.keyword_value') . ' <span class="text-danger">*</span>', ['class' => 'form-control-label'], false) }}
        {{ Form::text('keyword_value', old('keyword_value'), ['placeholder' => __('message.keyword_value'), 'class' => 'form-control', 'required']) }}
    </div>
    <div class="form-group col-md-12">
        {{ Form::label('screen_id', __('message.screen_name') . ' <span class="text-danger">*</span>', ['class' => 'form-control-label'], false) }}
        {{ Form::select(
            'screen_id',
            isset($id) ? [optional($data->screen)->screenId => optional($data->screen)->screenName] : [],
            old('screen_id'),
            [
                'class' => 'select2 form-group',
                'id' => 'screenName',
                'data-placeholder' => __('message.select_name', ['select' => __('message.screen_name')]),
                'data-ajax--url' => route('ajax-list', ['type' => 'screen']),
                'required',
            ],
        ) }}
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-md btn-secondary" data-bs-dismiss="modal">{{ __('message.close') }}</button>
    <button type="submit" class="btn btn-md btn-primary" id="btn_submit" data-form="ajax" >{{ __('message.save') }}</button>
</div>
{{ Form::close() }}
<script>
    $('#screenName').select2({
        dropdownParent: $('#formModal'),
        width: '100%',
        placeholder: "{{ __('message.select_name', ['select' => __('message.screen_name')]) }}",
    });
</script>
