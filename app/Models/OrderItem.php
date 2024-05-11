<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $table = 'item_order_item';

    protected $fillable = [
        'order_id',
        'item_source_id',
        'quantity',
    ];

    public function item_source() {
      return $this->belongsTo(ItemSource::class, 'item_source_id', 'id');
    }
}
