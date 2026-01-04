# Personal System (Work in Progress)

This Laravel 12 project hosts a personal finance tracker designed for Bluehost shared hosting (PHP 8.2+). It runs without Node/Vite builds and uses Bootstrap via CDN.

## Deployment Target
- Bluehost shared hosting with MySQL administered via phpMyAdmin.
- Application path: `/leo_finances/` (e.g., `https://portillosdesign.com/leo_finances/`).

## Database Setup on Bluehost
1. Open **Advanced** ➜ **MySQL Databases** in the control panel.
2. Create a new database (e.g., `leo_finances`) and a database user with a strong password. Grant the user **ALL PRIVILEGES** on the database.
3. Open **phpMyAdmin** from the control panel and confirm the new database exists. No tables are needed yet—migrations will create them.

## Installation and Configuration
1. Enable SSH access and connect to the server.
2. From the project directory, run `composer install`.
3. Copy `.env.example` to `.env` and set:
   - `APP_KEY` (run `php artisan key:generate --ansi` to fill it)
   - `APP_ENV`, `APP_DEBUG`, `APP_URL`
   - `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` for the MySQL database you created
4. Run migrations: `php artisan migrate --force`.
5. Optionally seed default categories for all users: `php artisan db:seed --class=DefaultCategoriesSeeder --force`.
6. Confirm deployment by visiting `/leo_finances/health` (or `/leo_finances/up`). If routing is still being validated, `/leo_finances/diag.php` remains temporarily available and will be removed once routing is stable.

## Using the App
1. Create a user record in the `users` table (via tinker or phpMyAdmin) with a bcrypt-hashed password.
2. Log in at `/leo_finances/login`.
3. Create accounts, including one **funding** account to mark the income source.
4. For credit cards, first create an account of type `credit_card`, then add the card details and autopay settings.
5. Add categories or rely on the default set (ensured on first dashboard visit or via seeder).
6. Define recurring income, expenses, or transfers with the desired frequency (weekly, biweekly, semimonthly, monthly) and day rules.
7. Generate scheduled items from **Dashboard → Generate schedule** (creates the next 90 days). Upcoming items and totals for the next 30 days appear on the dashboard.

## Bluehost Subfolder Routing
- Ensure the provided `.htaccess` files are deployed so requests route into `public/index.php` under the `/leo_finances/` subfolder.
- If `/leo_finances/health` still returns 404, try `/leo_finances/diag.php` and `/leo_finances/ping.txt` to confirm requests reach the `public/` folder.
