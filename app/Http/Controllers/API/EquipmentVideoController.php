<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\EquipmentVideo;
use Illuminate\Http\Request;

class EquipmentVideoController extends Controller
{
    public function getList(Request $request)
    {
        $equipmentIds = $request->filled('equipment_ids')
            ? array_filter(explode(',', $request->equipment_ids))
            : ($request->filled('equipment_id') ? [$request->equipment_id] : []);

        $languageId = $request->input('language_id', $request->input('lang_id'));

        $query = EquipmentVideo::with([
            'equipment:id,title',
            'languageList:id,language_name',
        ]);

        if (!empty($equipmentIds)) {
            $query->whereIn('equipment_id', $equipmentIds);
        }

        if (!empty($languageId)) {
            $query->where('languagelist_id', $languageId);
        }

        if ($request->filled('status')) {
            $query->where('transcoding_status', $request->status);
        } else {
            $query->where('transcoding_status', 'done');
        }

        $videos = $query->orderByDesc('id')->get()->map(function ($video) {
            $preferredPath = $video->hls_master_url ?: $video->video_url;

            return [
                'id' => $video->id,
                'equipment_id' => $video->equipment_id,
                'equipment_title' => optional($video->equipment)->title,
                'language_id' => $video->languagelist_id,
                'language_name' => optional($video->languageList)->language_name,
                'video_url' => $preferredPath ? cloudfrontUrl($preferredPath) : null,
                'hls_master_url' => $video->hls_master_url ? cloudfrontUrl($video->hls_master_url) : null,
                'hls_1080p_url' => $video->hls_1080p_url ? cloudfrontUrl($video->hls_1080p_url) : null,
                'hls_720p_url' => $video->hls_720p_url ? cloudfrontUrl($video->hls_720p_url) : null,
                'hls_480p_url' => $video->hls_480p_url ? cloudfrontUrl($video->hls_480p_url) : null,
                'transcoding_status' => $video->transcoding_status,
                'created_at' => $video->created_at,
            ];
        });

        $response = [
            'data' => $videos,
        ];

        return json_custom_response($response);
    }
}
