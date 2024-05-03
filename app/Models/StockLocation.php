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
}
