# Cleanup Old Folders

After verifying the new structure works correctly, you can safely delete these old folders:

## Old Folders (Can be deleted)

1. **`frontend/`** - Old frontend code (now in `web/`)
2. **`src/`** - Old backend code (now in `server/src/`)

## How to Clean Up

### Windows (PowerShell)
```powershell
Remove-Item -Recurse -Force frontend
Remove-Item -Recurse -Force src
```

### Linux/Mac
```bash
rm -rf frontend src
```

## ⚠️ Important

**DO NOT DELETE** until you have:
1. ✅ Verified `npm run build` works
2. ✅ Verified `npm start` works
3. ✅ Tested the app loads correctly
4. ✅ Verified all functionality works

The backup is in `C:\Codelock\report-pro-backup` if you need to restore anything.

