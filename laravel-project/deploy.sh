#!/bin/bash

# Laravel Deployment Script
# This script handles the deployment process for the Laravel API

set -e  # Exit on any error

echo "🚀 Starting Laravel deployment..."

# Pull latest code
echo "📥 Pulling latest code..."
git pull origin main

# Install dependencies
echo "📦 Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader

# Clear and cache Laravel configurations
echo "🧹 Clearing and caching configurations..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run database migrations
echo "🗄️ Running database migrations..."
php artisan migrate --force

# Restart queue workers
echo "🔄 Restarting queue workers..."
php artisan queue:restart

# Set correct permissions
echo "🔐 Setting file permissions..."
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Restart web services
echo "🌐 Restarting web services..."
sudo systemctl reload nginx
sudo systemctl reload php-fpm

echo "✅ Deployment completed successfully!"
