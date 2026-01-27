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
        ->leftJoin('diet_translations as dt', function ($join) use ($languageId) {
            $join->on('diets.id', '=', 'dt.diet_id')
                 ->where('dt.language_id', $languageId);
        })
        ->leftJoin('category_diets as cd', 'diets.categorydiet_id', '=', 'cd.id')
        ->select(
            'diets.id',
            DB::raw('COALESCE(dt.title, diets.title) as title'),
            DB::raw('COALESCE(dt.ingredients, diets.ingredients) as ingredients'),
            DB::raw('COALESCE(dt.description, diets.description) as description'),
            'diets.variety',
            'diets.gender',
            'diets.diet_image',
            'diets.is_featured',
            'diets.status',
            'diets.is_premium',
            'diets.categorydiet_id',
            'cd.title as categorydiet_title',
            'diets.created_at',
            'diets.updated_at'
        );

    /* ---------------- USER GENDER FILTER ---------------- */
    $gender = $request->input('gender', $user->gender);
    if (!empty($gender)) {
        $diet->whereIn('diets.gender', [$gender, 'unisex']);
    }

    /* ---------------- REQUEST FILTERS ---------------- */
    $diet->when($request->title, function ($q, $title) {
        $q->where('dt.title', 'LIKE', "%{$title}%");
    });

    $diet->when($request->variety, function ($q, $variety) {
        $q->where('diets.variety', $variety);
    });

    $diet->when($request->category, function ($q, $category) {
        if (is_numeric($category)) {
            $q->where('cd.id', (int) $category);
        } else {
            $q->where('cd.title', 'LIKE', "%{$category}%");
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

    $items = $diet->getCollection()->map(function ($row) {
        return [
            'id' => $row->id,
            'title' => $row->title,
            'variety' => $row->variety,
            'diet_image' => $row->diet_image,
            'diet_image_url' => $row->diet_image ? asset('https://fitness.completepersonaltraining.com/storage/' . $row->diet_image) : null,
            'is_featured' => $row->is_featured,
            'status' => $row->status,
            'ingredients' => $row->ingredients,
            'description' => $row->description,
            'is_premium' => $row->is_premium,
            'categorydiet_id' => $row->categorydiet_id,
            'categorydiet_title' => $row->categorydiet_title,
            'created_at' => $row->created_at,
            'updated_at' => $row->updated_at,
        ];
    });

    $pagination = [
        'total_items' => $diet->total(),
        'per_page' => $diet->perPage(),
        'currentPage' => $diet->currentPage(),
        'totalPages' => $diet->lastPage(),
    ];

    return json_custom_response([
        'pagination' => $pagination,
        'data'       => $items,
    ]);
}

    public function getListV2(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized access'
            ], 401);
        }

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

        $languageId = (int) ($request->input('lang_id')
            ?? $request->input('language_id')
            ?? $request->input('lang')
            ?? 2);

        $diet = Diet::query()
            ->leftJoin('diet_translations as dt', function ($join) use ($languageId) {
                $join->on('diets.id', '=', 'dt.diet_id')
                     ->where('dt.language_id', $languageId);
            })
            ->leftJoin('category_diets as cd', 'diets.categorydiet_id', '=', 'cd.id')
            ->select(
                'diets.id',
                DB::raw('COALESCE(dt.title, diets.title) as title'),
                DB::raw('COALESCE(dt.ingredients, diets.ingredients) as ingredients'),
                DB::raw('COALESCE(dt.description, diets.description) as description'),
                'dt.language_id as translation_language_id',
                'dt.title as translation_title',
                'dt.ingredients as translation_ingredients',
                'dt.description as translation_description',
                'diets.variety',
                'diets.gender',
                'diets.diet_image',
                'diets.is_featured',
                'diets.status',
                'diets.is_premium',
                'diets.categorydiet_id',
                'cd.title as categorydiet_title',
                'diets.created_at',
                'diets.updated_at'
            );

        $gender = $request->input('gender', $user->gender);
        if (!empty($gender)) {
            $diet->whereIn('diets.gender', [$gender, 'unisex']);
        }

        $diet->when($request->title, function ($q, $title) {
            $q->where('dt.title', 'LIKE', "%{$title}%");
        });

        $diet->when($request->variety, function ($q, $variety) {
            $q->where('diets.variety', $variety);
        });

        $diet->when($request->category, function ($q, $category) {
            if (is_numeric($category)) {
                $q->where('cd.id', (int) $category);
            } else {
                $q->where('cd.title', 'LIKE', "%{$category}%");
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

        $per_page = config('constant.PER_PAGE_LIMIT');
        if ($request->filled('per_page')) {
            $per_page = $request->per_page == -1
                ? $diet->count()
                : (int) $request->per_page;
        }

        $diet = $diet->orderBy('title', 'asc')->paginate($per_page);

        $debug = $request->boolean('debug');

        $items = $diet->getCollection()->map(function ($row) use ($debug, $languageId) {
            $item = [
                'id' => $row->id,
                'title' => $row->title,
                'variety' => $row->variety,
                'diet_image' => $row->diet_image,
                'diet_image_url' => $row->diet_image ? asset('https://fitness.completepersonaltraining.com/storage/' . $row->diet_image) : null,
                'is_featured' => $row->is_featured,
                'status' => $row->status,
                'ingredients' => $row->ingredients,
                'description' => $row->description,
                'is_premium' => $row->is_premium,
                'categorydiet_id' => $row->categorydiet_id,
                'categorydiet_title' => $row->categorydiet_title,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ];

            if ($debug) {
                $item['debug'] = [
                    'requested_lang_id' => $languageId,
                    'translation_language_id' => $row->translation_language_id,
                    'translation_title' => $row->translation_title,
                    'translation_ingredients' => $row->translation_ingredients,
                    'translation_description' => $row->translation_description,
                    'raw_title' => $row->getRawOriginal('title'),
                    'raw_ingredients' => $row->getRawOriginal('ingredients'),
                    'raw_description' => $row->getRawOriginal('description'),
                    'row_attributes' => array_intersect_key(
                        $row->getAttributes(),
                        array_flip(['gender', 'variety', 'categorydiet_id'])
                    ),
                ];
            }

            return $item;
        });

        $pagination = [
            'total_items' => $diet->total(),
            'per_page' => $diet->perPage(),
            'currentPage' => $diet->currentPage(),
            'totalPages' => $diet->lastPage(),
        ];

        return json_custom_response([
            'pagination' => $pagination,
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
