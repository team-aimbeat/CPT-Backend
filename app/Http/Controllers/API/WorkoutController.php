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
use App\Models\StretchingVideo;
use App\Models\WarmupVideo;
use App\Models\WorkoutType;
use App\Models\AssignWorkout;
use App\Http\Resources\WorkoutDayResource;
use App\Models\WorkoutDayExercise;
use App\Models\LanguageList;
use App\Models\UserCompletedExercise;
use App\Models\Subscription;
use App\Models\CouponRedemption;
use App\Http\Resources\WorkoutDayExerciseResource;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WorkoutController extends Controller
{
    

public function getAbsenteeCircularWorkouts(Request $request)
{
    /* -------------------------------
     | 1. LANGUAGE SETUP
     |--------------------------------*/
    $preferredLanguageId = (int) $request->input('lang', 2);

    /* -------------------------------
     | 2. FETCH ALL CIRCULAR WORKOUTS
     |--------------------------------*/
    $circularWorkouts = Workout::whereHas('workoutType', function ($q) {
            $q->where('title', 'Circular');
        })
        ->where('status', 'active')
        ->with([
            'workoutDays.workoutDayExercises.exercise.exerciseVideos'
        ])
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
    $formattedWorkouts = $circularWorkouts->map(function ($workout) use ($preferredLanguageId) {

        $allExercises = $workout->workoutDays
            ->flatMap(function ($day) use ($preferredLanguageId) {

                return $day->workoutDayExercises->map(function ($wde) use ($preferredLanguageId) {

                    $exercise = $wde->exercise;
                    if (!$exercise) return null;

                    // 🎯 Preferred language → fallback English
                    $videoRow =
                        $exercise->exerciseVideos
                            ->firstWhere('languagelist_id', $preferredLanguageId)
                        ?? $exercise->exerciseVideos
                            ->firstWhere('languagelist_id', 2);

                    // 🎯 Resolution priority (HLS > MP4)
                    $videoPath =
                        $videoRow->hls_master_url
                        ?? $videoRow->hls_720p_url
                        ?? $videoRow->video_url
                        ?? null;

                    return [
                        'id' => $exercise->id,
                        'title' => $exercise->title,
                        'description' => $exercise->description,
                        'selected_video_url' => $videoPath
                            ? cloudfrontUrl($videoPath)
                            : null,
                        'thumbnail_url' => $exercise->thumbnail_url
                            ? cloudfrontUrl($exercise->thumbnail_url)
                            : null,
                    ];
                });
            })
            ->filter()
            ->unique('id')
            ->values();

        return [
            'workout_id'   => $workout->id,
            'workout_name' => $workout->title,
            'workout_type' => 'Circular',

            // 🔥 Warmup video via CloudFront
            'warmup_video' => $workout->video_url
                ? cloudfrontUrl($workout->video_url)
                : null,

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

    
    
    
//     public function getAbsenteeCircularWorkouts(Request $request)
// {
//     /* -------------------------------
//      | 1. LANGUAGE SETUP
//      |--------------------------------*/
//     $preferredLanguageId = (int) $request->input('lang', 2);
//     $preferredVideoKey = ($preferredLanguageId === 1) ? 'video_url_full' : 'english_video_url';
//     $fallbackVideoKey = 'english_video_url';

//     $excludedKeys = [
//         'bodypart_ids', 'duration', 'based',
//         'type', 'equipment_id', 'level_id', 'sets'
//     ];

//     /* -------------------------------
//      | 2. FETCH ALL CIRCULAR WORKOUTS
//      |--------------------------------*/
//     $circularWorkouts = Workout::whereHas('workoutType', function ($q) {
//             $q->where('title', 'Circular');
//         })
//         ->where('status', 'active')
//         ->with(['workoutDays.workoutDayExercises.exercise.exerciseVideos'])
//         ->get();

//     if ($circularWorkouts->isEmpty()) {
//         return response()->json([
//             'success' => true,
//             'message' => 'No circular workouts available.',
//             'workouts' => []
//         ]);
//     }

//     /* -------------------------------
//      | 3. FORMAT RESPONSE
//      |--------------------------------*/
//     $formattedWorkouts = $circularWorkouts->map(function ($workout) use (
//         $preferredLanguageId,
//         $preferredVideoKey,
//         $fallbackVideoKey,
//         $excludedKeys
//     ) {

//         // 🔁 All exercises from all days (no week/day filter)
//         $allExercises = $workout->workoutDays
//             ->flatMap(function ($day) use (
//                 $preferredLanguageId,
//                 $preferredVideoKey,
//                 $fallbackVideoKey,
//                 $excludedKeys
//             ) {
//                 return $day->workoutDayExercises->map(function ($wde) use (
//                     $preferredLanguageId,
//                     $preferredVideoKey,
//                     $fallbackVideoKey,
//                     $excludedKeys
//                 ) {
//                     $exercise = $wde->exercise;
//                     if (!$exercise) return null;

//                     $selectedVideoUrl =
//                         optional($exercise->exerciseVideos
//                             ->firstWhere('languagelist_id', $preferredLanguageId)
//                         )->video_url
//                         ?? optional($exercise->exerciseVideos
//                             ->firstWhere('languagelist_id', 2)
//                         )->video_url
//                         ?? $exercise->{$preferredVideoKey}
//                         ?? $exercise->{$fallbackVideoKey};

//                     $exerciseArray = collect($exercise->toArray())
//                         ->except($excludedKeys)
//                         ->toArray();

//                     $exerciseArray['selected_video_url'] = $selectedVideoUrl;

//                     return $exerciseArray;
//                 });
//             })
//             ->filter()
//             ->unique('id')
//             ->values();

//         return [
//             'workout_id'   => $workout->id,
//             'workout_name' => $workout->title,
//             'workout_type' => 'Circular',
//             'warmup_video' => $workout->video_url,
//             'exercises'    => $allExercises,
//         ];
//     });

//     /* -------------------------------
//      | 4. FINAL RESPONSE
//      |--------------------------------*/
//     return response()->json([
//         'success' => true,
//         'message' => 'Circular workouts available for all users.',
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
    
    
  



//new backup

// public function getUserAssignedWorkouts(Request $request)
// {
//     /* -------------------------------------------------
//      | 1. AUTH USER
//      |--------------------------------------------------*/
//     $user = auth('sanctum')->user();

//     if (!$user) {
//         return response()->json([
//             'success' => false,
//             'message' => 'Unauthorized'
//         ], 401);
//     }

//     /* -------------------------------------------------
//      | 2. LANGUAGE SETUP
//      |--------------------------------------------------*/
//     $preferredLanguageId = (int) $request->input('lang');
//     $fallbackLanguageId  = 2;

//     if (!$preferredLanguageId || $preferredLanguageId <= 0) {
//         $preferredLanguageId = $fallbackLanguageId;
//     }

//     /* -------------------------------------------------
//      | 3. DAY & WEEK
//      |--------------------------------------------------*/

//     $dayNames = [
//         1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday',
//         4 => 'Thursday', 5 => 'Friday',
//         6 => 'Saturday', 7 => 'Sunday'
//     ];

//     $currentDayName = $dayNames[$currentDayNumber] ?? null;

//     /* -------------------------------------------------
//      | 4. ✅ CURRENT ACTIVE CYCLE (FIXED LOGIC)
//      |--------------------------------------------------*/
//     // $currentCycle = DB::table('assign_workouts')
//     //     ->where('user_id', $user->id)
//     //     ->where('assigned_from', 'workout_mode_update')
//     //     ->where('disable', 0)
//     //     ->orderByDesc('id')
//     //     ->value('cycle_no');

//     $currentCycle = DB::table('assign_workouts')
//     ->where('user_id', $user->id)
//     ->where('is_active', 1)
//     ->value('cycle_no');

//     // 🛟 fallback: first-time users / old users
//     if (!$currentCycle) {
//         $currentCycle = DB::table('assign_workouts')
//             ->where('user_id', $user->id)
//             ->where('disable', 0)
//             ->orderByDesc('id')
//             ->value('cycle_no');
//     }

//     if (!$currentCycle) {
//         return response()->json([
//             'success' => true,
//             'message' => 'No active workout plan',
//             'workouts_for_today' => []
//         ]);
//     }

//     /* -------------------------------------------------
//      | 5. LOAD ASSIGNED WORKOUTS
//      |--------------------------------------------------*/
//     $user->load([
//         'assignedWorkouts' => function ($query) use (
//             $currentWeekNumber,
//             $currentDayNumber,
//             $currentCycle
//         ) {
//             $query->where('assign_workouts.cycle_no', $currentCycle)
//                 ->where('assign_workouts.disable', 0)
//                 ->with([
//                     'workoutDays' => function ($q) use (
//                         $currentWeekNumber,
//                         $currentDayNumber
//                     ) {
//                         $q->where('week', $currentWeekNumber)
//                           ->where('day', $currentDayNumber)
//                           ->with('workoutDayExercises.exercise.exerciseVideos');
//                     }
//                 ]);
//         }
//     ]);

//     /* -------------------------------------------------
//      | 6. RESPONSE BUILD
//      |--------------------------------------------------*/
//     $excludedKeys = [
//         'bodypart_ids', 'duration', 'based',
//         'type', 'equipment_id', 'level_id', 'sets'
//     ];

//     $workoutsForToday = $user->assignedWorkouts
//         ->filter(fn ($workout) => $workout->workoutDays->isNotEmpty())
//         ->map(function ($workout) use (
//             $dayNames,
//             $preferredLanguageId,
//             $fallbackLanguageId,
//             $excludedKeys
//         ) {

//             $today = $workout->workoutDays->first();
//             $today->day_name = $dayNames[$today->day] ?? null;

//             $exercises = $today->workoutDayExercises
//                 ->map(fn ($wde) => $wde->exercise)
//                 ->filter()
//                 ->map(function ($exercise) use (
//                     $preferredLanguageId,
//                     $fallbackLanguageId,
//                     $excludedKeys
//                 ) {

//                     /* ---------------------------
//                      | MAIN VIDEO (HLS priority)
//                      |---------------------------*/
//                     $videoRow =
//                         $exercise->exerciseVideos
//                             ->firstWhere('languagelist_id', $preferredLanguageId)
//                         ?? $exercise->exerciseVideos
//                             ->firstWhere('languagelist_id', $fallbackLanguageId);

//                     $selectedVideoPath =
//                         $videoRow->hls_master_url
//                         ?? $videoRow->hls_720p_url
//                         ?? $videoRow->video_url
//                         ?? null;

//                     /* ---------------------------
//                      | ALTERNATE EXERCISE
//                      |---------------------------*/
//                     $alternate = null;

//                     if (!empty($exercise->exercise_id)) {

//                         $alt = Exercise::with('exerciseVideos')
//                             ->find($exercise->exercise_id);

//                         if ($alt) {

//                             $altRow =
//                                 $alt->exerciseVideos
//                                     ->firstWhere('languagelist_id', $preferredLanguageId)
//                                 ?? $alt->exerciseVideos
//                                     ->firstWhere('languagelist_id', $fallbackLanguageId);

//                             $altVideoPath =
//                                 $altRow->hls_master_url
//                                 ?? $altRow->hls_720p_url
//                                 ?? $altRow->video_url
//                                 ?? null;

//                             $alternate = collect($alt->toArray())
//                                 ->except($excludedKeys)
//                                 ->toArray();

//                             $alternate['selected_video_url'] =
//                                 $altVideoPath ? cloudfrontUrl($altVideoPath) : null;

//                             $alternate['exercise_image'] =
//                                 $alt->exercise_image ? cloudfrontUrl($alt->exercise_image) : null;

//                             $alternate['exercise_gif'] =
//                                 $alt->exercise_gif ? cloudfrontUrl($alt->exercise_gif) : null;
//                         }
//                     }

//                     /* ---------------------------
//                      | MAIN EXERCISE RESPONSE
//                      |---------------------------*/
//                     $exerciseArray = collect($exercise->toArray())
//                         ->except($excludedKeys)
//                         ->toArray();

//                     $exerciseArray['selected_video_url'] =
//                         $selectedVideoPath ? cloudfrontUrl($selectedVideoPath) : null;

//                     $exerciseArray['exercise_image'] =
//                         $exercise->exercise_image ? cloudfrontUrl($exercise->exercise_image) : null;

//                     $exerciseArray['exercise_gif'] =
//                         $exercise->exercise_gif ? cloudfrontUrl($exercise->exercise_gif) : null;

//                     unset(
//                         $exerciseArray['exercise_image_url'],
//                         $exerciseArray['exercise_gif_url']
//                     );

//                     $exerciseArray['alternate_exercise'] = $alternate;

//                     return $exerciseArray;
//                 });

//             return [
//                 'workout_id' => $workout->id,
//                 'workout_name' => $workout->title,
//                 'day_name' => $today->day_name,
//                 'workout_week' => (string) $today->week,
//                 'workout_day_number' => (string) $today->day,

//                 'warmup_video' =>
//                     $workout->video_url ? cloudfrontUrl($workout->video_url) : null,

//                 'stretch_video' =>
//                     $workout->stetch_video ? cloudfrontUrl($workout->stetch_video) : null,

//                 'warmup_video_url' =>
//                     $workout->video_url_warmup ? cloudfrontUrl($workout->video_url_warmup) : null,

//                 'exercises' => $exercises,
//             ];
//         })
//         ->values();

//     /* -------------------------------------------------
//      | 7. FINAL RESPONSE
//      |--------------------------------------------------*/
//     return response()->json([
//         'success' => true,
//         'user_id' => $user->id,
//         'user_name' => $user->first_name,
//         'today_is' => $currentDayName,
//         'current_week' => $currentWeekNumber,
//         'current_cycle' => $currentCycle, // ✅ FIXED
//         'selected_language_id' => $preferredLanguageId,
//         'workouts_for_today' => $workoutsForToday,
//     ]);
// }




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

    $subscription = Subscription::where('user_id', $user->id)
        ->where('status', 'active')
        ->where('payment_status', 'paid')
        ->whereHas('package', function ($q) {
            $q->whereIn('package_type', ['workout', 'both']);
        })
        ->orderByDesc('subscription_start_date')
        ->orderByDesc('created_at')
        ->first();

    $hasAccess = (bool) $subscription;

    $hasCouponAccess = $user->hasActiveCouponAccess();

    $hasAccess = $hasAccess || $hasCouponAccess;

    if (!$hasAccess) {
        return response()->json([
            'success' => false,
            'message' => 'Please subscribe or apply an active coupon to unlock and watch all workout videos.',
        ], 403);
    }

    /* -------------------------------------------------
     | 2. LANGUAGE SETUP
     |--------------------------------------------------*/
    $preferredLanguageId = (int) $request->input('lang', 2);
    $fallbackLanguageId  = 2;

    $workoutDays = (int) optional($user->userProfile)->workout_days;
    if (!in_array($workoutDays, [3, 6], true)) {
        $workoutDays = 6;
    }
    /* -------------------------------------------------
     | 2.1 START DATE (SUBSCRIPTION > COUPON)
     |--------------------------------------------------*/
    $startDate = null;
    if ($subscription) {
        $startDate = $subscription->subscription_start_date ?: $subscription->created_at;
    }

    if (!$startDate && $hasCouponAccess) {
        $couponRedemption = CouponRedemption::where('user_id', $user->id)
            ->whereHas('coupon', function ($q) {
                $q->where('status', 'active');
            })
            ->orderByDesc('redeemed_at')
            ->orderByDesc('created_at')
            ->first();

        $startDate = $couponRedemption?->redeemed_at ?: $couponRedemption?->created_at;
    }

    $startDate = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::now()->startOfDay();
    $today = Carbon::now()->startOfDay();

    if ($today->lt($startDate)) {
        $startDate = $today;
    }
    /* -------------------------------------------------
     | 3. DAY & WEEK
     |--------------------------------------------------*/
    $daysSinceStart = $startDate->diffInDays($today);
    $currentWeekNumber = (int) floor($daysSinceStart / 7) + 1;
    $currentDayNumber = (int) ($daysSinceStart % 7) + 1;

    $dayNames = [
        1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday',
        4 => 'Thursday', 5 => 'Friday',
        6 => 'Saturday', 7 => 'Sunday'
    ];

    if ($currentDayNumber === 7) {
        return response()->json([
            'success' => true,
            'message' => 'Today is a rest day.',
            'user_id' => $user->id,
            'user_name' => $user->first_name,
            'today_is' => 'Day ' . $currentDayNumber,
            'current_week' => $currentWeekNumber,
            'current_cycle' => null,
            'workout_days_plan' => $workoutDays,
            'completed_days_this_week' => 0,
            'selected_language_id' => $preferredLanguageId,
            'workouts_for_today' => []
        ]);
    }

    $weekStart = (clone $startDate)->addDays(($currentWeekNumber - 1) * 7);
    $weekEnd = (clone $weekStart)->addDays(5); // Mon-Sat (6 days), day 7 is rest
    $completedDates = AssignWorkout::where('user_id', $user->id)
        ->where('status', 1)
        ->whereBetween('updated_at', [$weekStart, $weekEnd])
        ->get()
        ->map(function ($item) {
            return $item->updated_at->format('Y-m-d');
        })
        ->unique()
        ->values();

    if ($workoutDays === 3) {
        $todayKey = $today->format('Y-m-d');
        if ($completedDates->contains($todayKey)) {
            return response()->json([
                'success' => true,
                'message' => 'Workout already completed for today.',
                'user_id' => $user->id,
                'user_name' => $user->first_name,
                'today_is' => 'Day ' . $currentDayNumber,
                'current_week' => $currentWeekNumber,
                'current_cycle' => null,
                'workout_days_plan' => $workoutDays,
                'completed_days_this_week' => $completedDates->count(),
                'selected_language_id' => $preferredLanguageId,
                'workouts_for_today' => []
            ]);
        }

        if ($completedDates->count() >= 3) {
            return response()->json([
                'success' => true,
                'message' => 'Weekly workout limit completed.',
                'user_id' => $user->id,
                'user_name' => $user->first_name,
                'today_is' => 'Day ' . $currentDayNumber,
                'current_week' => $currentWeekNumber,
                'current_cycle' => null,
                'workout_days_plan' => $workoutDays,
                'completed_days_this_week' => $completedDates->count(),
                'selected_language_id' => $preferredLanguageId,
                'workouts_for_today' => []
            ]);
        }
    }

    /* -------------------------------------------------
     | 4. CURRENT ACTIVE CYCLE
     |--------------------------------------------------*/
    $currentCycle = DB::table('assign_workouts')
        ->where('user_id', $user->id)
        ->where('is_active', 1)
        ->value('cycle_no');

    if (!$currentCycle) {
        return response()->json([
            'success' => true,
            'user_id' => $user->id,
            'user_name' => $user->first_name,
            'today_is' => 'Day ' . $currentDayNumber,
            'current_week' => $currentWeekNumber,
            'current_cycle' => null,
            'selected_language_id' => $preferredLanguageId,
            'workouts_for_today' => []
        ]);
    }

    /* -------------------------------------------------
     | 5. LOAD ASSIGNED WORKOUTS
     |--------------------------------------------------*/
    $user->load([
        'assignedWorkouts' => function ($q) use (
            $currentWeekNumber,
            $currentDayNumber,
            $currentCycle
        ) {
            $q->where('assign_workouts.cycle_no', $currentCycle)
              ->where('assign_workouts.disable', 0)
              ->with([
                  'workoutDays' => function ($qd) use (
                      $currentWeekNumber,
                      $currentDayNumber
                  ) {
                      $qd->where('week', $currentWeekNumber)
                         ->where('day', $currentDayNumber)
                         ->with('workoutDayExercises.exercise.exerciseVideos');
                  }
              ]);
        }
    ]);

    /* -------------------------------------------------
     | 6. BUILD RESPONSE
     |--------------------------------------------------*/
    $warmupVideo = WarmupVideo::where('languagelist_id', $preferredLanguageId)
        ->where('transcoding_status', 'done')
        ->orderByDesc('id')
        ->first();
    if (!$warmupVideo && $fallbackLanguageId) {
        $warmupVideo = WarmupVideo::where('languagelist_id', $fallbackLanguageId)
            ->where('transcoding_status', 'done')
            ->orderByDesc('id')
            ->first();
    }

    $stretchingVideo = StretchingVideo::where('languagelist_id', $preferredLanguageId)
        ->where('transcoding_status', 'done')
        ->orderByDesc('id')
        ->first();
    if (!$stretchingVideo && $fallbackLanguageId) {
        $stretchingVideo = StretchingVideo::where('languagelist_id', $fallbackLanguageId)
            ->where('transcoding_status', 'done')
            ->orderByDesc('id')
            ->first();
    }

    $warmupUrl = null;
    if ($warmupVideo) {
        $warmupUrl = $warmupVideo->hls_master_url ?: $warmupVideo->video_url;
        $warmupUrl = $warmupUrl ? cloudfrontUrl($warmupUrl) : null;
    }
    $stretchingUrl = null;
    if ($stretchingVideo) {
        $stretchingUrl = $stretchingVideo->hls_master_url ?: $stretchingVideo->video_url;
        $stretchingUrl = $stretchingUrl ? cloudfrontUrl($stretchingUrl) : null;
    }

    $workoutsForToday = $user->assignedWorkouts
        ->filter(fn ($w) => $w->workoutDays->isNotEmpty())
        ->map(function ($workout) use (
            $dayNames,
            $preferredLanguageId,
            $fallbackLanguageId,
            $warmupUrl,
            $stretchingUrl
        ) {

            $days = $workout->workoutDays;
            $day = $days->first();

            $exercises = $days
                ->flatMap(function ($day) {
                    return $day->workoutDayExercises;
                })
                ->filter(fn ($wde) => $wde->exercise)
                ->sortBy('sequence')
                ->values()
                ->map(function ($wde) use (
                    $preferredLanguageId,
                    $fallbackLanguageId
                ) {

                    $exercise = $wde->exercise;

                    /* ---------------- VIDEO SELECTION ---------------- */
                    $videoRow =
                        $exercise->exerciseVideos
                            ->firstWhere('languagelist_id', $preferredLanguageId)
                        ?? $exercise->exerciseVideos
                            ->firstWhere('languagelist_id', $fallbackLanguageId);

                    $selectedPath =
                        $videoRow->hls_master_url
                        ?? $videoRow->hls_720p_url
                        ?? $videoRow->video_url
                        ?? null;

                    /* ---------------- ALTERNATE EXERCISE ---------------- */
                    $alternate = null;

                    if ($exercise->exercise_id) {
                        $alt = Exercise::with('exerciseVideos')
                            ->find($exercise->exercise_id);

                        if ($alt) {
                            $altVideo =
                                $alt->exerciseVideos
                                    ->firstWhere('languagelist_id', $preferredLanguageId)
                                ?? $alt->exerciseVideos
                                    ->firstWhere('languagelist_id', $fallbackLanguageId);

                            $alternate = [
                                'id' => $alt->id,
                                'title' => $alt->title,
                                'description' => $alt->instruction,
                                'exercise_image' => $alt->exercise_image
                                    ? cloudfrontUrl($alt->exercise_image)
                                    : null,
                                'exercise_gif' => ($alt->exercise_gif_hls_master_url ?? null)
                                    ? cloudfrontUrl($alt->exercise_gif_hls_master_url)
                                    : ($alt->exercise_gif ? cloudfrontUrl($alt->exercise_gif) : null),
                                'exercise_gif_poster_url' => $alt->exercise_gif_poster_url
                                    ? cloudfrontUrl($alt->exercise_gif_poster_url)
                                    : null,
                                'selected_video_url' => $altVideo
                                    ? cloudfrontUrl(
                                        $altVideo->hls_master_url
                                        ?? $altVideo->video_url
                                    )
                                    : null,
                            ];
                        }
                    }

                    return [
                        'id' => $exercise->id,
                        'title' => $exercise->title,
                        'exercise_title' => $wde->exercise_title,
                        // ✅ IMPORTANT: instruction from workout_day_exercises
                        'instruction' => $wde->instruction,

                        'exercise_image' => $exercise->exercise_image
                            ? cloudfrontUrl($exercise->exercise_image)
                            : null,

                        'exercise_gif' => ($exercise->exercise_gif_hls_master_url ?? null)
                            ? cloudfrontUrl($exercise->exercise_gif_hls_master_url)
                            : ($exercise->exercise_gif ? cloudfrontUrl($exercise->exercise_gif) : null),
                        'exercise_gif_poster_url' => $exercise->exercise_gif_poster_url
                            ? cloudfrontUrl($exercise->exercise_gif_poster_url)
                            : null,

                        'exercise_videos' => $exercise->exerciseVideos->map(fn ($v) => [
                            'id' => $v->id,
                            'languagelist_id' => $v->languagelist_id,
                            'exercise_id' => $v->exercise_id,
                            'video_url' => $v->video_url,
                            'hls_master_url' => $v->hls_master_url,
                            'poster_url' => $v->poster_url,
                        ]),

                        'selected_video_url' => $selectedPath
                            ? cloudfrontUrl($selectedPath)
                            : null,

                        'alternate_exercise' => $alternate,
                    ];
                });

            return [
                'workout_id' => $workout->id,
                'workout_name' => $workout->title,
                'day_name' => $dayNames[$day->day] ?? null,
                'workout_week' => (string) $day->week,
                'workout_day_number' => (string) $day->day,
                'warmup_video' => $warmupUrl,
                'stretch_video' => $stretchingUrl,
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
        'today_is' => 'Day ' . $currentDayNumber,
        'current_week' => $currentWeekNumber,
        'current_cycle' => $currentCycle,
        'workout_days_plan' => $workoutDays,
        'completed_days_this_week' => $completedDates->count(),
        'selected_language_id' => $preferredLanguageId,
        'workouts_for_today' => $workoutsForToday,
    ]);
}






//old bacup

// public function getUserAssignedWorkouts(Request $request)
// {
//     /* -------------------------------------------------
//      | 1. AUTH USER
//      |--------------------------------------------------*/
//     $user = auth('sanctum')->user();

//     if (!$user) {
//         return response()->json([
//             'success' => false,
//             'message' => 'Unauthorized'
//         ], 401);
//     }

//     /* -------------------------------------------------
//      | 2. LANGUAGE SETUP
//      |--------------------------------------------------*/
//     $preferredLanguageId = (int) $request->input('lang');
//     $fallbackLanguageId  = 2; // English

//     if (!$preferredLanguageId || $preferredLanguageId <= 0) {
//         $preferredLanguageId = $fallbackLanguageId;
//     }

//     $preferredVideoKey = ($preferredLanguageId === 1)
//         ? 'video_url_full'
//         : 'english_video_url';

//     $fallbackVideoKey = 'english_video_url';

//     /* -------------------------------------------------
//      | 3. DAY & WEEK CALCULATION
//      |--------------------------------------------------*/
//     $currentDayNumber  = date('N'); // 1–7
//     $currentWeekNumber = (int) ceil(date('j') / 7);

//     $dayNames = [
//         1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday',
//         4 => 'Thursday', 5 => 'Friday',
//         6 => 'Saturday', 7 => 'Sunday'
//     ];

//     $currentDayName = $dayNames[$currentDayNumber] ?? null;

//     /* -------------------------------------------------
//      | 4. CURRENT ACTIVE CYCLE
//      |--------------------------------------------------*/
    
    
//     $currentCycle = DB::table('assign_workouts')
//     ->where('user_id', $user->id)
//     ->where(function ($q) {
//         $q->where('status', '!=', 1)
//           ->orWhere('disable', '!=', 1);
//     })
//     ->max('cycle_no');

//     if (!$currentCycle) {
//         return response()->json([
//             'success' => true,
//             'message' => 'No active workout plan',
//             'workouts_for_today' => []
//         ]);
//     }

//     /* -------------------------------------------------
//      | 5. LOAD ACTIVE WORKOUTS (PIVOT SAFE)
//      |--------------------------------------------------*/
//     $user->load([
//         'assignedWorkouts' => function ($query) use (
//             $currentWeekNumber,
//             $currentDayNumber,
//             $currentCycle
//         ) {
           
            
//             $query->where('assign_workouts.cycle_no', $currentCycle)
//               ->where(function ($q) {
//                   $q->where('assign_workouts.status', '!=', 1)
//                     ->orWhere('assign_workouts.disable', '!=', 1);
//               })
            
//                   ->with([
//                       'workoutDays' => function ($query) use (
//                           $currentWeekNumber,
//                           $currentDayNumber
//                       ) {
//                           $query->where('week', $currentWeekNumber)
//                                 ->where('day', $currentDayNumber)
//                                 ->with('workoutDayExercises.exercise.exerciseVideos');
//                       }
//                   ]);
//         }
//     ]);

//     /* -------------------------------------------------
//      | 6. BUILD RESPONSE
//      |--------------------------------------------------*/
//     $excludedKeys = [
//         'bodypart_ids', 'duration', 'based',
//         'type', 'equipment_id', 'level_id', 'sets'
//     ];

//     $workoutsForToday = $user->assignedWorkouts
//         ->filter(fn ($workout) => $workout->workoutDays->isNotEmpty())
//         ->map(function ($workout) use (
//             $dayNames,
//             $preferredLanguageId,
//             $fallbackLanguageId,
//             $preferredVideoKey,
//             $fallbackVideoKey,
//             $excludedKeys
//         ) {

//             $today = $workout->workoutDays->first();
//             $today->day_name = $dayNames[$today->day] ?? null;

//             $exercises = $today->workoutDayExercises
//                 ->map(fn ($wde) => $wde->exercise)
//                 ->filter()
//                 ->map(function ($exercise) use (
//                     $preferredLanguageId,
//                     $fallbackLanguageId,
//                     $preferredVideoKey,
//                     $fallbackVideoKey,
//                     $excludedKeys
//                 ) {

//                     /* ---------------------------
//                      | MAIN EXERCISE VIDEO
//                      |---------------------------*/
//                     $selectedVideoUrl =
//                         optional($exercise->exerciseVideos
//                             ->firstWhere('languagelist_id', $preferredLanguageId)
//                         )->video_url
//                         ?? optional($exercise->exerciseVideos
//                             ->firstWhere('languagelist_id', $fallbackLanguageId)
//                         )->video_url
//                         ?? $exercise->{$preferredVideoKey}
//                         ?? $exercise->{$fallbackVideoKey};

//                     /* ---------------------------
//                      | ALTERNATE EXERCISE
//                      |---------------------------*/
//                     $alternate = null;

//                     if (!empty($exercise->exercise_id)) {

//                         $alternateExercise = Exercise::with('exerciseVideos')
//                             ->find($exercise->exercise_id);

//                         if ($alternateExercise) {

//                             $alternateSelectedVideo =
//                                 optional($alternateExercise->exerciseVideos
//                                     ->firstWhere('languagelist_id', $preferredLanguageId)
//                                 )->video_url
//                                 ?? optional($alternateExercise->exerciseVideos
//                                     ->firstWhere('languagelist_id', $fallbackLanguageId)
//                                 )->video_url
//                                 ?? $alternateExercise->{$preferredVideoKey}
//                                 ?? $alternateExercise->{$fallbackVideoKey};

//                             $alternate = collect($alternateExercise->toArray())
//                                 ->except($excludedKeys)
//                                 ->toArray();

//                             $alternate['exercise_videos'] =
//                                 $alternateExercise->exerciseVideos->map(fn ($video) => [
//                                     'id' => $video->id,
//                                     'languagelist_id' => $video->languagelist_id,
//                                     'exercise_id' => $video->exercise_id,
//                                     'video_url' => $video->video_url,
//                                 ])->toArray();

//                             $alternate['selected_video_url'] = $alternateSelectedVideo;
//                         }
//                     }

//                     /* ---------------------------
//                      | MAIN EXERCISE RESPONSE
//                      |---------------------------*/
//                     $exerciseArray = collect($exercise->toArray())
//                         ->except($excludedKeys)
//                         ->toArray();

//                     $exerciseArray['exercise_videos'] =
//                         $exercise->exerciseVideos->map(fn ($video) => [
//                             'id' => $video->id,
//                             'languagelist_id' => $video->languagelist_id,
//                             'exercise_id' => $video->exercise_id,
//                             'video_url' => $video->video_url,
//                         ])->toArray();

//                     $exerciseArray['selected_video_url'] = $selectedVideoUrl;
//                     $exerciseArray['alternate_exercise'] = $alternate;

//                     return $exerciseArray;
//                 });

//             return [
//                 'workout_id' => $workout->id,
//                 'workout_name' => $workout->title,
//                 'day_name' => $today->day_name,
//                 'workout_week' => (string) $today->week,
//                 'workout_day_number' => (string) $today->day,
//                 'warmup_video' => $workout->video_url,
//                 'stetch_video' => $workout->stetch_video,
//                 'warmup_video_url' => $workout->video_url_warmup,
//                 'exercises' => $exercises,
//             ];
//         })
//         ->values();

//     /* -------------------------------------------------
//      | 7. FINAL RESPONSE
//      |--------------------------------------------------*/
//     return response()->json([
//         'success' => true,
//         'user_id' => $user->id,
//         'user_name' => $user->first_name,
//         'today_is' => $currentDayName,
//         'current_week' => $currentWeekNumber,
//         'current_cycle' => $currentCycle,
//         'selected_language_id' => $preferredLanguageId,
//         'workouts_for_today' => $workoutsForToday,
//     ]);
// }








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




