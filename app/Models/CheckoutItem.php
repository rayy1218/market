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

    public function sale_data() {
      return $this->belongsTo(ItemSaleData::class, 'item_sale_data_id', 'id');
    }

    public function checkout() {
      return $this->belongsTo(Checkout::class, 'checkout_id', 'id');
    }
}
