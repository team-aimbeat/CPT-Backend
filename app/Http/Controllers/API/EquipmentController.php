<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Equipment;
use App\Http\Resources\EquipmentResource;

class EquipmentController extends Controller
{
    // public function getList(Request $request)
    // {
    //     $equipment = Equipment::where('status', 'active');

    //     $equipment->when(request('title'), function ($q) {
    //         return $q->where('title', 'LIKE', '%' . request('title') . '%');
    //     })->when(request('workout_mode'), function ($q) {
    //         return $q->whereRaw('FIND_IN_SET(?, workout_modes)', [request('workout_mode')]);
    //     });
                
    //     $per_page = config('constant.PER_PAGE_LIMIT');
    //     if( $request->has('per_page') && !empty($request->per_page)){
    //         if(is_numeric($request->per_page))
    //         {
    //             $per_page = $request->per_page;
    //         }
    //         if($request->per_page == -1 ){
    //             $per_page = $equipment->count();
    //         }
    //     }

    //     $equipment = $equipment->orderBy('title', 'asc')->paginate($per_page);

    //     $items = EquipmentResource::collection($equipment);

    //     $response = [
    //         'pagination'    => json_pagination_response($items),
    //         'data'          => $items,
    //     ];
        
    //     return json_custom_response($response);
    // }
    
    
     public function getList(Request $request)
    {
        try {
            $equipmentList = Equipment::select('id', 'title', 'created_at', 'updated_at')->get();

            if ($equipmentList->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No category found.',
                    'data' => []
                ], 200);
            }

            return response()->json([
                'success' => true,
                'message' => 'Category list fetched successfully.',
                'data' => $equipmentList
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching category data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}