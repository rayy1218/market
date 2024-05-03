<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccessRight extends Model
{
  protected $table = 'access_right';

  protected $fillable = [
    'label',
    'name',
  ];
}
