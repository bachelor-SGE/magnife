<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Slots;
use App\User;
use Auth;
use App\Payment;
use App\Services\CasinoDogService;

class SlotsController extends Controller
{
    protected $casinoDogService;

    public function __construct(CasinoDogService $casinoDogService)
    {
        $this->casinoDogService = $casinoDogService;
    }

    public function getGames(Request $request)
    {
        $show = $request->page * 30 - 30;

        // Удаляем встроенные слоты из выдачи
        // $builtInGames = [...];

        // Используем casino-slots-aggregation-app для получения игр
        $casinoDogGames = $this->casinoDogService->getGames(
            $request->provider,
            $request->page,
            30
        );

        // Если casino-slots-aggregation-app недоступен, используем локальную БД
        if (empty($casinoDogGames['games'])) {
            $slots = Slots::orderBy('priority', 'desc')->where([
                [function ($query) use ($request) {
                    if(($provider = $request->provider)) {
                        $query->where('provider', $provider)->get();
                    }
                    if(($search = $request->search)) {
                        $query->where('title', 'like', '%' .$search. '%')->get();
                    }
                }]
            ])->where([['show', 1], ['is_live', 0]])->offset($show)->limit(30)->get();

            foreach($slots as $slot) {
                $slot->icon = '/img/slots/'. implode('', explode(' ', $slot->title)) . '.jpg';
            }

            $formattedGames = [];
            foreach ($slots as $slot) {
                $formattedGames[] = [
                    'game_id' => $slot->game_id,
                    'title' => $slot->title,
                    'provider' => $slot->provider,
                    'icon' => $slot->icon,
                    'alias' => $slot->alias
                ];
            }

            // Не добавляем встроенные слоты
            // if ($request->page == 1) {
            //     $formattedGames = array_merge($builtInGames, $formattedGames);
            // }

            return [
                'games' => $formattedGames
            ];
        }

        // Форматируем игры от casino-slots-aggregation-app
        $formattedGames = [];
        foreach ($casinoDogGames['games'] as $game) {
            $formattedGames[] = [
                'game_id' => $game['id'] ?? $game['game_id'],
                'title' => $game['name'] ?? $game['title'],
                'provider' => $game['provider'],
                'icon' => $game['image'] ?? '/img/slots/default.jpg',
                'alias' => $game['alias'] ?? $game['slug']
            ];
        }

        // Не добавляем встроенные слоты
        // if ($request->page == 1) {
        //     $formattedGames = array_merge($builtInGames, $formattedGames);
        // }

        return [
            'games' => $formattedGames
        ];
    }

    public function getGameURI(Request $request)
    {
        $user = User::where('id', Auth::id())->first();

        if(!$user) {
            return ['error' => 'Авторизуйтесь'];
        }

        if($user->api_token == null) {
            $user->api_token = bin2hex(random_bytes(20));
            $user->save();
        }

        // Используем casino-slots-aggregation-app для получения URL игры
        $gameUrl = $this->casinoDogService->getGameUrl(
            $request->id,
            $user->id,
            $user->type_balance == 1 // demo mode
        );

        if (isset($gameUrl['error'])) {
            // Fallback к старому методу если casino-slots-aggregation-app недоступен
            $slot = Slots::where('game_id', $request->id)->first();
            if(!$slot) {
                return ['error' => 'Игра не найдена'];
            }

            $url = "https://test.partners.casinomobule.com/games.start?partner.alias=".($user->admin == 3 ? 'soyoustartvhguru' : 'soyoustartvhguru')."&partner.session={$user->api_token}&game.provider={$slot->provider}&game.alias={$slot->alias}&lang=ru&lobby_url=https://beta.so-you-start.ru/slots&currency=RUB&mobile=false";

            return [
                'url' => $url,
                'image' => $slot->icon,
                'name' => $slot->title
            ];
        }

        return [
            'url' => $gameUrl['url'] ?? $gameUrl['game_url'],
            'image' => $gameUrl['image'] ?? '/img/slots/default.jpg',
            'name' => $gameUrl['name'] ?? 'Unknown Game'
        ];
    }

    public function callback($method, Request $r) {
        // Делегируем обработку колбэков в casino-slots-aggregation-app сервис
        return $this->casinoDogService->handleCallback($method, $r->all());
    }

