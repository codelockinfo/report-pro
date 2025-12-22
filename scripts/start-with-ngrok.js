const { spawn } = require('child_process');
const ngrok = require('ngrok');
const fs = require('fs');
const path = require('path');
require('dotenv').config();

(async function() {
  try {
    console.log('🚀 Starting development server with ngrok...\n');

    // Start your server
    const server = spawn('npm', ['run', 'dev:server'], { 
      stdio: 'inherit',
      shell: true 
    });
    
    // Wait a bit for server to start
    await new Promise(resolve => setTimeout(resolve, 2000));
    
    // Start ngrok
    console.log('🌐 Starting ngrok tunnel...\n');
    const url = await ngrok.connect({
      addr: 3000,
      authtoken: process.env.NGROK_AUTH_TOKEN || undefined,
    });
    
    console.log(`\n✅ ngrok tunnel established!`);
    console.log(`📍 Public URL: ${url}\n`);
    
    // Update .env file
    const envPath = path.join(__dirname, '..', '.env');
    let envContent = fs.readFileSync(envPath, 'utf8');
    
    // Update SHOPIFY_APP_URL
    if (envContent.includes('SHOPIFY_APP_URL=')) {
      envContent = envContent.replace(
        /SHOPIFY_APP_URL=.*/,
        `SHOPIFY_APP_URL=${url}`
      );
    } else {
      envContent += `\nSHOPIFY_APP_URL=${url}\n`;
    }
    
    fs.writeFileSync(envPath, envContent);
    console.log('✅ Updated .env file with ngrok URL\n');
    
    console.log('📝 Update your Shopify app settings:\n');
    console.log(`   App URL: ${url}`);
    console.log(`   Callback URL: ${url}/api/auth/shopify/callback\n`);
    console.log('⚠️  Press Ctrl+C to stop\n');
    
    // Handle cleanup
    process.on('SIGINT', async () => {
      console.log('\n\n🛑 Shutting down...');
      await ngrok.kill();
      server.kill();
      process.exit();
    });
    
    process.on('SIGTERM', async () => {
      await ngrok.kill();
      server.kill();
      process.exit();
    });
    
  } catch (error) {
    console.error('❌ Error:', error);
    process.exit(1);
  }
})();

