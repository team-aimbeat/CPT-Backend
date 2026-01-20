@push('scripts')
<script>
    (function ($) {
        $(document).ready(function () {
            tinymceEditor('.tinymce-description', ' ', function (ed) {

            }, 450)
        });
    })(jQuery);

</script>
@endpush
<x-app-layout :assets="$assets ?? []">
    <div>
        <?php $id = $id ?? null;?>
        @if(isset($id))
        {!! Form::model($data, [ 'route' => ['product.update', $id], 'method' => 'patch', 'enctype' =>
        'multipart/form-data' ]) !!}
        @else
        {!! Form::open(['route' => ['product.store'], 'method' => 'post', 'enctype' => 'multipart/form-data' ]) !!}
        @endif
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <div class="header-title">
                            <h4 class="card-title">{{ $pageTitle }}</h4>
                        </div>
                        <div class="card-action">
                            <a href="{{ route('product.index') }} " class="btn btn-sm btn-primary"
                                role="button">{{ __('message.back') }}</a>
                        </div>

                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="form-group col-md-4">
                                {{ Form::label('title', __('message.title').' <span class="text-danger">*</span>',[ 'class' => 'form-control-label' ], false ) }}
                                {{ Form::text('title', old('title'),[ 'placeholder' => __('message.title'),'class' =>'form-control','required']) }}
                            </div>
                            <div class="form-group col-md-4">
                                {{ Form::label('affiliate_link', __('message.affiliate_link').' <span class="text-danger">*</span>',[ 'class' => 'form-control-label' ], false ) }}
                                {{ Form::url('affiliate_link', old('affiliate_link'),[ 'placeholder' => __('message.affiliate_link'),'class' =>'form-control','required']) }}
                            </div>
                            <div class="form-group col-md-4">
                                {{ Form::label('price', __('message.price').' <span class="text-danger">*</span>',[ 'class' => 'form-control-label' ], false ) }}
                                {{ Form::number('price', old('price'),[ 'placeholder' => __('message.price'),'class' =>'form-control', 'min' => 0, 'step' => 'any', 'required']) }}
                            </div>
                            <div class="form-group col-md-4">
                                {{ Form::label('productcategory_id', __('message.productcategory').' <span class="text-danger">*</span>',[ 'class' => 'form-control-label' ], false) }}
                                {{ Form::select('productcategory_id', isset($id) ? [ optional($data->productcategory)->id => optional($data->productcategory)->title ] : [], old('productcategory_id'), [
                                        'class' => 'select2js form-group productcategory',
                                        'data-placeholder' => __('message.select_name',[ 'select' => __('message.productcategory') ]),
                                        'data-ajax--url' => route('ajax-list', ['type' => 'productcategory']),
                                        'required'
                                    ])
                                }}
                            </div>
                            <div class="form-group col-md-4">
                                {{ Form::label('featured', __('message.featured'), ['class' => 'form-control-label']) }}
                                <div class="form-check">
                                    <div class="custom-control custom-radio d-inline-block col-4">
                                        <label class="form-check-label" for="featured-yes"> {{__('message.yes')}}
                                        </label>
                                        {{ Form::radio('featured', 'yes', old('featured') || true, [ 'class' => 'form-check-input', 'id' => 'featured-yes']) }}
                                    </div>
                                    <div class="custom-control custom-radio d-inline-block col-4">
                                        <label class="form-check-label" for="featured-no"> {{__('message.no')}} </label>
                                        {{ Form::radio('featured', 'no', old('featured'), [ 'class' => 'form-check-input', 'id' => 'featured-no']) }}
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-md-4">
                                {{ Form::label('status',__('message.status').' <span class="text-danger">*</span>',['class'=>'form-control-label'],false) }}
                                {{ Form::select('status',[ 'active' => __('message.active'), 'inactive' => __('message.inactive') ], old('status'), [ 'class' => 'form-control select2js','required']) }}
                            </div>
                            <div class="form-group col-md-12">
                                {{ Form::label('description',__('message.description'), ['class' => 'form-control-label']) }}
                                {{ Form::textarea('description', null, ['class'=> 'form-control tinymce-description' , 'placeholder'=> __('message.description') ]) }}
                            </div>
                            <div class="form-group col-md-6">
                                <label class="form-control-label" for="image">{{ __('message.image') }} </label>
                                <div class="">
                                    <input class="form-control file-input" type="file" name="product_image"
                                        accept="image/*">
                                </div>
                            </div>
                            @if( isset($id) && getMediaFileExit($data, 'product_image'))
                            <div class="col-md-2 mb-2 position-relative">
                                <img id="product_image_preview" src="{{ getSingleMedia($data,'product_image') }}"
                                    alt="product_image" class="avatar-100 mt-1">
                                <a class="text-danger remove-file"
                                    href="{{ route('remove.file', ['id' => $data->id, 'type' => 'product_image']) }}"
                                    data--submit='confirm_form' data--confirmation='true' data--ajax='true'
                                    data-toggle='tooltip'
                                    title='{{ __("message.remove_file_title" , ["name" =>  __("message.image") ]) }}'
                                    data-title='{{ __("message.remove_file_title" , ["name" =>  __("message.image") ]) }}'
                                    data-message='{{ __("message.remove_file_msg") }}'>
                                    <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path opacity="0.4"
                                            d="M16.34 1.99976H7.67C4.28 1.99976 2 4.37976 2 7.91976V16.0898C2 19.6198 4.28 21.9998 7.67 21.9998H16.34C19.73 21.9998 22 19.6198 22 16.0898V7.91976C22 4.37976 19.73 1.99976 16.34 1.99976Z"
                                            fill="currentColor"></path>
                                        <path
                                            d="M15.0158 13.7703L13.2368 11.9923L15.0148 10.2143C15.3568 9.87326 15.3568 9.31826 15.0148 8.97726C14.6728 8.63326 14.1198 8.63426 13.7778 8.97626L11.9988 10.7543L10.2198 8.97426C9.87782 8.63226 9.32382 8.63426 8.98182 8.97426C8.64082 9.31626 8.64082 9.87126 8.98182 10.2123L10.7618 11.9923L8.98582 13.7673C8.64382 14.1093 8.64382 14.6643 8.98582 15.0043C9.15682 15.1763 9.37982 15.2613 9.60382 15.2613C9.82882 15.2613 10.0518 15.1763 10.2228 15.0053L11.9988 13.2293L13.7788 15.0083C13.9498 15.1793 14.1728 15.2643 14.3968 15.2643C14.6208 15.2643 14.8448 15.1783 15.0158 15.0083C15.3578 14.6663 15.3578 14.1123 15.0158 13.7703Z"
                                            fill="currentColor"></path>
                                    </svg>
                                </a>
                            </div>
                            @endif
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