    // Новые методы для встроенных слотов
    public function showBuiltInSlot($gameId)
    {
        $validGames = ['egyptian-treasures', 'rock-climber'];
        
        if (!in_array($gameId, $validGames)) {
            abort(404);
        }

        return view('slots.built-in', compact('gameId'));
    }

    public function getBuiltInSlotScript($gameId)
    {
        $validGames = ['egyptian-treasures', 'rock-climber'];
        
        if (!in_array($gameId, $validGames)) {
            abort(404);
        }

        $scriptPath = public_path("js/slots/{$gameId}.js");
        
        if (!file_exists($scriptPath)) {
            abort(404);
        }

        return response()->file($scriptPath, [
            'Content-Type' => 'application/javascript'
        ]);
    }

    // Оставляем старые методы для совместимости
    private function trxCancel($data) {
        return response()->json(['status' => 200]);
    }

    private function trxComplete($data) {
        return response()->json(['status' => 200]);
    }

    private function checkSession($data) {
        if(!$data->session) return response()->json(['status' => 404, 'method' => 'check.session', 'message' => 'Unknown session']);
        $user = User::where('api_token', $data->session)->first();
        if(!$user) return response()->json(['status' => 404, 'method' => 'check.session', 'message' => 'Unknown user']);

        return response()->json(['status' => 200, 'method' => 'check.session', 'response' => ['id_player' => $user->id, 'id_group' => 'default', 'balance' => round($user->type_balance == 0 ? $user->balance * 100 : $user->demo_balance * 100)]]);
    }

    private function checkBalance($data) {
        if(!$data->session) return response()->json(['status' => 404, 'method' => 'check.balance', 'message' => 'Unknown session']);
        $user = User::where('api_token', $data->session)->first();
        if(!$user) return response()->json(['status' => 404, 'method' => 'check.balance', 'message' => 'Unknown user']);

        return response()->json(['status' => 200, 'method' => 'check.balance', 'response' => ['currency' => 'RUB', 'balance' => round($user->type_balance == 0 ? $user->balance * 100 : $user->demo_balance * 100)]]);
    }

    public function userBet($data) {
        if(!$data->session) return response()->json(['status' => 404, 'method' => 'withdraw.bet', 'message' => 'Unknown session']);
        $user = User::where('api_token', $data->session)->first();
        if(!$user) return response()->json(['status' => 404, 'method' => 'withdraw.bet', 'message' => 'Unknown user']);

        if($user->type_balance == 0) {
            if($user->balance < ($data->amount / 100)) return response()->json(['status' => 404, 'method' => 'withdraw.bet', 'message' => 'Fail balance']);
        } else {
            if($user->demo_balance < ($data->amount / 100)) return response()->json(['status' => 404, 'method' => 'withdraw.bet', 'message' => 'Fail balance']);
        }

        $wager = ($user->sum_to_withdraw - $data->amount / 100) < 0 ? 0 : $user->sum_to_withdraw - $data->amount / 100;

        if($user->type_balance == 0) {
            $user->balance -= $data->amount / 100;
            $user->sum_to_withdraw = $wager;
        } else {
            $user->demo_balance -= $data->amount / 100;
        }
        $user->save();

        return response()->json(['status' => 200, 'method' => 'withdraw.bet', 'response' => ['currency' => 'RUB', 'balance' => round($user->type_balance == 0 ? $user->balance * 100 : $user->demo_balance * 100)]]);
    }

    public function userWin($data) {
        if(!$data->session) return response()->json(['status' => 404, 'method' => 'deposit.win', 'message' => 'Unknown session']);
        $user = User::where('api_token', $data->session)->first();
        if(!$user) return response()->json(['status' => 404, 'method' => 'deposit.win', 'message' => 'Unknown user']);

        if($user->type_balance == 0) {
            $user->balance += $data->amount / 100;
        } else {
            $user->demo_balance += $data->amount / 100;
        }
        $user->save();

        return response()->json(['status' => 200, 'method' => 'deposit.win', 'response' => ['currency' => 'RUB', 'balance' => round($user->type_balance == 0 ? $user->balance * 100  : $user->demo_balance * 100)]]);
    }
}
