<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Common\ResponseCode;
use App\Models\User;
use App\Jobs\ProcessUserActive;


class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'binggan' => 'required|string',
        ]);

        $binggan  = $request->get('binggan');
        $user = User::where('binggan', $binggan)->first();
        if (!$user) {
            return response()->json([
                'code' => ResponseCode::USER_NOT_FOUND,
                'message' => ResponseCode::$codeMap[ResponseCode::USER_NOT_FOUND],
            ]);
        }

        if ($user->is_banned) {
            return response()->json(
                [
                    'code' => ResponseCode::USER_BANNED,
                    'message' => ResponseCode::$codeMap[ResponseCode::USER_BANNED],
                    'data' => [
                        'binggan' => $binggan,
                    ],
                ],
            );
        }

        //为管理员颁发token
        // switch ($user->admin) {
        //     case 0:
        //         $token = $user->createToken($binggan, ['normal'])->plainTextToken;
        //         break;
        //     case 1:
        //         $token = $user->createToken($binggan, ['admin'])->plainTextToken;
        //         break;
        //     case 99:
        //         $token = $user->createToken($binggan, ['super_admin', 'admin'])->plainTextToken;
        //         break;
        //     default:
        //         $token = $user->createToken($binggan, ['normal'])->plainTextToken;
        // }
        $token = $user->createToken($binggan, ['normal'])->plainTextToken;

        return response()->json(
            [
                'code' => ResponseCode::SUCCESS,
                'message' => '登陆成功',
                'data' => [
                    'binggan' => $binggan,
                    'token' => $token,
                ],
            ],
            200
        );

        ProcessUserActive::dispatch(
            [
                'binggan' => $user->binggan,
                'user_id' => $user->id,
                'active' => '用户导入了饼干',
                'content' => $request->ip(),
            ]
        );
    }

    public function logout(Request $request)
    {
        $request->validate([
            'binggan' => 'required|string',
        ]);
        $binggan  = $request->get('binggan');


        $user = request()->user();

        if (isset($user)) {
            if ($binggan !== $user->binggan) {
                return response()->json(
                    [
                        'code' => ResponseCode::USER_NOT_FOUND,
                        'message' => '找不到此饼干',
                        'data' => [
                            'binggan' => $binggan,
                        ],
                    ],
                    401,
                );
            } else {
                $request->user()->currentAccessToken()->delete();
                return response()->json(
                    [
                        'code' => ResponseCode::SUCCESS,
                        'message' => '已登出此饼干',
                        'data' => [
                            'binggan' => $binggan,
                        ],
                    ],
                    200,
                );
            }
        }
    }

    public function admin_login(Request $request)
    {
        $request->validate([
            'binggan' => 'required|string',
            'password' => 'required|string',
        ]);

        $binggan  = $request->get('binggan');
        $user = User::where('binggan', $binggan)->first();
        if (!$user) {
            return response()->json([
                'code' => ResponseCode::USER_NOT_FOUND,
                'message' => ResponseCode::$codeMap[ResponseCode::USER_NOT_FOUND],
            ]);
        }

        //确认是管理员才能用用admin_login登陆
        if ($user->admin == 0) {
            return response()->json([
                'code' => ResponseCode::LOGIN_FAILED,
                'message' => ResponseCode::$codeMap[ResponseCode::LOGIN_FAILED],
            ]);
        }

        //如果管理员密码为空，则以本次输入作为密码
        if ($user->password == null) {
            $user->password = hash('sha256', $request->password . config('app.password_salt'));
            $user->save();
        } else {
            //确认管理员密码
            if ($user->password != hash('sha256', $request->password . config('app.password_salt'))) {
                return response()->json([
                    'code' => ResponseCode::USER_PASSWORD_ERROR,
                    'message' => ResponseCode::$codeMap[ResponseCode::USER_PASSWORD_ERROR],
                ]);
            }
        }
        if ($user->is_banned) {
            return response()->json(
                [
                    'code' => ResponseCode::USER_BANNED,
                    'message' => ResponseCode::$codeMap[ResponseCode::USER_BANNED],
                    'data' => [
                        'binggan' => $binggan,
                    ],
                ],
            );
        }

        //为管理员颁发token
        switch ($user->admin) {
            case 0:
                $token = $user->createToken($binggan, ['normal'])->plainTextToken;
                break;
            case 1:
                $token = $user->createToken($binggan, ['admin'])->plainTextToken;
                break;
            case 99:
                $token = $user->createToken($binggan, ['super_admin', 'admin'])->plainTextToken;
                break;
            default:
                $token = $user->createToken($binggan, ['normal'])->plainTextToken;
        }

        return response()->json(
            [
                'code' => ResponseCode::SUCCESS,
                'message' => ' 管理员登陆成功！',
                'data' => [
                    'binggan' => $binggan,
                    'token' => $token,
                ],
            ],
            200
        );
        ProcessUserActive::dispatch(
            [
                'binggan' => $user->binggan,
                'user_id' => $user->id,
                'active' => '管理员登陆了饼干',
                'content' => $request->ip(),
            ]
        );
    }
}
