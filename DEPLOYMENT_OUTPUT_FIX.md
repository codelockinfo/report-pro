# Deployment Output Directory Fix

## Problem
Build completes successfully but deployment platform shows:
```
ERROR: No output directory found after build
```

## Root Cause
The deployment platform expects build output in a specific location (likely root `dist/` folder), but our project has:
- `server/dist/` - Backend build output
- `web/dist/` - Frontend build output

## Solution Options

### Option 1: Create root dist folder (Recommended)
Create a build script that copies outputs to root `dist/` folder that deployment platforms expect.

### Option 2: Configure deployment platform
Tell the deployment platform where the output is located (e.g., `server/dist/`).

### Option 3: Build everything to root dist
Modify build process to output everything to a root `dist/` folder.

