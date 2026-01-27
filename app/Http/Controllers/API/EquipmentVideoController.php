<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\EquipmentVideo;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class EquipmentVideoController extends Controller
{
    public function getList(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized access',
            ], 401);
        }

        $hasAccess = Subscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('payment_status', 'paid')
            ->whereHas('package', function ($q) {
                $q->whereIn('package_type', ['workout', 'both']);
            })
            ->exists();

        $equipmentIds = $request->filled('equipment_ids')
            ? array_filter(explode(',', $request->equipment_ids))
            : ($request->filled('equipment_id') ? [$request->equipment_id] : []);

        $languageId = $request->input('language_id', $request->input('lang_id'));

        $query = EquipmentVideo::query()
            ->select([
                'id',
                'equipment_id',
                'languagelist_id',
                'video_url',
                'thumbnail_url',
                'hls_master_url',
                'hls_1080p_url',
                'hls_720p_url',
                'hls_480p_url',
                'transcoding_status',
                'created_at',
            ])
            ->with([
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

        $requestedRes = (string) $request->query('res', '');
        $resolutionMap = [
            '1080' => 'hls_1080p_url',
            '720' => 'hls_720p_url',
            '480' => 'hls_480p_url',
            '420' => 'hls_480p_url',
        ];
        $selectedField = $resolutionMap[$requestedRes] ?? null;

        $cacheKey = 'equipment_videos:' . md5(json_encode([
            'user_id' => $user->id,
            'has_access' => $hasAccess,
            'equipment_ids' => $equipmentIds,
            'language_id' => $languageId,
            'status' => $request->input('status', 'done'),
            'res' => $requestedRes,
        ]));

        $videos = $request->boolean('no_cache')
            ? $query->orderByDesc('id')->get()
            : Cache::remember($cacheKey, now()->addMinutes(5), function () use ($query) {
                return $query->orderByDesc('id')->get();
            });

        if ($hasAccess) {
            $videos = $videos->map(function ($video) use ($selectedField) {
                $resolutionPath = $selectedField ? $video->{$selectedField} : null;
                $preferredPath = $resolutionPath ?: $video->hls_master_url ?: $video->video_url;

                return [
                    'id' => $video->id,
                    'equipment_id' => $video->equipment_id,
                    'equipment_title' => optional($video->equipment)->title,
                    'language_id' => $video->languagelist_id,
                    'language_name' => optional($video->languageList)->language_name,
                    'video_url' => $preferredPath ? cloudfrontUrl($preferredPath) : null,
                    'thumbnail_url' => $video->thumbnail_url ? cloudfrontUrl($video->thumbnail_url) : null,
                    'is_locked' => false,
                ];
            });
        } else {
            $grouped = $videos->groupBy('equipment_id');
            $lockedData = collect();

            foreach ($grouped as $equipmentId => $items) {
                $isFirst = true;

                foreach ($items as $video) {
                    $resolutionPath = $selectedField ? $video->{$selectedField} : null;
                    $preferredPath = $resolutionPath ?: $video->hls_master_url ?: $video->video_url;

                    $lockedData->push([
                        'id' => $video->id,
                        'equipment_id' => $video->equipment_id,
                        'equipment_title' => optional($video->equipment)->title,
                        'language_id' => $video->languagelist_id,
                        'language_name' => optional($video->languageList)->language_name,
                        'video_url' => $isFirst ? ($preferredPath ? cloudfrontUrl($preferredPath) : null) : null,
                        'thumbnail_url' => $video->thumbnail_url ? cloudfrontUrl($video->thumbnail_url) : null,
                        'is_locked' => !$isFirst,
                    ]);

                    $isFirst = false;
                }
            }

            $videos = $lockedData->values();
        }

        $response = [
            'has_access' => $hasAccess,
            'message' => $hasAccess
                ? null
                : 'Please subscribe to a 12-month or 24-month plan to unlock and watch all workout videos.',
            'data' => $videos,
        ];

        return json_custom_response($response);
    }
}
