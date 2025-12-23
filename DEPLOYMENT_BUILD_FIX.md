# Deployment Build Fix - Final Solution

## Problem
The deployment was failing with:
- `tsc: command not found` 
- Or `npx tsc` trying to install wrong package

## Root Cause
During deployment, TypeScript might not be installed or accessible. The `npx tsc` command was trying to install a package called `tsc` instead of using TypeScript from the `typescript` package.

## Solution
Use `tsc` directly in npm scripts. npm automatically adds `node_modules/.bin` to PATH for scripts, so `tsc` will work if TypeScript is installed.

Additionally, added `prebuild` hooks to ensure dependencies are installed before building.

## Changes Made

### server/package.json
```json
{
  "scripts": {
    "prebuild": "npm install",  // Ensures deps are installed
    "build": "tsc"              // Uses tsc directly (npm handles PATH)
  }
}
```

### web/package.json
```json
{
  "scripts": {
    "prebuild": "npm install",  // Ensures deps are installed
    "build": "tsc && vite build" // Uses tsc directly
  }
}
```

### package.json (root)
```json
{
  "scripts": {
    "build:server": "cd server && npm install && npm run build",
    "build:client": "cd web && npm install && npm run build",
    "prebuild": "npm run install:all"  // Ensures all deps are installed
  }
}
```

## Why This Works

1. **npm scripts automatically handle PATH**: When you run `tsc` in an npm script, npm automatically adds `node_modules/.bin` to PATH
2. **prebuild hooks ensure installation**: The `prebuild` hooks run `npm install` to ensure all dependencies (including TypeScript) are installed before building
3. **Defensive programming**: Multiple levels of `npm install` ensure dependencies are available

## Deployment Checklist

✅ Ensure your deployment platform runs `npm install` before build  
✅ Or the `prebuild` hooks will handle it automatically  
✅ TypeScript is in `devDependencies` - make sure devDependencies are installed during build  
✅ The build command should be: `npm run build`

## Notes

- TypeScript is in `devDependencies` - it's needed for building but not for running
- Some platforms skip devDependencies by default - you may need to enable them
- The `prebuild` hooks are defensive - they ensure dependencies are installed even if the platform doesn't

