<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuthenticationMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $request->requestFrom = User::where('token', $request->bearerToken() ?? '')->first();
        return $next($request);
    }
}
