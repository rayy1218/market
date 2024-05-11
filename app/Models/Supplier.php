<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
  protected $table = 'supplier';

  protected $fillable = [
      'company_id',
      'address_id',
      'name',
      'phone_number',
      'email',
  ];

  public static function of($company_id) {
    return self::where('company_id', $company_id);
  }

  public function address() {
    return $this->belongsTo(Address::class, 'address_id', 'id');
  }

  public function sources() {
    return $this->hasMany(ItemSource::class, 'supplier_id', 'id');
  }

  public function orders() {
    return $this->hasMany(Order::class, 'supplier_id', 'id');
  }
}
