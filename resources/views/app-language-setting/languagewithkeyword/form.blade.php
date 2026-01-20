
@if(isset($id))
    {!! Form::model($data, ['route' => ['languagewithkeyword.update', $id], 'method' => 'patch' ]) !!}
@else
    {!! Form::open(['route' => ['languagewithkeyword.store'], 'method' => 'post']) !!}
@endif
    <div class="row">
        <div class="form-group col-md-12">
            {{ Form::label('language', __('message.language',[ 'select' => __('message.language') ]),[ 'class' => 'form-control-label' ]) }}
            {{ Form::select('language', isset($data) ? [ $data->languagelist->id => optional($data->languagelist)->language_name ] : [], old('language'), [
                    'class' => 'form-control select2 language',
                    'disabled' => true,
            ]) }}
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('keyword', __('message.keyword_title',[ 'select' => __('message.keyword') ]),[ 'class' => 'form-control-label' ]) }}
            {{ Form::select('keyword', isset($data) ? [ $data->defaultkeyword->id => optional($data->defaultkeyword)->keyword_name ] : [], old('keyword'), [
                    'class' => 'form-control select2 keyword',
                    'disabled' => true,
            ]) }}
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('keyword_value', __('message.keyword_value').' <span class="text-danger">*</span>',['class' => 'form-control-label'], false ) }}
            {{ Form::text('keyword_value', old('keyword_value'),[ 'placeholder' => __('message.keyword_value'),'class' =>'form-control','required']) }}
        </div>
    </div>   
    <div class="modal-footer">
        <button type="button" class="btn btn-md btn-secondary" data-bs-dismiss="modal">{{ __('message.close') }}</button>
        <button type="submit" class="btn btn-md btn-primary" id="btn_submit" data-form="ajax" >{{ __('message.save') }}</button>
    </div>
{{ Form::close() }}