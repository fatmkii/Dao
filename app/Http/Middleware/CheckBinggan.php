<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Common\ResponseCode;
use App\Models\User;

class CheckBinggan
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $mode)
    {
        if ($request->has('binggan')) {
            $user = User::where('binggan', $request->binggan)->first();
        } else {
            $user = null;
        }
        switch ($mode) {
            case 'create': //发帖等
                {
                    //如果饼干不存在，返回错误
                    if (!$user) {
                        return response()->json(
                            [
                                'code' => ResponseCode::USER_NOT_FOUND,
                                'message' => ResponseCode::$codeMap[ResponseCode::USER_NOT_FOUND],
                            ],
                        );
                    }
                    //如果饼干被ban，返回错误
                    if ($user->is_banned) {
                        return response()->json(
                            [
                                'code' => ResponseCode::USER_BANNED,
                                'message' => ResponseCode::$codeMap[ResponseCode::USER_BANNED],
                                'data' => [
                                    'binggan' => $user->binggan,
                                ],
                            ],
                            401
                        );
                    }
                    //查询饼干是否在封禁期
                    if ($user->lockedTTL) {
                        $lockTTL_hours = intval($user->lockedTTL / 3600) + 1;
                        return response()->json(
                            [
                                'code' => ResponseCode::USER_LOCKED,
                                'message' => ResponseCode::$codeMap[ResponseCode::USER_LOCKED] . '，将于' . $lockTTL_hours . '小时后解封',
                            ],
                        );
                    }
                    break;
                }
            case 'show': //看帖等
                {
                    //如果饼干被ban，返回错误
                    if ($user->is_banned) {
                        return response()->json(
                            [
                                'code' => ResponseCode::USER_BANNED,
                                'message' => ResponseCode::$codeMap[ResponseCode::USER_BANNED],
                                'data' => [
                                    'binggan' => $user->binggan,
                                ],
                            ],
                            401
                        );
                    }
                    break;
                }
        }

        //把$user注入$request中，供后续controller使用。避免再次查询数据库。
        $request->request->add(['user' => $user]);

        return $next($request);
    }
}
