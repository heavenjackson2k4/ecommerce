<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    //

    protected $fillable = [
        'cart_id', 'product_id', 'variant_type', 'variant_id', 'size','color', 'stud_type', 'quantity', 'price'
    ];

    protected $casts = [
        'quantity'=>'integer',
        'price'=>'decimal:2'
    ];

    public function product():BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getVariantAttribute(){
        if($this->variant_type =='shoe'){
            return ShoesVariant::find($this->variant_id);
        }
        return ClothesVariant::find($this->variant_id);
    }
}
