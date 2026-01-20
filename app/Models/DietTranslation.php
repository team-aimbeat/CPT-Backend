<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class DietTranslation extends Model
{
    protected $fillable = [
        'diet_id',
        'language_id',
        'title',
        'ingredients',
        'description'
    ];
}