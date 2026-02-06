<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Workout;

class WorkoutCompletion extends Model
{
    protected $fillable = [
        'user_id',
        'workout_id',
        'completed_date',
    ];

    protected $casts = [
        'completed_date' => 'date',
    ];

    public function workout()
    {
        return $this->belongsTo(Workout::class, 'workout_id');
    }
}
