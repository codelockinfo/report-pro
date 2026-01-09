# Fix: Redirect URLs Error

## ❌ Error Message

```
"https://reportpro.codelocksolutions.com/oauth_callback.php https://reportpro.codelocksolutions.com/auth/callback" is not a valid HTTP URL
```

## ✅ Solution

Shopify requires redirect URLs to be **comma-separated**, NOT on separate lines.

### Wrong Format (Causes Error):
```
https://reportpro.codelocksolutions.com/oauth_callback.php
https://reportpro.codelocksolutions.com/auth/callback
```

### Correct Format:
```
https://reportpro.codelocksolutions.com/oauth_callback.php,https://reportpro.codelocksolutions.com/auth/callback
```

## 📝 Step-by-Step Fix

1. **Clear the input field** (remove all URLs)

2. **Enter both URLs on ONE line, separated by a comma** (no spaces):
   ```
   https://reportpro.codelocksolutions.com/oauth_callback.php,https://reportpro.codelocksolutions.com/auth/callback
   ```

3. **Make sure:**
   - ✅ No line breaks
   - ✅ No spaces after the comma
   - ✅ Both URLs use HTTPS
   - ✅ No trailing slashes

4. **Click Save** - The error should disappear

## 🎯 Quick Copy-Paste

Copy this exact text into the Redirect URLs field:

```
https://reportpro.codelocksolutions.com/oauth_callback.php,https://reportpro.codelocksolutions.com/auth/callback
```

## ⚠️ Common Mistakes

1. ❌ **Line breaks** - Shopify doesn't accept multi-line input
2. ❌ **Spaces after comma** - Must be: `url1,url2` not `url1, url2`
3. ❌ **Missing comma** - URLs must be separated by comma
4. ❌ **HTTP instead of HTTPS** - Must use HTTPS

## ✅ Verification

After entering correctly, you should see:
- ✅ No error message
- ✅ Both URLs visible in the field
- ✅ Field border is not red

