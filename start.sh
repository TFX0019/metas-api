#!/bin/bash

# Generar APP_KEY si no existe
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

# Ejecutar migraciones
php artisan migrate --force

# Limpiar y cachear configuración
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Crear enlace de storage
php artisan storage:link

# Iniciar PHP-FPM en background
php-fpm -D

# Iniciar Nginx en foreground
nginx -g 'daemon off;'