@push('scripts')
    <script>
        $(document).ready(function() {
            $('.selectAll').click(function(){
                var usertype = $(this).attr('data-usertype');
                
                if($(this).is(':checked') ) {
                    $('#'+usertype+'_list').find('option').prop('selected', true);
                    $('#'+usertype+'_list').trigger('change');
                } else {
                    $('#'+usertype+'_list').find('option').prop('selected', false);
                    $('#'+usertype+'_list').trigger('change');
                }
            });
        }); 
    </script>
@endpush
<x-app-layout :assets="$assets ?? []">
    <div>
        <?php $id = $id ?? null;?>
        @if(isset($id))
            {!! Form::model($data, ['route' => ['pushnotification.update', $id], 'method' => 'patch', 'enctype' => 'multipart/form-data' ]) !!}
        @else
            {!! Form::open(['route' => ['pushnotification.store'], 'method' => 'post', 'enctype' => 'multipart/form-data' ]) !!}
        @endif
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <div class="header-title">
                            <h4 class="card-title">{{ $pageTitle }}</h4>
                        </div>
                        <div class="card-action">
                            <a href="{{ route('pushnotification.index') }} " class="btn btn-sm btn-primary" role="button">{{ __('message.back') }}</a>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="form-group col-md-4">
                                {{ Form::label('user', __('message.user').' <span class="text-danger">*</span>',['class' => 'form-control-label' ], false ) }}
                                {{ Form::select('user[]', $user , old('user') , [ 'id' => 'user_list', 'class' => 'select2js form-control', 'multiple' => 'multiple', 'required'] ) }}
                            </div>

                            <div class="form-group col-md-2">
                                <div class="custom-control custom-checkbox mt-4 pt-3">
                                    <input type="checkbox" class="custom-control-input selectAll" id="all_user" data-usertype="user">
                                    <label class="custom-control-label" for="all_user">{{ __('message.selectall') }}</label>
                                </div>
                            </div>

                            <div class="form-group col-md-6">
                                {{ Form::label('title', __('message.title').' <span class="text-danger">*</span>',['class' => 'form-control-label'], false ) }}
                                {{ Form::text('title', old('title'),[ 'placeholder' => __('message.title'),'class' =>'form-control','required']) }}
                            </div>

                            <div class="form-group col-md-12">
                                {{ Form::label('message',__('message.message').' <span class="text-danger">*</span>',['class' => 'form-control-label'], false ) }}
                                {{ Form::textarea('message', null, [ 'class' => 'form-control textarea', 'rows' => 3, 'required', 'placeholder' => __('message.message') ]) }}
                            </div>

                            <div class="form-group col-md-4">
                                <label class="form-control-label" for="notification_image">{{ __('message.image') }} </label>
                                <div class="">
                                    <input class="form-control file-input" type="file" name="notification_image" accept="image/*" data--target='notification_image_preview'>
                                </div>
                            </div>
                            
                            <div class="col-md-2 mb-2">
                                <img id="notification_image_preview" src="{{ asset('images/default.png') }}" alt="image" class="attachment-image mt-1 notification_image_preview">
                            </div>
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
