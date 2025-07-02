#!/bin/bash

echo "🎰 Быстрый запуск casino-slots-aggregation-app интеграции"
echo "=================================================="

# Проверяем наличие Docker
if ! command -v docker &> /dev/null; then
    echo "❌ Docker не установлен. Установите Docker и попробуйте снова."
    exit 1
fi

if ! command -v docker-compose &> /dev/null; then
    echo "❌ Docker Compose не установлен. Установите Docker Compose и попробуйте снова."
    exit 1
fi

echo "✅ Docker и Docker Compose найдены"

# Проверяем наличие Git
if ! command -v git &> /dev/null; then
    echo "❌ Git не установлен. Установите Git и попробуйте снова."
    exit 1
fi

echo "✅ Git найден"

# Создаем директории если их нет
mkdir -p casinodog
mkdir -p logs
mkdir -p config

# Проверяем есть ли уже casino-slots-aggregation-app
if [ ! -d "casinodog/.git" ]; then
    echo "📥 Клонирование casino-slots-aggregation-app..."
    git clone https://github.com/four-by-two/casino-slots-aggregation-app.git casinodog
else
    echo "✅ casino-slots-aggregation-app уже установлен"
fi

# Переходим в директорию casino-slots-aggregation-app
cd casinodog

# Проверяем есть ли composer.json
if [ ! -f "composer.json" ]; then
    echo "❌ Ошибка: composer.json не найден. Проверьте клонирование репозитория."
    exit 1
fi

# Устанавливаем зависимости
echo "📦 Установка зависимостей..."
composer install --no-dev --optimize-autoloader

# Копируем конфигурацию
if [ ! -f ".env" ]; then
    echo "⚙️ Настройка конфигурации..."
    cp .env.example .env
    
    # Настраиваем .env для Docker
    sed -i 's/DB_CONNECTION=mysql/DB_CONNECTION=pgsql/' .env
    sed -i 's/DB_HOST=127.0.0.1/DB_HOST=casinodog-db/' .env
    sed -i 's/DB_PORT=3306/DB_PORT=5432/' .env
    sed -i 's/DB_DATABASE=laravel/DB_DATABASE=casinodog/' .env
    sed -i 's/DB_USERNAME=root/DB_USERNAME=casinodog/' .env
    sed -i 's/DB_PASSWORD=/DB_PASSWORD=casinodog_password/' .env
    sed -i 's/REDIS_HOST=127.0.0.1/REDIS_HOST=casinodog-redis/' .env
fi

# Возвращаемся в корневую директорию
cd ..

# Запускаем Docker контейнеры
echo "🚀 Запуск Docker контейнеров..."
docker-compose up -d

# Ждем немного для запуска сервисов
echo "⏳ Ожидание запуска сервисов..."
sleep 30

# Проверяем статус контейнеров
echo "📊 Статус контейнеров:"
docker-compose ps

# Проверяем подключение к API
echo "🔍 Проверка подключения к API..."
sleep 10

if curl -s http://localhost:8080/health > /dev/null; then
    echo "✅ API доступен"
else
    echo "⚠️ API пока недоступен, подождите немного..."
fi

echo ""
echo "🎉 Установка завершена!"
echo ""
echo "📋 Следующие шаги:"
echo "1. Добавьте переменные окружения в основной .env файл:"
echo "   CASINODOG_BASE_URL=http://localhost:8080"
echo "   CASINODOG_API_KEY=your-api-key"
echo "   CASINODOG_SECRET_KEY=your-secret-key"
echo ""
echo "2. Проверьте статус интеграции:"
echo "   php artisan casinodog:manage status"
echo ""
echo "3. Протестируйте подключение:"
echo "   php artisan casinodog:manage test"
echo ""
echo "4. Посмотрите доступные игры:"
echo "   php artisan casinodog:manage games"
echo ""
echo "🌐 API будет доступен по адресу: http://localhost:8080"
echo "📊 Логи: docker-compose logs -f casinodog-api"
echo "🛑 Остановка: docker-compose down" 