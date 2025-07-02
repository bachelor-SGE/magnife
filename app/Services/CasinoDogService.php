<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\User;

class CasinoDogService
{
    protected $baseUrl;
    protected $apiKey;
    protected $secretKey;

    public function __construct()
    {
        // Настройки для casino-slots-aggregation-app
        $this->baseUrl = env('CASINODOG_BASE_URL', 'http://localhost:8000');
        $this->apiKey = env('CASINODOG_API_KEY', 'your-api-key');
        $this->secretKey = env('CASINODOG_SECRET_KEY', 'your-secret-key');
    }

    /**
     * Получить список игр от провайдера
     */
    public function getGames($provider = null, $page = 1, $limit = 30)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json'
            ])->get($this->baseUrl . '/api/v1/games', [
                'provider' => $provider,
                'page' => $page,
                'limit' => $limit
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('CasinoDog API Error: ' . $response->body());
            return ['games' => []];
        } catch (\Exception $e) {
            Log::error('CasinoDog Service Error: ' . $e->getMessage());
            return ['games' => []];
        }
    }

    /**
     * Получить URL для запуска игры
     */
    public function getGameUrl($gameId, $userId, $demo = false)
    {
        try {
            $user = User::find($userId);
            if (!$user) {
                throw new \Exception('User not found');
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json'
            ])->post($this->baseUrl . '/api/v1/games/launch', [
                'game_id' => $gameId,
                'user_id' => $user->id,
                'session_token' => $user->api_token,
                'demo' => $demo,
                'currency' => 'RUB',
                'language' => 'ru'
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('CasinoDog Launch Error: ' . $response->body());
            return ['error' => 'Failed to launch game'];
        } catch (\Exception $e) {
            Log::error('CasinoDog Launch Error: ' . $e->getMessage());
            return ['error' => 'Service unavailable'];
        }
    }

    /**
     * Обработать колбэк от casino-slots-aggregation-app
     */
    public function handleCallback($method, $data)
    {
        try {
            // Проверяем подпись для безопасности
            if (!$this->verifySignature($data)) {
                return response()->json(['error' => 'Invalid signature'], 400);
            }

            switch ($method) {
                case 'check.session':
                    return $this->checkSession($data);
                case 'check.balance':
                    return $this->checkBalance($data);
                case 'withdraw.bet':
                    return $this->withdrawBet($data);
                case 'deposit.win':
                    return $this->depositWin($data);
                default:
                    return response()->json(['error' => 'Unknown method'], 400);
            }
        } catch (\Exception $e) {
            Log::error('CasinoDog Callback Error: ' . $e->getMessage());
            return response()->json(['error' => 'Internal error'], 500);
        }
    }

    /**
     * Проверить сессию пользователя
     */
    private function checkSession($data)
    {
        $user = User::where('api_token', $data['session'])->first();
        if (!$user) {
            return response()->json(['status' => 404, 'message' => 'Unknown session']);
        }

        return response()->json([
            'status' => 200,
            'response' => [
                'id_player' => $user->id,
                'id_group' => 'default',
                'balance' => round($user->type_balance == 0 ? $user->balance * 100 : $user->demo_balance * 100)
            ]
        ]);
    }

    /**
     * Проверить баланс пользователя
     */
    private function checkBalance($data)
    {
        $user = User::where('api_token', $data['session'])->first();
        if (!$user) {
            return response()->json(['status' => 404, 'message' => 'Unknown session']);
        }

        return response()->json([
            'status' => 200,
            'response' => [
                'currency' => 'RUB',
                'balance' => round($user->type_balance == 0 ? $user->balance * 100 : $user->demo_balance * 100)
            ]
        ]);
    }

    /**
     * Списать ставку
     */
    private function withdrawBet($data)
    {
        $user = User::where('api_token', $data['session'])->first();
        if (!$user) {
            return response()->json(['status' => 404, 'message' => 'Unknown session']);
        }

        $amount = $data['amount'] / 100; // Конвертируем из копеек

        if ($user->type_balance == 0) {
            if ($user->balance < $amount) {
                return response()->json(['status' => 404, 'message' => 'Insufficient balance']);
            }
            $user->balance -= $amount;
        } else {
            if ($user->demo_balance < $amount) {
                return response()->json(['status' => 404, 'message' => 'Insufficient demo balance']);
            }
            $user->demo_balance -= $amount;
        }

        $user->save();

        return response()->json([
            'status' => 200,
            'response' => [
                'currency' => 'RUB',
                'balance' => round($user->type_balance == 0 ? $user->balance * 100 : $user->demo_balance * 100)
            ]
        ]);
    }

    /**
     * Зачислить выигрыш
     */
    private function depositWin($data)
    {
        $user = User::where('api_token', $data['session'])->first();
        if (!$user) {
            return response()->json(['status' => 404, 'message' => 'Unknown session']);
        }

        $amount = $data['amount'] / 100; // Конвертируем из копеек

        if ($user->type_balance == 0) {
            $user->balance += $amount;
        } else {
            $user->demo_balance += $amount;
        }

        $user->save();

        return response()->json([
            'status' => 200,
            'response' => [
                'currency' => 'RUB',
                'balance' => round($user->type_balance == 0 ? $user->balance * 100 : $user->demo_balance * 100)
            ]
        ]);
    }

    /**
     * Проверить подпись запроса
     */
    private function verifySignature($data)
    {
        if (!isset($data['signature'])) {
            return false;
        }

        $expectedSignature = hash_hmac('sha256', json_encode($data), $this->secretKey);
        return hash_equals($expectedSignature, $data['signature']);
    }
} 