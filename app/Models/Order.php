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
}
