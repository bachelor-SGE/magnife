# Настройка переменных окружения

## Добавьте в основной .env файл проекта:

```env
# =============================================================================
# CASINO SLOTS AGGREGATION APP INTEGRATION
# =============================================================================

# Базовый URL для casino-slots-aggregation-app API
CASINODOG_BASE_URL=http://localhost:8080

# API ключ для аутентификации
CASINODOG_API_KEY=your-api-key-here

# Секретный ключ для подписи запросов
CASINODOG_SECRET_KEY=your-secret-key-here

# URL для получения колбэков
CASINODOG_CALLBACK_URL=https://your-domain.com/slots/callback

# Логирование
CASINODOG_LOGGING=true
CASINODOG_LOG_CHANNEL=daily

# Безопасность
CASINODOG_VERIFY_SIGNATURE=true
CASINODOG_ALLOWED_IPS=127.0.0.1,::1
```

## Генерация ключей:

### 1. API Key
Получите в админке casino-slots-aggregation-app или сгенерируйте:

```bash
# В контейнере casino-slots-aggregation-app
docker-compose exec casinodog-api php artisan tinker
```

```php
// Генерируем API ключ
echo bin2hex(random_bytes(32));
```

### 2. Secret Key
Сгенерируйте секретный ключ:

```bash
# В контейнере casino-slots-aggregation-app
docker-compose exec casinodog-api php artisan casinodog:generate-salt
```

### 3. JWT Secret
Сгенерируйте JWT секрет:

```bash
# В контейнере casino-slots-aggregation-app
docker-compose exec casinodog-api php artisan jwt:secret
```

## Проверка настроек:

```bash
# Проверяем подключение к API
curl -H "Authorization: Bearer YOUR_API_KEY" http://localhost:8080/api/v1/games

# Проверяем health check
curl http://localhost:8080/health
```

## Безопасность:

1. **Никогда не коммитьте** реальные ключи в Git
2. Используйте **разные ключи** для разработки и продакшена
3. **Регулярно ротируйте** ключи
4. Ограничьте **IP адреса** для колбэков
5. Включите **проверку подписи** запросов 