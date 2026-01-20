<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    use HasFactory,InteractsWithMedia;
    
    protected $fillable = [ 'title', 'description', 'affiliate_link', 'price', 'productcategory_id', 'featured', 'status' ];

    protected $casts = [
        'productcategory_id'  => 'integer',
        'price' => 'double',
    ];

    public function productcategory()
    {
        return $this->belongsTo(ProductCategory::class, 'productcategory_id', 'id');
    }


}
