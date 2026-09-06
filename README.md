# WTG Backend

Laravel 12 REST API that asynchronously imports supplier offers, returns the
cheapest actual offer per property, and lets clients safely reserve a unit
without overselling.

Repository: https://github.com/ArtemPD/wtg-backend

## Stack

PHP 8.5, Laravel 12, MySQL 8.4, Redis queue (`predis/predis`), Laravel Sail,
PHPUnit 11, [Scribe](https://scribe.knuckles.wtf/) API docs, `archtechx/enums`.

## Setup from scratch

```bash
git clone git@github.com:ArtemPD/wtg-backend.git
cd wtg-backend
composer up
```

`composer up` does everything needed to go from a fresh clone to a running
stack:

1. copies `.env.example` → `.env` if `.env` doesn't exist yet;
2. copies `.env.testing.example` → `.env.testing` if it doesn't exist yet;
3. `composer install`;
4. `php artisan key:generate` (sets `APP_KEY` in `.env`);
5. `./vendor/bin/sail up` — builds the app image and starts every container
   (see below). The one-shot `wtg-init` container runs
   `migrate --seed --force` automatically, so the database is ready as soon
   as the stack is up — no manual migrate/seed step needed.

Requires local PHP 8.2+ and Composer for steps 1–4 (Docker only takes over
from step 5). Once it's running, open http://localhost:8080/docs for the API
docs (generate them first — see below) and `http://localhost:8080/up` for
the health check.

Day-to-day: `composer start` / `composer stop`
(`./vendor/bin/sail up -d` / `./vendor/bin/sail stop`).

## Containers (`compose.yaml`)

| Service | Image | Purpose |
|---|---|---|
| `wtg-backend` | built from `docker/8.5` | the app container, serves the API on `:8080` |
| `wtg-init` | built from `docker/8.5` | one-shot: runs `migrate --seed --force` on the working DB, then exits; everything else waits for it to finish |
| `wtg-worker` | built from `docker/8.5` | runs `queue:work redis` continuously — processes `ProcessImportJob` |
| `mysql` | `mysql:8.4` | working database `wtg` |
| `mysql-testing` | `mysql:8.4` | separate database `wtg_testing`, used only by `./vendor/bin/sail artisan test` |
| `redis` | `redis:alpine` | queue backend (`predis` client) |
| `mailpit` | `axllent/mailpit` | catches outgoing mail in dev (unused by this API directly) |

## Common commands

All commands run through Sail so they execute inside the `wtg-backend`
container, on the same Docker network as `mysql`/`mysql-testing`/`redis`.

| Task | Command |
|---|---|
| Start the stack (detached) | `composer start` (or `./vendor/bin/sail up -d`) |
| Stop the stack | `composer stop` (or `./vendor/bin/sail stop`) |
| Run migrations (working DB) | `./vendor/bin/sail artisan migrate` |
| Migrate + seed from scratch | `./vendor/bin/sail artisan migrate:fresh --seed` |
| Run the queue worker manually | `./vendor/bin/sail artisan queue:work redis` (normally not needed — the `wtg-worker` container already runs this) |
| Run the test suite | `./vendor/bin/sail artisan test` |
| Run one test file | `./vendor/bin/sail artisan test --filter=ReservationTest` |
| Code style check/fix | `./vendor/bin/sail php ./vendor/bin/pint` |
| Generate API documentation | `./vendor/bin/sail artisan scribe:generate` |
| Open a tinker shell | `./vendor/bin/sail artisan tinker` |

### Tests use a separate database

Tests run against the **`mysql-testing`** container and the **`wtg_testing`**
database (`.env.testing`, loaded automatically because `phpunit.xml` sets
`APP_ENV=testing`) — the working `mysql` container and `wtg` database are
never touched by `./vendor/bin/sail artisan test`. SQLite in-memory is intentionally not
used: the search query needs real MySQL 8 window functions, and the
reservation tests need real row locking (`SELECT ... FOR UPDATE`).

### API documentation

```bash
./vendor/bin/sail artisan scribe:generate
```

This regenerates:
- `resources/views/scribe/` — the Blade views served at `/docs`;
- `.scribe/` — intermediate Markdown/cache used by the generator;
- `storage/app/private/scribe/openapi.yaml` and `collection.json` — also
  exposed at `/docs.openapi` and `/docs.postman`.

Run it again whenever a controller, Form Request, or API Resource changes.
Verified end-to-end for this submission: `/docs` (200, correct title and all
four endpoint titles present), `/docs.openapi` (200), `/docs.postman` (200),
and the theme's CSS/JS assets under `/vendor/scribe/` all load correctly.

## API

Base path `/api`, all responses JSON.

| Method | Path | Purpose |
|---|---|---|
| `POST` | `/imports` | Register a supplier import; returns `202` immediately and processes it in the background |
| `GET` | `/imports/{import}` | Check an import's status |
| `GET` | `/properties` | Search properties by dates/guests/city; each result carries its cheapest actual offer |
| `POST` | `/offers/{offer}/reservations` | Reserve one unit of an offer |

Full request/response contracts, validation rules and example payloads are
in the generated docs at `/docs`.
