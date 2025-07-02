@extends('layouts.app')

@section('content')
<div class="wrapper">
    <style>
        .built-in-slot-container {
            background: #20273a;
            border-radius: 15px;
            padding: 20px;
            margin: 20px 0;
            min-height: 600px;
            position: relative;
        }
        
        .slot-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 15px;
            background: #1b2030;
            border-radius: 10px;
        }
        
        .slot-title {
            font-size: 24px;
            font-weight: 700;
            color: #fff;
        }
        
        .back-button {
            background: #f2ac44;
            color: #171b28;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: 0.25s ease;
        }
        
        .back-button:hover {
            background: #e89c34;
            color: #171b28;
            text-decoration: none;
        }
        
        .game-canvas-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 500px;
            background: #1b2030;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .loading {
            color: #fff;
            font-size: 18px;
            font-weight: 600;
        }
    </style>

    <div class="built-in-slot-container">
        <div class="slot-header">
            <div class="slot-title">
                @if($gameId === 'egyptian-treasures')
                    Egyptian Treasures
                @elseif($gameId === 'rock-climber')
                    Rock Climber
                @endif
            </div>
            <a href="/slots" class="back-button">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 8px;">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
                Назад к слотам
            </a>
        </div>
        
        <div class="game-canvas-container" id="gameContainer">
            <div class="loading">Загрузка игры...</div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pixi.js/6.5.0/pixi.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    
    @if($gameId === 'egyptian-treasures')
        <script src="/js/slots/egyptian-treasures.js"></script>
    @elseif($gameId === 'rock-climber')
        <script src="/js/slots/rock-climber.js"></script>
    @endif
    
    <script>
        // Инициализация игры
        document.addEventListener('DOMContentLoaded', function() {
            const gameContainer = document.getElementById('gameContainer');
            const gameId = '{{ $gameId }}';
            
            if (gameId === 'egyptian-treasures') {
                new EgyptianTreasuresSlot(gameContainer);
            } else if (gameId === 'rock-climber') {
                new RockClimberSlot(gameContainer);
            }
        });
    </script>
</div>
@endsection 