# USM PANTAS — Library Management System

Laravel application for cataloging, circulation, patron registration, attendance scanning, room reservations, e-resources, and staff administration.

**Repository:** [github.com/borskenetic/usm](https://github.com/borskenetic/usm)

## Stack

| Layer | Technology |
| --- | --- |
| Backend | PHP 8.2+, Laravel 12 |
| Legacy UI | Blade, Bootstrap 5, Alpine, jQuery, Font Awesome |
| Modern UI | Inertia.js + React, shadcn/ui (Radix), Tailwind CSS 4 |
| Assets | Vite |

Admin and staff pages use a shared **React admin shell** (sidebar, header, breadcrumbs, notifications). Page content is still mostly Blade/Bootstrap and is being migrated to Inertia + React page-by-page. The book catalog (`/books`) is the first Inertia screen.

Design tokens and shadcn theme vars live in `resources/css/app.css` and `resources/css/usm-brand.css`. Preview components at `/design-system` (local dev only).

## Requirements

- PHP 8.2+ with common Laravel extensions (`pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `gd`, `zip`)
- Composer
- MySQL 8+ (or MariaDB)
- `mysqldump` available on `PATH` (required for database backups)
- Node.js 18+ and npm (required for Vite — admin shell and Inertia pages will not load without built or dev assets)

## Quick start (local)

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
cp public/branding/branding.css.example public/branding/branding.css
```

Create a MySQL database (example name `demo_2`), then set in `.env`:

```env
APP_URL=http://127.0.0.1:8000
DB_DATABASE=demo_2
DB_USERNAME=root
DB_PASSWORD=
```

Run migrations and seed MARC catalog framework:

```bash
php artisan migrate
php artisan db:seed --class=MarcFrameworkSeeder
php artisan storage:link
```

Optional — full sample data (programs, students, books, attendance, test admin user):

```bash
php artisan db:seed
```

That creates `test@example.com` / `password` (admin role).

Optional — 10 sample students only for QR/attendance testing (`S-00000001` … `S-00000010`):

```bash
php artisan db:seed --class=StudentSampleSeeder
```

Serve the app (PHP + queue + Vite together):

```bash
composer run dev
```

Or run separately:

```bash
php artisan serve
npm run dev
```

Open **http://127.0.0.1:8000** and sign in with an **admin** or **staff** user from the database, the seeded test account, or **Create Account** in the admin UI.

For a production-style asset build without the Vite dev server:

```bash
npm run build
php artisan serve
```

## Common commands

```bash
composer run dev          # php artisan serve + queue + npm run dev
php artisan serve
npm run dev
npm run build
npm run build:tailwind    # legacy Tailwind utility bundle for Blade pages
php artisan test --filter=SomeTest
./vendor/bin/pint
php artisan route:list
```

## Frontend layout

| Path | Purpose |
| --- | --- |
| `resources/views/layouts/sec.blade.php` | Admin layout — mounts React admin shell around Blade `@yield('content')` |
| `resources/js/admin-shell.jsx` | Entry for the Blade admin shell |
| `resources/js/app.jsx` | Entry for full Inertia pages |
| `resources/js/Layouts/AdminLayout.jsx` | Inertia wrapper using the same shell |
| `resources/js/components/ui/` | shadcn/ui components |
| `resources/js/Pages/` | Inertia page components |
| `app/Support/AdminShell.php` | Shared nav, user, and notification props for shell |

Add shadcn components with `npx shadcn@latest add <component>` (requires `components.json`).

## Environment notes

| Variable | Purpose |
| --- | --- |
| `BRANDING_CSS` | Per-school stylesheet under `public/branding/` (see `public/branding/README.md`) |
| `SMS_MODEM_URL` / `SMS_MODEM_API_KEY` | Local Flask SMS bridge (optional) |
| `GOOGLE_BOOKS_API_KEY` | ISBN lookup quota for cataloging (optional) |
| `BACKUP_ARCHIVE_PASSWORD` | Required secret used to encrypt local database backup archives |
| `DB_DUMP_BINARY_PATH` | Directory containing `mysqldump` when it is not available on `PATH` (optional) |

Copy `.env.example` — **never commit** your real `.env` file.

`public/branding/branding.css` is gitignored; use `branding.css.example` as the template.

## Scheduled database backups

The application creates an encrypted, database-only backup every day at 1:30 AM Asia/Manila and cleans up old backups at 2:30 AM. Archives are private under `storage/app/private/backups/`; application files are not included.

Set a strong `BACKUP_ARCHIVE_PASSWORD` in production and preserve it separately from the server. If `mysqldump` is not on `PATH`, set `DB_DUMP_BINARY_PATH` to its directory (do not include the executable name).

Run Laravel's scheduler every minute on the production server:

```cron
* * * * * cd /path/to/usm && php artisan schedule:run >> /dev/null 2>&1
```

Useful backup checks:

```bash
php artisan backup:run --only-db
php artisan backup:list
php artisan backup:clean
php artisan schedule:list
```

Scheduler command output is appended to `storage/logs/scheduler.log`. Periodically restore an archive into a disposable database to confirm that the backups remain usable.

## Pushing to GitHub

Remote is configured as `origin` → `https://github.com/borskenetic/usm.git`.

From the project root:

```bash
git status
git add -A
git commit -m "Initial commit"
git branch -M main
git push -u origin main
```

Before pushing, confirm `git status` does **not** list:

- `.env`
- `public/branding/branding.css`
- Uploads under `public/images/profile_pictures/`, etc.

## Fresh clone on another machine

```bash
git clone https://github.com/borskenetic/usm.git
cd usm
composer install
npm install
cp .env.example .env
cp public/branding/branding.css.example public/branding/branding.css
# edit .env for DB credentials and APP_URL
php artisan key:generate
php artisan migrate
php artisan db:seed --class=MarcFrameworkSeeder
php artisan storage:link
composer run dev
```

Ensure writable directories: `storage/`, `bootstrap/cache/`, and upload folders under `public/images/` (see `.gitkeep` files).

**Videos:** MP4 files under `public/videos/` are not stored on GitHub (too large). After cloning, copy your slideshow/background videos into `public/videos/` on the server.

**Database copy (optional):** To mirror an existing environment instead of seeding, export/import the MySQL database and copy upload folders from the source machine. Still run `composer install`, `npm install`, `php artisan key:generate`, and `php artisan storage:link` on the new machine.

## Main features

- **Catalog** — MARC-based books, programs, circulation, fines, trash/archive
- **E-Resources** — `/ebooks` digital collection with program/subject filters
- **Patrons** — student registration, ID cards, pending approvals
- **Attendance** — QR scan in/out, reports, optional logout feedback
- **Rooms** — reservations, schedule, pending queue, logs
- **Admin** — user accounts (admin/staff/faculty/student roles)

## License

Application code follows your project license. Laravel framework components are [MIT](https://opensource.org/licenses/MIT).
