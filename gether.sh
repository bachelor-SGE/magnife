#!/usr/bin/env bash
OUTDIR=~/server_env_info
mkdir -p "$OUTDIR"

echo "=== OS & Kernel ===" > "$OUTDIR"/01_system.txt
uname -a >> "$OUTDIR"/01_system.txt
lsb_release -a 2>/dev/null >> "$OUTDIR"/01_system.txt || cat /etc/*-release >> "$OUTDIR"/01_system.txt

echo -e "\n=== Installed APT Packages (nginx, php, mysql, node) ===" > "$OUTDIR"/02_packages.txt
dpkg-query -W 'nginx*' 'php*' 'mysql*' 'node*' 'npm*' >> "$OUTDIR"/02_packages.txt

echo -e "\n=== Nginx Version & Configs ===" > "$OUTDIR"/03_nginx.txt
nginx -v 2>&1 >> "$OUTDIR"/03_nginx.txt
nginx -V 2>&1 >> "$OUTDIR"/03_nginx.txt
# список подключённых модулей
nginx -V 2>&1 | tr ' ' '\n' | grep -- '--with-' >> "$OUTDIR"/03_nginx.txt

echo -e "\n=== PHP Version & Modules ===" > "$OUTDIR"/04_php.txt
php -v >> "$OUTDIR"/04_php.txt
php -m >> "$OUTDIR"/04_php.txt

echo -e "\n=== Composer Packages (если есть composer.json) ===" > "$OUTDIR"/05_composer.txt
if [ -f /var/www/magnife.ru/html/composer.json ]; then
  cd /var/www/magnife.ru/html
  composer show --no-dev >> "$OUTDIR"/05_composer.txt
else
  echo "composer.json не найден" >> "$OUTDIR"/05_composer.txt
fi

echo -e "\n=== Node & NPM Versions и глобальные пакеты ===" > "$OUTDIR"/06_node.txt
node -v >> "$OUTDIR"/06_node.txt
npm -v >> "$OUTDIR"/06_node.txt
npm list -g --depth=0 >> "$OUTDIR"/06_node.txt

echo -e "\n=== MySQL/MariaDB Version и дамп настроек ===" > "$OUTDIR"/07_mysql.txt
mysql --version >> "$OUTDIR"/07_mysql.txt
# вывести текущее значение ключевых переменных
mysql -e "SHOW VARIABLES LIKE 'version'; SHOW VARIABLES WHERE Variable_name IN ('datadir','socket','port');" >> "$OUTDIR"/07_mysql.txt

echo -e "\n=== Python (если используется) ===" > "$OUTDIR"/08_python.txt
if command -v python3 >/dev/null; then
  python3 --version >> "$OUTDIR"/08_python.txt
  pip3 freeze >> "$OUTDIR"/08_python.txt
else
  echo "python3 не установлен" >> "$OUTDIR"/08_python.txt
fi

echo -e "\n=== Файловая система сайта ===" > "$OUTDIR"/09_www_tree.txt
# структура директорий до 3 уровней
tree -L 3 /var/www/magnife.ru/html >> "$OUTDIR"/09_www_tree.txt 2>/dev/null

echo "Сбор информации завершён. Смотрите папку $OUTDIR"
