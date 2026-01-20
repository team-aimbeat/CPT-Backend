<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;


class Post extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [ 'title','tags_id','category_ids','datetime','status','is_featured','description', 'video_type', 'video_url'];

    public function getTagsIdAttribute($value)
    {
        return isset($value) ? json_decode($value, true) : null; 
    }

    public function setTagsIdAttribute($value)
    {
        $this->attributes['tags_id'] = isset($value) ? json_encode($value) : null;
    }
    public function getCategoryIdsAttribute($value)
    {
        return isset($value) ? json_decode($value, true) : null; 
    }

    public function setCategoryIdsAttribute($value)
    {
        $this->attributes['category_ids'] = isset($value) ? json_encode($value) : null;
    }
}
