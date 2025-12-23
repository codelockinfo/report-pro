#!/usr/bin/env node
/**
 * Build Output Script
 * Copies build outputs to root dist/ folder for deployment platforms
 */

const fs = require('fs');
const path = require('path');

const rootDir = __dirname;
const serverDist = path.join(rootDir, 'server', 'dist');
const webDist = path.join(rootDir, 'web', 'dist');
const outputDir = path.join(rootDir, 'dist');

// Create output directory structure
const serverOutput = path.join(outputDir, 'server');
const webOutput = path.join(outputDir, 'web');

function copyRecursiveSync(src, dest) {
  const exists = fs.existsSync(src);
  const stats = exists && fs.statSync(src);
  const isDirectory = exists && stats.isDirectory();

  if (isDirectory) {
    if (!fs.existsSync(dest)) {
      fs.mkdirSync(dest, { recursive: true });
    }
    fs.readdirSync(src).forEach((childItemName) => {
      copyRecursiveSync(
        path.join(src, childItemName),
        path.join(dest, childItemName)
      );
    });
  } else {
    const destDir = path.dirname(dest);
    if (!fs.existsSync(destDir)) {
      fs.mkdirSync(destDir, { recursive: true });
    }
    fs.copyFileSync(src, dest);
  }
}

try {
  console.log('📦 Copying build outputs to root dist/ folder...');

  // Remove old dist if exists
  if (fs.existsSync(outputDir)) {
    fs.rmSync(outputDir, { recursive: true, force: true });
  }

  // Create output directories
  fs.mkdirSync(outputDir, { recursive: true });
  fs.mkdirSync(serverOutput, { recursive: true });
  fs.mkdirSync(webOutput, { recursive: true });

  // Copy server dist
  if (fs.existsSync(serverDist)) {
    console.log('  → Copying server/dist to dist/server/');
    copyRecursiveSync(serverDist, serverOutput);
  } else {
    console.warn('  ⚠️  server/dist not found');
  }

  // Copy web dist
  if (fs.existsSync(webDist)) {
    console.log('  → Copying web/dist to dist/web/');
    copyRecursiveSync(webDist, webOutput);
  } else {
    console.warn('  ⚠️  web/dist not found');
  }

  // Create a package.json in dist for deployment platforms
  const distPackageJson = {
    name: 'report-pro-dist',
    version: '1.0.0',
    main: 'server/server.js',
    scripts: {
      start: 'node server/server.js'
    }
  };
  fs.writeFileSync(
    path.join(outputDir, 'package.json'),
    JSON.stringify(distPackageJson, null, 2)
  );

  console.log('✅ Build outputs copied to dist/ folder');
  console.log(`   - Server: dist/server/`);
  console.log(`   - Web: dist/web/`);
} catch (error) {
  console.error('❌ Error copying build outputs:', error);
  process.exit(1);
}

