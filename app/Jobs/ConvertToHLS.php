<?php

namespace App\Jobs;

use App\Models\Exercise;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ConvertToHLS implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $exerciseId;
    public $fileName;

    public function __construct($exerciseId, $fileName)
    {
        $this->exerciseId = $exerciseId;
        $this->fileName = $fileName;
    }

    public function handle()
    {
        $exercise = Exercise::find($this->exerciseId);

        $input = public_path("storage/uploads/exercise_videos/original/" . $this->fileName);
        $outputFolder = public_path("storage/uploads/exercise_videos/hls/" . $this->exerciseId);

        if (!file_exists($outputFolder)) {
            mkdir($outputFolder, 0777, true);
        }

        $output = $outputFolder . "/index.m3u8";

        // FFmpeg HLS Command
        $ffmpeg = "/home/completepers/public_html/FFmpeg/bin/ffmpeg";

        $cmd = "$ffmpeg -i \"$input\" -profile:v baseline -level 3.0 -start_number 0 -hls_time 6 -hls_list_size 0 -f hls \"$output\"";

        // $cmd = "ffmpeg -i {$input} -profile:v baseline -level 3.0 -start_number 0 -hls_time 6 -hls_list_size 0 -f hls {$output}";
        shell_exec($cmd);

        // Save HLS path in DB
        $exercise->hls_video = "uploads/exercise_videos/hls/" . $this->exerciseId . "/index.m3u8";
        $exercise->save();
    }
}
