<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Telegram Login</title>
</head>
<body>
    <script async src="https://telegram.org/js/telegram-widget.js?7"
        data-telegram-login="magnife_bot"
        data-size="large"
        data-auth-url="{{ route('tg_auth') }}"
        data-request-access="write"
        data-userpic="true">
    </script>
</body>
</html>
