<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupAccessRight extends Model
{
  protected $table = 'group_access_right';

  protected $fillable = [
    'group_id',
    'access_right_id',
    'is_enabled',
  ];

  public function meta() {
    return $this->hasOne(AccessRight::class, 'id', 'access_right_id');
  }
}
