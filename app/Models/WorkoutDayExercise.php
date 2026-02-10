<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkoutDayExercise extends Model
{
    use HasFactory;

    protected $fillable = [
        'workout_id',
        'workout_day_id',
        'exercise_id',
        'alternate_exercise_id',
        'exercise_title',
        'instruction',
        'alternate_exercise_description',
        'sets',
        'sequence',
        'duration'
    ];

    protected $casts = [
        'workout_id'        => 'integer',
        'workout_day_id'    => 'integer',
        'exercise_id'       => 'integer',
        'sequence'          => 'integer',
    ];

    public function exercise()
    {
        return $this->belongsTo(Exercise::class, 'exercise_id', 'id');
    }

    public function alternateExercise()
    {
        return $this->belongsTo(Exercise::class, 'alternate_exercise_id', 'id');
    }
}
