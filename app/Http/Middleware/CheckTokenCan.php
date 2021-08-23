<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Common\ResponseCode;

class CheckTokenCan
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $role)
    {
        $user = $request->user();
        if (!$user->tokenCan($role)) {
            return response()->json([
                'code' => ResponseCode::ADMIN_UNAUTHORIZED,
                'message' => ResponseCode::$codeMap[ResponseCode::ADMIN_UNAUTHORIZED],
            ]);
        }
        return $next($request);
    }
}
