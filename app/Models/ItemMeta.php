<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
    ];

    protected $casts = [
        'others' => 'array',
    ];

    public static function of($company_id) {
      return self::where('company_id', $company_id);
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
}
