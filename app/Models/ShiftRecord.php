<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class ShiftRecord extends Model
{
    protected $table = 'shift_record';

    protected $fillable = [
        'user_id',
        'shift_record_type',
    ];

    public static function of($user_id) {
      return self::where('user_id', $user_id);
    }

    public static function currentStatus($user_id) {
      $last = ShiftRecord::of($user_id)
        ->whereBetween('created_at', [Carbon::now('Asia/Kuala_Lumpur')->startOfDay(), Carbon::now('Asia/Kuala_Lumpur')->endOfDay()])
        ->orderBy('created_at', 'desc')
        ->first();

      if (!$last) {
        return 'start';
      }

      return match ($last->shift_record_type) {
        'start_shift', 'end_break' => 'work',
        'end_shift' => 'end',
        'start_break' => 'rest',
        default => throw new Exception('Error with shift record type'),
      };
    }

    public function today() {
      return $this->whereBetween('created_at', [Carbon::now('Asia/Kuala_Lumpur')->startOfDay(), Carbon::now('Asia/Kuala_Lumpur')->endOfDay()]);
    }
}
