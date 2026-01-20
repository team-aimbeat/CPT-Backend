<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Workout;
use App\Http\Resources\WorkoutResource;
use App\Http\Resources\WorkoutDetailResource;
use App\Models\UserFavouriteWorkout;
use App\Models\WorkoutDay;
use App\Models\User;
use App\Models\Exercise;
use App\Models\WorkoutType;
use App\Models\AssignWorkout;
use App\Http\Resources\WorkoutDayResource;
use App\Models\WorkoutDayExercise;
use App\Models\LanguageList;
use App\Models\UserCompletedExercise;
use App\Http\Resources\WorkoutDayExerciseResource;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WorkoutController extends Controller
{
    
    // public function getAbsenteeCircularWorkouts(Request $request, $userId)
    //     {
    //         $user = User::find($userId);
        
    //         if (!$user) {
    //             return response()->json(['success' => false, 'message' => 'User not found.'], 404);
    //         }
        
    //         // --- 15 Days condition removed ---
    //         // Ab ye har baar data return karega agar circular workouts assigned hain.
        
    //         // 1. Fetch "Circular" Workouts and their exercises
    //         $preferredLanguageId = (int) $request->input('lang', 2);
    //         $preferredVideoKey = ($preferredLanguageId === 1) ? 'video_url_full' : 'english_video_url';
    //         $fallbackVideoKey = 'english_video_url';
    //         $excludedKeys = ['bodypart_ids', 'duration', 'based', 'type', 'equipment_id', 'level_id', 'sets'];
        
    //         $circularWorkouts = $user->assignedWorkouts()
    //             ->whereHas('workoutType', function($q) {
    //                 $q->where('title', 'Circular'); 
    //             })
    //             ->where('disable', 0)
    //             ->with(['workoutDays.workoutDayExercises.exercise.exerciseVideos'])
    //             ->get();
        
    //         // Agar koi circular workout nahi mila
    //         if ($circularWorkouts->isEmpty()) {
    //             return response()->json([
    //                 'success' => true,
    //                 'message' => 'No circular workouts found for this user.',
    //                 'workouts' => []
    //             ]);
    //         }
        
    //         $formattedWorkouts = $circularWorkouts->map(function ($workout) use ($preferredLanguageId, $preferredVideoKey, $fallbackVideoKey, $excludedKeys) {
                
    //             // Saari exercises nikalna (No week/day filter)
    //             $allExercises = $workout->workoutDays->flatMap(function ($day) use ($preferredLanguageId, $preferredVideoKey, $fallbackVideoKey, $excludedKeys) {
    //                 return $day->workoutDayExercises->map(function ($wde) use ($preferredLanguageId, $preferredVideoKey, $fallbackVideoKey, $excludedKeys) {
    //                     $exercise = $wde->exercise;
    //                     if (!$exercise) return null;
        
    //                     // Video selection logic
    //                     $selectedVideoUrl = $exercise->exerciseVideos->firstWhere('languagelist_id', $preferredLanguageId)->video_url 
    //                                         ?? $exercise->exerciseVideos->firstWhere('languagelist_id', 2)->video_url 
    //                                         ?? $exercise->{$preferredVideoKey} 
    //                                         ?? $exercise->{$fallbackVideoKey};
        
    //                     $exerciseArray = collect($exercise->toArray())->except($excludedKeys)->toArray();
    //                     $exerciseArray['selected_video_url'] = $selectedVideoUrl;
                        
    //                     return $exerciseArray;
    //                 });
    //             })->filter()->unique('id')->values();
        
    //             return [
    //                 'workout_id' => $workout->id,
    //                 'workout_name' => $workout->title,
    //                 'workout_type' => 'Circular',
    //                 'warmup_video' => $workout->video_url,
    //                 'exercises' => $allExercises,
    //             ];
    //         });
        
    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Returning all circular workouts.',
    //             'workouts' => $formattedWorkouts
    //         ]);
    //     }
    
    
    public function getAbsenteeCircularWorkouts(Request $request)
{
    /* -------------------------------
     | 1. LANGUAGE SETUP
     |--------------------------------*/
    $preferredLanguageId = (int) $request->input('lang', 2);
    $preferredVideoKey = ($preferredLanguageId === 1) ? 'video_url_full' : 'english_video_url';
    $fallbackVideoKey = 'english_video_url';

    $excludedKeys = [
        'bodypart_ids', 'duration', 'based',
        'type', 'equipment_id', 'level_id', 'sets'
    ];

    /* -------------------------------
     | 2. FETCH ALL CIRCULAR WORKOUTS
     |--------------------------------*/
    $circularWorkouts = Workout::whereHas('workoutType', function ($q) {
            $q->where('title', 'Circular');
        })
        ->where('status', 'active')
        ->with(['workoutDays.workoutDayExercises.exercise.exerciseVideos'])
        ->get();

    if ($circularWorkouts->isEmpty()) {
        return response()->json([
            'success' => true,
            'message' => 'No circular workouts available.',
            'workouts' => []
        ]);
    }

    /* -------------------------------
     | 3. FORMAT RESPONSE
     |--------------------------------*/
    $formattedWorkouts = $circularWorkouts->map(function ($workout) use (
        $preferredLanguageId,
        $preferredVideoKey,
        $fallbackVideoKey,
        $excludedKeys
    ) {

        // 🔁 All exercises from all days (no week/day filter)
        $allExercises = $workout->workoutDays
            ->flatMap(function ($day) use (
                $preferredLanguageId,
                $preferredVideoKey,
                $fallbackVideoKey,
                $excludedKeys
            ) {
                return $day->workoutDayExercises->map(function ($wde) use (
                    $preferredLanguageId,
                    $preferredVideoKey,
                    $fallbackVideoKey,
                    $excludedKeys
                ) {
                    $exercise = $wde->exercise;
                    if (!$exercise) return null;

                    $selectedVideoUrl =
                        optional($exercise->exerciseVideos
                            ->firstWhere('languagelist_id', $preferredLanguageId)
                        )->video_url
                        ?? optional($exercise->exerciseVideos
                            ->firstWhere('languagelist_id', 2)
                        )->video_url
                        ?? $exercise->{$preferredVideoKey}
                        ?? $exercise->{$fallbackVideoKey};

                    $exerciseArray = collect($exercise->toArray())
                        ->except($excludedKeys)
                        ->toArray();

                    $exerciseArray['selected_video_url'] = $selectedVideoUrl;

                    return $exerciseArray;
                });
            })
            ->filter()
            ->unique('id')
            ->values();

        return [
            'workout_id'   => $workout->id,
            'workout_name' => $workout->title,
            'workout_type' => 'Circular',
            'warmup_video' => $workout->video_url,
            'exercises'    => $allExercises,
        ];
    });

    /* -------------------------------
     | 4. FINAL RESPONSE
     |--------------------------------*/
    return response()->json([
        'success' => true,
        'message' => 'Circular workouts available for all users.',
        'workouts' => $formattedWorkouts
    ]);
}

    
    
    // public function getAbsenteeCircularWorkouts(Request $request, $userId)
    // {
    //     $user = User::find($userId);
    
    //     if (!$user) {
    //         return response()->json(['success' => false, 'message' => 'User not found.'], 404);
    //     }
    
    //     // 1. Check if user is absent for last 15 days
    //     // Hum 'updated_at' check kar rahe hain AssignWorkout table mein jahan status 1 (completed) ho
    //     $lastActivity = AssignWorkout::where('user_id', $userId)
    //         ->where('status', 1)
    //         ->orderBy('updated_at', 'desc')
    //         ->first();
    
    //     $isAbsent = false;
    //     if (!$lastActivity) {
    //         // Agar kabhi workout kiya hi nahi, to bhi hum use absent treat kar sakte hain
    //         $isAbsent = true; 
    //     } else {
    //         $daysSinceLastWorkout = Carbon::parse($lastActivity->updated_at)->diffInDays(Carbon::now());
    //         if ($daysSinceLastWorkout >= 15) {
    //             $isAbsent = true;
    //         }
    //     }
    
    //     if (!$isAbsent) {
    //         return response()->json([
    //             'success' => true,
    //             'message' => 'User is active. No circular workouts needed.',
    //             'data' => []
    //         ]);
    //     }
    
    //     // 2. Fetch "Circular" Workouts and their exercises
    //     // Hum week aur day filter ko hata rahe hain jaisa aapne pucha tha
    //     $preferredLanguageId = (int) $request->input('lang', 2);
    //     $preferredVideoKey = ($preferredLanguageId === 1) ? 'video_url_full' : 'english_video_url';
    //     $fallbackVideoKey = 'english_video_url';
    //     $excludedKeys = ['bodypart_ids', 'duration', 'based', 'type', 'equipment_id', 'level_id', 'sets'];
    
    //     $circularWorkouts = $user->assignedWorkouts()
    //         ->whereHas('workoutType', function($q) {
    //             $q->where('title', 'Circular'); // Yahan workout_types table check ho raha hai
    //         })
    //         ->where('disable', 0)
    //         ->with(['workoutDays.workoutDayExercises.exercise.exerciseVideos'])
    //         ->get();
    
    //     $formattedWorkouts = $circularWorkouts->map(function ($workout) use ($preferredLanguageId, $preferredVideoKey, $fallbackVideoKey, $excludedKeys) {
            
    //         // Saari exercises nikalna bina week/day filter ke
    //         $allExercises = $workout->workoutDays->flatMap(function ($day) use ($preferredLanguageId, $preferredVideoKey, $fallbackVideoKey, $excludedKeys) {
    //             return $day->workoutDayExercises->map(function ($wde) use ($preferredLanguageId, $preferredVideoKey, $fallbackVideoKey, $excludedKeys) {
    //                 $exercise = $wde->exercise;
    //                 if (!$exercise) return null;
    
    //                 // Video selection logic (Same as your reference)
    //                 $selectedVideoUrl = $exercise->exerciseVideos->firstWhere('languagelist_id', $preferredLanguageId)->video_url 
    //                                     ?? $exercise->exerciseVideos->firstWhere('languagelist_id', 2)->video_url 
    //                                     ?? $exercise->{$preferredVideoKey} 
    //                                     ?? $exercise->{$fallbackVideoKey};
    
    //                 $exerciseArray = collect($exercise->toArray())->except($excludedKeys)->toArray();
    //                 $exerciseArray['selected_video_url'] = $selectedVideoUrl;
                    
    //                 return $exerciseArray;
    //             });
    //         })->filter()->unique('id')->values();
    
    //         return [
    //             'workout_id' => $workout->id,
    //             'workout_name' => $workout->title,
    //             'workout_type' => 'Circular',
    //             'warmup_video' => $workout->video_url,
    //             'exercises' => $allExercises,
    //         ];
    //     });
    
    //     return response()->json([
    //         'success' => true,
    //         'is_absent' => true,
    //         'message' => 'Returning circular workouts due to 15+ days absence.',
    //         'workouts' => $formattedWorkouts
    //     ]);
    // }
    
    
    public function getList(Request $request)
    {
        $workout = Workout::where('status', 'active');

        $workout->when(request('title'), function ($q) {
            return $q->where('title', 'LIKE', '%' . request('title') . '%');
        });

        $workout->when(request('level_id'), function ($q) {
            $level_ids = explode(',', request('level_id'));
            return $q->whereIn('level_id', $level_ids);
        });

        $workout->when(request('level_ids'), function ($q) {
            $level_ids = explode(',', request('level_ids'));
            return $q->whereIn('level_id', $level_ids);
        });

        $workout->when(request('workout_type_id'), function ($q) {
            return $q->where('workout_type_id', request('workout_type_id'));
        });

        $workout->when(request('workout_type_ids'), function ($q) {
            $workout_type_ids = explode(',', request('workout_type_ids'));
            return $q->whereIn('workout_type_id', $workout_type_ids);
        });
        
        $workout->when(request('eqiupment_ids'), function ($query) {
            return $query->whereHas('workoutExercise',function ($q1) {
                $q1->whereHas('exercise', function ($q) {
                    $equipment_ids = explode(',', request('equipment_ids'));
                    $q->whereIn('equipment_id', $equipment_ids);
                });
            });
        });

        if( $request->has('is_premium') && isset($request->is_premium) ) {
            $workout = $workout->where('is_premium', request('is_premium'));
        }
        
        $per_page = config('constant.PER_PAGE_LIMIT');
        if( $request->has('per_page') && !empty($request->per_page)){
            if(is_numeric($request->per_page))
            {
                $per_page = $request->per_page;
            }
            if($request->per_page == -1 ){
                $per_page = $workout->count();
            }
        }

        $workout = $workout->orderBy('title', 'asc')->paginate($per_page);

        $items = WorkoutResource::collection($workout);

        $response = [
            'pagination'    => json_pagination_response($items),
            'data'          => $items,
        ];
        
        return json_custom_response($response);
    }

    public function getDetail(Request $request)
    {
        $workout = Workout::where('id',request('id'))->first();
           
        if( $workout == null )
        {
            return json_message_response( __('message.not_found_entry',['name' => __('message.workout') ]) );
        }

        $workout_data = new WorkoutDetailResource($workout);

        $response = [
            'data' => $workout_data,
            'workoutday' => $workout->workoutDay ?? [],
        ];
             
        return json_custom_response($response);
    }

    public function getUserFavouriteWorkout(Request $request)
    {
        $workout = Workout::myWorkout();

        $per_page = config('constant.PER_PAGE_LIMIT');
        if( $request->has('per_page') && !empty($request->per_page)) {
            if(is_numeric($request->per_page)) {
                $per_page = $request->per_page;
            }
            if($request->per_page == -1 ) {
                $per_page = $workout->count();
            }
        }

        $workout = $workout->orderBy('title', 'asc')->paginate($per_page);

        $items = WorkoutResource::collection($workout);

        $response = [
            'pagination'    => json_pagination_response($items),
            'data'          => $items,
        ];
        
        return json_custom_response($response);
    }

    public function userFavouriteWorkout(Request $request)
    {
        $user_id = auth()->id();
        $workout_id = $request->workout_id;

        $workout = Workout::where('id', $workout_id )->first();
        if( $workout == null )
        {
            return json_message_response( __('message.not_found_entry',['name' => __('message.workout') ]) );
        }
        $user_favourite_workout = UserFavouriteWorkout::where('user_id', $user_id)->where('workout_id', $workout_id)->first();
        
        if($user_favourite_workout != null) {
            $user_favourite_workout->delete();
            $message = __('message.unfavourite_workout_list');
        } else {
            $data = [
                'user_id'      => $user_id,
                'workout_id'   => $workout_id,
            ];
            
            UserFavouriteWorkout::create($data);
            $message = __('message.favourite_workout_list');
        }

        return json_message_response($message);
    }

    public function workoutDayList(Request $request)
    {
        $workoutday = WorkoutDay::where('workout_id',request('workout_id'));
        
        $per_page = config('constant.PER_PAGE_LIMIT');
        if( $request->has('per_page') && !empty($request->per_page)){
            if(is_numeric($request->per_page))
            {
                $per_page = $request->per_page;
            }
            if($request->per_page == -1 ){
                $per_page = $workoutday->count();
            }
        }

        $workoutday = $workoutday->paginate($per_page);

        $items = WorkoutDayResource::collection($workoutday);

        $response = [
            'pagination'    => json_pagination_response($items),
            'data'          => $items,
        ];
        
        return json_custom_response($response);
    }
    
    public function workoutDayExerciseList(Request $request)
    {
        $day_exercise = WorkoutDayExercise::where('workout_day_id',request('workout_day_id'));
        
        $per_page = config('constant.PER_PAGE_LIMIT');
        if( $request->has('per_page') && !empty($request->per_page)){
            if(is_numeric($request->per_page))
            {
                $per_page = $request->per_page;
            }
            if($request->per_page == -1 ){
                $per_page = $day_exercise->count();
            }
        }

        $day_exercise = $day_exercise->paginate($per_page);

        $items = WorkoutDayExerciseResource::collection($day_exercise);

        $response = [
            'pagination'    => json_pagination_response($items),
            'data'          => $items,
        ];
        
        return json_custom_response($response);
    }
    
    
  







public function getUserAssignedWorkouts(Request $request)
{
    /* -------------------------------------------------
     | 1. AUTH USER
     |--------------------------------------------------*/
    $user = auth('sanctum')->user();

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized'
        ], 401);
    }

    /* -------------------------------------------------
     | 2. LANGUAGE SETUP
     |--------------------------------------------------*/
    $preferredLanguageId = (int) $request->input('lang');
    $fallbackLanguageId  = 2; // English

    if (!$preferredLanguageId || $preferredLanguageId <= 0) {
        $preferredLanguageId = $fallbackLanguageId;
    }

    $preferredVideoKey = ($preferredLanguageId === 1)
        ? 'video_url_full'
        : 'english_video_url';

    $fallbackVideoKey = 'english_video_url';

    /* -------------------------------------------------
     | 3. DAY & WEEK CALCULATION
     |--------------------------------------------------*/
    $currentDayNumber  = date('N'); // 1–7
    $currentWeekNumber = (int) ceil(date('j') / 7);

    $dayNames = [
        1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday',
        4 => 'Thursday', 5 => 'Friday',
        6 => 'Saturday', 7 => 'Sunday'
    ];

    $currentDayName = $dayNames[$currentDayNumber] ?? null;

    /* -------------------------------------------------
     | 4. CURRENT ACTIVE CYCLE
     |--------------------------------------------------*/
    
    
    $currentCycle = DB::table('assign_workouts')
    ->where('user_id', $user->id)
    ->where(function ($q) {
        $q->where('status', '!=', 1)
          ->orWhere('disable', '!=', 1);
    })
    ->max('cycle_no');

    if (!$currentCycle) {
        return response()->json([
            'success' => true,
            'message' => 'No active workout plan',
            'workouts_for_today' => []
        ]);
    }

    /* -------------------------------------------------
     | 5. LOAD ACTIVE WORKOUTS (PIVOT SAFE)
     |--------------------------------------------------*/
    $user->load([
        'assignedWorkouts' => function ($query) use (
            $currentWeekNumber,
            $currentDayNumber,
            $currentCycle
        ) {
           
            
            $query->where('assign_workouts.cycle_no', $currentCycle)
              ->where(function ($q) {
                  $q->where('assign_workouts.status', '!=', 1)
                    ->orWhere('assign_workouts.disable', '!=', 1);
              })
            
                  ->with([
                      'workoutDays' => function ($query) use (
                          $currentWeekNumber,
                          $currentDayNumber
                      ) {
                          $query->where('week', $currentWeekNumber)
                                ->where('day', $currentDayNumber)
                                ->with('workoutDayExercises.exercise.exerciseVideos');
                      }
                  ]);
        }
    ]);

    /* -------------------------------------------------
     | 6. BUILD RESPONSE
     |--------------------------------------------------*/
    $excludedKeys = [
        'bodypart_ids', 'duration', 'based',
        'type', 'equipment_id', 'level_id', 'sets'
    ];

    $workoutsForToday = $user->assignedWorkouts
        ->filter(fn ($workout) => $workout->workoutDays->isNotEmpty())
        ->map(function ($workout) use (
            $dayNames,
            $preferredLanguageId,
            $fallbackLanguageId,
            $preferredVideoKey,
            $fallbackVideoKey,
            $excludedKeys
        ) {

            $today = $workout->workoutDays->first();
            $today->day_name = $dayNames[$today->day] ?? null;

            $exercises = $today->workoutDayExercises
                ->map(fn ($wde) => $wde->exercise)
                ->filter()
                ->map(function ($exercise) use (
                    $preferredLanguageId,
                    $fallbackLanguageId,
                    $preferredVideoKey,
                    $fallbackVideoKey,
                    $excludedKeys
                ) {

                    /* ---------------------------
                     | MAIN EXERCISE VIDEO
                     |---------------------------*/
                    $selectedVideoUrl =
                        optional($exercise->exerciseVideos
                            ->firstWhere('languagelist_id', $preferredLanguageId)
                        )->video_url
                        ?? optional($exercise->exerciseVideos
                            ->firstWhere('languagelist_id', $fallbackLanguageId)
                        )->video_url
                        ?? $exercise->{$preferredVideoKey}
                        ?? $exercise->{$fallbackVideoKey};

                    /* ---------------------------
                     | ALTERNATE EXERCISE
                     |---------------------------*/
                    $alternate = null;

                    if (!empty($exercise->exercise_id)) {

                        $alternateExercise = Exercise::with('exerciseVideos')
                            ->find($exercise->exercise_id);

                        if ($alternateExercise) {

                            $alternateSelectedVideo =
                                optional($alternateExercise->exerciseVideos
                                    ->firstWhere('languagelist_id', $preferredLanguageId)
                                )->video_url
                                ?? optional($alternateExercise->exerciseVideos
                                    ->firstWhere('languagelist_id', $fallbackLanguageId)
                                )->video_url
                                ?? $alternateExercise->{$preferredVideoKey}
                                ?? $alternateExercise->{$fallbackVideoKey};

                            $alternate = collect($alternateExercise->toArray())
                                ->except($excludedKeys)
                                ->toArray();

                            $alternate['exercise_videos'] =
                                $alternateExercise->exerciseVideos->map(fn ($video) => [
                                    'id' => $video->id,
                                    'languagelist_id' => $video->languagelist_id,
                                    'exercise_id' => $video->exercise_id,
                                    'video_url' => $video->video_url,
                                ])->toArray();

                            $alternate['selected_video_url'] = $alternateSelectedVideo;
                        }
                    }

                    /* ---------------------------
                     | MAIN EXERCISE RESPONSE
                     |---------------------------*/
                    $exerciseArray = collect($exercise->toArray())
                        ->except($excludedKeys)
                        ->toArray();

                    $exerciseArray['exercise_videos'] =
                        $exercise->exerciseVideos->map(fn ($video) => [
                            'id' => $video->id,
                            'languagelist_id' => $video->languagelist_id,
                            'exercise_id' => $video->exercise_id,
                            'video_url' => $video->video_url,
                        ])->toArray();

                    $exerciseArray['selected_video_url'] = $selectedVideoUrl;
                    $exerciseArray['alternate_exercise'] = $alternate;

                    return $exerciseArray;
                });

            return [
                'workout_id' => $workout->id,
                'workout_name' => $workout->title,
                'day_name' => $today->day_name,
                'workout_week' => (string) $today->week,
                'workout_day_number' => (string) $today->day,
                'warmup_video' => $workout->video_url,
                'stetch_video' => $workout->stetch_video,
                'warmup_video_url' => $workout->video_url_warmup,
                'exercises' => $exercises,
            ];
        })
        ->values();

    /* -------------------------------------------------
     | 7. FINAL RESPONSE
     |--------------------------------------------------*/
    return response()->json([
        'success' => true,
        'user_id' => $user->id,
        'user_name' => $user->first_name,
        'today_is' => $currentDayName,
        'current_week' => $currentWeekNumber,
        'current_cycle' => $currentCycle,
        'selected_language_id' => $preferredLanguageId,
        'workouts_for_today' => $workoutsForToday,
    ]);
}








 public function getExerciseDetails($exerciseId)
    {
        $exercise = Exercise::find($exerciseId);

        if (!$exercise) {
            return response()->json([
                'success' => false,
                'message' => 'Exercise not found.'
            ], 404);
        }

       
        if ($exercise->video_type === 'upload_video' && empty($exercise->video_url)) {
            
            
            if (function_exists('getSingleMedia')) {
                $resolvedVideoUrl = getSingleMedia($exercise, 'exercise_video');
            } else {
                $resolvedVideoUrl = null; 
            }
            
            $exercise->video_url = $resolvedVideoUrl;
        }

        return response()->json([
            'success' => true,
            'exercise' => $exercise->toArray(),
            'message' => 'Exercise details retrieved successfully.'
        ]);
    }
    
    
    
    
    public function markExerciseComplete(Request $request)
{
    $validated = $request->validate([
        'user_id' => 'required|exists:users,id',
        'exercise_id' => 'required|exists:exercises,id',
        'workout_id' => 'nullable|exists:workouts,id',
        'workout_day_id' => 'nullable|exists:workout_days,id',
    ]);

    $userId = $validated['user_id'];
    $exerciseId = $validated['exercise_id'];
    $workoutId = $validated['workout_id'] ?? null;
    $workoutDayId = $validated['workout_day_id'] ?? null;

    $existing = UserCompletedExercise::where('user_id', $userId)
        ->where('exercise_id', $exerciseId)
        ->where('workout_id', $workoutId)
        ->first();

    if ($existing) {
        return response()->json([
            'success' => false,
            'message' => 'Exercise already marked as completed.'
        ]);
    }

    $completed = UserCompletedExercise::create([
        'user_id' => $userId,
        'exercise_id' => $exerciseId,
        'workout_id' => $workoutId,
        'workout_day_id' => $workoutDayId,
        'completed_at' => now(),
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Exercise marked as completed successfully.',
        'data' => $completed,
    ]);
}



