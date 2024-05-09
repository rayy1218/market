<?php

namespace App\Helper;

class AuthenticationHelper {
  static public function haveAccessRight($user, $accessRightsNeeded): bool
  {
    $userAccessRight = $user->group->access_right;

    return $userAccessRight->where('meta.name', $accessRightsNeeded)->first()->is_enabled;
  }
}
