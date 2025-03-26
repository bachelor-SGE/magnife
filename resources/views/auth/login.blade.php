<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Авторизация через Telegram</title>
    <!-- Подключите стили по необходимости -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <div class="container" style="margin-top:50px;">
        <h1>Авторизация через Telegram</h1>
        <p>Нажмите кнопку ниже, чтобы войти через Telegram.</p>
        <a href="{{ route('telegram.auth') }}" class="btn btn-primary" style="font-size:18px;padding:10px 20px;">Войти через Telegram</a>
    </div>
</body>
</html>
