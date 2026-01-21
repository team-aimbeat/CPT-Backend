<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DataTables\WorkoutDataTable;
use App\DataTables\WorkoutDayExercise as DataTablesWorkoutDayExercise;
use App\Helpers\AuthHelper;
use App\Models\Workout;
use App\Models\WorkoutDayExercise;
use App\Models\WorkoutDay;
use App\Http\Requests\WorkoutRequest;
use App\Models\Exercise;
use Yajra\DataTables\DataTables;

class WorkoutController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(WorkoutDataTable $dataTable)
    {
        $pageTitle = __('message.list_form_title',['form' => __('message.workout')] );
        $auth_user = AuthHelper::authSession();
        if( !$auth_user->can('workout-list') ) {
            $message = __('message.permission_denied_for_account');
            return redirect()->back()->withErrors($message);
        }
        $assets = ['data-table'];

        $headerAction = $auth_user->can('workout-add') ? '<a href="'.route('workout.create').'" class="btn btn-sm btn-primary" role="button">'.__('message.add_form_title', [ 'form' => __('message.workout')]).'</a>' : '';
        $gridColumn = $dataTable->getColumnsForGrid();
        return $dataTable->render('global.datatable_with_grid', compact('pageTitle', 'auth_user', 'assets', 'headerAction', 'gridColumn'));
    }

    public function getAjaxList(WorkoutDataTable $dataTable)
    {
        return $dataTable->dataTableForGrid()->toJson();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if( !auth()->user()->can('workout-add') ) {
            $message = __('message.permission_denied_for_account');
            return redirect()->back()->withErrors($message);
        }
        $pageTitle = __('message.add_form_title',[ 'form' => __('message.workout')]);

        return view('workout.form', compact('pageTitle'));
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    // public function store(WorkoutRequest $request)
    // {
    //     if( !auth()->user()->can('workout-add') ) {
    //         $message = __('message.permission_denied_for_account');
    //         return redirect()->back()->withErrors($message);
    //     }

    //     $workout = Workout::create($request->all());

    //     storeMediaFile($workout,$request->workout_image, 'workout_image'); 
    //     if( $workout->video_type == 'upload_video' ) {
    //         storeMediaFile($workout,$request->workout_video, 'workout_video');
    //     }

    //     if(isset($request->is_rest) && $request->is_rest != null ){
    //         foreach($request->is_rest as $i => $value){
    //             if($value != null ){
    //                 if($value == 1){
    //                     $exercise_ids = null;
    //                 } else {
    //                     $exercise_ids = isset($request->exercise_ids[$i]) ? $request->exercise_ids[$i] : null;
    //                 }
    //                 $save_workdays_data = [
    //                     'id' => null,
    //                     'workout_id' => $workout->id,
    //                     'is_rest' => $value,
    //                     'sequence' => $i,
    //                 ];

    //                 $workoutday = WorkoutDay::create($save_workdays_data);

    //                 if( $workoutday->is_rest == 0 && !empty($exercise_ids) ) {
    //                     foreach ($exercise_ids as $key => $value) {
    //                         $days_exercise = [
    //                             'id' => null,
    //                             'workout_id' => $workout->id,
    //                             'workout_day_id' => $workoutday->id,
    //                             'exercise_id' => (int) $value,
    //                             'sequence' => $key,
    //                         ];
    //                         $workout_days_exercise = WorkoutDayExercise::create($days_exercise);
    //                     }
    //                 }
    //             }
    //         }
    //     }

    //     return redirect()->route('workout.index')->withSuccess(__('message.save_form', ['form' => __('message.workout')]));
    // }


    public function store(WorkoutRequest $request)
{
    if (!auth()->user()->can('workout-add')) {
        return redirect()->back()
            ->withErrors(__('message.permission_denied_for_account'));
    }

    $workout = Workout::create($request->all());

    // ✅ Gender safety
    $workout->gender = $request->gender ?? 'both';

    // ✅ Warmup video URL (text)
    if ($request->filled('video_url')) {
        $workout->video_url = $request->video_url;
    }

    // ✅ Stretching video
    if ($request->filled('stetch_video')) {
        $workout->stetch_video = $request->stetch_video;
    }

    $workout->save();

    // ✅ Workout days & exercises
    if (!empty($request->is_rest)) {
        foreach ($request->is_rest as $i => $value) {

            $workoutday = WorkoutDay::create([
                'workout_id' => $workout->id,
                'is_rest' => $value,
                'sequence' => $i,
                'week' => $request->week[$i] ?? null,
                'day' => $request->day[$i] ?? null,
            ]);

            if ($value == 0 && !empty($request->exercise_ids[$i])) {
                foreach ($request->exercise_ids[$i] as $key => $exerciseId) {
                    WorkoutDayExercise::create([
                        'workout_id' => $workout->id,
                        'workout_day_id' => $workoutday->id,
                        'exercise_id' => (int)$exerciseId,
                        'sequence' => $key,
                    ]);
                }
            }
        }
    }

    return redirect()
        ->route('workout.index')
        ->withSuccess(__('message.save_form', ['form' => __('message.workout')]));
}

    
    
    
    // public function store(WorkoutRequest $request)
    //     {
    //         if( !auth()->user()->can('workout-add') ) {
    //             $message = __('message.permission_denied_for_account');
    //             return redirect()->back()->withErrors($message);
    //         }
        
    //         $workout = Workout::create($request->all());
            
    //         // dd($workout);
        
    //         storeMediaFile($workout,$request->workout_image, 'workout_image'); 
    //         if( $workout->video_type == 'upload_video' ) {
    //             storeMediaFile($workout,$request->workout_video, 'workout_video');
    //         }
            
            
    //          if ($request->hasFile('video_url')) {
    //             $file = $request->file('video_url');
            
    //             $filename = time() . '_' . $file->getClientOriginalName();
            
    //             $targetFolder = public_path('storage/uploads/exercise_gif');
            
    //             $file->move($targetFolder, $filename);
            
    //             $workout->video_url = 'uploads/exercise_gif/' . $filename;
    //             $workout->save();
    //         }
            
            
            
        
    //         if(isset($request->is_rest) && $request->is_rest != null ){
    //             foreach($request->is_rest as $i => $value){
    //                 if($value != null ){

    //                     $week = isset($request->week[$i]) ? $request->week[$i] : null;
    //                     $day = isset($request->day[$i]) ? $request->day[$i] : null;
        
    //                     if($value == 1){
    //                         $exercise_ids = null;
    //                     } else {
    //                         $exercise_ids = isset($request->exercise_ids[$i]) ? $request->exercise_ids[$i] : null;
    //                     }
                        
    //                     $save_workdays_data = [
    //                         'id' => null,
    //                         'workout_id' => $workout->id,
    //                         'is_rest' => $value,
    //                         'sequence' => $i,
    //                         'week' => $week, 
    //                         'day' => $day,   
    //                     ];
        
    //                     $workoutday = WorkoutDay::create($save_workdays_data);
        
    //                     if( $workoutday->is_rest == 0 && !empty($exercise_ids) ) {
    //                         foreach ($exercise_ids as $key => $value) {
    //                             $days_exercise = [
    //                                 'id' => null,
    //                                 'workout_id' => $workout->id,
    //                                 'workout_day_id' => $workoutday->id,
    //                                 'exercise_id' => (int) $value,
    //                                 'sequence' => $key,
    //                             ];
    //                             $workout_days_exercise = WorkoutDayExercise::create($days_exercise);
    //                         }
    //                     }
    //                 }
    //             }
    //         }
        
    //         return redirect()->route('workout.index')->withSuccess(__('message.save_form', ['form' => __('message.workout')]));
    //     }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = Workout::findOrFail($id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // public function edit($id)
    // {
    //     if( !auth()->user()->can('workout-edit') ) {
    //         $message = __('message.permission_denied_for_account');
    //         return redirect()->back()->withErrors($message);
    //     }
    //     $data = Workout::findOrFail($id);
    //     $pageTitle = __('message.update_form_title',[ 'form' => __('message.workout') ]);
    //     if(isset($id) && count($data->workoutDay) > 0){
    //         foreach($data->workoutDay as &$field){
    //             $exercise_ids = [];
    //             if($field->is_rest == 0){   
    //                 $exercise_ids = $field->workoutDayExercise->mapWithKeys(function ($item) {
    //                     return [ $item->exercise_id => optional($item->exercise)->title ];
    //                 });
    //                 $field['exercise_data'] = $exercise_ids;
                    
    //                 $exercise_id = $field->workoutDayExercise->pluck('exercise_id')->toArray();
    //                 $field['exercise_ids'] = array_map('strval', $exercise_id);
    //             }
    //         }
    //     }
    //     return view('workout.form', compact('data','id','pageTitle'));
    // }


  public function edit($id)
{
    if (!auth()->user()->can('workout-edit')) {
        return redirect()->back()->withErrors(__('message.permission_denied_for_account'));
    }

    $data = Workout::with([
        'workoutDay.workoutDayExercise.exercise'
    ])->findOrFail($id);

    $pageTitle = 'Edit Workout';

    foreach ($data->workoutDay as $index => $day) {

        if ($day->is_rest == 0) {

            // select2 display data
            $day->exercise_data = $day->workoutDayExercise
                ->pluck('exercise.title', 'exercise_id')
                ->toArray();

            // selected ids (STRING required for select2)
            $day->exercise_ids = $day->workoutDayExercise
                ->pluck('exercise_id')
                ->map(fn ($v) => (string) $v)
                ->toArray();

            // instruction text (per day)
            $day->exercise_description = $day->instruction ?? '';
        }
    }

    return view('workout.form', compact('data', 'id', 'pageTitle'));
}



    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // public function update(WorkoutRequest $request, $id)
    // {
    //     if( !auth()->user()->can('workout-edit') ) {
    //         $message = __('message.permission_denied_for_account');
    //         return redirect()->back()->withErrors($message);
    //     }

    //     $workout = Workout::findOrFail($id);
    //     // workout data...
    //     $workout->fill($request->all())->update();

    //     // Save workout image...
    //     if (isset($request->workout_image) && $request->workout_image != null) {
    //         $workout->clearMediaCollection('workout_image');
    //         $workout->addMediaFromRequest('workout_image')->toMediaCollection('workout_image');
    //     }

    //     if( $workout->video_type == 'upload_video' ) {
    //         if (isset($request->workout_video) && $request->workout_video != null) {
    //             $workout->clearMediaCollection('workout_video');
    //             $workout->addMediaFromRequest('workout_video')->toMediaCollection('workout_video');
    //         }
    //     }

    //     if(isset($request->is_rest) && $request->is_rest != null ){
    //         foreach($request->is_rest as $i => $value){
    //             if($value != null ){
    //                 if($value == 1){
    //                     $exercise_ids = null;
    //                 } else {
    //                     $exercise_ids = isset($request->exercise_ids[$i]) ? $request->exercise_ids[$i] : null;
    //                 }
    //                 $save_workdays_data = [
    //                     'id' => $request->workout_days_id[$i] ?? null,
    //                     'workout_id' => $workout->id,
    //                     'is_rest' => $value,
    //                     'sequence' => $i,
    //                 ];

    //                 $workoutday = WorkoutDay::updateOrCreate(['id' => $save_workdays_data['id']],$save_workdays_data);
    //                 $workoutday->workoutDayExercise()->delete();
    //                 if( $workoutday->is_rest == 0 && !empty($exercise_ids) ) {
    //                     foreach ($exercise_ids as $key => $value) {
    //                         $days_exercise = [
    //                             'id' => null,
    //                             'workout_id' => $workout->id,
    //                             'workout_day_id' => $workoutday->id,
    //                             'exercise_id' => (int) $value,
    //                             'sequence' => $key,
    //                         ];
    //                         $workout_days_exercise = WorkoutDayExercise::create($days_exercise);
    //                     }
    //                 }
    //             }
    //         }
    //     }

    //     if(auth()->check()){
    //         return redirect()->route('workout.index')->withSuccess(__('message.update_form',['form' => __('message.workout')]));
    //     }
    //     return redirect()->back()->withSuccess(__('message.update_form',['form' => __('message.workout') ] ));

    // }
    
    
    public function update(WorkoutRequest $request, $id)
{
    if( !auth()->user()->can('workout-edit') ) {
        $message = __('message.permission_denied_for_account');
        return redirect()->back()->withErrors($message);
    }

    $workout = Workout::findOrFail($id);
    // workout data...
    $workout->fill($request->all())->update();

    // Save workout image...
    if (isset($request->workout_image) && $request->workout_image != null) {
        $workout->clearMediaCollection('workout_image');
        $workout->addMediaFromRequest('workout_image')->toMediaCollection('workout_image');
    }

    if( $workout->video_type == 'upload_video' ) {
        if (isset($request->workout_video) && $request->workout_video != null) {
            $workout->clearMediaCollection('workout_video');
            $workout->addMediaFromRequest('workout_video')->toMediaCollection('workout_video');
        }
    }
    
    if ($request->hasFile('video_url')) {
        if ($workout->video_url) {
            $oldFile = public_path('storage/' . $workout->video_url);
            if (file_exists($oldFile)) {
                unlink($oldFile); 
            }
        }
    
        $file = $request->file('video_url');
        $filename = time() . '_' . $file->getClientOriginalName();
    
        $targetFolder = public_path('storage/uploads/exercise_gif');
    
        $file->move($targetFolder, $filename);
        $workout->video_url = 'uploads/exercise_gif/' . $filename;
        $workout->save();
    }
    
    
    
    

    if(isset($request->is_rest) && $request->is_rest != null ){
        foreach($request->is_rest as $i => $value){
            if($value != null ){
                
                $week = isset($request->week[$i]) ? $request->week[$i] : null;
                $day = isset($request->day[$i]) ? $request->day[$i] : null;

                if($value == 1){
                    $exercise_ids = null;
                } else {
                    $exercise_ids = isset($request->exercise_ids[$i]) ? $request->exercise_ids[$i] : null;
                }
                
                $save_workdays_data = [
                    'id' => $request->workout_days_id[$i] ?? null,
                    'workout_id' => $workout->id,
                    'is_rest' => $value,
                    'sequence' => $i,
                    'week' => $week, 
                    'day' => $day,  
                ];

                $workoutday = WorkoutDay::updateOrCreate(['id' => $save_workdays_data['id']],$save_workdays_data);
                
               
                $workoutday->workoutDayExercise()->delete();
                
                if( $workoutday->is_rest == 0 && !empty($exercise_ids) ) {
                    foreach ($exercise_ids as $key => $value) {
                        $days_exercise = [
                            'id' => null,
                            'workout_id' => $workout->id,
                            'workout_day_id' => $workoutday->id,
                            'exercise_id' => (int) $value,
                            'sequence' => $key,
                        ];
                        $workout_days_exercise = WorkoutDayExercise::create($days_exercise);
                    }
                }
            }
        }
        
      
    }

    if(auth()->check()){
        return redirect()->route('workout.index')->withSuccess(__('message.update_form',['form' => __('message.workout')]));
    }
    return redirect()->back()->withSuccess(__('message.update_form',['form' => __('message.workout') ] ));

}

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if(env('APP_DEMO')){
            $message = __('message.demo_permission_denied');
            return redirect()->route('workout.index')->withErrors($message);
        }
        if( !auth()->user()->can('workout-delete') ) {
            $message = __('message.permission_denied_for_account');
            return redirect()->back()->withErrors($message);
        }

        $workout = Workout::findOrFail($id);
        $status = 'errors';
        $message = __('message.not_found_entry', ['name' => __('message.workout')]);

        if($workout != '') {
            $workout->delete();
            $status = 'success';
            $message = __('message.delete_form', ['form' => __('message.workout')]);
        }

        if(request()->ajax()) {
            return response()->json(['status' => true, 'message' => $message ]);
        }

        return redirect()->back()->with($status,$message);
    }

    public function workoutDaysExerciseDelete(Request $request)
    {
        if(env('APP_DEMO')){
            $message = __('message.demo_permission_denied');
            if(request()->ajax()) {
                return response()->json(['status' => false, 'message' => $message ]);
            }
            return false;
        }
        $id = $request->id;

        $workout = WorkoutDay::findOrFail($id);
        $status = false;
        $message = __('message.not_found_entry', ['name' => __('message.workout_days')]);

        if($workout != '') {
            $workout->delete();
            $status = 'success';
            $message = __('message.delete_form', ['form' => __('message.workout_days')]);
        }
        return response()->json(['status'=> $status, 'message'=> $message ]);
    }

}
