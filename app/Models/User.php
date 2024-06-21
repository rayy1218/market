<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $table = 'user';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'last_name',
        'first_name',
        'group_id',
        'company_id',
        'token',
        'token_expired_at',
        'invite_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed',
    ];

    public function group() {
      return $this->hasOne(Group::class, 'id', 'group_id');
    }

    public function company() {
      return $this->hasOne(Company::class, 'id', 'company_id');
    }
}
