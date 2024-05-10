<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemStockData extends Model
{
  protected $table = 'item_stock_data';

  protected $fillable = [
      'item_meta_id',
      'location_id',
      'quantity',
  ];

  public function item_meta() {
    return $this->belongsTo(ItemMeta::class, 'item_meta_id', 'id');
  }

  public function stock_location() {
    return $this->belongsTo(StockLocation::class, 'location_id', 'id');
  }
}
