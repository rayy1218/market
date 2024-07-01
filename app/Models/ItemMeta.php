<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class ItemMeta extends Model
{
    protected $table = 'item_meta';

    protected $fillable = [
        'company_id',
        'name',
        'stock_keeping_unit',
        'universal_product_code',
        'brand',
        'category',
        'others',
        'default_receive_location'
    ];

    protected $casts = [
        'others' => 'array',
    ];

    public static function of($company_id) {
      return self::where('company_id', $company_id);
    }

    public function stockOutRate() {
      // calculate rate based on last week, last month and last year
      $now = Carbon::now();
      $query = CheckoutItem::with(['sale_data', 'sale_data.item_meta'])
        ->where('sale_date.item_meta.id', $this->id);
      $last_week_consumption = $query
        ->whereBetween('created_at', [$now->subWeek()->startOfDay(), $now])
        ->select(['id', 'SUM(quantity) AS total'])->get();
      $last_month_consumption = $query
        ->whereBetween('created_at', [$now->subMonth()->startOfDay(), $now])
        ->select(['id', 'SUM(quantity) AS total'])->get();
      $last_year_consumption = $query
        ->whereBetween('created_at', [$now->subYear()->startOfDay(), $now])
        ->select(['id', 'SUM(quantity) AS total'])->get();

      return [
        'week' => $last_week_consumption->total / 7,
        'month' => $last_month_consumption->total / 30,
        'year' => $last_year_consumption->total / 365,
      ];
    }

    public function stocks() {
      return $this->hasMany(ItemStockData::class, 'item_meta_id', 'id');
    }

    public function brand() {
      return $this->belongsTo(Brand::class, 'brand', 'id');
    }

    public function category() {
      return $this->belongsTo(Category::class, 'category', 'id');
    }

    public function sources() {
      return $this->hasMany(ItemSource::class, 'item_meta_id', 'id');
    }

    public function sale_data() {
      return $this->hasOne(ItemSaleData::class, 'item_meta_id', 'id')->latest('started_at');
    }

    public function supply_data() {
      return $this->hasOne(ItemSupplyData::class, 'item_meta_id', 'id');
    }

    public function default_receive_location() {
      return $this->hasOne(StockLocation::class, 'id', 'default_receive_location');
    }
}
