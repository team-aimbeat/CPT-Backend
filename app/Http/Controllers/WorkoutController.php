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
use App\Models\AssignWorkout;
use App\Models\Exercise;
use App\Models\UserProfile;
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
                    'month_no' => $request->month_no[$i] ?? 1,
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
                            'alternate_exercise_id' => $this->nullableExerciseId($request->alternate_exercise_ids[$i][$key] ?? null),
                            'exercise_title' => $request->exercise_titles[$i][$key] ?? null,
                            'instruction' => $request->exercise_description[$i][$key] ?? null,
                            'alternate_exercise_description' => $request->alternate_exercise_description[$i][$key] ?? null,
                            'sequence' => $key,
                        ]);
                    }
                }
            }
        }

        $this->syncWorkoutAssignmentsForExistingUsers($workout);

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
        'month_no',
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
                        'month_no'   => $request->month_no[$i] ?? 1,
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
                            'alternate_exercise_id' => $this->nullableExerciseId($request->alternate_exercise_ids[$i][$key] ?? null),
                            'exercise_title'  => $request->exercise_titles[$i][$key] ?? null,
                            'instruction'     => $request->exercise_description[$i][$key] ?? null,
                            'alternate_exercise_description' => $request->alternate_exercise_description[$i][$key] ?? null,
                            'sequence'        => $key,
                        ]);
                    }
                }
            }
        }

        $this->syncWorkoutAssignmentsForExistingUsers($workout);

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

    protected function nullableExerciseId($exerciseId): ?int
    {
        if ($exerciseId === null || $exerciseId === '') {
            return null;
        }

        return (int) $exerciseId;
    }

    protected function syncWorkoutAssignmentsForExistingUsers(Workout $workout): int
    {
        $workout->loadMissing(['goal', 'level', 'workouttype']);

        if (!$this->workoutCanBeAutoAssigned($workout)) {
            return 0;
        }

        $assignedCount = 0;

        UserProfile::with('user')
            ->whereHas('user', function ($query) {
                $query->where('user_type', 'user')
                    ->where('status', 'active');
            })
            ->chunkById(200, function ($profiles) use ($workout, &$assignedCount) {
                foreach ($profiles as $profile) {
                    if (!$profile->user || !$this->profileMatchesWorkout($profile, $workout)) {
                        continue;
                    }

                    $alreadyAssigned = AssignWorkout::where('user_id', $profile->user_id)
                        ->where('workout_id', $workout->id)
                        ->exists();

                    if ($alreadyAssigned) {
                        continue;
                    }

                    AssignWorkout::create([
                        'user_id' => $profile->user_id,
                        'workout_id' => $workout->id,
                        'status' => 0,
                        'disable' => 0,
                        'cycle_no' => $this->resolveAssignmentCycleNo((int) $profile->user_id),
                        'assigned_from' => 'workout_auto_sync',
                        'is_active' => 1,
                    ]);

                    $assignedCount++;
                }
            });

        return $assignedCount;
    }

    protected function workoutCanBeAutoAssigned(Workout $workout): bool
    {
        return $workout->status === 'active'
            && !empty($workout->level_id)
            && $workout->goal_id !== null
            && $workout->goal_id !== ''
            && !empty($workout->workout_type_id)
            && !empty($workout->workout_days_plan);
    }

    protected function profileMatchesWorkout(UserProfile $profile, Workout $workout): bool
    {
        return $this->profileModeMatchesWorkout($profile, $workout)
            && $this->profileLevelMatchesWorkout($profile, $workout)
            && $this->profileGoalMatchesWorkout($profile, $workout)
            && $this->profileGenderMatchesWorkout($profile, $workout)
            && (int) $this->resolveWorkoutDaysPlan($profile->workout_days) === (int) $workout->workout_days_plan;
    }

    protected function profileModeMatchesWorkout(UserProfile $profile, Workout $workout): bool
    {
        if (is_numeric($profile->workout_mode) && (int) $profile->workout_mode === (int) $workout->workout_type_id) {
            return true;
        }

        return UserProfile::normalizeWorkoutMode($profile->workout_mode)
            === UserProfile::normalizeWorkoutMode(optional($workout->workouttype)->title);
    }

    protected function profileLevelMatchesWorkout(UserProfile $profile, Workout $workout): bool
    {
        if (is_numeric($profile->workout_level) && (int) $profile->workout_level === (int) $workout->level_id) {
            return true;
        }

        return UserProfile::normalizeWorkoutLevel($profile->workout_level)
            === UserProfile::normalizeWorkoutLevel(optional($workout->level)->title);
    }

    protected function profileGoalMatchesWorkout(UserProfile $profile, Workout $workout): bool
    {
        if ($this->isBothGoal($workout->goal_id, optional($workout->goal)->title)) {
            return true;
        }

        if ($this->isBothGoal($profile->goal, $profile->goal)) {
            return true;
        }

        if (is_numeric($profile->goal) && (int) $profile->goal === (int) $workout->goal_id) {
            return true;
        }

        return $this->normalizeGoal($profile->goal) === $this->normalizeGoal(optional($workout->goal)->title);
    }

    protected function profileGenderMatchesWorkout(UserProfile $profile, Workout $workout): bool
    {
        $workoutGender = Str::lower(trim((string) ($workout->gender ?? 'both')));

        if ($workoutGender === '' || $workoutGender === 'both') {
            return true;
        }

        $userGender = Str::lower(trim((string) optional($profile->user)->gender));

        return $userGender !== '' && $userGender === $workoutGender;
    }

    protected function resolveAssignmentCycleNo(int $userId): int
    {
        $activeCycle = AssignWorkout::where('user_id', $userId)
            ->where('is_active', 1)
            ->max('cycle_no');

        if ($activeCycle) {
            return (int) $activeCycle;
        }

        $lastCycle = AssignWorkout::where('user_id', $userId)->max('cycle_no');

        return $lastCycle ? (int) $lastCycle : 1;
    }

    protected function resolveWorkoutDaysPlan($workoutDays): ?int
    {
        if ($workoutDays === null) {
            return null;
        }

        if (is_array($workoutDays)) {
            $numericDays = array_filter($workoutDays, function ($item) {
                return is_numeric($item) && trim((string) $item) !== '';
            });

            if (!empty($numericDays)) {
                return (int) reset($numericDays);
            }

            return count($workoutDays);
        }

        if (is_numeric($workoutDays)) {
            return (int) $workoutDays;
        }

        if (is_string($workoutDays)) {
            $parts = array_filter(array_map('trim', explode(',', $workoutDays)), function ($item) {
                return $item !== '';
            });

            if (count($parts) === 1 && is_numeric($parts[0])) {
                return (int) $parts[0];
            }

            return count($parts);
        }

        return null;
    }

    protected function isBothGoal($goalId, $goalTitle): bool
    {
        return (int) $goalId === 0 || $this->normalizeGoal($goalTitle) === 'both';
    }

    protected function normalizeGoal($goal): string
    {
        return preg_replace('/[^a-z0-9]+/', '', Str::lower(trim((string) $goal))) ?? '';
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
