<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EquipmentVideo extends Model
{
    use HasFactory;

    protected $table = 'equipment_videos';

    protected $fillable = [
        'equipment_id',
        'languagelist_id',
        'video_url',
    ];

    public function languageList()
    {
        return $this->belongsTo(LanguageList::class, 'languagelist_id');
    }

    public function equipment()
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }
}
