<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    //
    protected $fillable = [
        'product_id', 'sku', 'color', 'size', 'stock_quantity', 'price_override', 'status'
    ];

    protected $casts = [
        'stock_quantity'=>'integer',
        'price_override'=>'decimal:2',
        'status'=>'string'
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
