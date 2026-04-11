<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workout_days', function (Blueprint $table) {
            if (!Schema::hasColumn('workout_days', 'month_no')) {
                $table->unsignedInteger('month_no')->default(1)->after('workout_id');
            }
        });

        DB::table('workout_days')
            ->whereNull('month_no')
            ->update(['month_no' => 1]);

        Schema::table('workout_days', function (Blueprint $table) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexes = $sm->listTableIndexes('workout_days');

            if (!array_key_exists('workout_days_workout_id_month_no_week_day_sequence_index', $indexes)) {
                $table->index(
                    ['workout_id', 'month_no', 'week', 'day', 'sequence'],
                    'workout_days_workout_id_month_no_week_day_sequence_index'
                );
            }
        });
    }

    public function down(): void
    {
        Schema::table('workout_days', function (Blueprint $table) {
            if (Schema::hasColumn('workout_days', 'month_no')) {
                $table->dropIndex('workout_days_workout_id_month_no_week_day_sequence_index');
            }
        });

        Schema::table('workout_days', function (Blueprint $table) {
            if (Schema::hasColumn('workout_days', 'month_no')) {
                $table->dropColumn('month_no');
            }
        });
    }
};
