const fs = require('fs');
const path = require('path');

const envExampleContent = `# Server Configuration
PORT=3000
NODE_ENV=development

# Shopify App Configuration
SHOPIFY_API_KEY=your_shopify_api_key
SHOPIFY_API_SECRET=your_shopify_api_secret
SHOPIFY_SCOPES=read_orders,read_transactions,read_products,read_customers,read_inventory,read_locations,write_webhooks
SHOPIFY_APP_URL=https://your-app-url.com

# MySQL Database Configuration
DB_HOST=localhost
DB_PORT=3306
DB_NAME=report_pro
DB_USER=root
DB_PASSWORD=password

# Redis Configuration
REDIS_HOST=localhost
REDIS_PORT=6379
REDIS_PASSWORD=

# JWT Secret
JWT_SECRET=your_jwt_secret_key_change_in_production

# Email Configuration (for scheduled reports)
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=your_email@gmail.com
SMTP_PASS=your_app_password

# App Settings
APP_NAME=Better Reports
APP_URL=http://localhost:3000
`;

const envExamplePath = path.join(__dirname, '.env.example');
const envPath = path.join(__dirname, '.env');

// Create .env.example if it doesn't exist
if (!fs.existsSync(envExamplePath)) {
  fs.writeFileSync(envExamplePath, envExampleContent);
  console.log('✅ Created .env.example file');
} else {
  console.log('ℹ️  .env.example already exists');
}

// Create .env if it doesn't exist
if (!fs.existsSync(envPath)) {
  fs.writeFileSync(envPath, envExampleContent);
  console.log('✅ Created .env file');
  console.log('⚠️  Please update .env with your actual credentials!');
} else {
  console.log('ℹ️  .env already exists - skipping creation');
}