public function getMonthlyAttendance(Request $request)
{
    $validated = $request->validate([
        'user_id' => 'required|exists:users,id',
        'month' => 'required|integer|min:1|max:12', 
        'year' => 'required|integer|min:2020|max:' . date('Y'),
    ]);

    $userId = $validated['user_id'];
    $month = $validated['month'];
    $year = $validated['year'];

   
    $startDate = Carbon::create($year, $month, 1)->startOfDay();
    $endDate = Carbon::create($year, $month, 1)->endOfMonth()->endOfDay();

    $attendanceReport = [];
    $totalDaysAssigned = 0;
    $totalDaysPresent = 0;
    
    for ($date = clone $startDate; $date->lte($endDate); $date->addDay()) {
        $currentDayNumber = $date->format('N'); 
        $currentWeekNumber = (int)ceil($date->format('j') / 7); 
        
        if ($date->gt(now())) {
             break;
        }

        $assignedExercises = WorkoutDay::whereHas('workout.assignedWorkouts', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->where('week', $currentWeekNumber)
            ->where('day', $currentDayNumber)
            ->with('workoutDayExercises.exercise:id')
            ->get();
        
        $assignedExercisesCount = 0;
        $assignedWorkoutDayIds = [];
        $requiredExerciseIds = collect();

        foreach ($assignedExercises as $day) {
            $assignedWorkoutDayIds[] = $day->id;
            foreach ($day->workoutDayExercises as $wde) {
                if ($wde->exercise) {
                    $requiredExerciseIds->push($wde->exercise->id);
                    $assignedExercisesCount++;
                }
            }
        }
        
        if ($assignedExercisesCount === 0) {
             $attendanceReport[] = [
                'date' => $date->toDateString(),
                'day_name' => $date->format('l'),
                'status' => 'No Assignment/Rest Day',
                'details' => 'No exercises were assigned for this day in the current week/day cycle.',
            ];
            continue;
        }
        
        $totalDaysAssigned++;

        $completedCount = UserCompletedExercise::where('user_id', $userId)
            ->whereIn('exercise_id', $requiredExerciseIds->unique()->toArray())
            ->whereDate('completed_at', $date->toDateString())
            ->count();
        
        $isPresent = ($completedCount > 0 && $completedCount === $assignedExercisesCount);
        
        if ($isPresent) {
            $totalDaysPresent++;
        }
        
        $status = 'Absent'; 
        if ($isPresent) {
            $status = 'Present (Fully Completed)';
        } elseif ($assignedExercisesCount > 0 && $completedCount > 0) {
            $status = 'Partially Absent (' . $completedCount . '/' . $assignedExercisesCount . ' completed)';
        }

        $attendanceReport[] = [
            'date' => $date->toDateString(),
            'day_name' => $date->format('l'),
            'status' => $status,
            'completed_count' => $completedCount,
            'total_assigned' => $assignedExercisesCount,
        ];
    }
    
    $presentPercentage = $totalDaysAssigned > 0 ? round(($totalDaysPresent / $totalDaysAssigned) * 100, 2) : 0;

    return response()->json([
        'success' => true,
        'user_id' => $userId,
        'month' => $startDate->format('F'),
        'year' => $year,
        'summary' => [
            'total_assigned_days' => $totalDaysAssigned,
            'total_present_days' => $totalDaysPresent,
            'attendance_percentage' => $presentPercentage . '%',
        ],
        'attendance_report' => $attendanceReport,
    ]);
}






}

