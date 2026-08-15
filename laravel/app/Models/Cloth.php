<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cloth extends Model
{
    //

    protected $table = 'clothes';

    protected $fillable = [
        'product_id',
        'sleeve_type'
    ];

    protected $cast = [
        'sleeve_type'=>'string'
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ClothImage::class);
    }

    public function getPrimaryImageAttribute(){
        $primary = $this->images()->where('is_primary', true)->first();
        return $primary ? $primary->image_url : $this->images()->first()?->image_url;
    }

    public function getImagesByColor(string $color){
        return $this->images()->where('color', $color)->orderBy('display_order')->get();
    }
}
