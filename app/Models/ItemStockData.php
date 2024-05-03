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
}
