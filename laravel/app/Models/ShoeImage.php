<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShoeImage extends Model
{
    //
    protected $table = 'shoe_images';
    protected $fillable = [
        'shoe_id', 'image_url', 'public_id', 'color', 'stud_type', 'is_primary', 'display_order'
    ];

    protected $casts = [
        'is_primary'=>'boolean',
        'display_order'=>'integer'
    ];

    public function shoe(): BelongsTo
    {
        return $this->belongsTo(Shoe::class);
    }
}
