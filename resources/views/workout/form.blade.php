@push('scripts')
<script>
(function($){

    $(document).ready(function(){

        let resetIndex = function(){
            $('#table_list tbody tr').each(function(i){
                $(this).find('td:first').text(i + 1);
            });
        };
        resetIndex();

        $('.select2tagsjs').select2({ width:'100%' });

        let row = $('#table_list tbody tr').length - 1;

        $('#add_button').click(function(){

            $('.select2tagsjs').select2('destroy');

            let last = $('#table_list tbody tr:last');
            let clone = last.clone();

            row++;

            clone.attr('id','row_'+row).attr('row',row).attr('data-id',0);

            clone.find('input, textarea').val('');
            clone.find('select').val(null);

            clone.find('[name^="week"]').attr('name','week['+row+']').attr('id','week_'+row).val(1);
            clone.find('[name^="day"]').attr('name','day['+row+']').attr('id','day_'+row);

            clone.find('[name^="exercise_ids"]').attr('name','exercise_ids['+row+'][]').attr('id','exercise_ids_'+row);
            clone.find('[name^="exercise_description"]').attr('name','exercise_description['+row+'][]');

            clone.find('[name^="is_rest"]').attr('name','is_rest['+row+']').prop('checked',false);
            clone.find('[id^="remove_"]').attr('id','remove_'+row).attr('row',row);

            last.after(clone);

            $('.select2tagsjs').select2({ width:'100%' });
            resetIndex();
        });

        $(document).on('click','.removebtn',function(){
            if($('#table_list tbody tr').length > 1){
                $('#row_'+$(this).attr('row')).remove();
                resetIndex();
            }
        });

    });

})(jQuery);
</script>
@endpush
<x-app-layout :assets="$assets ?? []">
<div>

@php $id = $id ?? null; @endphp

@if($id)
    {!! Form::model($data, ['route'=>['workout.update',$id],'method'=>'patch']) !!}
@else
    {!! Form::open(['route'=>'workout.store','method'=>'post']) !!}
@endif

<div class="card">
<div class="card-header d-flex justify-content-between">
    <h4>{{ $pageTitle ?? 'Workout' }}</h4>
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
        {{ Form::select('goal_id',[],null,['class'=>'select2js','data-ajax--url'=>route('ajax-list',['type'=>'bodypart']),'required']) }}
    </div>

    <div class="col-md-4">
        {{ Form::label('level_id','Level *') }}
        {{ Form::select('level_id',[],null,['class'=>'select2js','data-ajax--url'=>route('ajax-list',['type'=>'level']),'required']) }}
    </div>

    <div class="col-md-4 mt-2">
        {{ Form::label('workout_type_id','Workout Type *') }}
        {{ Form::select('workout_type_id',[],null,['class'=>'select2js','data-ajax--url'=>route('ajax-list',['type'=>'workout_type']),'required']) }}
    </div>

    <div class="col-md-4 mt-2">
        {{ Form::label('status','Status *') }}
        {{ Form::select('status',['active'=>'Active','inactive'=>'Inactive'],null,['class'=>'form-control','required']) }}
    </div>

    <div class="col-md-4 mt-2">
        {{ Form::label('is_premium','Premium') }}<br>
        {{ Form::checkbox('is_premium',1,null) }}
    </div>
</div>

<hr>

{{-- DESCRIPTION --}}
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

@if(isset($data) && $data->workoutDay)
@foreach($data->workoutDay as $i => $day)
<tr id="row_{{ $i }}" row="{{ $i }}" data-id="{{ $day->id }}">
<td></td>

<td>{{ Form::select("week[$i]",range(1,12),$day->week,['class'=>'form-control']) }}</td>

<td>{{ Form::select("day[$i]",['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'],$day->day,['class'=>'form-control']) }}</td>

<td>
{{ Form::select("exercise_ids[$i][]",$day->exercise_data,$day->exercise_ids,[
'class'=>'select2tagsjs','multiple','data-ajax--url'=>route('ajax-list',['type'=>'exercise'])
]) }}
</td>

<td>
<textarea name="exercise_description[{{ $i }}][]" class="form-control">
{{ $day->exercise_description ?? '' }}
</textarea>
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

<td>{{ Form::select('day[0]',['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'],null,['class'=>'form-control']) }}</td>

<td>
{{ Form::select('exercise_ids[0][]',[],null,['class'=>'select2tagsjs','multiple','data-ajax--url'=>route('ajax-list',['type'=>'exercise'])]) }}
</td>

<td>
<textarea name="exercise_description[0][]" class="form-control"></textarea>
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

{{ Form::submit('Save Workout',['class'=>'btn btn-primary float-end']) }}

</div>
</div>

{!! Form::close() !!}
</div>
</x-app-layout>
