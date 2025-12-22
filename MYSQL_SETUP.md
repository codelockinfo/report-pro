# MySQL Database Setup

This application uses **MySQL 8.0+** as the database. All database queries have been optimized for MySQL syntax.

## Key MySQL Features Used

- **JSON Data Type**: Used for storing configuration objects (query_config, time_config, recipients, config)
- **AUTO_INCREMENT**: For primary key generation
- **FOREIGN KEY Constraints**: With ON DELETE CASCADE
- **Indexes**: For performance optimization
- **DATETIME**: For timestamp fields with automatic updates

## Database Schema

### Tables

1. **shops** - Shopify store information
2. **reports** - Report definitions
3. **schedules** - Scheduled report configurations
4. **settings** - App settings per shop
5. **integrations** - Third-party integrations
6. **charts** - Chart configurations

### Key Differences from PostgreSQL

- Uses `?` placeholders instead of `$1, $2`
- Uses `LIKE` instead of `ILIKE` (case-insensitive with utf8mb4_ci collation)
- Uses `JSON` instead of `JSONB`
- Uses `AUTO_INCREMENT` instead of `SERIAL`
- Uses `DATETIME` instead of `TIMESTAMP`
- No `RETURNING` clause - uses separate SELECT queries

## Connection Configuration

Update your `.env` file with MySQL credentials:

```env
DB_HOST=localhost
DB_PORT=3306
DB_NAME=report_pro
DB_USER=root
DB_PASSWORD=your_password
```

## Using Docker Compose

The `docker-compose.yml` file includes a MySQL 8.0 container:

```bash
docker-compose up -d mysql
```

This will create:
- Database: `report_pro`
- User: `report_user`
- Password: `password` (change in production!)

## Manual MySQL Setup

1. Install MySQL 8.0+
2. Create database:
   ```sql
   CREATE DATABASE report_pro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
3. Create user (optional):
   ```sql
   CREATE USER 'report_user'@'localhost' IDENTIFIED BY 'password';
   GRANT ALL PRIVILEGES ON report_pro.* TO 'report_user'@'localhost';
   FLUSH PRIVILEGES;
   ```

## Migration Notes

The database migrations run automatically on server start. Tables are created with `CREATE TABLE IF NOT EXISTS`, so it's safe to run multiple times.

## Testing Connection

```bash
# Using MySQL CLI
mysql -u root -p -e "USE report_pro; SELECT NOW();"

# Using Docker
docker exec -it report-pro-mysql mysql -u root -ppassword -e "USE report_pro; SELECT NOW();"
```

## Troubleshooting

### Connection Refused
- Verify MySQL is running: `mysqladmin ping -h localhost -u root -p`
- Check port 3306 is not blocked
- Verify credentials in `.env`

### Character Set Issues
- Ensure database uses `utf8mb4` character set
- MySQL 8.0+ uses utf8mb4 by default

### JSON Column Issues
- MySQL 5.7+ supports JSON data type
- For older versions, use TEXT and parse JSON manually

