<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class AssignWorkout extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'user_id',
        'workout_id',
        'status',
        'disable',
        'cycle_no',
        'assigned_from',
        'is_active',
    ];

    protected $casts = [
            'user_id'      => 'integer',
            'workout_id'   => 'integer',
            'status'       => 'integer',
            'disable'      => 'integer',
            'cycle_no'     => 'integer',
            'is_active'    => 'integer',
        ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function workout()
    {
        return $this->belongsTo(Workout::class, 'workout_id', 'id');
    }
}
