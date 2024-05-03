<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Models\AccessRight;
use App\Models\Address;
use App\Models\Company;
use App\Models\Group;
use App\Models\GroupAccessRight;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthenticationController extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function login(Request $request) {
        $user = User::where('email', $request->input('email'))->first();

        if (!isset($user) || !Hash::check($request->input('password'), $user->password)) {
            return ResponseHelper::rejected([
              'message' => 'FAILED_PASSWORD_EMAIL',
            ]);
        }

        $token = Str::random(32);

        $user->update([
            'token' => $token,
            'token_expired_at' => Carbon::now()->addHours(720),
        ]);

        return ResponseHelper::success([
          'token' => $token,
        ]);
    }

    public function register(Request $request) {
        $user = User::where('email', $request->input('email'))->first();

        if (isset($user)) {
          return ResponseHelper::rejected([
            'message' => 'FAILED_EMAIL',
          ]);
        }

        DB::beginTransaction();

        try {
          $address = Address::create([
            'line1' => $request->input('line1') || '',
            'line2' => $request->input('line2') || '',
            'city' => $request->input('city') || '',
            'state' => $request->input('state') || '',
            'country' => $request->input('country') || '',
            'zipcode' => $request->input('zipcode') || '',
          ]);

          $company = Company::create([
            'address_id' => $address->id,
            'company_name' => $request->input('company_name'),
            'status' => Company::STATUS_ACTIVE
          ]);

          $group = Group::create([
            'company_id' => $company->id,
            'name' => 'Owner',
          ]);

          $access_rights = AccessRight::all();

          foreach ($access_rights as $access_right) {
            GroupAccessRight::create([
              'group_id' => $group->id,
              'access_right_id' => $access_right->id,
              'is_enabled' => 1,
            ]);
          }

          $user = User::create([
            'company_id' => $company->id,
            'group_id' => $group->id,
            'token' => Str::random(32),
            'token_expired_at' => Carbon::now()->addHours(720),
            'email' => $request->input('email'),
            'username' => $request->input('username'),
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),
            'password' => $request->input('password'),
            'status' => 'active',
          ]);

          DB::commit();

          return ResponseHelper::success([
            'token' => $user->token,
          ]);
        }
        catch (\Exception $exception) {
          DB::rollBack();

          return ResponseHelper::error([
            'error_message' => $exception->getMessage(),
          ]);
        }
    }

    public function getAccessRight(Request $request) {
      return ResponseHelper::success([
        'data' => collect($request->requestFrom->group->access_right)->where('is_enabled', 1)->pluck('meta'),
      ]);
    }
}
