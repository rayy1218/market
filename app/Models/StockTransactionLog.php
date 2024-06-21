<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockTransactionLog extends Model
{
  protected $table = 'stock_transaction_log';

  protected $fillable = [
      'company_id',
      'user_id',
      'type',
      'stock_in_item_id',
      'stock_in_quantity',
      'stock_in_location_id',
      'stock_out_item_id',
      'stock_out_quantity',
      'stock_out_location_id',
      'checkout_item_id',
      'order_item_id',
  ];

  public const TYPE_STOCK_IN = 'stock_in';
  public const TYPE_STOCK_OUT = 'stock_out';
  public const TYPE_TRANSFER = 'transfer';
  public const TYPE_SPLIT = 'split';
  public const TYPE_ORDER_STOCK_IN = 'order_stock_in';
  public const TYPE_CHECKOUT_STOCK_OUT = 'checkout_stock_out';

  public static function of($company_id) {
    return self::where('company_id', $company_id);
  }

  public function user() {
    return $this->belongsTo(User::class, 'user_id', 'id');
  }

  public function stockInItem() {
    return $this->belongsTo(ItemMeta::class, 'stock_in_item_id', 'id');
  }

  public function stockInLocation() {
    return $this->belongsTo(StockLocation::class, 'stock_in_location_id', 'id');
  }

  public function stockOutItem() {
    return $this->belongsTo(ItemMeta::class, 'stock_out_item_id', 'id');
  }

  public function stockOutLocation() {
    return $this->belongsTo(StockLocation::class, 'stock_out_location_id', 'id');
  }

  public function checkoutItem() {
    return $this->belongsTo(CheckoutItem::class, 'checkout_item_id', 'id');
  }

  public function orderItem() {
    return $this
      ->hasOne(OrderItem::class, 'item_meta_id', 'stock_in_item_id')
      ->with('item_source');
  }
}
