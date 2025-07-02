# CasinoDog Integration

Интеграция [casino-slots-aggregation-app](https://github.com/four-by-two/casino-slots-aggregation-app) в проект magnife.ru

## 🎯 Что это дает

- **2000+ казино игр** от реальных провайдеров
- **Автоматические транзакции** через webhook систему
- **Поддержка демо и реальных денег**
- **Высокая производительность** благодаря Swoole
- **Готовое решение** - не нужно искать провайдеров

## 🚀 Быстрый старт

### 1. Установка

```bash
# Переходим в директорию интеграции
cd casinodog-integration

# Запускаем установку
chmod +x install.sh
./install.sh
```

### 2. Запуск

```bash
# Запускаем все сервисы
docker-compose up -d

# Проверяем статус
docker-compose ps
```

### 3. Настройка Laravel

Добавьте в `.env` файл основного проекта:

```env
# CasinoDog Integration
CASINODOG_BASE_URL=http://localhost:8080
CASINODOG_API_KEY=your-api-key
CASINODOG_SECRET_KEY=your-secret-key
CASINODOG_CALLBACK_URL=https://your-domain.com/slots/callback
CASINODOG_LOGGING=true
CASINODOG_VERIFY_SIGNATURE=true
```

## 📁 Структура проекта

```
casinodog-integration/
├── casinodog/                 # CasinoDog приложение
├── docker-compose.yml         # Docker конфигурация
├── install.sh                 # Скрипт установки
├── nginx.conf                 # Nginx конфигурация
├── logs/                      # Логи
└── README.md                  # Этот файл
```

## 🔧 Конфигурация

### Поддерживаемые провайдеры

- **Pragmatic Play** - популярные слоты (Gates of Olympus, Sweet Bonanza)
- **NetEnt** - классические игры (Starburst, Gonzo's Quest)
- **Play'n GO** - инновационные слоты (Book of Dead, Reactoonz)
- **Red Tiger** - современные игры (Gonzo's Quest Megaways)
- **Relax Gaming** - уникальные слоты (Money Train, Temple Tumble)

### API Endpoints

- `GET /api/v1/games` - получение списка игр
- `POST /api/v1/games/launch` - запуск игры
- `POST /api/v1/callbacks/{method}` - обработка колбэков

## 🔄 Интеграция с Laravel

### 1. Сервис

Создан `CasinoDogService` в `app/Services/` для работы с API.

### 2. Контроллер

Обновлен `SlotsController` для использования нового сервиса.

### 3. Конфигурация

Добавлен файл `config/casinodog.php` с настройками.

## 🛠️ Разработка

### Локальная разработка

```bash
# Запуск в режиме разработки
docker-compose -f docker-compose.dev.yml up -d

# Просмотр логов
docker-compose logs -f casinodog-api
```

### Тестирование

```bash
# Запуск тестов
docker-compose exec casinodog-api php artisan test

# Проверка кода
docker-compose exec casinodog-api composer lint
```

## 📊 Мониторинг

### Логи

```bash
# Логи API
docker-compose logs -f casinodog-api

# Логи базы данных
docker-compose logs -f casinodog-db

# Логи Redis
docker-compose logs -f casinodog-redis
```

### Метрики

- Количество активных игр
- Статистика транзакций
- Производительность API

## 🔒 Безопасность

- Проверка подписи запросов
- Ограничение IP адресов
- Таймауты сессий
- Логирование всех операций

## 🚨 Устранение неполадок

### Проблема: API недоступен

```bash
# Проверяем статус контейнеров
docker-compose ps

# Перезапускаем сервисы
docker-compose restart casinodog-api
```

### Проблема: База данных не подключается

```bash
# Проверяем подключение к БД
docker-compose exec casinodog-api php artisan tinker
```

### Проблема: Игры не загружаются

```bash
# Переимпортируем игры
docker-compose exec casinodog-api php artisan casinodog:restore-default-gameslist pragmatic upsert
```

## 📞 Поддержка

- [CasinoDog GitHub](https://github.com/four-by-two/casino-slots-aggregation-app)
- [Документация API](https://github.com/four-by-two/casino-slots-aggregation-app/wiki)
- [Issues](https://github.com/four-by-two/casino-slots-aggregation-app/issues)

## 📝 Лицензия

MIT License - см. файл LICENSE в репозитории casino-slots-aggregation-app. 