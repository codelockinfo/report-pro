# PowerShell script to start local development with ngrok
# Usage: .\start-local-dev.ps1

Write-Host "🚀 Starting Local Development Setup" -ForegroundColor Green
Write-Host ""

# Check if ngrok is installed
if (-not (Get-Command ngrok -ErrorAction SilentlyContinue)) {
    Write-Host "❌ ngrok is not installed!" -ForegroundColor Red
    Write-Host ""
    Write-Host "Please install ngrok first:" -ForegroundColor Yellow
    Write-Host "1. Download from: https://ngrok.com/download" -ForegroundColor Cyan
    Write-Host "2. Or use: choco install ngrok" -ForegroundColor Cyan
    Write-Host "3. Or use: scoop install ngrok" -ForegroundColor Cyan
    Write-Host ""
    exit 1
}

Write-Host "✅ ngrok is installed" -ForegroundColor Green
Write-Host ""

# Check if .env exists
if (-not (Test-Path .env)) {
    Write-Host "⚠️  .env file not found. Creating from template..." -ForegroundColor Yellow
    node setup-env.js
}

Write-Host "📝 Starting ngrok tunnel..." -ForegroundColor Cyan
Write-Host ""
Write-Host "After ngrok starts, you'll see a URL like: https://abc123.ngrok-free.app" -ForegroundColor Yellow
Write-Host "Copy that URL and:" -ForegroundColor Yellow
Write-Host "1. Update .env file: SHOPIFY_APP_URL=https://your-url" -ForegroundColor Yellow
Write-Host "2. Update Shopify app settings with the same URL" -ForegroundColor Yellow
Write-Host "3. Restart your server after updating .env" -ForegroundColor Yellow
Write-Host ""
Write-Host "Press Ctrl+C to stop ngrok" -ForegroundColor Gray
Write-Host ""

# Start ngrok
ngrok http 3000

