const { execSync } = require('child_process');
const fs = require('fs');
const path = require('path');

console.log('📦 Installing dependencies...\n');

// Install root dependencies
console.log('1️⃣ Installing backend dependencies...');
try {
  execSync('npm install', { stdio: 'inherit', cwd: __dirname + '/..' });
  console.log('✅ Backend dependencies installed\n');
} catch (error) {
  console.error('❌ Failed to install backend dependencies');
  process.exit(1);
}

// Install frontend dependencies
const frontendPath = path.join(__dirname, '..', 'frontend');
const frontendNodeModules = path.join(frontendPath, 'node_modules');

console.log('2️⃣ Installing frontend dependencies...');
if (!fs.existsSync(frontendNodeModules)) {
  try {
    execSync('npm install', { stdio: 'inherit', cwd: frontendPath });
    console.log('✅ Frontend dependencies installed\n');
  } catch (error) {
    console.error('❌ Failed to install frontend dependencies');
    process.exit(1);
  }
} else {
  console.log('ℹ️  Frontend dependencies already installed\n');
}

console.log('✅ All dependencies installed successfully!');

