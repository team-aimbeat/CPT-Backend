@push('scripts')
<script>
(function ($) {

    let row = $('#table_list tbody tr').length - 1;

    function resetIndex() {
        $('#table_list tbody tr').each(function (i) {
            $(this).find('td:first').text(i + 1);
        });
    }

    function initSelect2(ctx = document) {
        $(ctx).find('.select2tagsjs, .select2js').select2({ width:'100%' });
    }

    function initEditor(id) {
        tinymce.init({
            selector: '#' + id,
            height: 140,
            menubar: false,
            plugins: 'lists link',
            toolbar: 'bold italic | bullist numlist | link',
            branding: false,
            setup(editor) {
                editor.on('change keyup', () => editor.save());
            }
        });
    }

    $(document).ready(function () {

        $('.instruction-editor').each(function () {
            initEditor(this.id);
        });

        tinymce.init({
            selector: '.tinymce-main',
            height: 180,
            menubar: false,
            plugins: 'lists link',
            toolbar: 'bold italic | bullist numlist | link'
        });

        initSelect2();
        resetIndex();

        $('#add_button').click(function () {

            let last = $('#table_list tbody tr:last');
            let clone = last.clone();
            row++;

            clone.attr('id','row_'+row).attr('row',row);

            clone.find('input,select').val('');
            clone.find('.select2').remove();

            clone.find('[name^="week"]').attr('name','week['+row+']').val(1);
            clone.find('[name^="day"]').attr('name','day['+row+']');
            clone.find('[name^="exercise_ids"]').attr('name','exercise_ids['+row+'][]');

            clone.find('td:nth-child(5)').html(`
                <textarea
                    name="exercise_description[${row}][]"
                    id="instruction_${row}"
                    class="form-control instruction-editor"
                ></textarea>
            `);

            clone.find('[name^="is_rest"]').attr('name','is_rest['+row+']').prop('checked',false);
            clone.find('.removebtn').attr('row',row);

            last.after(clone);

            initSelect2(clone);
            initEditor('instruction_'+row);
            resetIndex();
        });

        $(document).on('click','.removebtn',function(){
            let r = $(this).attr('row');
            let id = 'instruction_'+r;
            if(tinymce.get(id)) tinymce.get(id).remove();
            $('#row_'+r).remove();
            resetIndex();
        });

    });

})(jQuery);
</script>
@endpush



@php
    $id   = $id ?? null;
    $data = $data ?? null;
@endphp

<x-app-layout>
<div>

@if($id)
    {!! Form::model($data, ['route'=>['workout.update',$id],'method'=>'patch']) !!}
@else
    {!! Form::open(['route'=>'workout.store','method'=>'post']) !!}
@endif

<div class="card">
<div class="card-header d-flex justify-content-between">
    <h4>{{ $id ? 'Edit Workout' : 'Add Workout' }}</h4>
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
        {{ Form::select('goal_id',
            $id ? [$data->goal->id => $data->goal->title] : [],
            old('goal_id',$data->goal_id ?? null),
            ['class'=>'select2js','data-ajax--url'=>route('ajax-list',['type'=>'bodypart']),'required']
        )}}
    </div>

    <div class="col-md-4">
        {{ Form::label('level_id','Level *') }}
        {{ Form::select('level_id',
            $id ? [$data->level->id => $data->level->title] : [],
            old('level_id',$data->level_id ?? null),
            ['class'=>'select2js','data-ajax--url'=>route('ajax-list',['type'=>'level']),'required']
        )}}
    </div>

    <div class="col-md-4 mt-2">
        {{ Form::label('workout_type_id','Workout Type *') }}
        {{ Form::select('workout_type_id',
            $id ? [$data->workouttype->id => $data->workouttype->title] : [],
            old('workout_type_id',$data->workout_type_id ?? null),
            ['class'=>'select2js','data-ajax--url'=>route('ajax-list',['type'=>'workout_type']),'required']
        )}}
    </div>

    <div class="col-md-4 mt-2">
        {{ Form::label('gender','Gender *') }}
        {{ Form::select('gender',['male'=>'Male','female'=>'Female','both'=>'Both'],
            old('gender',$data->gender ?? 'both'),
            ['class'=>'form-control','required']
        )}}
    </div>

    <div class="col-md-4 mt-2">
        {{ Form::label('status','Status *') }}
        {{ Form::select('status',['active'=>'Active','inactive'=>'Inactive'],
            old('status',$data->status ?? 'active'),
            ['class'=>'form-control','required']
        )}}
    </div>
</div>

<hr>

{{-- DESCRIPTION --}}
{{ Form::label('description','Workout Description') }}
{{ Form::textarea('description',null,['class'=>'form-control tinymce-main']) }}

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
    <th>Instruction</th>
    <th>Rest</th>
    <th></th>
</tr>
</thead>

<tbody>

@if($id && $data->workoutDay->count())
@foreach($data->workoutDay as $i => $day)
<tr id="row_{{ $i }}" row="{{ $i }}">
<td></td>

<td>{{ Form::select("week[$i]",range(1,12),$day->week,['class'=>'form-control']) }}</td>

<td>{{ Form::select("day[$i]",
['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'],
$day->day,['class'=>'form-control']) }}</td>

<td>
{{ Form::select("exercise_ids[$i][]",
$day->exercise_data ?? [],
$day->exercise_ids ?? [],
['class'=>'select2tagsjs','multiple','data-ajax--url'=>route('ajax-list',['type'=>'exercise'])]
)}}
</td>

<td>
<textarea
    name="exercise_description[{{ $i }}][]"
    id="instruction_{{ $i }}"
    class="form-control instruction-editor"
>{{ $day->exercise_description }}</textarea>
</td>

<td>
<input type="hidden" name="is_rest[{{ $i }}]" value="0">
<input type="checkbox" name="is_rest[{{ $i }}]" value="1" {{ $day->is_rest ? 'checked':'' }}>
</td>

<td>
<button type="button" class="btn btn-danger btn-sm removebtn" row="{{ $i }}">X</button>
</td>
</tr>
@endforeach
@else
<tr id="row_0" row="0">
<td></td>

<td>{{ Form::select('week[0]',range(1,12),1,['class'=>'form-control']) }}</td>
<td>{{ Form::select('day[0]',
['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'],
null,['class'=>'form-control']) }}</td>

<td>
{{ Form::select('exercise_ids[0][]',[],
null,['class'=>'select2tagsjs','multiple','data-ajax--url'=>route('ajax-list',['type'=>'exercise'])]) }}
</td>

<td>
<textarea name="exercise_description[0][]" id="instruction_0" class="form-control instruction-editor"></textarea>
</td>

<td>
<input type="hidden" name="is_rest[0]" value="0">
<input type="checkbox" name="is_rest[0]" value="1">
</td>

<td>
<button type="button" class="btn btn-danger btn-sm removebtn" row="0">X</button>
</td>
</tr>
@endif

</tbody>
</table>

<hr>

{{ Form::submit('Save Workout',['class'=>'btn btn-success float-end']) }}

{!! Form::close() !!}
</div>
</div>
</x-app-layout>
