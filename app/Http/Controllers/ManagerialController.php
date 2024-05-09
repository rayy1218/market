<?php

namespace App\Http\Controllers;

use App\Helper\AuthenticationHelper;
use App\Helper\ResponseHelper;
use App\Models\AccessRight;
use App\Models\Group;
use App\Models\GroupAccessRight;
use App\Models\User;
use Database\Seeders\AccessRightSeeder;
use http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ManagerialController extends Controller
{
  public function index()
  {

  }

  public function getAllGroups(Request $request) {
    if (!AuthenticationHelper::haveAccessRight($request->requestFrom, 'managerial group view')) {
      return ResponseHelper::denied();
    }

    $company_id = $request->requestFrom->company_id;

    return ResponseHelper::success([
      'data' => Group::where('company_id', $company_id)->get(),
    ]);
  }

  public function getGroupsPagination(Request $request) {
    if (!AuthenticationHelper::haveAccessRight($request->requestFrom, 'managerial group view')) {
      return ResponseHelper::denied();
    }

    $company_id = $request->requestFrom->company_id;
    $length = $request->input('length', 50);
    $page = $request->input('page', 0);

    $query = Group::where('company_id', $company_id);

    $total = $query->count();

    $records = $query
      ->offset($page * $length)
      ->limit($length)
      ->get();

    return ResponseHelper::success([
      'data' => $records,
      'totalRecords' => $total,
    ]);
  }

  public function getGroupDetail(Request $request, $id) {
    if (!AuthenticationHelper::haveAccessRight($request->requestFrom, 'managerial group view')) {
      return ResponseHelper::denied();
    }

    $company_id = $request->requestFrom->company_id;

    $record = Group::with(['access_right', 'employees'])->where('company_id', $company_id)->where('id', $id)->first();

    if (!$record) {
      return ResponseHelper::rejected([
        'message' => 'FAILED_RECORD_NOT_FOUND',
      ], 404);
    }

    return ResponseHelper::success([
      'data' => $record,
    ]);
  }

  public function createGroup(Request $request) {
    if (!AuthenticationHelper::haveAccessRight($request->requestFrom, 'managerial group edit')) {
      return ResponseHelper::denied();
    }
    $company_id = $request->requestFrom->company_id;
    $group_name = $request->input('name');
    $group_access_rights = collect($request->input('access_right', []));

    $existing_group = Group::where('company_id', $company_id)
      ->where('name', $group_name)
      ->first();

    if ($existing_group) {
      return ResponseHelper::rejected([
        'message' => 'FAILED_RECORD_EXISTED'
      ]);
    }

    DB::beginTransaction();

    try {
      $group = Group::create([
        'company_id' => $company_id,
        'name' => $group_name,
      ]);

      $access_rights = array();

      $all_access_right = AccessRight::all();

      foreach ($all_access_right as $access_right) {
        $access_rights[] = [
          'group_id' => $group->id,
          'access_right_id' => $access_right->id,
          'is_enabled' => $group_access_rights->some(function ($value) use ($access_right) {
            return $value == $access_right->id;
          }),
        ];
      }

      GroupAccessRight::insert($access_rights);

      DB::commit();

      return ResponseHelper::success();
    }
    catch (\Exception $exception) {
      DB::rollBack();

      return ResponseHelper::error([
        'error_message' => $exception->getMessage(),
      ]);
    }
  }

  public function updateGroup(Request $request, $id) {
    if (!AuthenticationHelper::haveAccessRight($request->requestFrom, 'managerial group edit')) {
      return ResponseHelper::denied();
    }

    $company_id = $request->requestFrom->company_id;
    $new_name = $request->input('name');
    $new_access_rights = $request->input('access_right');
    $new_member = $request->input('member');

    DB::beginTransaction();

    try {
      $existing_group = Group::where('company_id', $company_id)
        ->where('name', $new_name)->first();

      if ($existing_group) {
        return ResponseHelper::rejected([
          'message' => 'FAILED_RECORD_EXISTED'
        ]);
      }

      $group = Group::where('id', $id)->first();

      if (!$group) {
        return ResponseHelper::rejected([
          'message' => 'FAILED_RECORD_NOT_FOUND',
        ], 404);
      }

      if ($new_name) {
        $group->update([
          'name' => $new_name,
        ]);
      }

      if ($new_access_rights) {
        GroupAccessRight::where('group_id', $id)->whereIn('access_right_id', $new_access_rights)->update([
          'is_enabled' => 1,
        ]);

        GroupAccessRight::where('group_id', $id)->whereNotIn('access_right_id', $new_access_rights)->update([
          'is_enabled' => 0,
        ]);
      }

      if ($new_member) {
        User::where('company_id', $company_id)->whereIn('id', $new_member)->update([
          'group_id' => $id,
        ]);
      }

      DB::commit();

      return ResponseHelper::success();
    }
    catch (\Exception $exception) {
      DB::rollBack();

      return ResponseHelper::error([
        'error_message' => $exception->getMessage(),
      ]);
    }
  }

  public function deleteGroup(Request $request, $id) {
    if (!AuthenticationHelper::haveAccessRight($request->requestFrom, 'managerial group edit')) {
      return ResponseHelper::denied();
    }
    $company_id = $request->requestFrom->company_id;

    $group = Group::where('company_id', $company_id)
      ->where('id', $id)
      ->first();

    if (!$group) {
      return ResponseHelper::rejected([
        'message' => 'FAILED_RECORD_NOT_FOUND',
      ]);
    }

    $group->delete();

    return ResponseHelper::success();
  }

  public function getEmployeesPagination(Request $request) {
    if (!AuthenticationHelper::haveAccessRight($request->requestFrom, 'managerial employee view')) {
      return ResponseHelper::denied();
    }

    $company_id = $request->requestFrom->company_id;
    $length = $request->input('length', 50);
    $page = $request->input('page', 0);

    $query = User::with('group')->where('company_id', $company_id);

    $records = $query->limit($length)->offset($page * $length)->get();

    $total = $query->count();

    return ResponseHelper::success([
      'data' => $records,
      'totalRecords' => $total,
    ]);
  }

  public function getEmployeeDetail(Request $request, $id) {
    if (!AuthenticationHelper::haveAccessRight($request->requestFrom, 'managerial employee view')) {
      return ResponseHelper::denied();
    }

    $company_id = $request->requestFrom->company_id;

    $record =  User::with('group')
      ->where('company_id', $company_id)
      ->where('id', $id)
      ->first();

    if (!$record) {
      return ResponseHelper::rejected([
        'message' => 'FAILED_RECORD_NOT_FOUND',
      ], 404);
    }

    return ResponseHelper::success([
      'data' => $record,
    ]);
  }

  public function createEmployee(Request $request) {
    if (!AuthenticationHelper::haveAccessRight($request->requestFrom, 'managerial employee edit')) {
      return ResponseHelper::denied();
    }

    $company_id = $request->requestFrom->company_id;
    $email = $request->input('email');
    $username = $request->input('username');
    $group = $request->input('group');

    $existing_email = User::where('email', $email)->first();

    if ($existing_email) {
      return ResponseHelper::rejected([
        'message' => 'FAILED_RECORD_EXISTED'
      ]);
    }

    DB::beginTransaction();

    try {
      User::create([
        'company_id' => $company_id,
        'group_id' => $group,
        'email' => $email,
        'username' => $username,
        'invite_token' => Str::random(32),
        'status' => 'pending'
      ]);

      DB::commit();

      return ResponseHelper::success();
    }
    catch (\Exception $exception) {
      DB::rollBack();

      return ResponseHelper::error([
        'error_message' => $exception->getMessage(),
      ]);
    }
  }

  public function deleteEmployee(Request $request, $id) {
    if (!AuthenticationHelper::haveAccessRight($request->requestFrom, 'managerial employee edit')) {
      return ResponseHelper::denied();
    }
    $company_id = $request->requestFrom->company_id;

    $record = User::where('company_id', $company_id)
      ->where('id', $id)
      ->first();

    if ($id == $request->requestFrom->id) {
      return ResponseHelper::rejected([
        'message' => 'FAILED_INVALID_ACTION',
      ]);
    }

    if (!$record) {
      return ResponseHelper::rejected([
        'message' => 'FAILED_RECORD_NOT_FOUND',
      ]);
    }

    switch ($record->status) {
      case 'inactive':
        return ResponseHelper::rejected([
          'message' => 'FAILED_INVALID_ACTION'
        ]);

      case 'active':
        $record->update([
          'status' => 'inactive'
        ]);
        return ResponseHelper::success();

      case 'pending':
        $record->delete();
        return ResponseHelper::success();

      default:
        return ResponseHelper::error([
          'message' => 'ERROR_WRONG_STATUS',
        ]);
    }
  }

  public function getAllAccessRight(Request $request) {
    if (!AuthenticationHelper::haveAccessRight($request->requestFrom, 'managerial group edit')) {
      return ResponseHelper::denied();
    }

    $userAccessRight = $request->requestFrom->group->access_right;
    $accessRight = AccessRight::all();

    // Filter if no owner access right
    if (!$userAccessRight->where('meta.name', 'managerial group managerial')) {
      $accessRight = $accessRight->whereNotIn('name', [
        'managerial setting',
        'managerial employee view',
        'managerial employee edit',
        'managerial group view',
        'managerial group edit',
        'managerial group managerial',
      ]);
    }

    return ResponseHelper::success([
      'data' => [
        'accessRight' => $accessRight->values(),
      ]
    ]);
  }

  public function getGroupDropdown(Request $request) {
    $company_id = $request->requestFrom->company_id;

    $groups = Group::where('company_id', $company_id)->get();

    if (!AuthenticationHelper::haveAccessRight($request->requestFrom, 'managerial group edit')) {
      $groups = $groups->filter(function ($group) {
        $access_right = $group->access_right;

        return !$access_right->where('meta.name', 'LIKE', 'managerial%');
      });
    }

    return ResponseHelper::success([
      'data' => $groups,
    ]);
  }

  public function getChangeMemberList(Request $request) {
    $company_id = $request->requestFrom->company_id;
    $group_id = $request->input('id');

    $employees = User::where('company_id', $company_id);

    if (!AuthenticationHelper::haveAccessRight($request->requestFrom, 'managerial group managerial')) {
      $manager_groups = Group::with(['access_right'])->where('company_id', $company_id)->get();
      $manager_groups = $manager_groups->filter(function ($group) {
        return $group->access_right->where('meta.name', 'LIKE', 'managerial%')->count() > 0;
      });

      $employees->whereNotIn('group_id', $manager_groups->pluck('id'));
    }

    $employees = $employees->where('group_id', '!=', $group_id)->get();

    return ResponseHelper::success([
      'data' => $employees,
    ]);
  }
}
