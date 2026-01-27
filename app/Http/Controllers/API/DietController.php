<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Diet;
use App\Http\Resources\DietResource;
use App\Http\Resources\DietDetailResource;
use App\Models\UserFavouriteDiet;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;

class DietController extends Controller
{
    
    // public function getList(Request $request)
    // {
    //      $user = auth()->user();
    //     if (!$user) {
    //         return response()->json(['status' => false, 'message' => 'Unauthorized access'], 401);
    //     }
    
    //     $hasAccess = Subscription::where('user_id', $user->id)
    //         ->where('status', 'active')
    //         ->where('payment_status', 'paid')
    //         ->whereHas('package', function($query) {
    //             $query->whereIn('package_type', ['diet', 'both']); 
    //         })
    //         ->exists();
    
    //     if (!$hasAccess) {
    //         return response()->json([
    //             'status' => false, 
    //             'message' => 'Please Subscribe Diet Plan.'
    //         ], 403);
    //     }
        
        
    //     $diet = Diet::query();
    //     $diet->join('category_diets', 'diets.categorydiet_id', '=', 'category_diets.id')
    //          ->select('diets.*');
    
    //     $diet->when(request('title'), function ($q, $title) {
    //         return $q->where('diets.title', 'LIKE', '%' . $title . '%');
    //     });
    
    //     $diet->when(request('variety'), function ($q, $variety) {
    //         return $q->where('diets.variety', $variety);
    //     });
    
    //     $diet->when(request('category'), function ($q, $category) {
    //         return $q->where('category_diets.title', 'LIKE', '%' . $category . '%');
    //     });
    
    //     $diet->when(request('categorydiet_id'), function ($q, $categorydiet_id) {
    //         return $q->where('diets.categorydiet_id', $categorydiet_id);
    //     });
    
    //     $diet->when($request->has('is_featured') && isset($request->is_featured), function ($q) use ($request) {
    //         return $q->where('diets.is_featured', $request->is_featured);
    //     });
    
    //     $diet->when($request->has('is_premium') && isset($request->is_premium), function ($q) use ($request) {
    //         return $q->where('diets.is_premium', $request->is_premium);
    //     });
    
    
    //     $per_page = config('constant.PER_PAGE_LIMIT');
    //     if ($request->has('per_page') && !empty($request->per_page)) {
    //         if (is_numeric($request->per_page)) {
    //             $per_page = $request->per_page;
    //         }
    //         if ($request->per_page == -1) {
    //             $per_page = $diet->count();
    //         }
    //     }
    
    //     $diet = $diet->orderBy('diets.title', 'asc')->paginate($per_page);
    
    //     $items = DietResource::collection($diet);
    
    //     $response = [
    //         'pagination' => json_pagination_response($items),
    //         'data'       => $items,
    //     ];
    
