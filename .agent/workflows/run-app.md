---
description: How to run the Gerindra Event Management application locally
---

# Running the Application

## Prerequisites

-   PHP 8.2+
-   Composer
-   MySQL 8
-   Redis Server
-   Laragon (recommended for Windows)

## Steps

// turbo

1. Navigate to project directory

```bash
cd d:/laragon/www/Gerindra
```

// turbo 2. Install dependencies (if not already installed)

```bash
composer install
```

// turbo 3. Create database (if not exists)

```bash
mysql -u root -e "CREATE DATABASE IF NOT EXISTS gerindra_event"
```

// turbo 4. Run migrations and seeders

```bash
php artisan migrate:fresh --seed
```

// turbo 5. Create storage link for public files

```bash
php artisan storage:link
```

// turbo 6. Start the development server

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

7. Access the application at `http://localhost:8000` or `http://gerindra.test` (if Laragon virtual host is configured)

## Running Queue Worker (for background jobs)

```bash
php artisan queue:work
```

## Troubleshooting

### Clear all caches

```bash
php artisan optimize:clear
```

### Regenerate autoload

```bash
composer dump-autoload
```
