# Hotel KS - Setup Guide

## 📋 Prerequisites

- PHP 7.4 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Node.js 14+ and npm
- Web server (Apache/Nginx)
- Composer (optional, for PHP dependencies)

## 🐳 Docker deployment (Coolify / docker-compose)

If you run the app with Docker (`docker-compose` or Coolify), the API and dashboard need correct database credentials from a **root-level `.env`** (next to `docker-compose.yml`).

### 1. Create `.env` at project root

```bash
cd hotel-ks
cp .env.example .env
```

Edit `.env` and set at least:

- **`DB_DATABASE`** – e.g. `hotel_ks`
- **`DB_USERNAME`** – e.g. `hotel_ks` (MySQL will create this user)
- **`DB_PASSWORD`** – a strong password (used for both root and this user)
- **`APP_KEY`** – Laravel app key (e.g. run `php artisan key:generate --show` in `laravel-backend` and paste the value)
- **`ADMIN_DEFAULT_PASSWORD`** – default password for the dashboard admin

### 2. Reset MySQL if you already ran with wrong credentials

If you previously started the stack **without** a proper `.env` (or with different `DB_*` values), the MySQL volume may have been created with user `mysql` and database `default`, which causes "Access denied". Remove the volume and start again:

```bash
docker compose down -v
docker compose up -d
```

Then run migrations inside the API container:

```bash
docker compose exec api php artisan migrate
```

(Optional) Generate a new `APP_KEY` from the Laravel backend and put it in the root `.env`:

```bash
cd laravel-backend && php artisan key:generate --show
```

## 🚀 Installation Steps

### 1. Clone the Repository

```bash
git clone https://github.com/MuhamedR01/hotel-ks.git
cd hotel-ks
```

### 2. Configure Database Connection

```bash
# Copy the example configuration
cp backend/config.example.php backend/config.php

# Edit with your actual database credentials
nano backend/config.php
```

Update these values in `backend/config.php`:
- `DB_HOST`: Your database host (usually `localhost`)
- `DB_USER`: Your MySQL username
- `DB_PASS`: Your MySQL password
- `DB_NAME`: Your database name
- `ALLOWED_ORIGIN`: Your frontend URL (e.g., `https://yourdomain.com`)

### 3. Create and Setup Database

```bash
# Create the database
mysql -u root -p -e "CREATE DATABASE your_database_name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Import the schema
mysql -u root -p your_database_name < database/schema.sql
```

### 4. Install Frontend Dependencies

```bash
cd frontend
npm install
```

### 5. Configure Frontend Environment

Create `frontend/.env`:
```env
VITE_API_BASE_URL=http://localhost/backend
```

For production, use your actual backend URL.

### 6. Build Frontend (Production)

```bash
npm run build
```

Or for development:
```bash
npm run dev
```

### 7. Configure Web Server

#### Apache
Point your DocumentRoot to the project root directory. The frontend build will be in `frontend/dist`.

Example virtual host configuration:
```apache
<VirtualHost *:80>
    ServerName yoursite.com
    DocumentRoot /path/to/hotel-ks
    
    <Directory /path/to/hotel-ks>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

#### Nginx
```nginx
server {
    listen 80;
    server_name yoursite.com;
    root /path/to/hotel-ks;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 8. Create Initial Admin Account

Access the dashboard setup page (create a temporary setup script or use direct database insert):

```sql
-- Create admin user with password 'your_secure_password'
-- First, generate password hash in PHP: password_hash('your_secure_password', PASSWORD_DEFAULT);

INSERT INTO admins (username, password, name, email, role, created_at) 
VALUES (
    'admin', 
    '$2y$10$YourPasswordHashHere',
    'Administrator',
    'admin@yoursite.com',
    'super_admin',
    NOW()
);
```

### 9. Set Proper Permissions

```bash
# Make sure web server can read files
chmod -R 755 .

# Protect sensitive files
chmod 600 backend/config.php

# Make sure uploads directory is writable (if you have one)
chmod -R 775 uploads/
```

## 🔒 Security Best Practices

### Post-Installation Security

1. **Change Default Credentials**
   - Change the admin password immediately after first login
   - Use strong, unique passwords

2. **Delete Setup Files**
   - Remove any setup/installation scripts after initial setup
   - Delete `database/schema.sql` from production server

3. **Enable HTTPS**
   - Always use HTTPS in production
   - Update `ALLOWED_ORIGIN` in config to use https://

4. **File Permissions**
   - Config files: 600 (readable only by owner)
   - PHP files: 644
   - Directories: 755

5. **Database Security**
   - Use strong database passwords
   - Create a dedicated database user with limited privileges
   - Never use root user for the application

6. **Regular Updates**
   - Keep PHP, MySQL, and dependencies updated
   - Monitor security advisories

## 🧪 Testing the Installation

1. **Test Backend**
   - Visit: `http://yoursite.com/backend/products.php`
   - Should return JSON data

2. **Test Frontend**
   - Visit: `http://yoursite.com`
   - Should load the homepage

3. **Test Admin Dashboard**
   - Visit: `http://yoursite.com/dashboard`
   - Login with your admin credentials

## 🐛 Troubleshooting

### Database Connection Error
- Verify credentials in `backend/config.php`
- Check if MySQL service is running
- Ensure database exists

### 403 Forbidden Error
- Check file permissions
- Verify web server configuration
- Check `.htaccess` files

### CORS Errors
- Update `ALLOWED_ORIGIN` in `backend/config.php`
- Ensure frontend and backend URLs match

### Can't Login to Dashboard
- Verify admin user exists in database
- Check password hash is correct
- Clear browser cookies/cache

## 📞 Support

For issues, please open an issue on GitHub: https://github.com/MuhamedR01/hotel-ks/issues
