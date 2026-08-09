# RentNest

RentNest is a multi-tenant rental management app for landlords and tenants. Landlords manage properties, leases, rent collection, maintenance, documents, expenses, and notices. Tenants track payments, raise maintenance requests, read notices, and download shared documents.

Production URL: [https://rent.websprintx.com](https://rent.websprintx.com)

## Stack

- **Backend:** PHP 8.3+, Laravel 13
- **Frontend:** Vue 3, Inertia.js, Tailwind CSS, Vite
- **Auth:** Laravel Breeze (Inertia)
- **Database:** MySQL
- **Queue:** database driver
- **PDF:** barryvdh/laravel-dompdf (rent receipts)

## Requirements

- PHP 8.3+ with common extensions (pdo_mysql, mbstring, openssl, tokenizer, xml, ctype, json, fileinfo)
- Composer
- MySQL 8+ (WAMP / MySQL service)
- Node.js 20+ and npm

## Local installation (WAMP)

1. **Clone / place the project** under your web root, e.g. `e:\wamp64\www\rentnest`.

2. **Create the database** in phpMyAdmin or MySQL:

   ```sql
   CREATE DATABASE rentnest CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

3. **Install PHP dependencies and env:**

   ```bash
   composer install
   copy .env.example .env
   php artisan key:generate
   ```

4. **Configure `.env` for MySQL** (defaults match RentNest):

   ```env
   APP_NAME=RentNest
   APP_URL=http://localhost:8000

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=rentnest
   DB_USERNAME=root
   DB_PASSWORD=

   QUEUE_CONNECTION=database
   ```

5. **Migrate and seed demo data:**

   ```bash
   php artisan migrate --seed
   ```

   Fresh reset (destructive):

   ```bash
   php artisan migrate:fresh --seed
   ```

6. **Frontend assets:**

   ```bash
   npm install
   npm run build
   ```

7. **Run the app** — pick one:

   - Full local stack (HTTP + queue + Vite + logs):

     ```bash
     composer run dev
     ```

   - Or separately:

     ```bash
     php artisan serve
     npm run dev
     php artisan queue:work
     ```

Open `http://localhost:8000` (or your WAMP vhost) and sign in with a demo account.

## Demo accounts

| Role     | Email                   | Password   |
|----------|-------------------------|------------|
| Landlord | `landlord@rentnest.test` | `password` |
| Tenant   | `tenant@rentnest.test`   | `password` |

Demo organization: **WebSprintX Properties** (`websprintx-properties`), timezone `Asia/Kolkata`, currency `INR`. Seeded data includes an occupied 1BHK in Derabassi, an active lease, rent history, a plumbing maintenance ticket, documents, notices, an expense, and sample notifications.

## Registration / invite-only tenants

Public self-registration is disabled. Tenants are invite-only: landlords create tenant profiles (and optional login users) from the Tenants area. Use the seeded accounts above for local demos.

## Architecture notes

- **Organization-scoped SaaS-ready:** almost all business records belong to an `organizations` row. Users join via `organization_user` with a role (`landlord` | `tenant`) and `users.current_organization_id` selects the active context.
- Middleware `organization` + `role:landlord|tenant` guard portal routes.
- Models use `BelongsToOrganization` / `forOrganization()` for consistent scoping.
- Designed so multiple organizations can share one deployment later without rewriting domain tables.

## Queue

Default queue connection is **database** (`QUEUE_CONNECTION=database`). Jobs and queued notifications use the `jobs` table from Laravel’s default migrations.

```bash
php artisan queue:work
# or, with composer run dev, queue:listen is already started
```

## Scheduler

Rent reminders and lease-expiry checks are intended to run on the Laravel scheduler.

**Local development:**

```bash
php artisan schedule:work
```

**Production (cron):**

```cron
* * * * * cd /path/to/rentnest && php artisan schedule:run >> /dev/null 2>&1
```

Scheduled commands (when present):

| Command | Purpose |
|---------|---------|
| `rentnest:send-rent-reminders` | Daily rent due / overdue reminders |
| `rentnest:check-lease-expiry` | Mark leases expiring within 60 days and notify |

## Key Artisan commands

```bash
php artisan migrate --seed          # apply migrations + DemoSeeder
php artisan migrate:fresh --seed    # wipe DB and reseed
php artisan db:seed                 # run seeders only
php artisan db:seed --class=DemoSeeder

php artisan serve                   # local HTTP server
php artisan queue:work              # process queued jobs
php artisan schedule:work           # run scheduler loop locally
php artisan schedule:run            # single scheduler tick (cron)

php artisan storage:link            # public disk symlink (if needed)
php artisan config:cache            # production config cache
php artisan route:list              # inspect routes
```

## Project layout (high level)

```
app/
  Enums/                 # backed string enums (status, type, role, …)
  Http/Controllers/      # Landlord + Tenant + Auth
  Models/                # Organization-scoped domain models
  Notifications/         # Mail + database notifications
  Policies/              # Authorization
database/
  migrations/
  seeders/DemoSeeder.php
resources/js/
  Pages/Landlord|Tenant|Auth/
  Layouts/
  Components/ui/
routes/
  web.php                # landlord + shared
  tenant.php
  auth.php
  console.php            # scheduler
```

## License

Proprietary — WebSprintX / RentNest.
