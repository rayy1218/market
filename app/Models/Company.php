<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $table = 'company';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'INACTIVE';

    protected $fillable = [
        'address_id',
        'company_name',
        'status',
    ];
}