    //     return json_custom_response($response);
    // }
    
    
    
    
    public function getList(Request $request)
{
    $user = auth()->user();

    if (!$user) {
        return response()->json([
            'status' => false,
            'message' => 'Unauthorized access'
        ], 401);
    }

    /* ---------------- SUBSCRIPTION CHECK ---------------- */
    $hasAccess = Subscription::where('user_id', $user->id)
        ->where('status', 'active')
        ->where('payment_status', 'paid')
        ->whereHas('package', function ($query) {
            $query->whereIn('package_type', ['diet', 'both']);
        })
        ->exists();

    if (!$hasAccess) {
        return response()->json([
            'status' => false,
            'message' => 'Please Subscribe Diet Plan.'
        ], 403);
    }

    /* ---------------- LANGUAGE ---------------- */
    $languageId = (int) $request->input('lang_id', 2); // default English

    /* ---------------- BASE QUERY ---------------- */
    $diet = Diet::query()
        ->join('category_diets', 'diets.categorydiet_id', '=', 'category_diets.id')
        ->leftJoin('diet_translations', function ($join) use ($languageId) {
            $join->on('diets.id', '=', 'diet_translations.diet_id')
                 ->where('diet_translations.language_id', $languageId);
        })
        ->select(
            'diets.*',
            DB::raw('COALESCE(diet_translations.title, diets.title) as title'),
            DB::raw('COALESCE(diet_translations.ingredients, diets.ingredients) as ingredients'),
            DB::raw('COALESCE(diet_translations.description, diets.description) as description')
        );

    /* ---------------- USER GENDER FILTER ---------------- */
    $gender = $request->input('gender', $user->gender);
    if (!empty($gender)) {
        $diet->whereIn('diets.gender', [$gender, 'unisex']);
    }

    /* ---------------- REQUEST FILTERS ---------------- */
    $diet->when($request->title, function ($q, $title) {
        $q->where('diet_translations.title', 'LIKE', "%{$title}%");
    });

    $diet->when($request->variety, function ($q, $variety) {
        $q->where('diets.variety', $variety);
    });

    $diet->when($request->category, function ($q, $category) {
        if (is_numeric($category)) {
            $q->where('category_diets.id', (int) $category);
        } else {
            $q->where('category_diets.title', 'LIKE', "%{$category}%");
        }
    });

    $diet->when($request->categorydiet_id, function ($q, $id) {
        $q->where('diets.categorydiet_id', $id);
    });

    $diet->when($request->has('is_featured'), function ($q) use ($request) {
        $q->where('diets.is_featured', $request->is_featured);
    });

    $diet->when($request->has('is_premium'), function ($q) use ($request) {
        $q->where('diets.is_premium', $request->is_premium);
    });

    /* ---------------- PAGINATION ---------------- */
    $per_page = config('constant.PER_PAGE_LIMIT');

    if ($request->filled('per_page')) {
        $per_page = $request->per_page == -1
            ? $diet->count()
            : (int) $request->per_page;
    }

    $diet = $diet->orderBy('title', 'asc')->paginate($per_page);

    /* ---------------- RESPONSE ---------------- */
    $items = DietResource::collection($diet);

    return json_custom_response([
        'pagination' => json_pagination_response($items),
        'data'       => $items,
    ]);
}


    public function getDetail(Request $request)
    {
        $diet = Diet::where('id',request('id'))->first();
           
        if( $diet == null )
        {
            return json_message_response( __('message.not_found_entry',['name' => __('message.diet') ]) );
        }

        $diet_data = new DietDetailResource($diet);
            $response = [
                'data' => $diet_data,
            ];
             
        return json_custom_response($response);
        
    }

    public function getUserFavouriteDiet(Request $request)
    {
        $diet = Diet::myDiet();

        $per_page = config('constant.PER_PAGE_LIMIT');
        if( $request->has('per_page') && !empty($request->per_page)) {
            if(is_numeric($request->per_page)) {
                $per_page = $request->per_page;
            }
            if($request->per_page == -1 ) {
                $per_page = $diet->count();
            }
        }

        $diet = $diet->orderBy('title', 'asc')->paginate($per_page);

        $items = DietResource::collection($diet);

        $response = [
            'pagination'    => json_pagination_response($items),
            'data'          => $items,
        ];
        
        return json_custom_response($response);
    }

    public function userFavouriteDiet(Request $request)
    {
        $user_id = auth()->id();
        $diet_id = $request->diet_id;

        $diet = Diet::where('id', $diet_id )->first();
        if( $diet == null )
        {
            return json_message_response( __('message.not_found_entry',['name' => __('message.diet') ]) );
        }
        $user_favourite_diet = UserFavouriteDiet::where('user_id', $user_id)->where('diet_id',$diet_id)->first();
        
        if($user_favourite_diet != null) {
            $user_favourite_diet->delete();
            $message = __('message.unfavourite_diet_list');
        } else {
            $data = [
                'user_id'   => $user_id,
                'diet_id'   => $diet_id,
            ];
            
            UserFavouriteDiet::create($data);
            $message = __('message.favourite_diet_list');
        }

        return json_message_response($message);
    }
}
