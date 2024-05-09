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
