<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Address extends Model
{
    protected $table = 'address';

    protected $fillable = [
        'line1',
        'line2',
        'city',
        'state',
        'zipcode',
        'country',
    ];

  public static function fromRequest(Request $request) {
    return Address::create([
      'line1' => $request->input('line1'),
      'line2' => $request->input('line2'),
      'city' => $request->input('city'),
      'state' => $request->input('state'),
      'zipcode' => $request->input('zipcode'),
      'country' => $request->input('country'),
    ]);
  }
}
