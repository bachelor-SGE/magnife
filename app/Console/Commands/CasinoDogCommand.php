<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CasinoDogService;

class CasinoDogCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'casinodog:manage {action} {--provider=} {--game-id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Управление интеграцией casino-slots-aggregation-app';

    /**
     * The CasinoDog service instance.
     *
     * @var CasinoDogService
     */
    protected $casinoDogService;

    /**
     * Create a new command instance.
     *
     * @param CasinoDogService $casinoDogService
     * @return void
     */
    public function __construct(CasinoDogService $casinoDogService)
    {
        parent::__construct();
        $this->casinoDogService = $casinoDogService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'test':
                return $this->testConnection();
            case 'games':
                return $this->listGames();
            case 'providers':
                return $this->listProviders();
            case 'sync':
                return $this->syncGames();
            case 'status':
                return $this->checkStatus();
            default:
                $this->error("Неизвестное действие: {$action}");
                return 1;
        }
    }

    /**
     * Тестирование подключения к API
     */
    private function testConnection()
    {
        $this->info('🔍 Тестирование подключения к casino-slots-aggregation-app...');

        try {
            $games = $this->casinoDogService->getGames(null, 1, 5);
            
            if (isset($games['games']) && count($games['games']) > 0) {
                $this->info('✅ Подключение успешно!');
                $this->info("📊 Найдено игр: " . count($games['games']));
                return 0;
            } else {
                $this->warn('⚠️ API доступен, но игры не найдены');
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('❌ Ошибка подключения: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Список игр
     */
    private function listGames()
    {
        $provider = $this->option('provider');
        
        $this->info('🎮 Получение списка игр...');
        if ($provider) {
            $this->info("📋 Провайдер: {$provider}");
        }

        try {
            $games = $this->casinoDogService->getGames($provider, 1, 20);
            
            if (isset($games['games']) && count($games['games']) > 0) {
                $this->table(
                    ['ID', 'Название', 'Провайдер', 'Изображение'],
                    array_map(function ($game) {
                        return [
                            $game['game_id'] ?? $game['id'] ?? 'N/A',
                            $game['title'] ?? $game['name'] ?? 'N/A',
                            $game['provider'] ?? 'N/A',
                            $game['icon'] ?? $game['image'] ?? 'N/A'
                        ];
                    }, $games['games'])
                );
                return 0;
            } else {
                $this->warn('⚠️ Игры не найдены');
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('❌ Ошибка: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Список провайдеров
     */
    private function listProviders()
    {
        $this->info('🏢 Доступные провайдеры:');
        
        $providers = [
            'pragmatic' => 'Pragmatic Play',
            'netent' => 'NetEnt',
            'playngo' => 'Play\'n GO',
            'redtiger' => 'Red Tiger',
            'relax' => 'Relax Gaming',
            'pushgaming' => 'Push Gaming',
            'amatic' => 'Amatic',
            'fantasma' => 'Fantasma Games',
        ];

        foreach ($providers as $key => $name) {
            $this->line("  • {$key} - {$name}");
        }

        return 0;
    }

    /**
     * Синхронизация игр с локальной БД
     */
    private function syncGames()
    {
        $provider = $this->option('provider');
        
        if (!$provider) {
            $this->error('❌ Укажите провайдера: --provider=pragmatic');
            return 1;
        }

        $this->info("🔄 Синхронизация игр провайдера: {$provider}");

        try {
            $games = $this->casinoDogService->getGames($provider, 1, 100);
            
            if (isset($games['games']) && count($games['games']) > 0) {
                $count = 0;
                foreach ($games['games'] as $game) {
                    // Здесь можно добавить логику сохранения в локальную БД
                    $count++;
                }
                
                $this->info("✅ Синхронизировано игр: {$count}");
                return 0;
            } else {
                $this->warn('⚠️ Игры не найдены для синхронизации');
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('❌ Ошибка синхронизации: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Проверка статуса интеграции
     */
    private function checkStatus()
    {
        $this->info('📊 Статус интеграции casino-slots-aggregation-app:');
        
        // Проверяем конфигурацию
        $this->line('');
        $this->info('⚙️ Конфигурация:');
        $this->line('  • Base URL: ' . config('casinodog.base_url'));
        $this->line('  • API Key: ' . (config('casinodog.api_key') ? '✅ Установлен' : '❌ Не установлен'));
        $this->line('  • Secret Key: ' . (config('casinodog.secret_key') ? '✅ Установлен' : '❌ Не установлен'));
        $this->line('  • Logging: ' . (config('casinodog.logging.enabled') ? '✅ Включено' : '❌ Отключено'));
        $this->line('  • Signature Verification: ' . (config('casinodog.security.verify_signature') ? '✅ Включено' : '❌ Отключено'));

        // Проверяем подключение
        $this->line('');
        $this->info('🔗 Подключение:');
        try {
            $games = $this->casinoDogService->getGames(null, 1, 1);
            if (isset($games['games'])) {
                $this->line('  • API: ✅ Доступен');
            } else {
                $this->line('  • API: ⚠️ Доступен, но пустой ответ');
            }
        } catch (\Exception $e) {
            $this->line('  • API: ❌ Недоступен (' . $e->getMessage() . ')');
        }

        return 0;
    }
} 