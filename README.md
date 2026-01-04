# Personal System (Work in Progress)

This repository is being prepared for a future PHP + MySQL personal system. Detailed requirements will be defined in [`docs/REQUIREMENTS.md`](docs/REQUIREMENTS.md) before any application code is generated.

## Deployment Target
- Bluehost shared hosting with MySQL administered via phpMyAdmin.

## Next Steps
- Capture requirements in the template before implementing features or database structures.

## Bluehost Shared Hosting Setup
1. Set the PHP version to 8.2 or newer (8.3 recommended when available).
2. Enable SSH access from your Bluehost control panel.
3. From the project directory, run `composer install`.
4. Copy `.env.example` to `.env` and configure `APP_KEY`, `APP_ENV`, `APP_DEBUG`, `APP_URL`, and MySQL credentials (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).
5. Run `php artisan key:generate` and `php artisan migrate`.
6. Confirm the deployment by visiting `/health` in your browser to see `ok`.

## Bluehost Subfolder Routing
- The application is deployed under `/leo_finances/` (e.g., `https://portillosdesign.com/leo_finances/`).
- Ensure the provided `.htaccess` files are deployed so requests route into `public/index.php`.
- If `/leo_finances/health` still returns 404, try `/leo_finances/diag.php` and `/leo_finances/ping.txt` to confirm requests reach the `public/` folder.
