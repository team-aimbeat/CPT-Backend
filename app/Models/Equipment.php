<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Equipment extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [ 'title', 'status' , 'video_type', 'video_url', 'workout_modes'];
    
    public function getWorkoutModesAttribute($value)
    {
        return isset($value) ? explode(",",$value) : null; 
    }

    public function setWorkoutModesAttribute($value)
    {
        $this->attributes['workout_modes'] = isset($value) ? implode(",",$value) : null;
    }
}
