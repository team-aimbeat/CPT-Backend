<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCompletedExercise extends Model
{
    protected $fillable = [
        'user_id', 'exercise_id', 'workout_id', 'workout_day_id', 'completed_at'
    ];
}
