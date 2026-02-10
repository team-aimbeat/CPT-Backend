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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Jobs\TranscodeWorkoutVideo;

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
   


    public function store(WorkoutRequest $request)
    {
        if (!auth()->user()->can('workout-add')) {
            return redirect()->back()
                ->withErrors(__('message.permission_denied_for_account'));
        }

        $workout = Workout::create($request->except(['video_url', 'stetch_video']));

        // ✅ Gender safety
        $workout->gender = $request->gender ?? 'both';

        // ✅ Warmup video URL (text)
        if ($request->hasFile('video_url')) {
            $workout->video_url = $this->storeWorkoutVideo($request->file('video_url'), 'warmup');
        }

        // ✅ Stretching video
        if ($request->hasFile('stetch_video')) {
            $workout->stetch_video = $this->storeWorkoutVideo($request->file('stetch_video'), 'stretching');
        }

        $workout->save();

        if (!empty($workout->video_url)) {
            TranscodeWorkoutVideo::dispatch($workout->id, 'video_url', $workout->video_url);
        }

        if (!empty($workout->stetch_video)) {
            TranscodeWorkoutVideo::dispatch($workout->id, 'stetch_video', $workout->stetch_video);
        }

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
                            'alternate_exercise_id' => $request->alternate_exercise_ids[$i][$key] ?? null,
                            'exercise_title' => $request->exercise_titles[$i][$key] ?? null,
                            'instruction' => $request->exercise_description[$i][$key] ?? null,
                            'alternate_exercise_description' => $request->alternate_exercise_description[$i][$key] ?? null,
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

    


  public function edit($id)
    {
        if (!auth()->user()->can('workout-edit')) {
            return redirect()->back()->withErrors(__('message.permission_denied_for_account'));
        }

        $data = Workout::with([
            'workoutDay.workoutDayExercise.exercise',
            'goal',
            'level',
            'workouttype'
        ])->findOrFail($id);

        // Prepare workout day data
        foreach ($data->workoutDay as $day) {

            if ($day->is_rest == 0) {

                $day->exercise_data = $day->workoutDayExercise
                    ->mapWithKeys(function ($item) {
                        return [
                            $item->exercise_id => $item->exercise->title
                        ];
                    })
                    ->toArray();

                $day->exercise_ids = $day->workoutDayExercise
                    ->pluck('exercise_id')
                    ->map(fn ($v) => (string) $v)
                    ->toArray();

                $day->alternate_exercise_data = $day->workoutDayExercise
                    ->filter(fn ($item) => !empty($item->alternate_exercise_id))
                    ->mapWithKeys(function ($item) {
                        return [
                            $item->alternate_exercise_id => optional($item->alternateExercise)->title
                        ];
                    })
                    ->toArray();

                $day->alternate_exercise_ids = $day->workoutDayExercise
                    ->pluck('alternate_exercise_id')
                    ->map(fn ($v) => $v ? (string) $v : null)
                    ->toArray();

                $day->exercise_description = $day->workoutDayExercise
                    ->pluck('instruction')
                    ->toArray();

                $day->exercise_titles = $day->workoutDayExercise
                    ->pluck('exercise_title')
                    ->toArray();

                $day->alternate_exercise_descriptions = $day->workoutDayExercise
                    ->pluck('alternate_exercise_description')
                    ->toArray();

            } else {
                $day->exercise_data = [];
                $day->exercise_ids = [];
                $day->exercise_description = [];
                $day->exercise_titles = [];
                $day->alternate_exercise_data = [];
                $day->alternate_exercise_ids = [];
                $day->alternate_exercise_descriptions = [];
            }
        }

        $pageTitle = 'Edit Workout';

        return view('workout.form', compact('data', 'id', 'pageTitle'));
    }


    public function update(WorkoutRequest $request, $id)
    {
        if (!auth()->user()->can('workout-edit')) {
            return redirect()->back()->withErrors(__('message.permission_denied_for_account'));
        }

        $workout = Workout::findOrFail($id);

        /* ---------------- BASIC DATA ---------------- */
    $workout->update($request->except([
        'week',
        'day',
        'exercise_ids',
        'exercise_description',
        'is_rest',
        'workout_days_id',
        'video_url',
        'stetch_video'
    ]));

        /* ---------------- IMAGE ---------------- */
        if ($request->hasFile('workout_image')) {
            $workout->clearMediaCollection('workout_image');
            $workout->addMediaFromRequest('workout_image')->toMediaCollection('workout_image');
        }

        /* ---------------- VIDEO UPLOAD ---------------- */
        if ($workout->video_type === 'upload_video' && $request->hasFile('workout_video')) {
            $workout->clearMediaCollection('workout_video');
            $workout->addMediaFromRequest('workout_video')->toMediaCollection('workout_video');
        }

    if ($request->hasFile('video_url')) {
        if (!empty($workout->video_url)) {
            Storage::disk('s3')->delete($workout->video_url);
        }
        $workout->video_url = $this->storeWorkoutVideo($request->file('video_url'), 'warmup');
    }

    if ($request->hasFile('stetch_video')) {
        if (!empty($workout->stetch_video)) {
            Storage::disk('s3')->delete($workout->stetch_video);
        }
        $workout->stetch_video = $this->storeWorkoutVideo($request->file('stetch_video'), 'stretching');
    }

    $workout->save();

    if ($request->hasFile('video_url') && !empty($workout->video_url)) {
        TranscodeWorkoutVideo::dispatch($workout->id, 'video_url', $workout->video_url);
    }

    if ($request->hasFile('stetch_video') && !empty($workout->stetch_video)) {
        TranscodeWorkoutVideo::dispatch($workout->id, 'stetch_video', $workout->stetch_video);
    }

        /* ---------------- CLEAN OLD DAYS ---------------- */
        $existingDayIds = $request->workout_days_id ?? [];
        WorkoutDay::where('workout_id', $workout->id)
            ->whereNotIn('id', array_filter($existingDayIds))
            ->delete();

        /* ---------------- WORKOUT DAYS ---------------- */
        if (!empty($request->week)) {

            foreach ($request->week as $i => $week) {

                $workoutDay = WorkoutDay::updateOrCreate(
                    ['id' => $request->workout_days_id[$i] ?? null],
                    [
                        'workout_id' => $workout->id,
                        'week'       => $week,
                        'day'        => $request->day[$i] ?? null,
                        'is_rest'    => $request->is_rest[$i] ?? 0,
                        'sequence'   => $i,
                    ]
                );

                /* REMOVE OLD EXERCISES */
                $workoutDay->workoutDayExercise()->delete();

                /* ---------------- SAVE EXERCISES ---------------- */
                if (($request->is_rest[$i] ?? 0) == 0 && !empty($request->exercise_ids[$i])) {

                    foreach ($request->exercise_ids[$i] as $key => $exerciseId) {

                        WorkoutDayExercise::create([
                            'workout_id'      => $workout->id,
                            'workout_day_id'  => $workoutDay->id,
                            'exercise_id'     => (int) $exerciseId,
                            'alternate_exercise_id' => $request->alternate_exercise_ids[$i][$key] ?? null,
                            'exercise_title'  => $request->exercise_titles[$i][$key] ?? null,
                            'instruction'     => $request->exercise_description[$i][$key] ?? null,
                            'alternate_exercise_description' => $request->alternate_exercise_description[$i][$key] ?? null,
                            'sequence'        => $key,
                        ]);
                    }
                }
            }
        }

    return redirect()
        ->route('workout.index')
        ->withSuccess(__('message.update_form', ['form' => __('message.workout')]));
}

    protected function storeWorkoutVideo($file, $label)
    {
        $now = now();
        $uuid = (string) Str::uuid();
        $dir = 'videos/originals/' . $now->format('Y') . '/' . $now->format('m') . '/' . $uuid;
        $filename = $label . '.' . $file->getClientOriginalExtension();

        return Storage::disk('s3')->putFileAs($dir, $file, $filename);
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
