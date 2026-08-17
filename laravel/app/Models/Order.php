<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    //

    protected $fillable = [
        'user_id', 'order_code', 'customer_name', 'customer_phone', 'shipping_address', 'note', 'total_amount', 'shipping_fee',
        'discount_amount', 'payment_method', 'status'
    ];

    protected $casts = [
        'total_amount'=>'decimal:2', 
        'shipping_fee'=>'decimal:2',
        'discount_amount'=>'decimal:2'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items():HasMany
    {
        return $this->hasMany(OrderItem::class);

    }

    public function getStatusLabelAttribute(){
        $statuses = [
            'pending'=>'Chờ xác nhận', 
            'processing'=>'Đang xử lý',
            'shipped'=>'Đã giao hàng', 
            'delivered'=>'Đã nhận hàng', 
            'cancelled'=>'Đã hủy'
        ];

        return $statuses[$this->status] ?? $this->status;
    }


    public function getStatusColorAttribute()
    {
        $colors = [
            'pending' => 'yellow',
            'processing' => 'blue',
            'shipped' => 'purple',
            'delivered' => 'green',
            'cancelled' => 'red',
        ];
        return $colors[$this->status] ?? 'gray';
    }
}
