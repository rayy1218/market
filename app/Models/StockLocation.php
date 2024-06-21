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

  public function stockIn($user, $item_id, $quantity, $orderItemId = null) {
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

    StockTransactionLog::create([
      'company_id' => $user->company_id,
      'user_id' => $user->id,
      'type' => $orderItemId
        ? StockTransactionLog::TYPE_ORDER_STOCK_IN
        : StockTransactionLog::TYPE_STOCK_IN,
      'stock_in_location_id' => $this->id,
      'stock_in_item_id' => $item_id,
      'stock_in_quantity' => $quantity,
      'order_item_id' => $orderItemId
    ]);
  }

  public function stockOut($user, $item_id, $quantity, $checkoutItemId = null) {
    $stock = ItemStockData::where('location_id', $this->id)
      ->where('item_meta_id', $item_id)
      ->first();
    if ($stock) {
      $stock->update([
        'quantity' => $stock->quantity - $quantity,
      ]);
    }
    else {
      ItemStockData::create([
        'location_id' => $this->id,
        'item_meta_id' => $item_id,
        'quantity' => 0 - $quantity,
      ]);
    }

    StockTransactionLog::create([
      'company_id' => $user->company_id,
      'user_id' => $user->id,
      'type' => $checkoutItemId
        ? StockTransactionLog::TYPE_CHECKOUT_STOCK_OUT
        : StockTransactionLog::TYPE_STOCK_OUT,
      'stock_out_location_id' => $this->id,
      'stock_out_item_id' => $item_id,
      'stock_out_quantity' => $quantity,
      'checkout_item_id' => $checkoutItemId,
    ]);
  }

  public function transferTo($user, $location_id, $item_id, $quantity) {
    $currentStock = ItemStockData::where('location_id', $this->id)
      ->where('item_meta_id', $item_id)
      ->first();

    $new_item_stock_data = ItemStockData::where('location_id', $location_id)
      ->where('item_meta_id', $item_id)
      ->first();

    if ($new_item_stock_data) {
      $new_item_stock_data->update([
        'quantity' => $new_item_stock_data->quantity + $quantity,
      ]);
    }
    else {
      ItemStockData::create([
        'location_id' => $location_id,
        'item_meta_id' => $item_id,
        'quantity' => $quantity,
      ]);
    }

    $currentStock->update([
      'quantity' => $currentStock->quantity - $quantity,
    ]);

    StockTransactionLog::create([
      'company_id' => $user->company_id,
      'user_id' => $user->id,
      'type' => StockTransactionLog::TYPE_TRANSFER,
      'stock_in_location_id' => $location_id,
      'stock_in_item_id' => $item_id,
      'stock_in_quantity' => $quantity,
      'stock_out_location_id' => $this->id,
      'stock_out_item_id' => $item_id,
      'stock_out_quantity' => $quantity,
    ]);
  }

  public function splitTo($user, $from_item_id, $from_quantity, $to_item_id, $to_quantity) {
    $stock_to = ItemStockData::where('location_id', $this->id)
      ->where('item_meta_id', $to_item_id)
      ->first();

    if ($stock_to) {
      $stock_to->update([
        'quantity' => $stock_to->quantity + $to_quantity,
      ]);
    }
    else {
      ItemStockData::create([
        'location_id' => $this->id,
        'item_meta_id' => $to_item_id,
        'quantity' => $to_quantity,
      ]);
    }

    $stock_from = ItemStockData::where('location_id', $this->id)
      ->where('item_meta_id', $from_item_id)
      ->first();

    if ($stock_from) {
      $stock_from->update([
        'quantity' => $stock_from->quantity - $from_quantity,
      ]);
    }
    else {
      ItemStockData::create([
        'location_id' => $this->id,
        'item_meta_id' => $from_item_id,
        'quantity' => 0 - $from_quantity,
      ]);
    }

    StockTransactionLog::create([
      'company_id' => $user->company_id,
      'user_id' => $user->id,
      'type' => StockTransactionLog::TYPE_SPLIT,
      'stock_in_location_id' => $this->id,
      'stock_in_item_id' => $to_item_id,
      'stock_in_quantity' => $to_quantity,
      'stock_out_location_id' => $this->id,
      'stock_out_item_id' => $from_item_id,
      'stock_out_quantity' => $from_quantity,
    ]);
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
