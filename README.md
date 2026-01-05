# Tracer Study (CodeIgniter 4)

This repository contains a starter CodeIgniter 4 application for the Tracer Study project.

## Requirements
- PHP 8.1+
- Composer
- Database (MySQL/MariaDB recommended)
- Redis (optional, if you switch the session handler)

## Getting started
1. Install dependencies:
   ```bash
   composer install
   ```
2. Copy the environment file (this repo already ships with `.env`) and adjust database credentials, base URL, timezone, and session handler settings.
3. Run the database migrations:
   ```bash
   php spark migrate
   ```
4. Seed the base data (roles, permissions, Super Admin account):
   ```bash
   php spark db:seed DatabaseSeeder
   ```
5. Serve the application:
   ```bash
   php spark serve
   ```

## Notes
- The Super Admin account created by the seeder uses:
  - Email: `superadmin@example.com`
  - Password: `SuperAdmin@123`
- Session storage defaults to the database using the `ci_sessions` table; switch to Redis by updating `app.sessionDriver` and `app.sessionSavePath` in `.env`.
