# PowerShell Script to Clean Git History
# This creates a fresh branch without the commit history containing secrets

Write-Host "Creating fresh branch without secrets..." -ForegroundColor Yellow

# Create new orphan branch (no history)
git checkout --orphan clean-main

# Add all current files
git add .

# Create initial commit
git commit -m "Initial commit - Report Pro Shopify App (credentials in .env only)"

# Delete old main branch
git branch -D main

# Rename current branch to main
git branch -m main

Write-Host "`nFresh branch created successfully!" -ForegroundColor Green
Write-Host "`nNext steps:" -ForegroundColor Yellow
Write-Host "1. Review the changes: git log" -ForegroundColor White
Write-Host "2. Force push: git push origin main --force" -ForegroundColor White
Write-Host "3. Regenerate API secret in Shopify (old one is compromised)" -ForegroundColor White
Write-Host "`n⚠️  WARNING: Force push will overwrite remote history!" -ForegroundColor Red
Write-Host "Make sure no one else is working on this repository." -ForegroundColor Red

