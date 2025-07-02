<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\SystemDep;
use App\DepPromo;
use App\User;
use App\ActivePromo;
use App\Setting;
use App\Status;
use App\Payment;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Http;

class PaymentController extends Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->redis = Redis::connection();
		$this->clientId = config('services.crocopay.client_id');
		$this->clientSecret = config('services.crocopay.client_secret');
	}

	public function go(Request $r){
		$sum = $r->sum;
		$system = $r->system;
		$promo = $r->promo;

		if(!is_numeric($sum)){
			return response(['success' => false, 'mess' => 'Введите корректно сумму пополнения']);
		}

		if(\Auth::guest()){return response(['success' => false, 'mess' => 'Авторизуйтесь' ]);}

		$user = \Auth::user();
		if($user->type_balance == 1){
			return response(['success' => false, 'mess' => 'Переключитесь на реальный баланс']);
		}
		$countSystemDep = SystemDep::where('id', $system)->count();
		if($countSystemDep == 0){
			return response(['success' => false, 'mess' => 'Ошибка']);
		}

		$systemDep = SystemDep::where('id', $system)->first();
		//$minDep = $systemDep->min_sum;
		$psDep = $systemDep->ps;
		$img = $systemDep->img;

		$minDep = 1000;

		if($sum < $minDep){
			return response(['success' => false, 'mess' => "Минимальная сумма пополнения {$minDep}р."]);
		}


		$percent = 0;

		if($promo != ''){
			$deppromo_count = DepPromo::where('name', $promo)->count();
			if($deppromo_count == 0){
				return response(['success' => false, 'mess' => 'Промокод не найден или закончился' ]);
			}

			$promo_act_count = ActivePromo::where('promo', $promo)->where('user_id', $user->id)->count();
			if ($promo_act_count > 0)  {
				return response(['success' => false, 'mess' =>  "Вы уже использовали этот код"]);
			}
			$deppromo = DepPromo::where('name', $promo)->first();
			$start = strtotime($deppromo->start);
			$end = strtotime($deppromo->end);
			$now_time = time();
			if($deppromo->actived >= $deppromo->active){
				return response(['success' => false, 'mess' => 'Промокод не найден или закончился' ]);
			}
			if($now_time < $start){
				return response(['success' => false, 'mess' => 'Промокод будет доступен '.date('d.m в H:i', $start) ]);
			}
			if($now_time > $end){
				return response(['success' => false, 'mess' => 'Промокод не найден или закончился' ]);
			}

			$percent = $deppromo->percent;
			$deppromo->actived += 1;
			$deppromo->save();

			ActivePromo::create([
				'promo' => $promo,
				'user_id' => $user->id,
				'type_promo' => 1,
				'promo_id' => $deppromo->id,
			]);
		}

		$unique_id = time() * $user->id;
		$modal = 0;
		$transfer = 'false';

		// Генерация ссылки оплаты через Crocopay
		$callbackUrl = "https://magnife.ru/deposit/callback?user_id=" . $user->id;
		$successUrl = "https://magnife.ru/";
		$cancelUrl = "https://magnife.ru/";

		$payload = [
			'client_id'     => $this->clientId,
			'client_secret' => $this->clientSecret,
			'amount'        => $sum,
			'currency'      => 'RUB',
			'successUrl'    => $successUrl,
			'cancelUrl'     => $cancelUrl,
			'callbackUrl'   => $callbackUrl,
		];

		$ch = curl_init('https://crocopay.tech/api/v2/initiate-payment');
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		$response = curl_exec($ch);
		curl_close($ch);

		$responseData = json_decode($response, true);

		if (!isset($responseData['redirect_url'])) {
			return response(['success' => false, 'mess' => 'Ошибка создания ссылки оплаты: ' . ($responseData['message'] ?? 'Неизвестно')]);
		}

		$link = $responseData['redirect_url'];

		Payment::create([
			'user_id' => $user->id,
			'login' => $user->name,
			'avatar' => $user->avatar,
			'sum' => $sum,
			'data' => date('d.m.Y H:i'),
			'transaction' => $unique_id,
			'beforepay' => $user->balance,
			'percent' => $percent,
			'img_system' => $img
		]);

		return response([
			'success' => true,
			'link' => $link,
			'modal' => $modal,
			'transfer' => $transfer,
			'img' => $img
		]);
	}

	public function callback(Request $r)
	{
		// Получаем transaction_id и user_id из запроса (CrocoPay может слать по-разному, подстрой под реальные данные)
		$transaction_id = $r->input('transaction_id') ?? $r->input('transaction') ?? $r->input('id');
		$user_id = $r->input('user_id');

		if (!$transaction_id || !$user_id) {
			return response(['success' => false, 'mess' => 'Нет данных для проверки'], 400);
		}

		// Получаем access_token для CrocoPay
		$client_id = config('services.crocopay.client_id');
		$client_secret = config('services.crocopay.client_secret');
		$token_response = Http::asForm()->post('https://crocopay.tech/api/v2/access-token', [
			'client_id' => $client_id,
			'client_secret' => $client_secret,
		]);
		$access_token = $token_response['access_token'] ?? null;
		if (!$access_token) {
			return response(['success' => false, 'mess' => 'Ошибка получения токена'], 500);
		}

		// Проверяем статус платежа
		$verify_response = Http::withHeaders([
			'Authorization' => 'Bearer ' . $access_token,
		])->post('https://crocopay.tech/api/v2/payment-verify', [
			'transaction_id' => $transaction_id,
		]);

		if (!isset($verify_response['status']) || $verify_response['status'] !== 'Success') {
			return response(['success' => false, 'mess' => 'Платеж не подтвержден'], 400);
		}

		// Находим платеж в базе
		$payment = \App\Payment::where('transaction', $transaction_id)->first();
		if (!$payment) {
			return response(['success' => false, 'mess' => 'Платеж не найден'], 404);
		}

		// Проверяем, не зачисляли ли уже
		if (isset($payment->status) && $payment->status === 'success') {
			return response(['success' => true, 'mess' => 'Баланс уже пополнен']);
		}

		// Зачисляем баланс
		$user = \App\User::find($user_id);
		if (!$user) {
			return response(['success' => false, 'mess' => 'Пользователь не найден'], 404);
		}

		$user->balance += $payment->sum;
		$user->save();

		// Обновляем статус платежа (если есть поле status)
		if (isset($payment->status)) {
			$payment->status = 'success';
			$payment->save();
		}

		// Логируем в историю баланса
		if (!\Cache::has('user.'.$user->id.'.historyBalance')) {
			\Cache::put('user.'.$user->id.'.historyBalance', '[]');
		}
		$hist_balance = [
			'user_id' => $user->id,
			'type' => 'Пополнение через CrocoPay',
			'balance_before' => $user->balance - $payment->sum,
			'balance_after' => $user->balance,
			'date' => date('d.m.Y H:i')
		];
		$cashe_hist_user = \Cache::get('user.'.$user->id.'.historyBalance');
		$cashe_hist_user = json_decode($cashe_hist_user);
		$cashe_hist_user[] = $hist_balance;
		$cashe_hist_user = json_encode($cashe_hist_user);
		\Cache::put('user.'.$user->id.'.historyBalance', $cashe_hist_user);

		return response(['success' => true, 'mess' => 'Баланс пополнен']);
	}

}