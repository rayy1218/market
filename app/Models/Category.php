<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
  protected $table = 'category';

  protected $fillable = [
      'name',
      'company_id',
  ];

  public static function of($company_id) {
    return self::where('company_id', $company_id);
  }
}
