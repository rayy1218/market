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
        'payment_method',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
    ];

    public static function of($company_id) {
      return self::where('company_id', $company_id);
    }

    public function items($company_id) {
      return $this->hasMany(CheckoutItem::class, 'checkout_id', 'id');
    }
}
