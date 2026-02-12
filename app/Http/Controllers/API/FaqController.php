<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function getList(Request $request)
    {
        $faq = Faq::query();

        $faq->when($request->filled('status'), function ($q) use ($request) {
            $q->where('status', $request->status);
        }, function ($q) {
            $q->where('status', 'active');
        });

        $faq->when($request->filled('title'), function ($q) use ($request) {
            $q->where('title', 'LIKE', '%' . $request->title . '%');
        });

        $per_page = config('constant.PER_PAGE_LIMIT');
        if ($request->has('per_page') && !empty($request->per_page)) {
            if (is_numeric($request->per_page)) {
                $per_page = (int) $request->per_page;
            }
            if ((int) $request->per_page === -1) {
                $per_page = $faq->count();
            }
        }

        $faq = $faq->orderBy('id', 'desc')->paginate($per_page);

        $items = $faq->through(function ($item) {
            return [
                'id' => $item->id,
                'title' => $item->title,
                'description' => $item->description,
                'status' => $item->status,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
            ];
        });

        $response = [
            'pagination' => json_pagination_response($items),
            'data' => $items->items(),
        ];

        return json_custom_response($response);
    }
}

