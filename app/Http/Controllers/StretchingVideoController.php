<?php

namespace App\Http\Controllers;

use App\Jobs\TranscodeStretchingVideo;
use App\Models\LanguageList;
use App\Models\StretchingVideo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StretchingVideoController extends Controller
{
    public function index()
    {
        $pageTitle = 'Stretching Videos';
        $stretchingVideos = StretchingVideo::with('languageList')->orderByDesc('id')->get();

        return view('workout.stretching-video-list', compact('pageTitle', 'stretchingVideos'));
    }

    public function create()
    {
        $pageTitle = 'Add Stretching Video';
        $languages = LanguageList::all();

        return view('workout.stretching-video-create', compact('pageTitle', 'languages'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'language_id' => 'required|exists:language_lists,id',
            'video_file' => 'required|file|mimetypes:video/*|max:2048000',
        ]);

        $videoPath = $this->storeStretchingVideoFile($request->file('video_file'));
        if (empty($videoPath) || !Storage::disk('s3')->exists($videoPath)) {
            return redirect()->back()->withErrors('Video upload failed. Please try again.');
        }

        $stretchingVideo = StretchingVideo::create([
            'languagelist_id' => $request->language_id,
            'video_url' => $videoPath,
            'transcoding_status' => 'pending',
        ]);

        TranscodeStretchingVideo::dispatch($stretchingVideo->id, $videoPath);

        return redirect()->route('stretching.video.list')->with('success', 'Stretching video added successfully!');
    }

    public function destroy(StretchingVideo $stretchingVideo)
    {
        $stretchingVideo->delete();

        return redirect()->route('stretching.video.list')->with('success', 'Stretching video deleted successfully!');
    }

    protected function storeStretchingVideoFile($file)
    {
        $now = now();
        $uuid = (string) Str::uuid();
        $dir = 'videos/originals/stretching/' . $now->format('Y') . '/' . $now->format('m') . '/' . $uuid;
        $filename = 'original.' . $file->getClientOriginalExtension();

        return Storage::disk('s3')->putFileAs($dir, $file, $filename);
    }
}
