<?php

namespace App\Exceptions;

use Exception;
use App\Common\ResponseCode;

class UserException extends Exception
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
            'code' => ResponseCode::USER_ERROR,
            'message' => $this->getMessage(),
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
