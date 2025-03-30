<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // ← вот это строка
use App\User;

class TelegramController extends Controller
{
    public function showAuthForm()
    {
        return view('tg_auth');
    }

    public function handleCallback(Request $request)
    {
        $data = $request->all();
        Log::info('Telegram callback data', $data); // пример использования

        if (!$this->isTelegramHashValid($data)) {
            abort(403, 'Invalid Telegram auth data.');
        }

        $user = User::firstOrCreate(
            ['telegram_id' => $data['id']],
            [
                'name' => $data['first_name'] ?? 'Telegram User',
                'username' => $data['username'] ?? null,
                'avatar' => $data['photo_url'] ?? null,
            ]
        );

        Auth::login($user);

        return redirect('/');
    }

    private function isTelegramHashValid(array $data): bool
    {
        if (!isset($data['hash'])) {
            return false;
        }

        $checkHash = $data['hash'];
        unset($data['hash']);

        ksort($data);
        $dataCheckString = implode("\n", array_map(
            fn($k, $v) => "$k=$v",
            array_keys($data),
            $data
        ));

        $secretKey = hash('sha256', config('services.telegram.token'), true);

        $hash = hash_hmac('sha256', $dataCheckString, $secretKey);

        Log::info('Secret token: ' . config('services.telegram.token'));
        Log::info('Expected hash: ' . $hash);
        Log::info('Incoming hash: ' . $checkHash);


        return hash_equals($hash, $checkHash);
    }
}
