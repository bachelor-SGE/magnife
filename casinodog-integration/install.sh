#!/bin/bash

echo "🚀 Установка casino-slots-aggregation-app..."

# Создаем директории
mkdir -p casinodog
mkdir -p logs
mkdir -p config

# Клонируем репозиторий casino-slots-aggregation-app
echo "📥 Клонирование репозитория..."
git clone https://github.com/four-by-two/casino-slots-aggregation-app.git casinodog

# Переходим в директорию
cd casinodog

# Устанавливаем зависимости
echo "📦 Установка зависимостей..."
composer install --no-dev --optimize-autoloader

# Копируем конфигурационные файлы
echo "⚙️ Настройка конфигурации..."
cp .env.example .env

# Генерируем ключи
echo "🔑 Генерация ключей..."
php artisan key:generate
php artisan jwt:secret
php artisan casinodog:generate-salt

# Настраиваем .env файл
echo "🔧 Настройка переменных окружения..."
sed -i 's/DB_CONNECTION=mysql/DB_CONNECTION=pgsql/' .env
sed -i 's/DB_HOST=127.0.0.1/DB_HOST=casinodog-db/' .env
sed -i 's/DB_PORT=3306/DB_PORT=5432/' .env
sed -i 's/DB_DATABASE=laravel/DB_DATABASE=casinodog/' .env
sed -i 's/DB_USERNAME=root/DB_USERNAME=casinodog/' .env
sed -i 's/DB_PASSWORD=/DB_PASSWORD=casinodog_password/' .env
sed -i 's/REDIS_HOST=127.0.0.1/REDIS_HOST=casinodog-redis/' .env

# Запускаем миграции
echo "🗄️ Запуск миграций..."
php artisan migrate:fresh

# Импортируем игры
echo "🎮 Импорт игр..."
php artisan casinodog:restore-default-gameslist pragmatic upsert
php artisan casinodog:restore-default-gameslist netent upsert
php artisan casinodog:restore-default-gameslist playngo upsert
php artisan casinodog:restore-default-gameslist redtiger upsert
php artisan casinodog:restore-default-gameslist relax upsert

# Устанавливаем права
echo "🔐 Установка прав доступа..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache

echo "✅ Установка завершена!"
echo ""
echo "🚀 Для запуска выполните:"
echo "docker-compose up -d"
echo ""
echo "📊 Для просмотра логов:"
echo "docker-compose logs -f casinodog-api"
echo ""
echo "🌐 API будет доступен по адресу:"
echo "http://localhost:8080" 