<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
