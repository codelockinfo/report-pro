# Deployment Notes

## Build Script Fix

The build scripts have been updated to use `npx tsc` instead of `tsc` to fix the "tsc: command not found" error during deployment.

## What Changed

- ✅ `server/package.json`: `"build": "tsc"` → `"build": "npx tsc"`
- ✅ `web/package.json`: `"build": "tsc && vite build"` → `"build": "npx tsc && vite build"`

## Why This Works

- `npx` automatically finds executables in `node_modules/.bin/`
- Works even if TypeScript is not installed globally
- This is the standard approach for npm scripts

## Deployment Checklist

1. ✅ Ensure `npm install` is run (installs all dependencies including devDependencies)
2. ✅ Build step: `npm run build` (this installs and builds both server and web)
3. ✅ Start step: `npm start` (runs the built server)

## Important Notes

- **devDependencies are required for build**: TypeScript is in devDependencies and is needed for building
- Some deployment platforms skip devDependencies by default
- Make sure your deployment platform installs devDependencies during the build phase
- After build, the `dist/` folders contain the compiled code (no TypeScript needed to run)

## Deployment Platform Configuration

If your deployment platform has a setting to skip devDependencies, you may need to:
- Enable "Install devDependencies" or "Install all dependencies" option
- Or ensure TypeScript is available during build (it's needed to compile .ts files)

