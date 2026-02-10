<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workout_day_exercises', function (Blueprint $table) {
            $table->unsignedBigInteger('alternate_exercise_id')->nullable()->after('exercise_id');
            $table->text('alternate_exercise_description')->nullable()->after('instruction');
        });
    }

    public function down(): void
    {
        Schema::table('workout_day_exercises', function (Blueprint $table) {
            $table->dropColumn(['alternate_exercise_id', 'alternate_exercise_description']);
        });
    }
};
