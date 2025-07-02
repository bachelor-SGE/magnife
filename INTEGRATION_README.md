# 🎰 Интеграция Casino Slots Aggregation App

Полная интеграция [casino-slots-aggregation-app](https://github.com/four-by-two/casino-slots-aggregation-app) в проект magnife.ru

## 🎯 Что получили

✅ **2000+ казино игр** от реальных провайдеров  
✅ **Автоматические транзакции** через webhook систему  
✅ **Поддержка демо и реальных денег**  
✅ **Высокая производительность** благодаря Swoole  
✅ **Готовое решение** - не нужно искать провайдеров  
✅ **Fallback система** - если API недоступен, работает старая система  

## 🚀 Быстрый запуск

### 1. Установка casino-slots-aggregation-app

```bash
# Переходим в директорию интеграции
cd html/casinodog-integration

# Запускаем быструю установку
chmod +x quick-start.sh
./quick-start.sh
```

### 2. Настройка Laravel

Добавьте в основной `.env` файл проекта:

```env
# =============================================================================
# CASINO SLOTS AGGREGATION APP INTEGRATION
# =============================================================================

CASINODOG_BASE_URL=http://localhost:8080
CASINODOG_API_KEY=your-api-key-here
CASINODOG_SECRET_KEY=your-secret-key-here
CASINODOG_CALLBACK_URL=https://your-domain.com/slots/callback
CASINODOG_LOGGING=true
CASINODOG_VERIFY_SIGNATURE=true
```

### 3. Проверка интеграции

```bash
# Проверяем статус
php artisan casinodog:manage status

# Тестируем подключение
php artisan casinodog:manage test

# Смотрим доступные игры
php artisan casinodog:manage games

# Смотрим провайдеров
php artisan casinodog:manage providers
```

## 📁 Что создано

### Новые файлы:

```
html/
├── app/
│   ├── Services/
│   │   └── CasinoDogService.php          # Сервис для работы с API
│   └── Console/Commands/
│       └── CasinoDogCommand.php          # Artisan команды
├── config/
│   └── casinodog.php                     # Конфигурация интеграции
├── casinodog-integration/                # Директория интеграции
│   ├── docker-compose.yml                # Docker конфигурация
│   ├── nginx.conf                        # Nginx конфигурация
│   ├── install.sh                        # Скрипт установки
│   ├── quick-start.sh                    # Быстрый запуск
│   ├── README.md                         # Документация
│   └── ENV_SETUP.md                      # Настройка переменных
└── INTEGRATION_README.md                 # Этот файл
```

### Обновленные файлы:

- `app/Http/Controllers/SlotsController.php` - интеграция с новым сервисом
- `resources/views/welcome.blade.php` - раскомментирован блок SLOTS

## 🎮 Поддерживаемые провайдеры

- **Pragmatic Play** - Gates of Olympus, Sweet Bonanza, Big Bass Bonanza
- **NetEnt** - Starburst, Gonzo's Quest, Book of Dead
- **Play'n GO** - Book of Dead, Reactoonz, Rise of Merlin
- **Red Tiger** - Gonzo's Quest Megaways, Dragon's Luck
- **Relax Gaming** - Money Train, Temple Tumble, Snake Arena
- **Push Gaming** - Razor Shark, Fat Rabbit, Jammin Jars
- **Amatic** - Классические слоты
- **Fantasma Games** - Инновационные игры

## 🔧 Управление

### Artisan команды:

```bash
# Проверка статуса интеграции
php artisan casinodog:manage status

# Тестирование подключения
php artisan casinodog:manage test

# Список игр
php artisan casinodog:manage games
php artisan casinodog:manage games --provider=pragmatic

# Список провайдеров
php artisan casinodog:manage providers

# Синхронизация игр
php artisan casinodog:manage sync --provider=pragmatic
```

### Docker команды:

```bash
# Запуск
cd html/casinodog-integration
docker-compose up -d

# Остановка
docker-compose down

# Логи
docker-compose logs -f casinodog-api
docker-compose logs -f casinodog-db
docker-compose logs -f casinodog-redis

# Статус
docker-compose ps
```

## 🔄 Как это работает

### 1. Получение игр
```php
// SlotsController теперь использует CasinoDogService
$games = $this->casinoDogService->getGames($provider, $page, $limit);

// Если casino-slots-aggregation-app недоступен, используется локальная БД
if (empty($games['games'])) {
    // Fallback к старому методу
}
```

### 2. Запуск игры
```php
// Получаем URL для запуска игры
$gameUrl = $this->casinoDogService->getGameUrl($gameId, $userId, $demo);

// Если API недоступен, используется старый метод
if (isset($gameUrl['error'])) {
    // Fallback к casinomobule.com
}
```

### 3. Обработка колбэков
```php
// Все колбэки делегируются в CasinoDogService
return $this->casinoDogService->handleCallback($method, $data);
```

## 🔒 Безопасность

- ✅ Проверка подписи запросов
- ✅ Ограничение IP адресов
- ✅ Таймауты сессий
- ✅ Логирование всех операций
- ✅ CORS настройки
- ✅ Защита от XSS и CSRF

## 📊 Мониторинг

### Логи:
```bash
# Laravel логи
tail -f storage/logs/laravel.log

# CasinoDog логи
docker-compose logs -f casinodog-api

# Nginx логи
docker-compose logs -f casinodog-nginx
```

### Метрики:
- Количество активных игр
- Статистика транзакций
- Производительность API
- Ошибки подключения

## 🚨 Устранение неполадок

### Проблема: API недоступен
```bash
# Проверяем статус контейнеров
docker-compose ps

# Перезапускаем сервисы
docker-compose restart casinodog-api

# Проверяем логи
docker-compose logs casinodog-api
```

### Проблема: Игры не загружаются
```bash
# Проверяем подключение
php artisan casinodog:manage test

# Переимпортируем игры
docker-compose exec casinodog-api php artisan casinodog:restore-default-gameslist pragmatic upsert
```

### Проблема: Колбэки не работают
```bash
# Проверяем настройки
php artisan casinodog:manage status

# Проверяем логи колбэков
tail -f storage/logs/laravel.log | grep "CasinoDog"
```

## 🎉 Результат

Теперь у тебя есть:

1. **Работающие слоты** с реальными провайдерами
2. **Автоматические транзакции** через webhook
3. **Fallback система** - если что-то сломается, старая система продолжит работать
4. **Удобное управление** через Artisan команды
5. **Мониторинг и логирование** всех операций

## 📞 Поддержка

- [CasinoDog GitHub](https://github.com/four-by-two/casino-slots-aggregation-app)
- [Документация API](https://github.com/four-by-two/casino-slots-aggregation-app/wiki)
- [Issues](https://github.com/four-by-two/casino-slots-aggregation-app/issues)

---

**🎰 Готово! Теперь у тебя есть полноценная интеграция с casino-slots-aggregation-app!** 