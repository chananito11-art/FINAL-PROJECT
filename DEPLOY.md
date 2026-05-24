Render deployment notes

1. Environment variables (Render web service -> Environment):
   - `APP_ENV=production`
   - `APP_DEBUG=false`
   - `APP_KEY` — set from `php artisan key:generate --show` (keep secret)
   - `LOG_CHANNEL=errorlog`
   - `DB_CONNECTION=mysql`
   - `DB_HOST` — your MySQL host
   - `DB_PORT` — typically `3306`
   - `DB_DATABASE` — database name
   - `DB_USERNAME` — database user
   - `DB_PASSWORD` — database password

2. Recommended Render build & start settings:
   - Build Command: `composer install --no-dev --optimize-autoloader && npm install && npm run build || true`
   - Start Command: leave blank (uses Dockerfile)

3. Deployment steps:
   - Push to your repo `main` branch (already done).
   - In Render dashboard, create or open the Web Service, set env vars above, then click "Manual Deploy" -> "Deploy Latest Commit".

4. Post-deploy checks:
   - Verify logs (Render → Logs) for missing permission errors.
   - Ensure the site loads; if you see DB access denied, confirm DB credentials and that the DB allows connection from Render.

5. Notes:
   - The container includes an entrypoint script that fixes `storage` and `bootstrap/cache` permissions before starting Apache.
   - If your DB is not reachable during build, migrations are skipped at build-time; run migrations via an administrative task or during deployment if you prefer.
