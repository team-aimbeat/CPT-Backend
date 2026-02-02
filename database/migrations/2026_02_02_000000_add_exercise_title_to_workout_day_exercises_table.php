<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('workout_day_exercises', 'exercise_title')) {
            Schema::table('workout_day_exercises', function (Blueprint $table) {
                $table->string('exercise_title')->nullable()->after('exercise_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('workout_day_exercises', 'exercise_title')) {
            Schema::table('workout_day_exercises', function (Blueprint $table) {
                $table->dropColumn('exercise_title');
            });
        }
    }
};
