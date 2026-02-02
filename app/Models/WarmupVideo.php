<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarmupVideo extends Model
{
    protected $fillable = [
        'languagelist_id',
        'video_url',
        'hls_master_url',
        'hls_1080p_url',
        'hls_720p_url',
        'hls_480p_url',
        'thumbnail_url',
        'transcoding_status',
    ];

    public function languageList()
    {
        return $this->belongsTo(LanguageList::class, 'languagelist_id');
    }
}
