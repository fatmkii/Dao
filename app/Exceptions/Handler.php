<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use App\Exceptions\CoinException;
use App\Common\ResponseCode;
use Illuminate\Database\QueryException;

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
            return response()->json([
                'code' => ResponseCode::DATABASE_FAILED,
                'message' => ResponseCode::$codeMap[ResponseCode::DATABASE_FAILED] . '，请重试。',
                'error_message' => $e->getMessage(),
            ]);
        });
    }
}
