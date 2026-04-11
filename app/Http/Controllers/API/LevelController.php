<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Level;
use App\Models\UserProfile;
use App\Http\Resources\LevelResource;

class LevelController extends Controller
{
    public function getList(Request $request)
    {
        $level = Level::where('status', 'active');

        $level->when(request('title'), function ($q) {
            return $q->where('title', 'LIKE', '%' . request('title') . '%');
        });

        $level->when($request->filled('workout_mode'), function ($q) use ($request) {
            $modeKey = UserProfile::normalizeWorkoutMode($request->workout_mode);

            if ($modeKey === 'home') {
                $q->where(function ($query) {
                    $query->whereRaw('LOWER(title) LIKE ?', ['%beginner%'])
                        ->orWhereRaw('LOWER(title) LIKE ?', ['%advance%']);
                });
            }

            if ($modeKey === 'gym') {
                $q->where(function ($query) {
                    $query->whereRaw('LOWER(title) LIKE ?', ['%beginner%'])
                        ->orWhereRaw('LOWER(title) LIKE ?', ['%intermediate%'])
                        ->orWhereRaw('LOWER(title) LIKE ?', ['%advance%']);
                });
            }
        });
        
        $per_page = config('constant.PER_PAGE_LIMIT');
        if( $request->has('per_page') && !empty($request->per_page)){
            if(is_numeric($request->per_page))
            {
                $per_page = $request->per_page;
            }
            if($request->per_page == -1 ){
                $per_page = $level->count();
            }
        }

        $level = $level->orderBy('title', 'asc')->paginate($per_page);

        $items = LevelResource::collection($level);

        $response = [
            'pagination'    => json_pagination_response($items),
            'data'          => $items,
        ];
        
        return json_custom_response($response);
    }
}
