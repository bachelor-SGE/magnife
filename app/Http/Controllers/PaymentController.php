<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Models\Payment;
use App\Models\User;

class PaymentController extends Controller
{
    // Для интеграции CrocoPay Express нужны два ключа:
    // их можно задать в файле .env и получить через config('services.crocopay.*')
    private $clientId;
    private $clientSecret;

    public function __construct()
    {
        // Эти значения можно предварительно задать в .env и services.php, например:
        // CROCOPAY_CLIENT_ID=ваш_публичный_ключ
        // CROCOPAY_CLIENT_SECRET=ваш_секретный_ключ
        $this->clientId = config('services.crocopay.client_id'); 
        $this->clientSecret = config('services.crocopay.client_secret');
    }

    /**
     * Инициирует платеж через CrocoPay Express.
     *
     * Метод получает сумму из формы (в рублях) и выполняет следующие шаги:
     * 1. Получает access_token от CrocoPay.
     * 2. Отправляет запрос на создание платежного ордера (initiate-payment) с указанием:
     *    - суммы, валюты (RUB)
     *    - URL для успешного редиректа (successUrl), отмены (cancelUrl)
     *    - URL для callback-уведомления (callbackUrl) с передачей user_id.
     * 3. Сохраняет запись платежа в базе данных и перенаправляет пользователя на redirect_url.
     *
     * Старый функционал для других платежных систем (FreeKassa, Qiwi, и т.п.) удалён.
     */
    public function go(Request $request)
    {
        $user = Auth::user();
        $amount = floatval($request->input('amount'));

        // Валидация: сумма должна быть больше нуля
        if ($amount <= 0) {
            return redirect()->back()->with('error', 'Сумма должна быть больше 0');
        }

        // Шаг 1: Получаем access_token от CrocoPay
        $tokenResponse = Http::asForm()->post('https://crocopay.tech/api/v2/access-token', [
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
        ]);

        $tokenData = $tokenResponse->json();

        if (!isset($tokenData['access_token'])) {
            return redirect()->back()->with('error', 'Не удалось получить токен доступа от CrocoPay');
        }

        $accessToken = $tokenData['access_token'];

        // Шаг 2: Инициируем платеж через API CrocoPay
        $initResponse = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
        ])->asForm()->post('https://crocopay.tech/api/v2/initiate-payment', [
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'amount'        => number_format($amount, 2, '.', ''),
            'currency'      => 'RUB',
            'successUrl'    => route('deposit.success'),  // URL успешной оплаты
            'cancelUrl'     => route('deposit.cancel'),   // URL отмены оплаты
            'callbackUrl'   => route('deposit.callback', ['user_id' => $user->id]), // URL для callback с указанием user_id
        ]);

        $initData = $initResponse->json();

        if (isset($initData['status']) && $initData['status'] === 'success' && !empty($initData['redirect_url'])) {
            // Сохраняем запись платежа с первоначальным статусом "pending"
            Payment::create([
                'user_id'     => $user->id,
                'amount'      => $amount,
                'status'      => 'pending',
                'external_id' => $initData['transaction_id'] ?? null,
            ]);

            // Шаг 3: Перенаправляем пользователя на страницу оплаты CrocoPay
            return redirect()->away($initData['redirect_url']);
        } else {
            $message = $initData['message'] ?? 'Ошибка инициации платежа';
            return redirect()->back()->with('error', 'Оплата не создана: ' . $message);
        }
    }

    /**
     * Обработка callback-уведомления от CrocoPay.
     *
     * Здесь CrocoPay отправляет уведомление (обычно в формате JSON) о результате платежа.
     * Если статус платежа равен "Success", производится:
     * - увеличение баланса пользователя,
     * - обновление статуса записи платежа на "success".
     *
     * Обратите внимание, что метод ожидает данные в виде JSON.
     */
    public function callback(Request $request)
    {
        // Принимаем JSON-данные
        $data = $request->json()->all();

        // Если статус не равен "Success", уведомление игнорируется
        if (!isset($data['status']) || $data['status'] !== 'Success') {
            return response('Ignored', 200);
        }

        // Определяем идентификатор пользователя (передаётся через callbackUrl)
        $userId = $data['user_id'] ?? $request->query('user_id');
        $amount = isset($data['total']) 
                    ? floatval($data['total']) 
                    : (isset($data['amount']) ? floatval($data['amount']) : 0);

        if (!$userId || $amount <= 0) {
            return response('Bad data', 400);
        }

        // Зачисляем сумму на баланс пользователя
        $user = User::find($userId);
        if ($user) {
            $user->balance += $amount;
            $user->save();
        }

        // Обновляем запись платежа: меняем статус на "success" и сохраняем transaction_id от CrocoPay
        Payment::where('user_id', $userId)
            ->where('status', 'pending')
            ->update([
                'status'      => 'success',
                'external_id' => $data['transaction_id'] ?? null,
            ]);

        return response('OK', 200);
    }

    /**
     * Обработка возврата пользователя после успешной оплаты.
     *
     * Здесь можно вывести сообщение об успешном платеже и перенаправить пользователя
     * на страницу профиля или другую целевую страницу.
     */
    public function result(Request $request)
    {
        return redirect('/profile')->with('success', 'Оплата выполнена успешно!');
    }

    /**
     * Обработка возврата пользователя при отмене оплаты.
     *
     * Здесь выводится сообщение об отмене платежа.
     */
    public function cancel(Request $request)
    {
        return redirect('/profile')->with('error', 'Оплата была отменена.');
    }

    // --------------------------------------------------------------------
    // Ниже находились старые методы для других платежных систем (например, resultFK, resultPiastrix, resultQpay и пр.)
    // Они удалены, чтобы оставить только CrocoPay Express.
    // --------------------------------------------------------------------
}
