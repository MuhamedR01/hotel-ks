# Hotel KS - Coolify Deployment Guide

## Architecture

3 separate Coolify applications + 1 Coolify MySQL resource, all on the same server:

| Application   | Domain                   | Source              | Base Directory    |
| ------------- | ------------------------ | ------------------- | ----------------- |
| **MySQL**     | (internal)               | Coolify DB resource | —                 |
| **API**       | `api.hotel-ks.com`       | Same git repo       | `laravel-backend` |
| **Dashboard** | `dashboard.hotel-ks.com` | Same git repo       | `laravel-backend` |
| **Frontend**  | `hotel-ks.com`           | Same git repo       | `frontend`        |

Coolify automatically puts all resources on the `coolify` Docker network, so they can reach each other by hostname.

---

## Step 1: Create the MySQL Database in Coolify

1. Go to **Projects → your project → + New → Database → MySQL**
2. Note the auto-generated **hostname** (e.g. `zw4kw8w88o8o88kwo48w0wsw`)
3. Set a strong root password
4. The default database is `default` and user is `mysql` — that's fine

---

## Step 2: Create the API Application

1. **+ New → Application** (Docker Based)
2. Connect your git repo
3. Set:
   - **Base Directory**: `laravel-backend`
   - **Dockerfile Location**: `Dockerfile`
   - **Domain**: `api.hotel-ks.com`
   - **Port**: `80`

4. **Environment Variables** (paste all):

```env
SERVICE_ROLE=api
APP_NAME=Hotel KS API
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:GENERATE_WITH_php_artisan_key_generate_--show
APP_URL=https://api.hotel-ks.com
APP_LOCALE=sq

DB_CONNECTION=mysql
DB_HOST=YOUR_MYSQL_HOSTNAME_FROM_STEP1
DB_PORT=3306
DB_DATABASE=default
DB_USERNAME=mysql
DB_PASSWORD=YOUR_MYSQL_PASSWORD_FROM_STEP1

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

SANCTUM_STATEFUL_DOMAINS=hotel-ks.com,dashboard.hotel-ks.com
FRONTEND_URL=https://hotel-ks.com
ADMIN_DEFAULT_PASSWORD=YourSecureAdminPassword123!
```

5. Deploy. Check logs — you should see migrations running successfully.

---

## Step 3: Create the Dashboard Application

1. **+ New → Application** (Docker Based)
2. Connect the same git repo
3. Set:
   - **Base Directory**: `laravel-backend`
   - **Dockerfile Location**: `Dockerfile`
   - **Domain**: `dashboard.hotel-ks.com`
   - **Port**: `80`

4. **Environment Variables** (paste all):

```env
SERVICE_ROLE=dashboard
APP_NAME=Hotel KS Dashboard
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:SAME_KEY_AS_API
APP_URL=https://dashboard.hotel-ks.com
APP_LOCALE=sq

DASHBOARD_PATH=

DB_CONNECTION=mysql
DB_HOST=YOUR_MYSQL_HOSTNAME_FROM_STEP1
DB_PORT=3306
DB_DATABASE=default
DB_USERNAME=mysql
DB_PASSWORD=YOUR_MYSQL_PASSWORD_FROM_STEP1

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

SANCTUM_STATEFUL_DOMAINS=hotel-ks.com,dashboard.hotel-ks.com
FRONTEND_URL=https://hotel-ks.com
```

> **Important**: `DASHBOARD_PATH=` (empty) makes the dashboard routes live at `/` on its own domain.
> The `APP_KEY` must be the **same** as the API so they can share encrypted sessions/data.

5. Deploy. The dashboard waits for the DB (no migrations — API handles that).

---

## Step 4: Create the Frontend Application

1. **+ New → Application** (Docker Based)
2. Connect the same git repo
3. Set:
   - **Base Directory**: `frontend`
   - **Dockerfile Location**: `Dockerfile`
   - **Domain**: `hotel-ks.com`
   - **Port**: `80`

4. No environment variables needed. The API URL is read from `frontend/.env.production` at build time.
5. Deploy.

---

## Step 5: Verify

- `https://hotel-ks.com` — React SPA loads, products fetch from API
- `https://api.hotel-ks.com/api/products` — returns JSON product list
- `https://dashboard.hotel-ks.com/login` — admin login page

Default admin login: `admin` / your `ADMIN_DEFAULT_PASSWORD`

---

## Troubleshooting

**API can't reach MySQL**: Both must be in the same Coolify project. Coolify auto-connects them on the `coolify` network. Verify the `DB_HOST` matches the MySQL resource hostname.

**Dashboard 500 error**: Ensure `APP_KEY` is identical to the API's key. Check logs with Coolify's log viewer.

**CORS errors on frontend**: `FRONTEND_URL` must be `https://hotel-ks.com` (exact origin). Check the API logs.

**Migrations fail**: The API entrypoint retries 30 times (5s apart). If MySQL is still starting, it will catch up. If it keeps failing, check `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.

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
