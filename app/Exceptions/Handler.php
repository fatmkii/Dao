<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use App\Exceptions\CoinException;
use App\Common\ResponseCode;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        //已经由CoinException自定义异常类那边渲染了
        // $this->renderable(function (CoinException $e, $request) {
        //     return response()->json([
        //         'code' => ResponseCode::COIN_NOT_ENOUGH,
        //         'message' => ResponseCode::$codeMap[ResponseCode::COIN_NOT_ENOUGH] . '，请确认',
        //     ]);
        // });

        $this->renderable(function (QueryException $e, $request) {
            $error_timestamp = Carbon::now()->toDateTimeString();
            return response()->json([
                'code' => ResponseCode::DATABASE_FAILED,
                'message' => sprintf('%s，请重试。时间戳:%s', ResponseCode::$codeMap[ResponseCode::DATABASE_FAILED], $error_timestamp),
                'error_message' => $e->getMessage(),
            ]);
        });


        $this->renderable(function (ValidationException $e, $request) {
            return response()->json([
                'code' => 422,
                'message' => array_values($e->errors())[0],
            ], 422);
        });

        $this->renderable(function (Exception $e, $request) {
            //其他各种代码错误统一返回。一定要放在最后。
            if (method_exists($e, 'getStatusCode')) {
                $status_code = $e->getStatusCode();
            } else {
                $status_code = 500;
            }

            $error_timestamp = Carbon::now()->toDateTimeString();
            return response()->json([
                'code' =>  $status_code,
                'message' => sprintf('嗷！后端出错了，请重试或者联络管理员。错误代码：%s; 时间戳:%s; 错误信息:%s',   $status_code, $error_timestamp, $e->getMessage()),
            ],  $status_code);
        });
    }
}
