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
    'default_stock_out_location_id'
  ];

  protected $casts = [
    'started_at' => 'datetime',
  ];

  public function item_meta() {
    return $this->belongsTo(ItemMeta::class, 'item_meta_id', 'id');
  }

  public function default_stock_out_location() {
    return $this->belongsTo(StockLocation::class, 'default_stock_out_location_id', 'id');
  }

  public function checkout_items() {
    return $this->hasMany(CheckoutItem::class, 'item_sale_data_id', 'id');
  }
}
