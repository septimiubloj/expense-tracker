# Expense Tracker

A clean Laravel 13 rebuild of the existing expense tracker. The first milestone
provides the application foundation and authentication. Budget models, financial
calculations, the spreadsheet importer, and money-manager screens come next.
See [PLAN.md](PLAN.md) for the ordered checklist and agreed decisions.

## Local development

Requirements: PHP 8.3–8.5 with SQLite, mbstring, XML, intl, curl, and zip extensions;
Composer 2; and Node 24.11 or newer within the Node 24 release line.

```bash
nvm install
nvm use
composer setup
composer dev
```

`composer setup` installs locked dependencies, creates `.env` with a new app key
only when it is missing, applies migrations, and builds the frontend. If you create
`.env` yourself, run `php artisan key:generate` once before starting the app.
The local defaults use SQLite (`database/database.sqlite`) and write mail to
`storage/logs/laravel.log`. Register an account and follow the verification link
from the log. No default account is seeded automatically.

The frontend uses React 19, TypeScript, Inertia 3, Tailwind 4, and the official
starter kit's Vite Plus tooling around Vite 8. `npm run dev` starts only the asset
server; `composer dev` runs the application development processes together.

## Optional Docker development

The Sail configuration uses PHP 8.4, Node 24, and the same SQLite database. Docker
Compose must be available. Install Composer dependencies on the host first, then:

```bash
./vendor/bin/sail up -d
./vendor/bin/sail composer setup
./vendor/bin/sail npm run dev
```

Application and Vite ports bind to localhost. `APP_PORT` defaults to 8000 and
`VITE_PORT` to 5173. Set `WWWUSER` and `WWWGROUP` when your user/group IDs differ
from 1000. This configuration is for local development.

## Verification

```bash
nvm use
composer ci:check
```

This regenerates typed routes, checks frontend formatting/lint and TypeScript,
checks PHP formatting and Larastan level 7, runs the PHP tests, and builds production
assets. CI runs the same checks on PHP 8.3, 8.4, and 8.5 using `.nvmrc` for Node.
Composer resolves dependencies against PHP 8.3 so the lock file remains compatible
with the lowest supported version.

Focused commands:

```bash
php artisan test --compact tests/Feature/Auth
composer lint
composer types:check
npm run check:fix
npm run types:check
npm run build
```

## AI coding guidelines

Laravel Boost and `spatie/guidelines-skills` are Composer development dependencies.
The project includes generated `AGENTS.md`, project instructions in
`.ai/guidelines/project.md`, skills in `.agents/skills`, and Codex MCP configuration
in `.codex/config.toml`. Spatie's PHP/Laravel, JavaScript, security, and version
control skills guide implementation; formatting checks enforce the mechanical rules.
Keep custom instructions in `.ai/guidelines/project.md`, since Boost regenerates
`AGENTS.md`.

```bash
composer update spatie/guidelines-skills laravel/boost
php artisan boost:update --no-interaction
```

Review generated changes before committing them. The explicitly selected rebuild
branch name takes precedence over Spatie's general branch naming convention.

## Provenance and legacy source

Scaffold: `laravel/react-starter-kit` at
`87cce8705d712629ebddd70ccfbb06592ecbaac2` (official `main`, fetched 2026-09-06).
The latest tagged starter-kit release at rebuild time still targeted Laravel 12.

The original committed application remains at `legacy-laravel-8`. The previous
local `.env`, `composer.json`, and `composer.lock` are preserved in the ignored
`.legacy-backup/` directory on the original workstation. They are not used by the
new application. Do not commit credentials, local SQLite files, or spreadsheet
exports containing personal financial data.
