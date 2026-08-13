<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShoesVariant extends Model
{
    protected $table = 'shoes_variants';

    protected $fillable = [
        'product_id',
        'size',
        'color',
        'stud_type',
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