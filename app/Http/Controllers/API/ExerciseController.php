<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Exercise;
use App\Http\Resources\ExerciseResource;
use App\Http\Resources\ExerciseDetailResource;
use App\Http\Resources\UserExerciseResource;
use App\Models\UserExercise;
use App\Models\Subscription;

class ExerciseController extends Controller
{
    public function getList(Request $request)
    {
        $exercise = Exercise::where('status', 'active');

        $exercise->when(request('title'), function ($q) {
            return $q->where('title', 'LIKE', '%' . request('title') . '%');
        });

        $exercise->when(request('equipment_id'), function ($q) {
            return $q->where('equipment_id', request('equipment_id'));
        });

        $exercise->when(request('level_id'), function ($q) {
            $level_ids = explode(',', request('level_id'));
            return $q->whereIn('level_id', $level_ids);
        });

        $exercise->when(request('equipment_ids'), function ($q) {
            $equipment_ids = explode(',', request('equipment_ids'));
            return $q->whereIn('equipment_id', $equipment_ids);
        });

        $exercise->when(request('level_ids'), function ($q) {
            $level_ids = explode(',', request('level_ids'));
            return $q->whereIn('level_id', $level_ids);
        });

        $exercise->when(request('bodypart_id'), function ($q) {
            return $q->where('bodypart_id', request('bodypart_id'));
        });
        
        if( $request->has('is_premium') && isset($request->is_premium) ) {
            $exercise = $exercise->where('is_premium', request('is_premium'));
        }
                
        $per_page = config('constant.PER_PAGE_LIMIT');
        if( $request->has('per_page') && !empty($request->per_page)) {
            if(is_numeric($request->per_page))
            {
                $per_page = $request->per_page;
            }
            if($request->per_page == -1 ){
                $per_page = $exercise->count();
            }
        }

        $exercise = $exercise->orderBy('title', 'asc')->paginate($per_page);

        $items = ExerciseResource::collection($exercise);

        $response = [
            'pagination'    => json_pagination_response($items),
            'data'          => $items,
        ];
        
        return json_custom_response($response);
    }

    public function getDetail(Request $request)
    {
        $exercise = Exercise::where('id',request('id'))->first();
           
        if( $exercise == null )
        {
            return json_message_response( __('message.not_found_entry',['name' => __('message.exercise') ]) );
        }

        $exercise_data = new ExerciseDetailResource($exercise);
            $response = [
                'data' => $exercise_data,
            ];
             
        return json_custom_response($response);
    }

    public function storeUserExercise(Request $request)
    {
        $user_id = auth()->id();
        $exerciseID = $request->exercise_id;
        $workoutID = $request->workout_id;

        $exercise = Exercise::where('id', $exerciseID )->first();
        if( $exercise == null )
        {
            return json_message_response( __('message.not_found_entry',['name' => __('message.exercise') ]) );
        }
        $user_exercise = UserExercise::updateOrCreate(
            ['user_id' => $user_id, 'exercise_id' => $exerciseID],
            ['exercise_id' => $exerciseID,'workout_id' => $workoutID]
        );

        $message = $user_exercise->wasRecentlyCreated ? __('message.save_form', ['form' => __('message.exercise')]) : __('message.update_form', ['form' => __('message.exercise')]); 

        return json_message_response($message);
    }
    
