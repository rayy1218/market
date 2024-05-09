<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemPriceModifier extends Model
{
    public $table = 'item_price_modifier';

    protected $fillable = [
        'item_sale_data_id',
        'set_price',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];
}
