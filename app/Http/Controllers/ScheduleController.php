<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Models\ShiftRecord;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ScheduleController extends Controller
{
  public function getTodayTimestamps(Request $request) {
    $user_id = $request->requestFrom->id;
    $timestamps = ShiftRecord::of($user_id)
      ->whereBetween('created_at', [
        Carbon::now('Asia/Kuala_Lumpur')->startOfDay(),
        Carbon::now('Asia/Kuala_Lumpur')->endOfDay()]
      )
      ->get();

    return ResponseHelper::success([
      'data' => $timestamps,
    ]);
  }

  public function addTimestamp(Request $request) {
    $user_id = $request->requestFrom->id;
    $type = $request->input('type');

    try {
      $status = ShiftRecord::currentStatus($user_id);
      switch ($status) {
        case 'start':
          if ($type != 'start_shift') {
            return ResponseHelper::rejected([
              'message' => 'FAILED_INVALID_REQUEST1'
            ]);
          }
          break;

        case 'work':
          if ($type != 'end_shift' && $type != 'start_break') {
            return ResponseHelper::rejected([
              'message' => 'FAILED_INVALID_REQUEST2'
            ]);
          }
          break;

        case 'rest':
          if ($type != 'end_break' && $type != 'end_shift') {
            return ResponseHelper::rejected([
              'message' => 'FAILED_INVALID_REQUEST3'
            ]);
          }
          break;
      }

      ShiftRecord::create([
        'user_id' => $user_id,
        'shift_record_type' => $type,
      ]);

      return ResponseHelper::success();
    }
    catch (\Exception $exception) {
      return ResponseHelper::error([
        'message' => $exception->getMessage(),
      ]);
    }
  }
}
