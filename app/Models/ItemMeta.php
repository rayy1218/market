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
}
