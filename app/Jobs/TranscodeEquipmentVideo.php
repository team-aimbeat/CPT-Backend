<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;

class TranscodeEquipmentVideo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $equipmentVideoId;
    public $sourcePath;

    public function __construct($equipmentVideoId, $sourcePath)
    {
        $this->equipmentVideoId = $equipmentVideoId;
        $this->sourcePath = $sourcePath;
    }

    public function handle()
    {
        if (empty($this->sourcePath)) {
            return;
        }

        $disk = Storage::disk('s3');
        $tempDir = storage_path('app/temp/equipment_videos/' . $this->equipmentVideoId);
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        $inputFile = $tempDir . '/' . basename($this->sourcePath);
        $stream = $disk->readStream($this->sourcePath);
        if ($stream === false) {
            return;
        }

        $out = fopen($inputFile, 'w');
        stream_copy_to_stream($stream, $out);
        fclose($out);
        fclose($stream);

        $sourceDir = dirname($this->sourcePath);
        $processedDir = preg_replace('#^videos/originals/#', 'videos/processed/', $sourceDir);
        $thumbDir = preg_replace('#^videos/originals/#', 'thumbnails/videos/', $sourceDir);

        $variants = [
            '720p' => 720,
            '480p' => 480,
        ];

        $ffmpeg = env('FFMPEG_PATH', 'ffmpeg');

        foreach ($variants as $label => $height) {
            $outputFile = $tempDir . '/' . $label . '.mp4';
            $cmd = implode(' ', [
                escapeshellcmd($ffmpeg),
                '-y -i',
                escapeshellarg($inputFile),
                '-vf',
                escapeshellarg('scale=-2:' . $height),
                '-c:v libx264 -preset fast -crf 23 -c:a aac -b:a 128k',
                escapeshellarg($outputFile),
            ]);

            shell_exec($cmd);

            if (file_exists($outputFile)) {
                $disk->putFileAs($processedDir, new File($outputFile), $label . '.mp4');
                unlink($outputFile);
            }
        }

        $thumbs = [
            ['name' => 'thumb_1.jpg', 'time' => '00:00:03'],
            ['name' => 'thumb_2.jpg', 'time' => '00:00:10'],
            ['name' => 'poster.jpg', 'time' => '00:00:20'],
        ];

        foreach ($thumbs as $thumb) {
            $thumbFile = $tempDir . '/' . $thumb['name'];
            $cmd = implode(' ', [
                escapeshellcmd($ffmpeg),
                '-y -ss',
                escapeshellarg($thumb['time']),
                '-i',
                escapeshellarg($inputFile),
                '-vframes 1 -q:v 2',
                escapeshellarg($thumbFile),
            ]);

            shell_exec($cmd);

            if (file_exists($thumbFile)) {
                $disk->putFileAs($thumbDir, new File($thumbFile), $thumb['name']);
                unlink($thumbFile);
            }
        }

        if (file_exists($inputFile)) {
            unlink($inputFile);
        }
    }
}
