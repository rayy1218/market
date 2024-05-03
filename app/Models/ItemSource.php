<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemSource extends Model
{
    protected $table = 'item_source';

    protected $fillable = [
        'supplier_id',
        'item_meta_id',
        'unit_price',
        'min_order_quantity',
        'estimated_lead_time_day',
    ];
}
