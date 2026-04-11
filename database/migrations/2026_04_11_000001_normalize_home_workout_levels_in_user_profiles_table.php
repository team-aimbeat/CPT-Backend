<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $homeWorkoutTypeIds = DB::table('workout_types')
            ->whereRaw('LOWER(title) LIKE ?', ['%home%'])
            ->pluck('id')
            ->all();

        $intermediateLevelIds = DB::table('levels')
            ->whereRaw('LOWER(title) LIKE ?', ['%intermediate%'])
            ->pluck('id')
            ->all();

        $advancedLevelId = DB::table('levels')
            ->whereRaw('LOWER(title) LIKE ?', ['%advance%'])
            ->orderBy('id')
            ->value('id');

        DB::table('user_profiles')
            ->where(function ($query) use ($homeWorkoutTypeIds) {
                $query->where('workout_mode', 'home');

                if (!empty($homeWorkoutTypeIds)) {
                    $query->orWhereIn('workout_mode', $homeWorkoutTypeIds);
                }
            })
            ->where(function ($query) use ($intermediateLevelIds) {
                $query->where('workout_level', 'intermediate');

                if (!empty($intermediateLevelIds)) {
                    $query->orWhereIn('workout_level', $intermediateLevelIds);
                }
            })
            ->update([
                'workout_level' => $advancedLevelId ?? 'advance',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        // Intentionally left empty because previous intermediate records
        // cannot be safely reconstructed after normalization.
    }
};
