<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Регистрация отключена</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <div class="container" style="margin-top:50px;">
        <h1>Регистрация отключена</h1>
        <p>Регистрация и авторизация осуществляется только через Telegram.</p>
        <a href="{{ route('telegram.auth') }}" class="btn btn-primary" style="font-size:18px;padding:10px 20px;">Войти через Telegram</a>
    </div>
</body>
</html>
