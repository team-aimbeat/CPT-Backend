<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workout extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [ 'title', 'description', 'status', 'workout_type_id', 'workout_days_plan', 'level_id','goal_id', 'is_premium', 'video_type', 'video_url' ,'stetch_video','gender'];

    protected $casts = [
        'workout_type_id'   => 'integer',
        'workout_days_plan' => 'integer',
        'level_id'          => 'integer',
        'is_premium'        => 'integer',
    ];

    public function workouttype()
    {
        return $this->belongsTo(WorkoutType::class, 'workout_type_id', 'id');
    }

    public function level()
    {
        return $this->belongsTo(Level::class, 'level_id', 'id');
    }
    
    public function goal()
    {
        return $this->belongsTo(BodyPart::class, 'goal_id', 'id');
    }


    public function workoutDayExercise()
    {
        return $this->hasMany(WorkoutDayExercise::class, 'workout_id', 'id');
    }

    public function workoutDay()
    {
        return $this->hasMany(WorkoutDay::class, 'workout_id', 'id');
    }
    
    public function userFavouriteWorkout()
    {
        return $this->hasMany(UserFavouriteWorkout::class, 'workout_id', 'id');
    }
    public function userAssignWorkout()
    {
        return $this->hasMany(AssignWorkout::class, 'workout_id', 'id');
    }

    public function scopeMyWorkout($query, $user_id =null)
    {
        $user = auth()->user();

        if($user->hasRole(['user'])){
            $query = $query->whereHas('userFavouriteWorkout', function ($q) use($user) {
                $q->where('user_id', $user->id);
            });
        }

        if($user_id != null) {
            return $query->whereHas('userAssignWorkout', function ($q) use($user_id) {
                $q->where('user_id', $user_id);
            });
             
        }

        return $query;
    }
    public function scopeMyAssignWorkout($query, $user_id =null)
    {
        $user = auth()->user();

        if($user->hasRole(['user'])){
            $query = $query->whereHas('userAssignWorkout', function ($q) use($user) {
                $q->where('user_id', $user->id);
            });
        }

        return $query;
    }
    
    //added by pooja
    public function workoutDays(): HasMany
    {
        return $this->hasMany(WorkoutDay::class, 'workout_id', 'id');
    }

    public function workoutExercise() {
        return $this->hasManyThrough(
            WorkoutDayExercise::class,
            WorkoutDay::class,
        );
    }
    
    
     public function assignedWorkouts(): HasMany
    {
        return $this->hasMany(AssignWorkout::class, 'workout_id');
        
    }
    
    
    protected $appends = ['video_url_warmup'];
    
    public function getVideoUrlWarmupAttribute()
    {
        if ($this->video_url) {
            return asset('https://fitness.completepersonaltraining.com/storage/' . $this->video_url);
        }
        return null;
    }
}
