<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'customer';

    protected $fillable = [
        'company_id',
        'name',
        'email',
        'phone_number',
        'points',
    ];

    public static function of($company_id) {
      return self::where('company_id', $company_id);
    }
}
