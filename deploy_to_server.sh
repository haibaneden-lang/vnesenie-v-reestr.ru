#!/bin/bash

# 🚀 Скрипт для передачи файлов на сервер vnesenie-v-reestr.ru
# Использование: ./deploy_to_server.sh

echo "🚀 Начинаем деплой на сервер..."

# Настройки сервера (используем SSH конфигурацию)
SSH_HOST="vnesenie-v-reestr"
SERVER_PATH="/var/www/s261262/data/www/vnesenie-v-reestr.ru"

# Исключаем ненужные файлы
EXCLUDE_FILES=(
    ".git"
    ".DS_Store"
    "uploads"
    "logs"
    "*.log"
    "*.db"
    "*.zip"
    "*.xlsx"
    "production.xlsx"
    "__MACOSX"
    "node_modules"
    "vendor"
)

# Строим строку исключений для rsync
EXCLUDE_OPTIONS=""
for file in "${EXCLUDE_FILES[@]}"; do
    EXCLUDE_OPTIONS="$EXCLUDE_OPTIONS --exclude=$file"
done

echo "📁 Передаем файлы на сервер..."
echo "📍 Сервер: $SSH_HOST"
echo "📂 Путь: $SERVER_PATH"

# Передаем файлы на сервер с игнорированием ошибок
rsync -avz --partial --progress -e "ssh" \
    $EXCLUDE_OPTIONS \
    --delete \
    --ignore-errors \
    ./ $SSH_HOST:$SERVER_PATH/

if [ $? -eq 0 ] || [ $? -eq 23 ]; then
    echo "✅ Файлы успешно переданы на сервер!"
    
    # Выполняем команды на сервере
    echo "🔧 Выполняем команды на сервере..."
    ssh $SSH_HOST << 'EOF'
        cd /var/www/s261262/data/www/vnesenie-v-reestr.ru
        echo "📁 Устанавливаем права доступа..."
        chmod -R 755 .
        chown -R s261262:s261262 .
        
        echo "🔍 Проверяем PHP синтаксис..."
        find . -name "*.php" -exec php -l {} \; | grep -v "No syntax errors" || echo "✅ PHP синтаксис корректен"
        
        echo "🎉 Деплой завершен успешно!"
EOF
else
    echo "❌ Ошибка при передаче файлов на сервер"
    exit 1
fi

echo "🎯 Деплой завершен! Сайт обновлен на сервере."
