<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Exercise extends Model implements HasMedia
{
    use HasFactory ,InteractsWithMedia;

    protected $fillable = [ 'title', 'instruction', 'tips', 'video_type', 'video_url','hls_video', 'bodypart_ids', 'duration', 'sets', 'equipment_id','exercise_id', 'level_id', 'status','is_premium', 'based', 'type','exercise_gif','exercise_image','english_video' ];

    protected $casts = [
        'equipment_id'      => 'integer',
        'level_id'          => 'integer',
        'is_premium'        => 'integer',
    ];

    public function equipment()
    {
        return $this->belongsTo(Equipment::class, 'equipment_id', 'id');
    }

    public function level()
    {
        return $this->belongsTo(Level::class, 'level_id', 'id');
    }

    public function getBodypartIdsAttribute($value)
    {
        return isset($value) ? json_decode($value, true) : null; 
    }

    public function setBodypartIdsAttribute($value)
    {
        $this->attributes['bodypart_ids'] = isset($value) ? json_encode($value) : null;
    }

    public function getSetsAttribute($value)
    {
        return isset($value) ? json_decode($value, true) : null;
    }
    
    public function setSetsAttribute($value)
    {
        $this->attributes['sets'] = isset($value) ? json_encode($value) : null;
    }
    
    public function workoutDayExercise(){
        return $this->hasMany(WorkoutDayExercise::class, 'exercise_id', 'id');
    }

    protected static function boot()
    {
        parent::boot();

        static::deleted(function ($row) {
            $row->workoutDayExercise()->delete();
        });
    }
    
    
    public function alternate_exercise()
    {
        return $this->belongsTo(Exercise::class, 'exercise_id');
    }
    
    
   public function exerciseVideos()
    {
        return $this->hasMany(ExerciseVideo::class);
    }
    
    
    
     protected $appends = ['exercise_image_url','exercise_gif_url'];
    
    // protected $appends = ['exercise_gif_url', 'video_url_full','exercise_image_url','english_video_url','hls_video_url'];
    
    public function getExerciseGifUrlAttribute()
    {
        if ($this->exercise_gif) {
            return asset('https://fitness.completepersonaltraining.com/storage/' . $this->exercise_gif);
        }
        return null;
    }
    
    // public function getHlsVideoUrlAttribute()
    // {
    //     if ($this->hls_video) {
    //         return asset('https://fitness.completepersonaltraining.com/storage/' . $this->hls_video);
    //     }
    //     return null;
    // }
    
    
    // public function getEnglishVideoUrlAttribute()
    // {
    //     if ($this->english_video) {
    //         return asset('https://fitness.completepersonaltraining.com/storage/' . $this->english_video);
    //     }
    //     return null;
    // }
    
    //  public function getVideoUrlFullAttribute()
    // {
    //     if ($this->video_url) {
    //         return asset('https://fitness.completepersonaltraining.com/storage/' . $this->video_url);
    //     }
    //     return null;
    // }
    
     public function getExerciseImageUrlAttribute()
    {
        if ($this->exercise_image) {
            return asset('https://fitness.completepersonaltraining.com/storage/' . $this->exercise_image);
        }
        return null;
    }


}
