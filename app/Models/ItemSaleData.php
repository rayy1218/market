<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemSaleData extends Model
{
    public $table = 'item_sale_data';

    protected $fillable = [
        'item_meta_id',
        'price',
        'started_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
    ];

    public function item_meta() {
      return $this->belongsTo(ItemMeta::class, 'item_meta_id', 'id');
    }
}
