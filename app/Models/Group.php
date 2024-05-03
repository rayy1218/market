<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $table = 'group';

    protected $fillable = [
        'company_id',
        'name',
    ];

    public function access_right() {
      return $this->hasMany(GroupAccessRight::class)->with(['meta']);
    }

    public function employees() {
      return $this->hasMany(User::class, 'group_id', 'id');
    }
}
