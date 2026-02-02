<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWarmupVideosTable extends Migration
{
    public function up()
    {
        Schema::create('warmup_videos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('languagelist_id')->index();
            $table->text('video_url');
            $table->text('hls_master_url')->nullable();
            $table->text('hls_1080p_url')->nullable();
            $table->text('hls_720p_url')->nullable();
            $table->text('hls_480p_url')->nullable();
            $table->text('thumbnail_url')->nullable();
            $table->string('transcoding_status', 20)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('warmup_videos');
    }
}
