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

        if ($user) {
            if ($user->admin == 0) {
                $token = $user->createToken($binggan, ['normal'])->plainTextToken;
            } else {
                $token = $user->createToken($binggan, ['admin'])->plainTextToken;
            }
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
        }
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

        // $token_header = $request->header('Authorization');
        // if (isset($token_header)) {
        //     $token_header = str_replace('bearer', '', $token_header);
        //     [$token_id, $user_token] = explode('|', $token_header, 2);
        //     $token_data = DB::table('personal_access_tokens')->where('token', hash('sha256', $user_token))->first();
        //     $user_id = $token_data->tokenable_id;
        // }

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
}
