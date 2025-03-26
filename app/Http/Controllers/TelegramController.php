<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class TelegramController extends Controller
{
    public function auth(Request $request)
    {
        if ($request->has('id') && $request->has('hash')) {
            if (!$this->isValidTelegramCallback($request->all())) {
                return redirect('/')->withErrors(['telegram' => 'Ошибка верификации Telegram.']);
            }

            $user = User::firstOrCreate(
                ['telegram_id' => $request->get('id')],
                [
                    'name' => $request->get('first_name') . ' ' . $request->get('last_name'),
                    'username' => $request->get('username'),
                    'avatar' => $request->get('photo_url'),
                ]
            );

            Auth::login($user);

            return redirect('/');
        }

        return view('tg_auth');
    }

    private function isValidTelegramCallback(array $data): bool
    {
        $check_hash = $data['hash'];
        unset($data['hash']);
        ksort($data);

        $data_check_string = '';
        foreach ($data as $key => $value) {
            $data_check_string .= "$key=$value\n";
        }
        $data_check_string = trim($data_check_string);

        $secret_key = hash('sha256', env('TELEGRAM_BOT_TOKEN'), true);
        $hash = hash_hmac('sha256', $data_check_string, $secret_key);

        return hash_equals($hash, $check_hash);
    }
}
