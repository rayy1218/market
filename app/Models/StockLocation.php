<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockLocation extends Model
{
    protected $table = 'stock_location';

    protected $fillable = [
        'company_id',
        'parent_id',
        'name',
    ];

  public static function of($company_id) {
    return self::where('company_id', $company_id);
  }

  public function stockIn($item_id, $quantity) {
    $stock = ItemStockData::where('location_id', $this->id)
      ->where('item_meta_id', $item_id)
      ->first();
    if ($stock) {
      $stock->update([
        'quantity' => $stock->quantity + $quantity,
      ]);
    }
    else {
      ItemStockData::create([
        'location_id' => $this->id,
        'item_meta_id' => $item_id,
        'quantity' => $quantity,
      ]);
    }
  }

  public function stockOut($item_id, $quantity) {
    $stock = ItemStockData::where('location_id', $this->id)
      ->where('item_meta_id', $item_id)
      ->first();
    if ($stock) {
      $stock->update([
        'quantity' => $stock->quantity - $quantity,
      ]);
    }
  }

  public function children() {
    return $this->hasMany(StockLocation::class, 'parent_id');
  }

  public function parent() {
    return $this->belongsTo(StockLocation::class, 'parent_id');
  }

  public function stocks() {
    return $this->hasMany(ItemStockData::class, 'location_id', 'id');
  }
}