    public function getUserExercise(Request $request)
    {
        $user = auth()->user();

        $user_exercises = UserExercise::where('user_id', $user->id)->with('exercise');

        $per_page = config('constant.PER_PAGE_LIMIT', 10);
        if ($request->has('per_page') && !empty($request->per_page)) {
            if (is_numeric($request->per_page)) {
                $per_page = $request->per_page;
            } elseif ($request->per_page == -1) {
                $per_page = $user_exercises->count();
            }
        }

        $user_exercises = $user_exercises->orderBy('id', 'asc')->paginate($per_page);

        $items = UserExerciseResource::collection($user_exercises);

        $response = [
            'pagination'    => json_pagination_response($items),
            'data'          => $items,
        ];

        // $response = [
        //     'pagination' => json_pagination_response($user_exercises),
        //     'data' => $user_exercises->items(),
        // ];

        return json_custom_response($response);
    }
    
    
   
    
  public function getVideoUrls(Request $request)
{
    $user = auth()->user();

    if (!$user) {
        return response()->json([
            'status' => false,
            'message' => 'Unauthorized access'
        ], 401);
    }

    /* -------------------------------------------------
     | 1. CHECK SUBSCRIPTION
     |--------------------------------------------------*/
    $hasAccess = Subscription::where('user_id', $user->id)
        ->where('status', 'active')
        ->where('payment_status', 'paid')
        ->whereHas('package', function ($q) {
            $q->whereIn('package_type', ['workout', 'both']);
        })
        ->exists();

    $hasCouponAccess = $user->hasActiveCouponAccess();

    $hasAccess = $hasAccess || $hasCouponAccess;

    /* -------------------------------------------------
     | 2. LANGUAGE SETUP
     |--------------------------------------------------*/
    $preferredLanguageId = (int) $request->query('lang_id', 2);
    $fallbackLanguageId  = 2;

    if ($preferredLanguageId <= 0) {
        $preferredLanguageId = $fallbackLanguageId;
    }

    /* -------------------------------------------------
     | 3. BASE QUERY
     |--------------------------------------------------*/
    $equipmentId = $request->query('equipment_id');

    $query = Exercise::with('exerciseVideos')
        ->select('id', 'title', 'exercise_image', 'equipment_id')
        ->orderBy('id');

    if (!empty($equipmentId)) {
        $query->where('equipment_id', $equipmentId);
    }

    /* =================================================
       🔓 SUBSCRIBED USER → FULL ACCESS
       =================================================*/
    if ($hasAccess) {

        $exercises = $query->get();

        $data = $exercises->map(function ($exercise) use (
            $preferredLanguageId,
            $fallbackLanguageId
        ) {

            $preferredVideo = $exercise->exerciseVideos
                ->firstWhere('languagelist_id', $preferredLanguageId);

            $fallbackVideo = $exercise->exerciseVideos
                ->firstWhere('languagelist_id', $fallbackLanguageId);

            $selectedVideoUrl =
                $preferredVideo->video_url
                ?? $fallbackVideo->video_url
                ?? null;

            return [
                'id' => $exercise->id,
                'title' => $exercise->title,
                'selected_video_url' => $selectedVideoUrl,
                'exercise_image_url' => $exercise->exercise_image_url,
                'is_locked' => false,
                'exercise_videos' => $exercise->exerciseVideos->map(fn ($v) => [
                    'id' => $v->id,
                    'languagelist_id' => $v->languagelist_id,
                    'video_url' => $v->video_url,
                ]),
            ];
        });

        return response()->json([
            'status' => 'success',
            'selected_language_id' => $preferredLanguageId,
            'data' => $data
        ]);
    }

    /* =================================================
       🔒 NOT SUBSCRIBED → 1 UNLOCKED PER CATEGORY
       =================================================*/

    $grouped = $query->get()->groupBy('equipment_id');
    $demoData = collect();

    foreach ($grouped as $equipmentId => $items) {

        $isFirst = true;

        foreach ($items as $exercise) {

            $preferredVideo = $exercise->exerciseVideos
                ->firstWhere('languagelist_id', $preferredLanguageId);

            $fallbackVideo = $exercise->exerciseVideos
                ->firstWhere('languagelist_id', $fallbackLanguageId);

            $selectedVideoUrl =
                $preferredVideo->video_url
                ?? $fallbackVideo->video_url
                ?? null;

            $demoData->push([
                'id' => $exercise->id,
                'title' => $exercise->title,
                'selected_video_url' => $isFirst ? $selectedVideoUrl : null,
                'exercise_image_url' => $exercise->exercise_image_url,
                'is_locked' => !$isFirst,
                'exercise_videos' => $isFirst
                    ? [
                        [
                            'languagelist_id' => $preferredLanguageId,
                            'video_url' => $selectedVideoUrl
                        ]
                      ]
                    : []
            ]);

            $isFirst = false; // बाकी सब locked
        }
    }

    return response()->json([
        'status' => 'success',
        'selected_language_id' => $preferredLanguageId,
        'message' => 'Please subscribe to a 12-month or 24-month plan to unlock and watch all workout videos.',
        'data' => $demoData->values()
    ]);
}


    
//  public function getVideoUrls(Request $request)
//     {
//         $user = auth()->user();

//         if (!$user) {
//             return response()->json(['status' => false, 'message' => 'Unauthorized access'], 401);
//         }
    
//         $hasAccess = Subscription::where('user_id', $user->id)
//             ->where('status', 'active')
//             ->where('payment_status', 'paid')
//             ->whereHas('package', function($query) {
//                 $query->whereIn('package_type', ['workout', 'both']); 
//             })
//             ->exists();
    
//         if (!$hasAccess) {
//             return response()->json([
//                 'status' => false, 
//                 'message' => 'Please Subscribe Workout Plan.'
//             ], 403);
//         }
    
        
//         $preferredLanguageId = (int) $request->query('lang_id');
        
//         $fallbackLanguageId = 2; 
    
//         if (empty($preferredLanguageId) || $preferredLanguageId <= 0) {
//             $preferredLanguageId = $fallbackLanguageId;
//         }
        
//         $equipmentId = $request->query('equipment_id');
        
//         $query = Exercise::query();
        
//         if (!empty($equipmentId)) {
//             $query->where('equipment_id', $equipmentId);
//         }
        
//         $exercises = $query->with('exerciseVideos')->select('id', 'title', 'exercise_image')->get(); 
    
//         $transformedData = $exercises->map(function ($exercise) use ($preferredLanguageId, $fallbackLanguageId) {
            
        
//         $preferredVideo = $exercise->exerciseVideos
//             ->where('languagelist_id', $preferredLanguageId)
//             ->whereNotNull('video_url') 
//             ->first();

//         if ($preferredVideo && !empty($preferredVideo->video_url)) {
//             $selectedVideoUrl = $preferredVideo->video_url;
//             $finalLangId = $preferredLanguageId;
//         } else {
//             $fallbackVideo = $exercise->exerciseVideos
//                 ->where('languagelist_id', $fallbackLanguageId)
//                 ->whereNotNull('video_url')
//                 ->first();

//             $selectedVideoUrl = $fallbackVideo ? $fallbackVideo->video_url : null;
//             $finalLangId = $fallbackVideo ? $fallbackLanguageId : null;
//         }

//             return [
//                 'id' => $exercise->id,
//                 'title' => $exercise->title,
//                 'selected_video_url' => $selectedVideoUrl, 
//                 'exercise_image_url' => $exercise->exercise_image_url,
//                 'exercise_videos' => $exercise->exerciseVideos->map(function ($video) {
//                      return [
//                         'id' => $video->id,
//                         'languagelist_id' => $video->languagelist_id,
//                         'video_url' => $video->video_url,
//                     ];
//                 })->toArray(),
//             ];
//         });
    
//         return response()->json([
//             'status' => 'success',
//             'selected_language_id' => $preferredLanguageId, 
//             'data' => $transformedData
//         ]);
//     }
}
