<?php

namespace App\Common;

class ResponseCode
{
    const SUCCESS = 200;

    const FILED_ERROR = 20422;

    const USER_UNAUTHORIZED = 21401;

    const USER_REGISTER_FAIL = 21403;

    const USER_NOT_FOUND = 21404;

    const USER_LOCKED = 21443;

    const USER_BANNED = 21499;

    const COIN_NOT_ENOUGH = 21412;

    const USER_PASSWORD_ERROR = 21001;

    const USER_NEW_CLOSED = 21406;

    const ADMIN_UNAUTHORIZED = 22401;

    const CANNOTLOGIN = 21401;

    const THREAD_NOT_FOUND = 23404;

    const THREAD_WAS_NISSINED = 23410;

    const THREAD_TOO_MANY = 23429;

    const THREAD_UNAUTHORIZED = 23401;

    const POST_NOT_FOUND = 24404;

    const POST_TOO_MANY = 24429;

    const POST_UNAUTHORIZED = 23401;
    
    const LOGIN_FAILED = 20001;

    const DATABASE_FAILED = 5001;

    const DEFAULT = 99999;

    public static $codeMap = [
        self::SUCCESS => '请求成功',
        self::DEFAULT => '服务器异常',
        self::FILED_ERROR => '表单验证未通过',
        self::USER_NOT_FOUND => '饼干无法找到',
        self::USER_LOCKED => '你的饼干已封禁',
        self::USER_BANNED => '你的饼干已碎',
        self::USER_UNAUTHORIZED => "饼干错误",
        self::USER_NEW_CLOSED => "饼干领取已暂停",
        self::COIN_NOT_ENOUGH => "奥利奥不足了",
        self::USER_PASSWORD_ERROR => '密码输入错误',
        self::ADMIN_UNAUTHORIZED => '管理员权限不足',
        self::CANNOTLOGIN => '用户无法登录',
        self::THREAD_NOT_FOUND => '主题贴不存在',
        self::THREAD_WAS_NISSINED => '主题已被日清',
        self::THREAD_TOO_MANY => '发新主题过于频繁',
        self::POST_NOT_FOUND => '未找到该帖子',
        self::POST_TOO_MANY => '回帖过于频繁',
        self::LOGIN_FAILED => '登录失败',
        self::DATABASE_FAILED => '数据库错误',
        self::USER_REGISTER_FAIL => '申请饼干失败',
    ];
}

// 00	Bad Request	客户端请求的语法错误，服务器无法理解
// 401	Unauthorized	请求要求用户的身份认证
// 402	Payment Required	保留，将来使用
// 403	Forbidden	服务器理解请求客户端的请求，但是拒绝执行此请求
// 404	Not Found	服务器无法根据客户端的请求找到资源（网页）。通过此代码，网站设计人员可设置"您所请求的资源无法找到"的个性页面
// 405	Method Not Allowed	客户端请求中的方法被禁止
// 406	Not Acceptable	服务器无法根据客户端请求的内容特性完成请求
// 407	Proxy Authentication Required	请求要求代理的身份认证，与401类似，但请求者应当使用代理进行授权
// 408	Request Time-out	服务器等待客户端发送的请求时间过长，超时
// 409	Conflict	服务器完成客户端的 PUT 请求时可能返回此代码，服务器处理请求时发生了冲突
// 410	Gone	客户端请求的资源已经不存在。410不同于404，如果资源以前有现在被永久删除了可使用410代码，网站设计人员可通过301代码指定资源的新位置
// 411	Length Required	服务器无法处理客户端发送的不带Content-Length的请求信息
// 412	Precondition Failed	客户端请求信息的先决条件错误
// 413	Request Entity Too Large	由于请求的实体过大，服务器无法处理，因此拒绝请求。为防止客户端的连续请求，服务器可能会关闭连接。如果只是服务器暂时无法处理，则会包含一个Retry-After的响应信息
// 414	Request-URI Too Large	请求的URI过长（URI通常为网址），服务器无法处理
// 415	Unsupported Media Type	服务器无法处理请求附带的媒体格式
// 416	Requested range not satisfiable	客户端请求的范围无效
// 417	Expectation Failed	服务器无法满足Expect的请求头信息
// 429, 请求过于频繁.