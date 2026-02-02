<?php

namespace App\Jobs;

use App\Models\StretchingVideo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;

class TranscodeStretchingVideo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $stretchingVideoId;
    public $sourcePath;

    public function __construct($stretchingVideoId, $sourcePath)
    {
        $this->stretchingVideoId = $stretchingVideoId;
        $this->sourcePath = $sourcePath;
    }

    public function handle()
    {
        if (empty($this->sourcePath)) {
            return;
        }

        StretchingVideo::where('id', $this->stretchingVideoId)->update([
            'transcoding_status' => 'processing',
        ]);

        try {
            $disk = Storage::disk('s3');
            $tempDir = storage_path('app/temp/stretching_videos/' . $this->stretchingVideoId);
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0777, true);
            }

            $inputFile = $tempDir . '/' . basename($this->sourcePath);
            $stream = $disk->readStream($this->sourcePath);
            if ($stream === false) {
                throw new \RuntimeException('Unable to read source from S3.');
            }

            $out = fopen($inputFile, 'w');
            stream_copy_to_stream($stream, $out);
            fclose($out);
            fclose($stream);

            $sourceDir = dirname($this->sourcePath);
            $processedDir = preg_replace('#^videos/originals/#', 'videos/processed/', $sourceDir);
            $thumbDir = preg_replace('#^videos/originals/#', 'thumbnails/videos/', $sourceDir);
            $hlsDir = preg_replace('#^videos/originals/#', 'videos/hls/', $sourceDir);

            $variants = [
                '1080p' => [ 'height' => 1080, 'bitrate' => '5000k', 'resolution' => '1920x1080' ],
                '720p' => [ 'height' => 720, 'bitrate' => '3000k', 'resolution' => '1280x720' ],
                '480p' => [ 'height' => 480, 'bitrate' => '1500k', 'resolution' => '854x480' ],
            ];

            $ffmpeg = env('FFMPEG_PATH', 'ffmpeg');

            foreach ($variants as $label => $variant) {
                $outputFile = $tempDir . '/' . $label . '.mp4';
                $cmd = implode(' ', [
                    escapeshellcmd($ffmpeg),
                    '-y -i',
                    escapeshellarg($inputFile),
                    '-vf',
                    escapeshellarg('scale=-2:' . $variant['height']),
                    '-c:v libx264 -preset fast -crf 23 -c:a aac -b:a 128k',
                    escapeshellarg($outputFile),
                ]);

                shell_exec($cmd);

                if (file_exists($outputFile)) {
                    $disk->putFileAs($processedDir, new File($outputFile), $label . '.mp4');
                    unlink($outputFile);
                }
            }

            $masterLines = [
                '#EXTM3U',
                '#EXT-X-VERSION:3',
            ];

            foreach ($variants as $label => $variant) {
                $variantDir = $tempDir . '/hls_' . $label;
                if (!is_dir($variantDir)) {
                    mkdir($variantDir, 0777, true);
                }

                $playlistPath = $variantDir . '/' . $label . '.m3u8';
                $segmentPattern = $variantDir . '/' . $label . '_%03d.ts';

                $cmd = implode(' ', [
                    escapeshellcmd($ffmpeg),
                    '-y -i',
                    escapeshellarg($inputFile),
                    '-vf',
                    escapeshellarg('scale=-2:' . $variant['height']),
                    '-c:v libx264 -preset fast -b:v ' . $variant['bitrate'],
                    '-c:a aac -b:a 128k',
                    '-hls_time 6 -hls_playlist_type vod',
                    '-hls_segment_filename',
                    escapeshellarg($segmentPattern),
                    escapeshellarg($playlistPath),
                ]);

                shell_exec($cmd);

                $relativePlaylist = $label . '.m3u8';
                $masterLines[] = '#EXT-X-STREAM-INF:BANDWIDTH=' . (int) rtrim($variant['bitrate'], 'k') * 1000 . ',RESOLUTION=' . $variant['resolution'];
                $masterLines[] = $relativePlaylist;

                $files = glob($variantDir . '/*');
                foreach ($files as $filePath) {
                    $disk->putFileAs($hlsDir, new File($filePath), basename($filePath));
                    unlink($filePath);
                }
                rmdir($variantDir);
            }

            $masterPath = $tempDir . '/master.m3u8';
            file_put_contents($masterPath, implode("\n", $masterLines) . "\n");
            $disk->putFileAs($hlsDir, new File($masterPath), 'master.m3u8');
            $hlsMasterKey = $hlsDir . '/master.m3u8';
            StretchingVideo::where('id', $this->stretchingVideoId)->update([
                'hls_master_url' => $hlsMasterKey,
                'hls_1080p_url' => $hlsDir . '/1080p.m3u8',
                'hls_720p_url' => $hlsDir . '/720p.m3u8',
                'hls_480p_url' => $hlsDir . '/480p.m3u8',
            ]);
            unlink($masterPath);

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

            StretchingVideo::where('id', $this->stretchingVideoId)->update([
                'thumbnail_url' => $thumbDir . '/poster.jpg',
            ]);

            if (file_exists($inputFile)) {
                unlink($inputFile);
            }

            StretchingVideo::where('id', $this->stretchingVideoId)->update([
                'transcoding_status' => 'done',
            ]);
        } catch (\Throwable $e) {
            StretchingVideo::where('id', $this->stretchingVideoId)->update([
                'transcoding_status' => 'failed',
            ]);
            throw $e;
        }
    }
}
