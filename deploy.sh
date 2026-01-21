#!/bin/bash
# Deployment script for Report Pro

echo "🚀 Starting deployment..."

# Navigate to app directory
cd /path/to/your/production/report-pro

# Pull latest changes
echo "📥 Pulling latest code from GitHub..."
git pull origin main

# Clear any cache (if applicable)
echo "🧹 Clearing cache..."
# Add your cache clearing commands here if needed
# Example: php artisan cache:clear

echo "✅ Deployment complete!"
echo "🔗 Your app is now live at: https://reportpro.codelocksolutions.com"
