<?php

namespace App\Exceptions;

use Exception;
use App\Common\ResponseCode;

class CoinException extends Exception
{
    /**
     * 渲染异常为 HTTP 响应
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function render($request)
    {
        return response()->json([
            'code' => ResponseCode::COIN_NOT_ENOUGH,
            'message' => ResponseCode::$codeMap[ResponseCode::COIN_NOT_ENOUGH] . '，请确认',
        ]);
    }

    /**
     * 报告异常
     *
     * @return void
     */
    public function report()
    {
        //什么都不做，不用写入log
    }
}
