<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClothImage extends Model
{
    //

    protected $table='cloth_images';
    protected $fillable = [
        'cloth_id', 'image_url', 'public_id', 'color', 'is_primary', 'display_order'
    ];

    protected $cast = [
        'is_primary'=>'boolean',
        'display_order'=>'integer'
    ];

    public function cloth(): BelongsTo
    {
        return $this->belongsTo(Cloth::class);
    }
}
