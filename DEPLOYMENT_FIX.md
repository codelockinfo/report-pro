# Deployment Fix

## Issue
`tsc: command not found` error during deployment build.

## Solution
Updated build scripts to use `npx tsc` instead of `tsc` directly. This ensures the local TypeScript from `node_modules` is used even if TypeScript is not installed globally.

## Changes Made
- `server/package.json`: Changed `"build": "tsc"` to `"build": "npx tsc"`
- `web/package.json`: Changed `"build": "tsc && vite build"` to `"build": "npx tsc && vite build"`

## Why This Works
- `npx` automatically finds and runs executables from `node_modules/.bin/`
- This works even if TypeScript is not installed globally
- This is the recommended approach for npm scripts

## Additional Notes
If you're still having issues, make sure:
1. `npm install` has been run (which installs TypeScript as a devDependency)
2. The deployment platform installs devDependencies (some platforms skip them for production)
3. If devDependencies are skipped, you may need to ensure TypeScript is available during the build phase

