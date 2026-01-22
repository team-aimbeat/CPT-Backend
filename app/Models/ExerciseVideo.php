<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExerciseVideo extends Model
{
    use HasFactory;
    
    protected $table = 'exercise_video';

    protected $fillable = [
        'languagelist_id',
        'exercise_id',
        'video_url',
    ];
    
    
    public function languageList()
    {
        return $this->belongsTo(LanguageList::class, 'languagelist_id');
    }

    public function exercise()
    {
        return $this->belongsTo(Exercise::class, 'exercise_id');
    }

}
