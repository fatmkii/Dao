<?php

namespace App\Common;

class ResponseCode
{
    const SUCCESS = 200;
    const FILED_ERROR = 20422;

    const USER_UNAUTHORIZED = 21401;
    const USER_CANNOT = 214011;
    const USER_REGISTER_FAIL = 21403;
    const USER_NOT_FOUND = 21404;
    const USER_LOCKED = 21443;
    const USER_ERROR = 21422;
    const USER_BANNED = 21499;
    const COIN_NOT_ENOUGH = 21412;
    const USER_PASSWORD_ERROR = 21001;
    const USER_NEW_CLOSED = 21406;
    const CANNOTLOGIN = 21401;

    const ADMIN_UNAUTHORIZED = 29401;

    const FORUM_NOT_FOUND = 22404;
    const FORUM_UNAUTHORIZED = 22401;

    const THREAD_NOT_FOUND = 23404;
    const THREAD_WAS_NISSINED = 23410;
    const THREAD_TOO_MANY = 23429;
    const THREAD_UNAUTHORIZED = 23401;
    const THREAD_IS_PRIVATE = 234011;
    const SEARCH_TOO_MANY = 234291;

    const POST_NOT_FOUND = 24404;
    const POST_TOO_MANY = 24429;
    const POST_TOO_MANY_MAYBE_ROBOT = 244291;
    const POST_UNAUTHORIZED = 24401;
    const POST_ROBOT = 24403;

    const VOTE_NOT_FOUND = 25404;
    const VOTE_OPTION_NOT_FOUND = 254041;
    const VOTE_WAS_OUTDATE = 25410;
    const VOTE_HAVE_BEEN_VOTE = 25403;

    const GAMBLE_NOT_FOUND = 26404;
    const GAMBLE_OPTION_NOT_FOUND = 264041;
    const GAMBLE_WAS_OUTDATE = 26410;
    const GAMBLE_HAVE_BEEN_BET = 26403;
    const GAMBLE_HAS_BEEN_CLOSED = 264101;

    const BATTLE_NOT_FOUND = 27404;
    const BATTLE_WAS_OUTDATE = 27410;
    const BATTLE_HAVE_BEEN_BET = 27403;
    const BATTLE_HAS_BEEN_ROLL_I = 274031;
    const BATTLE_HAS_BEEN_ROLL_C = 274032;
    const BATTLE_HAS_BEEN_CLOSED = 274101;
    const BATTLE_CANNOT_CREATED = 274112;

    const CAPTCHA_NOT_FOUND = 28404;
    const CAPTCHA_WRONG = 24401;

    const ANNOUCEMENT_NOT_FOUND = 28404;

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
        self::USER_CANNOT => "无法这样做",
        self::COIN_NOT_ENOUGH => "奥利奥不足了",
        self::USER_PASSWORD_ERROR => '密码输入错误',
        self::ADMIN_UNAUTHORIZED => '管理员权限不足',
        self::CANNOTLOGIN => '用户无法登录',
        self::FORUM_NOT_FOUND => '此小岛不存在',
        self::FORUM_UNAUTHORIZED => '无法访问该小岛',
        self::THREAD_NOT_FOUND => '主题贴不存在',
        self::THREAD_WAS_NISSINED => '主题已被日清',
        self::THREAD_TOO_MANY => '发新主题过于频繁',
        self::THREAD_IS_PRIVATE => '本贴是私密主题，只有发帖者可以查看喔',
        self::SEARCH_TOO_MANY => '为保护服务器，设定了全局10秒只能搜索20次。',
        self::POST_NOT_FOUND => '未找到该帖子',
        self::POST_TOO_MANY => '回帖过于频繁',
        self::POST_ROBOT => '操作失败，请重试',
        self::POST_TOO_MANY_MAYBE_ROBOT => '你可能刷太多了，休息一下吧。',
        self::LOGIN_FAILED => '登录失败',
        self::DATABASE_FAILED => '数据库错误',
        self::USER_REGISTER_FAIL => '申请饼干失败',
        self::VOTE_NOT_FOUND => '投票不存在',
        self::VOTE_OPTION_NOT_FOUND => '投票选项不存在',
        self::VOTE_WAS_OUTDATE => '投票已过期',
        self::VOTE_HAVE_BEEN_VOTE => '你已经投过票了',
        self::GAMBLE_NOT_FOUND => '菠菜不存在',
        self::GAMBLE_OPTION_NOT_FOUND => '菠菜选项不存在',
        self::GAMBLE_WAS_OUTDATE => '菠菜已过期',
        self::GAMBLE_HAS_BEEN_CLOSED => '菠菜已关闭',
        self::GAMBLE_HAVE_BEEN_BET => '你已经下过注了',
        self::BATTLE_NOT_FOUND => '大乱斗不存在',
        self::BATTLE_WAS_OUTDATE => '大乱斗已过期',
        self::BATTLE_HAVE_BEEN_BET => '大乱斗已正常结束了',
        self::BATTLE_HAS_BEEN_CLOSED => '大乱斗已关闭',
        self::BATTLE_HAS_BEEN_ROLL_C => '已经有挑战者参加了',
        self::BATTLE_HAS_BEEN_ROLL_I => '大乱斗还在等待挑战者',
        self::BATTLE_CANNOT_CREATED => '本主题不能发起大乱斗',
        self::CAPTCHA_NOT_FOUND => '验证码不存在或已过期',
        self::CAPTCHA_WRONG => '验证码错误',
        self::ANNOUCEMENT_NOT_FOUND => '公告不存在',
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