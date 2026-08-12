<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    //

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'product_type',
        'base_price',
        'description',
        'status',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'product_type' => 'string',
        'status' => 'string',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function shoe(): HasOne
    {
        return $this->hasOne(Shoe::class);
    }

    public function cloth(): HasOne
    {
        return $this->hasOne(Cloth::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }
}
