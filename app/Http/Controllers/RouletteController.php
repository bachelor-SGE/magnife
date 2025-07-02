<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\RouletteRound;
use App\RouletteBet;
use App\User;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Auth;

class RouletteController extends Controller
{
    public function state() {
        $round = RouletteRound::where('status', 'active')->first();
        $bets = $round ? RouletteBet::where('round_id', $round->id)->get() : [];
        return response(['round' => $round, 'bets' => $bets]);
    }

    public function bet(Request $r) {
        $user = Auth::user();
        $round = RouletteRound::where('status', 'active')->first();
        if (!$round) return response(['error' => 'Нет активного раунда'], 400);
        // Валидация типа/значения/суммы
        $bet = RouletteBet::create([
            'user_id' => $user->id,
            'round_id' => $round->id,
            'type' => $r->type,
            'value' => $r->value,
            'amount' => $r->amount,
        ]);
        $user->balance -= $r->amount;
        $user->save();
        Redis::publish('rouletteBet', json_encode([
            'user' => $user->name,
            'type' => $r->type,
            'value' => $r->value,
            'amount' => $r->amount,
        ]));
        return response(['success' => true]);
    }

    public function adminRig(Request $r) {
        $round = RouletteRound::where('status', 'active')->first();
        $round->is_rigged = true;
        $round->rigged_value = $r->value;
        $round->save();
        return response(['success' => true]);
    }

    public function finishRound() {
        $round = RouletteRound::where('status', 'active')->first();
        $bets = RouletteBet::where('round_id', $round->id)->get();
        if ($round->is_rigged) {
            $result = $round->rigged_value;
        } else {
            $result = rand(0, 36);
            if ($this->willGoMinus($result, $bets)) {
                $result = $this->findSafeResult($bets);
            }
        }
        foreach ($bets as $bet) {
            $win = $this->calcPayout($bet, $result);
            if ($win > 0) {
                $user = User::find($bet->user_id);
                $user->balance += $win;
                $user->save();
            }
            $bet->payout = $win;
            $bet->status = 'finished';
            $bet->save();
        }
        $round->result = $result;
        $round->status = 'finished';
        $round->save();
        RouletteRound::create(['start_time' => now()]);
        Redis::publish('rouletteResult', json_encode([
            'result' => $result,
            'bets' => $bets,
        ]));
        return response(['success' => true]);
    }

    // Anti-minus: если банк уходит в минус — выбираем безопасный результат
    private function willGoMinus($result, $bets) {
        // TODO: реализуй свою логику проверки минуса
        return false;
    }
    private function findSafeResult($bets) {
        // TODO: реализуй свою логику поиска безопасного результата
        return 0;
    }
    private function calcPayout($bet, $result) {
        // Пример: классические выплаты
        if ($bet->type === 'number' && $bet->value == $result) return $bet->amount * 36;
        if ($bet->type === 'color') {
            $red = [1,3,5,7,9,12,14,16,18,19,21,23,25,27,30,32,34,36];
            $isRed = in_array($result, $red);
            if ($bet->value === 'red' && $isRed) return $bet->amount * 2;
            if ($bet->value === 'black' && !$isRed && $result != 0) return $bet->amount * 2;
        }
        if ($bet->type === 'even' && $result != 0 && $result % 2 == 0) return $bet->amount * 2;
        if ($bet->type === 'odd' && $result % 2 == 1) return $bet->amount * 2;
        // ... добавь остальные типы ставок
        return 0;
    }
} 