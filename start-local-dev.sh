#!/bin/bash
# Bash script to start local development with ngrok
# Usage: ./start-local-dev.sh

echo "🚀 Starting Local Development Setup"
echo ""

# Check if ngrok is installed
if ! command -v ngrok &> /dev/null; then
    echo "❌ ngrok is not installed!"
    echo ""
    echo "Please install ngrok first:"
    echo "1. Download from: https://ngrok.com/download"
    echo "2. Or use: brew install ngrok (Mac)"
    echo "3. Or use: sudo snap install ngrok (Linux)"
    echo ""
    exit 1
fi

echo "✅ ngrok is installed"
echo ""

# Check if .env exists
if [ ! -f .env ]; then
    echo "⚠️  .env file not found. Creating from template..."
    node setup-env.js
fi

echo "📝 Starting ngrok tunnel..."
echo ""
echo "After ngrok starts, you'll see a URL like: https://abc123.ngrok-free.app"
echo "Copy that URL and:"
echo "1. Update .env file: SHOPIFY_APP_URL=https://your-url"
echo "2. Update Shopify app settings with the same URL"
echo "3. Restart your server after updating .env"
echo ""
echo "Press Ctrl+C to stop ngrok"
echo ""

# Start ngrok
ngrok http 3000

