# SariPOS TFA7 Forms Validation and Avatar Upload

IT0049 Technical Formative Assessment 7 by **Lexus Gabrielle Medina**, TC31 BSITCST. Professor: Doc Roman Villoñes. This project extends the Laravel POS application from TFA6 while retaining its responsive navy, coral and teal interface and Eloquent database directories.

## Submission links

- GitHub: https://github.com/Lexxxx12/SariPOS-TFA7
- Render: https://saripos-tfa7-lexxxx12.onrender.com

## Run locally

Requirements: PHP 8.4+, Composer 2, Node 22+, npm, and PHP extensions GD (JPEG, PNG, WebP), mbstring, intl, PDO SQLite, XML, DOM, and ZIP.

```sh
composer install
cp .env.example .env
php artisan key:generate
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan migrate --seed
php artisan storage:link
npm ci
npm run build
php artisan serve
```

Open http://127.0.0.1:8000. SQLite is configured by default; MySQL is also supported via DB_* environment values. Dates use Asia/Manila. Six fictional customers and six fictional staff directory accounts are seeded without overwriting edited records. These are publicly editable demonstration directory records, not login accounts. Do not enter real personal or confidential data in the hosted lab app.

## Required workflows

| Page | Function |
| --- | --- |
| `/customers` | Customer directory and Edit links |
| `/customers/create` | Create customer; full_name required, email required and valid, optional phone |
| `/customers/{customer}/edit` | Prefilled customer form with update action |
| `/users` | User directory with prepared avatars or a local placeholder image |
| `/users/create` | Create user; required unique username and required full_name |
| `/users/{user}/edit` | Prefilled user form; optional profile picture upload |

Forms use POST for creation and PUT method spoofing for updates, Laravel CSRF protection, Form Request validation, old() input values, $errors summaries and per-field messages. A successful operation redirects to the directory with a success message. Username uniqueness excludes the current route-bound user when editing. Only validated intended attributes are passed to Eloquent. Uploaded files must be reselected after an error because browsers cannot refill file inputs.

## Avatar preparation

The new `add_avatar_to_users_table` migration adds a nullable avatar path. `AvatarStorage` uses **Intervention Image 3.11** with its GD driver to decode the validated file, crop/resize it to **256 × 256**, and re-encode it as JPEG at quality 85. The image package named by the instructor is not specified in the supplied handout; this implementation selects Intervention Image. Confirm it is on your instructor's approved list if a separate package list was provided.

Accepted images are JPG/JPEG, PNG, and WebP with a maximum upload size of **2048 KB (2 MB)** and dimensions no greater than 4096 × 4096. The prepared image receives a generated UUID filename and is written using `Storage::disk('public')->put(...)`. Only the relative `avatars/...jpg` path is saved in the database. Original upload names and source files are not retained. Updating without a new file preserves the existing avatar. Replacing an avatar deletes the old file only after the database update succeeds; a failed database update cleans up the newly written file. A bundled SVG placeholder is used when no image is uploaded. User-submitted SVGs are not accepted.

## Structure and database

- `database/migrations`: TFA6 customers/users schema plus the new avatar migration.
- `database/seeders`: six sample records per directory; repeatable non-destructive seeding.
- `app/Models`: Customer and User Eloquent models, fillable attributes and factories.
- `app/Http/Requests`: customer and user validation rules and messages.
- `app/Http/Controllers`: list/create/store/edit/update actions.
- `app/Services/AvatarStorage.php`: image preparation and public storage.
- `resources/views`: shared layout, reusable form field, directories and create/edit forms.
- `tests/Feature/AccountFormsTest.php`: creation, updates, validation, old input, uniqueness, prepared image dimensions and format, replacement, preservation and placeholders.

Migration and seeder files reproduce the database; a SQL dump is not necessary. Both models retain TFA6's created_at-only timestamp convention.

## Tests and build

```sh
php artisan test --compact
vendor/bin/pint --format agent
npm run build
php artisan route:list --except-vendor
```

## Render deployment

The included Dockerfile builds frontend assets, installs PHP GD and other extensions, then installs production Composer dependencies. `docker-entrypoint.sh` runs migrations and the repeatable seeder, creates the public storage link, caches configuration/routes/views and starts Laravel on Render's PORT. `render.yaml` specifies a free Docker web service with debug disabled. No paid resources are required by the deployment configuration.

Free Render storage is ephemeral: created/edited records and uploaded avatars may reset on a redeploy or instance replacement. Seeded records are restored at startup. For durable storage, configure a persistent disk or external database and object storage. Free services may sleep when idle and take time to wake. This laboratory intentionally offers public demo forms without authentication, matching its required scope.

References: [Laravel Validation](https://laravel.com/docs/13.x/validation), [File Storage](https://laravel.com/docs/13.x/filesystem), [Intervention Image](https://image.intervention.io/v3).
