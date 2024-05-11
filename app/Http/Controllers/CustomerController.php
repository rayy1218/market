<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Models\Customer;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
  public function getCustomers(Request $request) {
    $company_id = $request->requestFrom->company_id;

    $records = Customer::of($company_id)->get();

    return ResponseHelper::success([
      'data' => $records,
    ]);
  }

  public function getCustomer(Request $request, $id) {
    $company_id = $request->requestFrom->company_id;

    $record = Customer::of($company_id)->where('id', $id)->first();
    if (!$record)
      return ResponseHelper::rejected([
        'message' => 'FAILED_RECORD_NOT_FOUND',
      ]);

    return ResponseHelper::success([
      'data' => $record,
    ]);
  }

  public function updateCustomer(Request $request, $id) {
    $company_id = $request->requestFrom->company_id;
    $name = $request->input('name');
    $phone = $request->input('phone');
    $email = $request->input('email');

    $record = Customer::of($company_id)->where('id', $id)->first();
    if (!$record)
      return ResponseHelper::rejected([
        'message' => 'FAILED_RECORD_NOT_FOUND',
      ]);

    try {
      DB::beginTransaction();

      $record->update([
        'name' => $name,
        'phone_number' => $phone,
        'email' => $email
      ]);

      DB::commit();
      return ResponseHelper::success();
    }
    catch (Exception $e) {
      DB::rollBack();
      return ResponseHelper::error([
        'error_message' => $e->getMessage(),
      ]);
    }
  }

  public function createCustomer(Request $request) {
    $company_id = $request->requestFrom->company_id;
    $name = $request->input('name');
    $phone = $request->input('phone');
    $email = $request->input('email');

    $record = Customer::of($company_id)->where('name', $name)->first();
    if ($record)
      return ResponseHelper::rejected([
        'message' => 'FAILED_RECORD_EXISTED',
      ]);

    try {
      DB::beginTransaction();

      Customer::create([
        'company_id' => $company_id,
        'name' => $name,
        'phone_number' => $phone,
        'email' => $email,
      ]);

      DB::commit();
      return ResponseHelper::success();
    }
    catch (Exception $e) {
      DB::rollBack();
      return ResponseHelper::error([
        'error_message' => $e->getMessage(),
      ]);
    }
  }

  public function deleteCustomer(Request $request, $id) {
    $company_id = $request->requestFrom->company_id;
    $record = Customer::of($company_id)->where('id', $id)->first();
    if (!$record)
      return ResponseHelper::rejected([
        'message' => 'FAILED_RECORD_NOT_FOUND',
      ]);

    $record->delete();

    return ResponseHelper::success();
  }
}
