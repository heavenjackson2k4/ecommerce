<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shoe extends Model
{
    //
    protected $fillable = [
        'product_id',
        'silo'
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ShoeImage::class);
    }

    public function getPrimaryImageAttribute(){
        $images = $this->relationLoaded('images')
            ? $this->images
            : $this->images()->orderBy('display_order')->get();
        $primary = $images->firstWhere('is_primary', true);

        return ($primary ?? $images->first())?->image_url;
    }

    public function getImageByColor(string $color){
        return $this->images()->where('color', $color)->orderBy('display_order')->get();
    }

    
}
