<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemSupplyData extends Model
{
    protected $table = 'item_supply_data';

    protected $fillable = [
        'item_source_id',
        'item_meta_id',
        'on_low_stock_action',
        'default_restock_quantity',
        'restock_point',
    ];

    public function source() {
      return $this->belongsTo(ItemSource::class, 'item_source_id', 'id');
    }
}
