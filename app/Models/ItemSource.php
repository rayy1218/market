<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemSource extends Model
{
    protected $table = 'item_source';

    protected $fillable = [
        'supplier_id',
        'item_meta_id',
        'unit_price',
        'min_order_quantity',
        'estimated_lead_time_day',
    ];

    public function item_meta() {
      return $this->belongsTo(ItemMeta::class, 'item_meta_id', 'id');
    }

    public function supplier() {
      return $this->belongsTo(Supplier::class, 'supplier_id', 'id');
    }
}
