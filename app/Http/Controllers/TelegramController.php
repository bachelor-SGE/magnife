<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\User; // если модель находится в App\User, измените путь
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TelegramController extends Controller
{
    /**
     * Перенаправление на Telegram для авторизации.
     */
    public function redirectToProvider()
    {
        return Socialite::driver('telegram')->redirect();
    }

    /**
     * Обработка ответа от Telegram.
     */
    public function handleProviderCallback()
    {
        try {
            $telegramUser = Socialite::driver('telegram')->user();
        } catch (\Exception $e) {
            return redirect('/login')->withErrors(['msg' => 'Ошибка авторизации через Telegram']);
        }

        // Ищем пользователя по telegram_id или создаём нового
        $user = User::firstOrCreate(
            ['telegram_id' => $telegramUser->getId()],
            [
                'name' => $telegramUser->getName() ?? $telegramUser->getNickname() ?? 'Telegram User',
                // Если почта отсутствует, генерируем фиктивную почту (Telegram не возвращает email)
                'email' => $telegramUser->getEmail() ?? $telegramUser->getId().'@telegram-auth.com',
                // Сохраняем случайный пароль – он не будет использоваться для входа
                'password' => bcrypt(Str::random(16)),
            ]
        );

        // Авторизуем пользователя
        Auth::login($user, true);

        // Перенаправляем на главную страницу
        return redirect()->intended('/');
    }
}
