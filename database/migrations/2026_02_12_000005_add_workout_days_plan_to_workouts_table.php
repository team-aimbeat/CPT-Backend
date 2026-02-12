<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workouts', function (Blueprint $table) {
            $table->unsignedTinyInteger('workout_days_plan')
                ->default(6)
                ->after('workout_type_id');
        });

        DB::table('workouts')
            ->whereNull('workout_days_plan')
            ->update(['workout_days_plan' => 6]);
    }

    public function down(): void
    {
        Schema::table('workouts', function (Blueprint $table) {
            $table->dropColumn('workout_days_plan');
        });
    }
};

