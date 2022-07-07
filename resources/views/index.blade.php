<!DOCTYPE html>
<html lang="zh_CN">

<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <link rel="shortcut icon" href="/favicon2.ico">
    <link rel="stylesheet" type="text/css" href="{{ mix('css/app.css') }}">
    <title>小火锅</title>
</head>

<body>
    <div id='app' class='container'>
        <app></app>
        <navigation></navigation>
        <router-view></router-view>
        <footer_navi></footer_navi>
    </div>

</body>

<script src="//{{ Request::getHost() }}:6001/socket.io/socket.io.js"></script>
<script src="{{ mix('json/json.js') }}"></script>
<script src="{{ mix('js/manifest.js') }}"></script>
<script src="{{ mix('js/vendor.js') }}"></script>
<script src="{{ mix('js/app.js') }}"></script>

</html>
