# Quick Start Guide

## Prerequisites Check

Before starting, ensure you have:
- ✅ Node.js 18+ installed (`node --version`)
- ✅ MySQL 8.0+ or MariaDB 10.5+ installed (`mysql --version`)
- ✅ Redis 6+ installed (`redis-cli --version`)
- ✅ Shopify Partner Account with app credentials

## Step-by-Step Setup

### 1. Install Dependencies

```bash
# Install backend dependencies
npm install

# Install frontend dependencies
cd frontend && npm install && cd ..
```

### 2. Start MySQL and Redis

**Option A: Using Docker (Recommended)**
```bash
docker-compose up -d
```

**Option B: Manual Setup**
- Start MySQL service
- Start Redis service

### 3. Configure Environment

**Option A: Using Setup Script (Recommended)**
```bash
node setup-env.js
```

**Option B: Manual Creation**

Copy `.env.example` to `.env`:
```bash
# Windows (PowerShell)
Copy-Item .env.example .env

# Windows (CMD)
copy .env.example .env

# Linux/Mac
cp .env.example .env
```

Then edit `.env` file and update with your actual credentials:

```env
# Server
PORT=3000
NODE_ENV=development

# Shopify
SHOPIFY_API_KEY=your_api_key_here
SHOPIFY_API_SECRET=your_api_secret_here
SHOPIFY_SCOPES=read_orders,read_products,read_customers,read_transactions
SHOPIFY_APP_URL=http://localhost:3000

# Database
DB_HOST=localhost
DB_PORT=3306
DB_NAME=report_pro
DB_USER=root
DB_PASSWORD=password

# Redis
REDIS_HOST=localhost
REDIS_PORT=6379

# JWT
JWT_SECRET=your_secret_key_change_in_production

# Email (for scheduled reports)
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=your_email@gmail.com
SMTP_PASS=your_app_password
```

**Important:** Update the `.env` file with your actual:
- Shopify API credentials
- MySQL database password
- JWT secret (use a strong random string)
- Email credentials (if using scheduled reports)

### 4. Initialize Database

The database tables will be created automatically when you start the server. Alternatively, you can run:

```bash
npm run migrate
```

### 5. Start Development Servers

```bash
# Start both backend and frontend
npm run dev
```

This will start:
- Backend API: http://localhost:3000
- Frontend App: http://localhost:3001

### 6. Access the Application

Open your browser and navigate to:
- Frontend: http://localhost:3001
- API Health Check: http://localhost:3000/health

## Testing the Setup

### Test Database Connection
```bash
mysql -u root -p -e "USE report_pro; SELECT NOW();"
# Or if using Docker:
docker exec -it report-pro-mysql mysql -u root -ppassword -e "USE report_pro; SELECT NOW();"
```

### Test Redis Connection
```bash
redis-cli ping
# Should return: PONG
```

### Test API
```bash
curl http://localhost:3000/health
# Should return: {"status":"ok","timestamp":"..."}
```

## Next Steps

1. **Configure Shopify App**: Set up your Shopify app in the Partner Dashboard
2. **OAuth Setup**: Configure the OAuth callback URL
3. **Install App**: Install the app on a development store
4. **Test Features**: Explore Reports, Schedule, Chart Analysis, and Settings pages

## Troubleshooting

### Database Connection Issues
- Verify MySQL is running: `mysqladmin ping -h localhost -u root -p`
- Check database credentials in `.env` (DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASSWORD)
- Ensure database exists: `mysql -u root -p -e "CREATE DATABASE report_pro;"`
- If using Docker: `docker exec -it report-pro-mysql mysql -u root -ppassword -e "CREATE DATABASE IF NOT EXISTS report_pro;"`

### Redis Connection Issues
- Verify Redis is running: `redis-cli ping`
- Check Redis host/port in `.env`

### Port Already in Use
- Change `PORT` in `.env` for backend
- Change port in `frontend/vite.config.ts` for frontend

### Module Not Found Errors
- Run `npm install` in both root and `frontend/` directories
- Clear `node_modules` and reinstall if needed

## Development Tips

- Backend logs will show in the terminal running `npm run dev:server`
- Frontend hot-reloads automatically on file changes
- Database migrations run automatically on server start
- Use `npm run build` to create production builds

