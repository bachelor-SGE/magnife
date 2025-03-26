<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Подтверждение почты отключено</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <div class="container" style="margin-top:50px;">
        <h1>Подтверждение почты отключено</h1>
        <p>Авторизация осуществляется только через Telegram.</p>
        <a href="{{ route('telegram.auth') }}" class="btn btn-primary">Войти через Telegram</a>
    </div>
</body>
</html>
