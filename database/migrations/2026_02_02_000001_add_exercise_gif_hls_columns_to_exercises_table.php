<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExerciseGifHlsColumnsToExercisesTable extends Migration
{
    public function up()
    {
        Schema::table('exercises', function (Blueprint $table) {
            if (!Schema::hasColumn('exercises', 'exercise_gif_hls_master_url')) {
                $table->text('exercise_gif_hls_master_url')->nullable()->after('exercise_gif');
            }
            if (!Schema::hasColumn('exercises', 'exercise_gif_hls_1080p_url')) {
                $table->text('exercise_gif_hls_1080p_url')->nullable()->after('exercise_gif_hls_master_url');
            }
            if (!Schema::hasColumn('exercises', 'exercise_gif_hls_720p_url')) {
                $table->text('exercise_gif_hls_720p_url')->nullable()->after('exercise_gif_hls_1080p_url');
            }
            if (!Schema::hasColumn('exercises', 'exercise_gif_hls_480p_url')) {
                $table->text('exercise_gif_hls_480p_url')->nullable()->after('exercise_gif_hls_720p_url');
            }
            if (!Schema::hasColumn('exercises', 'exercise_gif_poster_url')) {
                $table->text('exercise_gif_poster_url')->nullable()->after('exercise_gif_hls_480p_url');
            }
            if (!Schema::hasColumn('exercises', 'exercise_gif_transcoding_status')) {
                $table->string('exercise_gif_transcoding_status', 20)->nullable()->after('exercise_gif_poster_url');
            }
        });
    }

    public function down()
    {
        Schema::table('exercises', function (Blueprint $table) {
            if (Schema::hasColumn('exercises', 'exercise_gif_transcoding_status')) {
                $table->dropColumn('exercise_gif_transcoding_status');
            }
            if (Schema::hasColumn('exercises', 'exercise_gif_poster_url')) {
                $table->dropColumn('exercise_gif_poster_url');
            }
            if (Schema::hasColumn('exercises', 'exercise_gif_hls_480p_url')) {
                $table->dropColumn('exercise_gif_hls_480p_url');
            }
            if (Schema::hasColumn('exercises', 'exercise_gif_hls_720p_url')) {
                $table->dropColumn('exercise_gif_hls_720p_url');
            }
            if (Schema::hasColumn('exercises', 'exercise_gif_hls_1080p_url')) {
                $table->dropColumn('exercise_gif_hls_1080p_url');
            }
            if (Schema::hasColumn('exercises', 'exercise_gif_hls_master_url')) {
                $table->dropColumn('exercise_gif_hls_master_url');
            }
        });
    }
}
