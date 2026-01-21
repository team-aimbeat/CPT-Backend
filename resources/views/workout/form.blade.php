@push('scripts')
<script>
(function ($) {

    let row = 0;

    function resetIndex() {
        $('#table_list tbody tr').each(function (i) {
            $(this).find('td:first').text(i + 1);
        });
    }

    function initSelect2() {
        $('.select2tagsjs, .select2js').select2({
            width: '100%'
        });
    }

    function initTinyMCE() {
        tinymce.remove();
        tinymce.init({
            selector: '.tinymce-description',
            height: 140,
            menubar: false,
            plugins: 'lists link',
            toolbar: 'bold italic | bullist numlist | link',
            branding: false,
            setup: function (editor) {
                editor.on('change keyup', function () {
                    editor.save();
                });
            }
        });
    }

    $(document).ready(function () {

        row = $('#table_list tbody tr').length - 1;

        initSelect2();
        initTinyMCE();
        resetIndex();

        $('#add_button').on('click', function () {

            tinymce.remove();
            $('.select2tagsjs').select2('destroy');

            let last = $('#table_list tbody tr:last');
            let clone = last.clone();

            row++;

            clone.attr('id', 'row_' + row).attr('row', row).attr('data-id', 0);

            clone.find('input').val('');
            clone.find('select').val(null);
            clone.find('textarea').val('');

            clone.find('[name^="week"]').attr('name', 'week[' + row + ']').val(1);
            clone.find('[name^="day"]').attr('name', 'day[' + row + ']');

            clone.find('[name^="exercise_ids"]')
                .attr('name', 'exercise_ids[' + row + '][]')
                .attr('id', 'exercise_ids_' + row);

            clone.find('[name^="exercise_description"]')
                .attr('name', 'exercise_description[' + row + '][]')
                .attr('id', 'instruction_' + row);

            clone.find('[name^="is_rest"]')
                .attr('name', 'is_rest[' + row + ']')
                .prop('checked', false);

            clone.find('.removebtn').attr('row', row);

            last.after(clone);

            initSelect2();
            initTinyMCE();
            resetIndex();
        });

        $(document).on('click', '.removebtn', function () {
            if ($('#table_list tbody tr').length > 1) {
                $('#row_' + $(this).attr('row')).remove();
                initTinyMCE();
                resetIndex();
            }
        });

    });

})(jQuery);
</script>
@endpush


<x-app-layout>
<div>

<!-- {!! Form::open(['route'=>'workout.store','method'=>'post','enctype'=>'multipart/form-data']) !!} -->

@if(isset($id))
    {!! Form::model($data, [
        'route' => ['workout.update', $id],
        'method' => 'patch',
        'enctype'=>'multipart/form-data'
    ]) !!}
@else
    {!! Form::open([
        'route'=>'workout.store',
        'method'=>'post',
        'enctype'=>'multipart/form-data'
    ]) !!}
@endif

<div class="card">
<div class="card-header d-flex justify-content-between">
    <h4>Add Workout</h4>
    <a href="{{ route('workout.index') }}" class="btn btn-primary btn-sm">Back</a>
</div>

<div class="card-body">

{{-- BASIC INFO --}}
<div class="row">

    <div class="col-md-4">
        {{ Form::label('title','Title *') }}
        {{ Form::text('title',null,['class'=>'form-control','required']) }}
    </div>

    <div class="col-md-4">
        {{ Form::label('goal_id','Goal *') }}
        {{ Form::select('goal_id',[],null,[
            'class'=>'select2js',
            'data-ajax--url'=>route('ajax-list',['type'=>'bodypart']),
            'required'
        ]) }}
    </div>

    <div class="col-md-4">
        {{ Form::label('level_id','Level *') }}
        {{ Form::select('level_id',[],null,[
            'class'=>'select2js',
            'data-ajax--url'=>route('ajax-list',['type'=>'level']),
            'required'
        ]) }}
    </div>

    <div class="col-md-4 mt-2">
        {{ Form::label('workout_type_id','Workout Type *') }}
        {{ Form::select('workout_type_id',[],null,[
            'class'=>'select2js',
            'data-ajax--url'=>route('ajax-list',['type'=>'workout_type']),
            'required'
        ]) }}
    </div>


    <div class="col-md-4 mt-2">
        {{ Form::label('gender','Gender *') }}
        {{ Form::select('gender', [
            'male' => 'Male',
            'female' => 'Female',
            'both' => 'Both'
        ], old('gender','both'), [
            'class' => 'form-control',
            'required'
        ]) }}
    </div>

    <div class="col-md-4 mt-2">
        {{ Form::label('status','Status *') }}
        {{ Form::select('status',['active'=>'Active','inactive'=>'Inactive'],'active',[
            'class'=>'form-control','required'
        ]) }}
    </div>

    <div class="col-md-4 mt-2">
        {{ Form::label('is_premium','Premium') }}<br>
        {{ Form::checkbox('is_premium',1,null) }}
    </div>

</div>

<hr>

{{-- VIDEOS --}}
<div class="row">
    <div class="col-md-6">
        {{ Form::label('video_url','Warmup Video (YouTube ID)') }}
        {{ Form::text('video_url',null,['class'=>'form-control']) }}
    </div>

    <div class="col-md-6">
        {{ Form::label('stetch_video','Stretching Video (YouTube ID)') }}
        {{ Form::text('stetch_video',null,['class'=>'form-control']) }}
    </div>
</div>

<hr>

{{-- WORKOUT DESCRIPTION --}}
<div class="form-group">
    {{ Form::label('description','Workout Description') }}
    {{ Form::textarea('description',null,['class'=>'form-control tinymce-description']) }}
</div>

<hr>

{{-- WORKOUT DAYS --}}
<h5>
    Workout Days
    <button type="button" id="add_button" class="btn btn-sm btn-primary float-end">Add</button>
</h5>

<table class="table" id="table_list">
<thead>
<tr>
    <th>#</th>
    <th>Week</th>
    <th>Day</th>
    <th>Exercise</th>
    <th>Instruction (Per Day)</th>
    <th>Rest</th>
    <th></th>
</tr>
</thead>

<tbody>
<tr id="row_0" row="0" data-id="0">
<td></td>

<td>{{ Form::select('week[0]',range(1,12),1,['class'=>'form-control']) }}</td>

<td>{{ Form::select('day[0]',[
    'Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'
],null,['class'=>'form-control']) }}</td>

<td>
{{ Form::select('exercise_ids[0][]',[],null,[
    'class'=>'select2tagsjs',
    'multiple',
    'data-ajax--url'=>route('ajax-list',['type'=>'exercise'])
]) }}
</td>

<td>
<textarea name="exercise_description[0][]" class="form-control tinymce-description"></textarea>
</td>

<td>
<input type="hidden" name="is_rest[0]" value="0">
<input type="checkbox" name="is_rest[0]" value="1">
</td>

<td>
<button type="button" class="btn btn-danger btn-sm removebtn" row="0">X</button>
</td>
</tr>
</tbody>
</table>

<hr>

{{ Form::submit('Save Workout',['class'=>'btn btn-success float-end']) }}

</div>
</div>

{!! Form::close() !!}
</div>
</x-app-layout>
