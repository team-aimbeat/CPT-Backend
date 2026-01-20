@php
    $data = $data ?? null;
@endphp

@push('scripts')
<script>
(function($){
    $(document).ready(function(){

        $('.tinymce').each(function(){
            tinymceEditor('#'+$(this).attr('id'),' ',function(){},450);
        });

        let videoType = $('select[name=video_type]').val();
        toggleVideo(videoType);

        $('.video_type').change(function(){
            toggleVideo(this.value);
        });

        function toggleVideo(type){
            if(type === 'url'){
                $('.video_url').removeClass('d-none');
                $('.video_upload').addClass('d-none');
            }else{
                $('.video_upload').removeClass('d-none');
                $('.video_url').addClass('d-none');
            }
        }
    });
})(jQuery);
</script>
@endpush

<x-app-layout :assets="$assets ?? []">
<div>

@if(isset($data))
    {!! Form::model($data,['route'=>['diet.update',$data->id],'method'=>'patch','enctype'=>'multipart/form-data']) !!}
@else
    {!! Form::open(['route'=>'diet.store','method'=>'post','enctype'=>'multipart/form-data']) !!}
@endif

<div class="card">
<div class="card-header d-flex justify-content-between">
    <h4 class="card-title">{{ $pageTitle ?? 'Diet Form' }}</h4>
    <a href="{{ route('diet.index') }}" class="btn btn-sm btn-primary">Back</a>
</div>

<div class="card-body">

{{-- ================= BASIC DETAILS ================= --}}
<div class="row">

    {{-- CATEGORY --}}
    <div class="col-md-4">
        {{ Form::label('categorydiet_id','Category *',['class'=>'form-control-label'],false) }}
        {{ Form::select(
            'categorydiet_id',
            isset($data?->categorydiet)
                ? [$data->categorydiet->id => $data->categorydiet->title]
                : [],
            old('categorydiet_id',$data?->categorydiet_id),
            [
                'class'=>'form-control select2js categorydiet',
                'data-placeholder'=>'Select Category',
                'data-ajax--url'=>route('ajax-list',['type'=>'categorydiet']),
                'required'
            ]
        )}}
    </div>

    {{-- GENDER --}}
    <div class="col-md-4">
        <label class="form-control-label">Gender *</label>
        <select name="gender" class="form-control select2js" required>
            <option value="male" {{ old('gender',$data?->gender)=='male'?'selected':'' }}>Male</option>
            <option value="female" {{ old('gender',$data?->gender)=='female'?'selected':'' }}>Female</option>
            <option value="both" {{ old('gender',$data?->gender ?? 'both')=='both'?'selected':'' }}>Both</option>
        </select>
    </div>

    {{-- VARIETY --}}
    <div class="col-md-4">
        {{ Form::label('variety','Variety *',['class'=>'form-control-label'],false) }}
        {{ Form::select(
            'variety',
            ['veg'=>'Veg','nonveg'=>'Non-Veg'],
            old('variety',$data?->variety),
            ['class'=>'form-control select2js','required']
        )}}
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-4">
        {{ Form::label('status','Status *',['class'=>'form-control-label'],false) }}
        {{ Form::select(
            'status',
            ['active'=>'Active','inactive'=>'Inactive'],
            old('status',$data?->status ?? 'active'),
            ['class'=>'form-control select2js','required']
        )}}
    </div>

    <div class="col-md-4">
        {{ Form::label('is_premium','Premium',['class'=>'form-control-label']) }}
        {!! Form::hidden('is_premium',0) !!}
        {!! Form::checkbox('is_premium',1,$data?->is_premium) !!}
    </div>
</div>

<hr>

{{-- ================= LANGUAGE TABS ================= --}}
<ul class="nav nav-tabs">
@foreach($languages as $lang)
<li class="nav-item">
    <a class="nav-link {{ $lang->is_default ? 'active' : '' }}"
       data-bs-toggle="tab"
       href="#lang_{{ $lang->id }}">
        {{ $lang->language_name }}
    </a>
</li>
@endforeach
</ul>

<div class="tab-content mt-3">
@foreach($languages as $lang)
@php
    $translation = $data?->translations
        ?->where('language_id',$lang->id)
        ->first();
@endphp

<div class="tab-pane fade {{ $lang->is_default ? 'show active' : '' }}"
     id="lang_{{ $lang->id }}">

<input type="hidden"
       name="translations[{{ $lang->id }}][language_id]"
       value="{{ $lang->id }}">

<div class="row">
    <div class="col-md-6">
        <label>Title ({{ $lang->language_name }})</label>
        <input type="text"
               name="translations[{{ $lang->id }}][title]"
               class="form-control"
               value="{{ old('translations.'.$lang->id.'.title',$translation?->title) }}"
               {{ $lang->is_default ? 'required' : '' }}>
    </div>
</div>

<div class="row mt-2">
    <div class="col-md-12">
        <label>Ingredients</label>
        <textarea id="ingredients_{{ $lang->id }}"
                  name="translations[{{ $lang->id }}][ingredients]"
                  class="form-control tinymce">{{ old('translations.'.$lang->id.'.ingredients',$translation?->ingredients) }}</textarea>
    </div>

    <div class="col-md-12 mt-2">
        <label>Description</label>
        <textarea id="description_{{ $lang->id }}"
                  name="translations[{{ $lang->id }}][description]"
                  class="form-control tinymce">{{ old('translations.'.$lang->id.'.description',$translation?->description) }}</textarea>
    </div>
</div>
</div>
@endforeach
</div>

<hr>

{{-- ================= VIDEO ================= --}}
<div class="row">
    
    <div class="col-md-4">
    {{ Form::label('diet_image','Image') }}
    <input type="file" name="diet_image" class="form-control" accept="image/*">
</div>
    
    <div class="col-md-4">
        {{ Form::label('video_type','Video Type *',['class'=>'form-control-label'],false) }}
        {{ Form::select(
            'video_type',
            ['url'=>'URL','upload_video'=>'Upload'],
            old('video_type',$data?->video_type),
            ['class'=>'form-control select2js video_type','required']
        )}}
    </div>

    <div class="col-md-4 video_url">
        {{ Form::label('video_url','Video URL',['class'=>'form-control-label']) }}
        {{ Form::url('video_url',old('video_url',$data?->video_url),['class'=>'form-control']) }}
    </div>

    <div class="col-md-4 video_upload">
        <label class="form-control-label">Upload Video</label>
        <input type="file" name="diet_video" class="form-control" accept="video/*">
    </div>
</div>

<hr>

<div class="text-end">
    {{ Form::submit('Save',['class'=>'btn btn-primary']) }}
</div>

</div>
</div>

{!! Form::close() !!}
</div>
</x-app-layout>
