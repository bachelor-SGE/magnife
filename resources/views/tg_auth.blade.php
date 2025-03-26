<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Telegram Login</title>
</head>
<body>
@guest
<script async src="https://telegram.org/js/telegram-widget.js?7"
        data-telegram-login="{{ env('TELEGRAM_BOT_USERNAME') }}"
        data-size="large"
        data-userpic="false"
        data-auth-url="{{ route('tg.auth') }}"
        data-request-access="write">
</script>
@endguest

</body>
</html>



