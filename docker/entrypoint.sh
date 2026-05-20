#!/bin/bash
set -e

# Clear compiled views and config cache so volume-cached files don't serve stale content
php artisan view:clear
php artisan config:clear
php artisan cache:clear

# Start Apache
exec apache2-foreground
