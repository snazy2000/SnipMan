# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

SnippetMan is a Laravel 13 code snippet manager with multi-tenant team support, folder organization, AI-powered processing (auto-description, explanation), public snippet sharing via UUID links, and version history.

## Commands

### Development
```bash
composer run dev        # Concurrent: PHP server + queue worker + Vite dev server
npm run dev             # Vite dev server only
```

### Setup
```bash
composer run setup      # Full setup: composer install, migrate, npm install + build
```

### Testing
```bash
composer run test       # Clears config cache, then runs Pest test suite
php artisan test --filter=SnippetControllerCoverageTest  # Run a single test file
php artisan test --filter="test name"                    # Run a single test by name
```

### Code Quality
```bash
./vendor/bin/pint                  # Laravel Pint code style fixer
./vendor/bin/phpstan analyse       # Static analysis (Larastan)
```

### Docker
```bash
docker compose up       # Starts web (port 8080) + queue worker
```
The Docker stack uses an external PostgreSQL database; configure via `.env` (`DB_HOST`, `DB_DATABASE`, etc.).

## Architecture

### AI Services

AI processing is abstracted via `AIService` (registered as a singleton in `AppServiceProvider`). The active provider is set by `AI_PROVIDER` env var and can be `openai`, `openrouter`, or `ollama` (local). Provider implementations live in `app/Services/` and share a common interface. AI settings (model, temperature, etc.) are stored in the `ai_settings` table and managed via `AISettingsController`.

### Multi-tenancy / Teams

Users can belong to multiple teams. Snippets are scoped to either a user or a team. The `Team` model uses a pivot table; most queries should filter by `team_id` or `user_id` to avoid data leakage across tenants.

### Snippet Sharing

Public sharing uses `SnippetShare` with a UUID. The shared view route `/s/{uuid}` is unauthenticated and rate-limited to 100 requests/minute.

### Queue

Background jobs handle AI processing calls. The queue driver is `database`; the Docker `queue` service runs `php artisan queue:work --tries=3 --timeout=90`.

### Authorization

Policies live in `app/Policies/`. The `SuperAdminMiddleware` gates all `/admin/*` routes. Apply `auth` + `verified` middleware to user-facing routes.

### Testing Conventions

Tests use Pest PHP. Feature tests use `RefreshDatabase` with an in-memory SQLite database. Telescope, Pulse, and Nightwatch are disabled in the test environment. Place feature tests in `tests/Feature/`, unit tests in `tests/Unit/`.

## Key Environment Variables

| Variable | Purpose |
|---|---|
| `AI_PROVIDER` | `openai`, `openrouter`, or `ollama` |
| `AI_AUTO_DESCRIPTION` | Enable automatic snippet description generation |
| `AI_USE_QUEUE` | Process AI requests via queue (recommended in production) |
| `DB_CONNECTION` | `sqlite` (dev/test) or `pgsql` (production) |
| `QUEUE_CONNECTION` | `database` in production, `sync` for immediate processing |
