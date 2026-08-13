<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClothesVariant extends Model
{
    protected $table = 'clothes_variants';

    protected $fillable = [
        'product_id',
        'size',
        'color',
        'price_override',
        'quantity',
        'status',
    ];

    protected $casts = [
        'price_override' => 'decimal:2',
        'quantity' => 'integer',
        'status' => 'string',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}