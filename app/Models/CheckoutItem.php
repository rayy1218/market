<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CheckoutItem extends Model
{
    protected $table = 'checkout_item';

    protected $fillable = [
        'checkout_id',
        'item_price_modifier_id',
        'item_sale_data_id',
        'quantity',
    ];
}
