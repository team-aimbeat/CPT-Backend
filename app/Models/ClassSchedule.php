<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ClassSchedule extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [ 'class_name', 'workout_id', 'workout_title', 'workout_type', 'start_date', 'end_date', 'start_time', 'end_time', 'name', 'link', 'is_paid', 'price', 'video_type', 'video_url'];

    protected $casts = [
        'workout_id'   => 'integer',
        'price'   => 'double',
    ];

    public function workout()
    {
        return $this->belongsTo(Workout::class, 'workout_id', 'id');
    }

    // public function getWorkoutIdAttribute()
    // {
    //     $workouts = [];
    //     if ( $this->workout_type == 'other' ) {
    //         $workouts = ['other' => 'Other'];
    //     }

    //     if ( $this->workout_type == 'workout' ) {
    //         $workouts = [ $this->workout_id => optional($this->workout)->title ];
    //     }

    //     return $workouts;
    // }
}
