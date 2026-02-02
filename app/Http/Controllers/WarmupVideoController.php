<?php

namespace App\Http\Controllers;

use App\Jobs\TranscodeWarmupVideo;
use App\Models\LanguageList;
use App\Models\WarmupVideo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WarmupVideoController extends Controller
{
    public function index()
    {
        $pageTitle = 'Warmup Videos';
        $warmupVideos = WarmupVideo::with('languageList')->orderByDesc('id')->get();

        return view('workout.warmup-video-list', compact('pageTitle', 'warmupVideos'));
    }

    public function create()
    {
        $pageTitle = 'Add Warmup Video';
        $languages = LanguageList::all();

        return view('workout.warmup-video-create', compact('pageTitle', 'languages'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'language_id' => 'required|exists:language_lists,id',
            'video_file' => 'required|file|mimetypes:video/*|max:2048000',
        ]);

        $videoPath = $this->storeWarmupVideoFile($request->file('video_file'));
        if (empty($videoPath) || !Storage::disk('s3')->exists($videoPath)) {
            return redirect()->back()->withErrors('Video upload failed. Please try again.');
        }

        $warmupVideo = WarmupVideo::create([
            'languagelist_id' => $request->language_id,
            'video_url' => $videoPath,
            'transcoding_status' => 'pending',
        ]);

        TranscodeWarmupVideo::dispatch($warmupVideo->id, $videoPath);

        return redirect()->route('warmup.video.list')->with('success', 'Warmup video added successfully!');
    }

    public function destroy(WarmupVideo $warmupVideo)
    {
        $warmupVideo->delete();

        return redirect()->route('warmup.video.list')->with('success', 'Warmup video deleted successfully!');
    }

    protected function storeWarmupVideoFile($file)
    {
        $now = now();
        $uuid = (string) Str::uuid();
        $dir = 'videos/originals/warmup/' . $now->format('Y') . '/' . $now->format('m') . '/' . $uuid;
        $filename = 'original.' . $file->getClientOriginalExtension();

        return Storage::disk('s3')->putFileAs($dir, $file, $filename);
    }
}
