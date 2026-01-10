# 🧹 Clean Git History - Remove Credentials

## Problem

GitHub is blocking pushes because credentials are in Git history from previous commits:
- Commit `d3fe729`: SECURITY_NOTES.md
- Commit `b52b045`: SETUP_CREDENTIALS.md

## ✅ Solution: Remove from Git History

### Option 1: Use GitHub's Allow Secret (Quickest)

1. Visit the URL from the error:
   ```
   https://github.com/codelockinfo/report-pro/security/secret-scanning/unblock-secret/381MutXMwIlJKpLA7uPgP5UY5tb
   ```

2. Click **"Allow secret"** to push this time

3. **⚠️ IMPORTANT**: After allowing, you MUST:
   - Regenerate your API secret in Shopify (old one is compromised)
   - Clean up Git history (see Option 2)
   - Update `.env` file with new secret

### Option 2: Rewrite Git History (Recommended)

#### Step 1: Remove Credentials from Current Files

✅ Already done - all current files are clean

#### Step 2: Rewrite History Using git filter-branch

```bash
# Create a script to remove secrets
git filter-branch --force --index-filter \
  "git rm --cached --ignore-unmatch SETUP_CREDENTIALS.md SECURITY_NOTES.md FIX_GIT_PUSH_ERROR.md CREATE_ENV_FILE.txt" \
  --prune-empty --tag-name-filter cat -- --all

# Force push (if you have permission)
git push origin main --force
```

#### Step 3: Clean Up

```bash
# Remove backup refs
git for-each-ref --format="%(refname)" refs/original/ | xargs -n 1 git update-ref -d

# Garbage collect
git reflog expire --expire=now --all
git gc --prune=now --aggressive
```

### Option 3: Start Fresh Branch (Simplest for New Repos)

If this is a new repository and you don't have important history:

```bash
# Create new orphan branch
git checkout --orphan clean-main

# Add all current files (without history)
git add .
git commit -m "Initial commit - credentials in .env only"

# Replace main branch
git branch -D main
git branch -m main

# Force push
git push origin main --force
```

### Option 4: Use BFG Repo-Cleaner (Most Thorough)

1. Download BFG: https://rtyley.github.io/bfg-repo-cleaner/

2. Create `secrets.txt`:
   ```
   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
   a53fcb46618232fcc1aca1bf585e700d
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

## 🔒 After Cleaning History

**CRITICAL**: Since your secret was exposed:

1. **Regenerate API Secret** in Shopify:
   - Partner Dashboard → App setup → Client credentials
   - Click "Regenerate" for API secret
   - Old secret is compromised

2. **Update `.env` file** with new secret

3. **Test OAuth** with new credentials

## ⚠️ Warning

Force pushing rewrites history. If others are working on this repo:
- Coordinate with your team
- They'll need to re-clone or reset their repos
- Consider the impact before force pushing

## 📝 Current Status

✅ All current files are clean (no credentials)
✅ Credentials removed from documentation
❌ Credentials still in Git history
⏭️ Need to clean history or allow secret temporarily

## 🎯 Recommended Approach

**For quick fix:**
- Use Option 1 (Allow secret) → Regenerate secret → Clean history later

**For permanent fix:**
- Use Option 3 (Fresh branch) if repo is new
- Use Option 4 (BFG) if you need to preserve history

