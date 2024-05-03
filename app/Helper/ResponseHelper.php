<?php

namespace App\Helper;

use Illuminate\Http\JsonResponse;

class ResponseHelper {
  public static function success($data = [], $statusCode = 200): JsonResponse
  {
    $data['status'] = 0;

    return response()->json($data)->setStatusCode($statusCode);
  }

  public static function rejected($data = [], $statusCode = 400): JsonResponse
  {
    $data['status'] = 1;

    return response()->json($data)->setStatusCode($statusCode);
  }

  public static function error($data = [], $statusCode = 500): JsonResponse
  {
    $data['status'] = 2;

    return response()->json($data)->setStatusCode($statusCode);
  }

  public static function denied($data = [], $statusCode = 403): JsonResponse
  {
    $data['status'] = 2;

    return response()->json($data)->setStatusCode($statusCode);
  }
}

