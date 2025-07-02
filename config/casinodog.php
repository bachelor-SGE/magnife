<?php

return [
    /*
    |--------------------------------------------------------------------------
    | CasinoDog Configuration
    |--------------------------------------------------------------------------
    |
    | Настройки для интеграции с casino-slots-aggregation-app
    |
    */

    // Базовый URL для casino-slots-aggregation-app API
    'base_url' => env('CASINODOG_BASE_URL', 'http://localhost:8001'),

    // API ключ для аутентификации
    'api_key' => env('CASINODOG_API_KEY', 'your-api-key'),

    // Секретный ключ для подписи запросов
    'secret_key' => env('CASINODOG_SECRET_KEY', 'your-secret-key'),

    // Настройки для игр
    'games' => [
        // Количество игр на странице
        'per_page' => 30,
        
        // Поддерживаемые провайдеры
        'providers' => [
            'pragmatic' => 'Pragmatic Play',
            'netent' => 'NetEnt',
            'playngo' => 'Play\'n GO',
            'redtiger' => 'Red Tiger',
            'relax' => 'Relax Gaming',
            'pushgaming' => 'Push Gaming',
            'amatic' => 'Amatic',
            'fantasma' => 'Fantasma Games',
        ],

        // Настройки для демо режима
        'demo' => [
            'enabled' => true,
            'balance' => 10000, // Демо баланс в копейках
        ],
    ],

    // Настройки для колбэков
    'callbacks' => [
        // URL для получения колбэков от casino-slots-aggregation-app
        'url' => env('CASINODOG_CALLBACK_URL', 'https://your-domain.com/slots/callback'),
        
        // Таймаут для колбэков (в секундах)
        'timeout' => 30,
        
        // Максимальное количество попыток
        'max_retries' => 3,
    ],

    // Настройки для логирования
    'logging' => [
        'enabled' => env('CASINODOG_LOGGING', true),
        'channel' => env('CASINODOG_LOG_CHANNEL', 'daily'),
    ],

    // Настройки для кэширования
    'cache' => [
        'enabled' => env('CASINODOG_CACHE', true),
        'ttl' => 3600, // Время жизни кэша в секундах
    ],

    // Настройки для безопасности
    'security' => [
        // Проверка подписи запросов
        'verify_signature' => env('CASINODOG_VERIFY_SIGNATURE', true),
        
        // Разрешенные IP адреса для колбэков
        'allowed_ips' => env('CASINODOG_ALLOWED_IPS', ''),
        
        // Таймаут для сессий (в секундах)
        'session_timeout' => 3600,
    ],

    // Настройки для валют
    'currency' => [
        'default' => 'RUB',
        'supported' => ['RUB', 'USD', 'EUR'],
        'exchange_rates' => [
            'USD' => 100, // 1 USD = 100 RUB
            'EUR' => 110, // 1 EUR = 110 RUB
        ],
    ],

    // Настройки для локализации
    'localization' => [
        'default_language' => 'ru',
        'supported_languages' => ['ru', 'en', 'de'],
    ],
]; 