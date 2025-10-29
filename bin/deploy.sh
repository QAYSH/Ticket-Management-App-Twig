#!/bin/bash

echo "🚀 Starting Symfony Twig App Deployment on Railway..."

# Display PHP version
php --version

# Create necessary directories
echo "📁 Creating necessary directories..."
mkdir -p var/cache var/log var/sessions
mkdir -p public/assets/css public/assets/js public/assets/images

# Set proper permissions
echo "🔒 Setting permissions..."
chmod -R 755 var/
chmod -R 755 public/

# Install dependencies
echo "📦 Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# Clear cache for production
echo "🗑️  Clearing cache..."
php bin/console cache:clear --env=prod --no-debug

# Warm up cache
echo "🔥 Warming up cache..."
php bin/console cache:warmup --env=prod

# Create tickets.json if it doesn't exist
echo "📝 Initializing data files..."
if [ ! -f var/tickets.json ]; then
    echo "[]" > var/tickets.json
    chmod 666 var/tickets.json
fi

echo "✅ Deployment preparation complete!"
echo "🌐 Application is ready to serve on port: ${PORT:-8000}"