# AGENTS.md

## Cursor Cloud specific instructions

This is a **Laravel 12** app (PHP 8.3, SQLite) — a car-dealership management panel
(Polish-language UI: vehicles, purchases, sales, costs, invoices, contractors,
statistics). Auth scaffolding is Laravel Breeze (Blade + Tailwind + Alpine, built
with Vite). Frontend/build tooling standard commands live in `package.json`;
PHP/dev scripts live in `composer.json` (`scripts` section).

### Running the app
- Dev (all-in-one, concurrent serve + queue + pail + vite): `composer run dev`.
  Or run `php artisan serve` and `npm run dev` separately.
- Seeded login accounts (from `php artisan db:seed`):
  `admin@cars.ie` / `password`, and `info@paytrade.ie` / `paytrade123`.

### NON-OBVIOUS: CSP blocks the Vite dev server in the browser
`app/Http/Middleware/SecurityHeaders.php` sets a Content-Security-Policy that only
allows scripts/styles/connections from `'self'` (plus fonts.bunny.net and the AI /
MotorCheck APIs). The Vite dev server is a different origin (`localhost:5173`), so
when `npm run dev` (HMR) is running, the browser refuses to load the CSS/JS and the
UI renders unstyled (giant SVG icons, no layout).
For browser-based work, use same-origin built assets instead: run `npm run build`
and make sure the Vite dev server is stopped (delete `public/hot` if present) so
Laravel serves `public/build/*` — these satisfy the CSP and the UI renders normally.
(Do not commit a relaxed CSP just to get HMR working.)

### NON-OBVIOUS: pre-existing test failures (not environment issues)
`php artisan test` (or `composer test`) shows ~5 failing tests that are application
bugs unrelated to setup — do not treat them as broken environment:
- `app/Models/User.php` uses `#[Fillable(...)]` / `#[Hidden(...)]` PHP attributes,
  which are **not** a real Eloquent API in Laravel 12. They are silently ignored
  (the `use` is never referenced), so `User` has no fillable fields and
  register / profile-update / password-update tests throw `MassAssignmentException`.
  Only the `User` model is affected; every other model uses a normal `$fillable`.
- `tests/Feature/ExampleTest` expects `/` to return 200, but `/` redirects to auth.

### NON-OBVIOUS: external integrations need API keys
MotorCheck, DealerHub.ie sync, Anthropic Claude AI parsing, and Google Sheets import
require credentials that are not set by default. The vehicle-create form runs a
registration lookup on blur that will fail silently (404/422, "Brak w MotorCheck")
without keys — this is harmless and does not block creating a vehicle manually.

### Notes
- `config.platform.php` in `composer.json` is set to `8.3.6` (deploy target is PHP 8.3
  via cPanel `ea-php83`); the repo's `require-dev` (`phpunit ^12.5.12`) needs PHP >= 8.3.
- Lint: `./vendor/bin/pint` (add `--test` to check without fixing). The repo currently
  has pre-existing style deviations, so `pint --test` reports failures out of the box.
- `.env`, `database/database.sqlite`, and `public/build/` are gitignored.
