<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Checkout extends Model
{
    protected $table = 'checkout';

    protected $fillable = [
        'company_id',
        'customer_id',
        'user_id',
        'amount',
        'timestamp',
        'status',
        'payment_method',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
    ];
}
