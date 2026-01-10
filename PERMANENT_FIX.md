# 🔧 Permanent Fix: Remove Secrets from Git History

## The Problem

Credentials are in Git history from these commits:
- `d3fe729`: API_CREDENTIALS_CONFIRMED.md, config/config.php
- `5068c88`: CREATE_ENV_FILE.txt  
- `b52b045`: FIX_GIT_PUSH_ERROR.md

## ✅ Solution: Rewrite Git History

### Option 1: Interactive Rebase (Recommended)

```bash
# Find the oldest commit with secrets (d3fe729)
# We'll rewrite from before that commit

# Start interactive rebase
git rebase -i d3fe729^1

# In the editor that opens:
# - Change "pick" to "edit" for commit d3fe729
# - Change "pick" to "edit" for commit 5068c88
# - Change "pick" to "edit" for commit b52b045
# Save and close

# For each commit, remove the files or edit them:
git rm API_CREDENTIALS_CONFIRMED.md  # If it exists
git rm CREATE_ENV_FILE.txt  # If it exists
# Edit files that still exist
git add config/config.php FIX_GIT_PUSH_ERROR.md
git commit --amend --no-edit
git rebase --continue

# Repeat for each commit

# Force push
git push origin main --force
```

### Option 2: Start Fresh Branch (Easiest)

If you don't have important history to preserve:

```bash
# Create new branch without history
git checkout --orphan clean-main

# Add all current files
git add .
git commit -m "Initial commit - Report Pro Shopify App (credentials in .env only)"

# Replace main branch
git branch -D main
git branch -m main

# Force push (this will overwrite remote)
git push origin main --force
```

### Option 3: Use BFG Repo-Cleaner (Most Thorough)

1. Download BFG: https://rtyley.github.io/bfg-repo-cleaner/

2. Create `secrets.txt`:
   ```
   shpss_YOUR_SECRET_HERE==>REMOVED
   YOUR_API_KEY_HERE==>REMOVED
   ```

3. Run BFG:
   ```bash
   java -jar bfg.jar --replace-text secrets.txt
   git reflog expire --expire=now --all
   git gc --prune=now --aggressive
   ```

4. Force push:
   ```bash
   git push origin main --force
   ```

## ⚠️ Quick Fix (Temporary)

If you need to push NOW:

1. Visit: https://github.com/codelockinfo/report-pro/security/secret-scanning/unblock-secret/381MutXMwIlJKpLA7uPgP5UY5tb
2. Click "Allow secret"
3. Push: `git push origin main`
4. **THEN** regenerate API secret in Shopify (old one is compromised)
5. Clean history later using one of the methods above

## 🔒 After Fixing

**CRITICAL**: Regenerate API secret:
1. Shopify Partner Dashboard → App setup → Client credentials
2. Click "Regenerate" for API secret
3. Update `.env` file with new secret

