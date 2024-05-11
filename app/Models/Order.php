<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'item_order';

    protected $fillable = [
        'supplier_id',
        'user_id',
        'status',
        'remark',
    ];

    public const STATUS_CREATED = 'created';
    public const STATUS_PENDING = 'pending';
    public const STATUS_PREPARING = 'preparing';
    public const STATUS_DELIVERING = 'delivering';
    public const STATUS_RECEIVED = 'received';
    public const STATUS_CANCELED = 'canceled';

    public static function of($company_id) {
      return self::with('supplier')
        ->whereHas('supplier', function($query) use ($company_id) {
          $query->where('company_id', $company_id);
        });
    }

    public function supplier() {
      return $this->belongsTo(Supplier::class, 'supplier_id', 'id');
    }

    public function order_items() {
      return $this->hasMany(OrderItem::class, 'order_id', 'id');
    }
}
