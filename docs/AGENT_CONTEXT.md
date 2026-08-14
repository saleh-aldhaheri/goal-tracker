# Goal Tracker — Agent Context

Read this before making changes. It's the current source of truth for what
this project is, what's built, what's in flight, and what's still open.

## What this is

A personal goal-tracking Laravel app for one real user (Saleh), built from
two source documents (kept for reference, not restated here):
- The original 92-section build spec (architecture, tech stack, MVP scope)
- An "initial user context" brief (who Saleh is, his real goals, and exact
  seed data — no fabricated progress/history, ever)

Repo: `github.com/saleh-aldhaheri/goal-tracker`, branch `main`. All commits
are logical/phase-based (`feat:`, `fix:`, `ci:`, `docs:`, `test:`) and
pushed directly to `main` — no PR workflow in use.

## Tech stack actually in use

- **Laravel 13**, **PHP 8.3** (not 11/8.2 — bumped mid-build because
  Composer's audit blocked the entire 11.9–11.55 range for known CVEs, and
  Laravel had moved to v13 by the time this was built)
- SQLite only (`database/database.sqlite`)
- Laravel Sanctum for both session auth and scoped API/MCP tokens
- Blade + Tailwind CSS + Alpine-free vanilla forms (no SPA framework)
- Vite for the frontend build, `package-lock.json` is committed (required
  for `actions/setup-node`'s cache to work in CI)
- PHPUnit via `php artisan test`

## Core architecture (do not violate)

- **One generic `Goal` model** for every goal type. Never branch on
  `if ($goal->name === 'Gym')` or similar — behavior comes from `type`,
  `tracking_mode`, and `settings` (JSON), not identity.
- `App\Enums\{GoalType,GoalStatus,GoalPriority,TrackingMode,ActivityType}` —
  labels/config only.
- `App\Services\GoalProgressService` — the only place progress % is
  computed, dispatched by `tracking_mode`, always clamped 0–100.
- `App\Services\StreakService` — streaks/completion rate always *derived*
  from `goal_activities`, never cached/stored.
- `App\Services\DashboardService` — main + per-goal dashboard aggregates,
  simple deterministic "needs attention" rules (no AI/ML).
- `App\Services\Mcp\McpToolService` — all MCP tools live here, one method
  per tool, dispatched by `App\Http\Controllers\Api\McpController`.
- Every activity (`goal_activities`) is permanent history — nothing here
  is a manually-edited running total. Progress/streaks are always derived.

## MCP design decision

No third-party Laravel MCP package was depended on (none was verified
stable/current at build time). Instead: a small self-contained tool-call
endpoint at `POST /api/mcp/tools/{tool}` (list at `GET /api/mcp/tools`),
authenticated by the same Sanctum tokens as the REST API, ability-checked
per tool (`goals:read`, `goals:write`, `activities:read`,
`activities:write`, `dashboard:read`). If a real MCP package becomes the
obvious standard later, wrap this controller rather than rewrite it.

## What's built and pushed (commits `f5c3c2a`..`661ef54`)

- Laravel skeleton, config, auth (register/login/logout, policies)
- Goal/Topic/Milestone/Activity CRUD, web UI, progress + streak calc
- Main + per-goal dashboards
- REST API (`/api/goals`, `/api/goals/{goal}/topics`, `/activities`,
  `/dashboard`) — all Sanctum + ability gated
- MCP tool endpoint, all 23 tools from the original spec section 38
- API/MCP token issuance UI at `/settings/tokens` (Sanctum abilities,
  revocable, plaintext shown once)
- `DemoSeeder` — generic placeholder demo data, NOT Saleh's real data
- Tests: auth, goal management, tracking, unit progress-calc, API, MCP
- GitHub Actions CI (`.github/workflows/tests.yml`) — genuinely green,
  verified via polling the Actions API and reading a real failure once
  (see "Debugging notes" below)
- README with install/API/MCP docs

## In flight — written locally, NOT yet committed/pushed as of last check

Triggered by the "initial user context" brief (Saleh's real goals, no fake
progress, question-coverage tracking, secure initial credentials). Files
touched:

- `app/Enums/ActivityType.php` — added `QuestionReview` case
- `app/Services/DashboardService.php` — `goalDashboard()` now adds
  `questions_total`/`questions_completed` when a goal has
  `target_unit === 'questions'` (summed from `question_review` activities,
  clamped to target). Optional metric — absent when not applicable.
- `resources/views/goals/show.blade.php` — renders question-coverage bar
  when present; quick-log form gained a generic numeric `value` field
  (needed to actually log question counts, not just minutes)
- `app/Http/Controllers/PasswordController.php` (new) + 
  `resources/views/settings/password.blade.php` (new) +
  routes in `routes/goals.php` — self-service password change. Added
  because the seeder must never hardcode a plaintext prod password; this
  gives a real way to change it after first login.
- `resources/views/components/layouts/app.blade.php` — nav link to Settings
- `resources/views/settings/tokens.blade.php` — cross-link to password page
- `database/seeders/InitialAccountSeeder.php` (new) — **Saleh's real seed
  data**, separate from `DemoSeeder`, NOT wired into `DatabaseSeeder`
  (must be run explicitly: `php artisan db:seed --class=InitialAccountSeeder`)
  so it never fires in CI/tests or silently re-seeds on redeploy. Reads
  `INITIAL_USER_EMAIL` / `INITIAL_USER_NAME` / `INITIAL_USER_PASSWORD` from
  env; if no password given, generates a random one and prints it once via
  `$this->command->warn()`. Uses `updateOrCreate` throughout — idempotent,
  safe to re-run, never duplicates goals/topics.
- Tests: `tests/Feature/InitialAccountSeederTest.php`,
  `tests/Feature/QuestionCoverageTest.php`,
  `tests/Feature/PasswordChangeTest.php`

**Next step for whoever picks this up:** run the test suite, commit these
in 2–3 logical groups (e.g. `feat: add question coverage tracking`,
`feat: add self-service password change`, `feat: add Saleh's initial
account seeder`), push, and verify CI is still green before considering
this phase done.

### Saleh's real seed data (from the initial-user-context brief — exact)

Five goals, **all starting at 0% progress, 0 activities, no fabricated
history**:

1. **PHP + Laravel Revision** — type `study`, priority `high`,
   `tracking_mode: topics`, `target_value: 550`, `target_unit: questions`.
   ~47 topics across 5 tiers (PHP core, Laravel core, Database, Tier A,
   Tier B) — exact list is in the seeder, sourced from the brief.
2. **.NET Revision** — type `study`, priority `high`, `tracking_mode: topics`,
   no question target (none was given). ~29 C#/.NET topics.
3. **Goal Tracker** (the project itself) — type `project`, priority
   `medium`, `tracking_mode: milestone`, zero milestones seeded (add later).
4. **Gym** — type `fitness`, priority `medium`, `tracking_mode: habit`,
   deliberately no fixed frequency in `settings` (brief explicitly says
   don't hard-code a day count).
5. **Family Call** — type `recurring`, priority `medium`,
   `tracking_mode: recurring`, `settings: {frequency: weekly, target_count: 1}`.

Do not add fake study sessions, gym visits, calls, or completed
topics/questions to this seeder. That's an explicit, repeated instruction
in the source brief.

## Deployment status

**Not deployed anywhere.** Explicitly deferred by the user. When resumed,
the agreed direction (not yet built) is:
- Northflank free tier (confirmed via live search to still exist as of
  Aug 2026: 2 services / 1 DB / 2 cron jobs, $0/mo, Dockerfile-based builds
  from GitHub, persistent volumes supported)
- A `Dockerfile` + persistent volume mounted at `database/` for
  `database.sqlite` — not written yet
- No Dockerfile, `docker-compose.yml`, or platform-specific config exists
  in the repo yet

## Security notes for whoever continues this

- A GitHub PAT was pasted into the chat that did this build and used to
  push directly; the user was told to rotate/revoke it. Don't assume it's
  still valid, and never rely on chat history for credentials.
- No secrets are committed anywhere (`.env` is gitignored,
  `database.sqlite` is gitignored, `.env.example` has no real values).
- `InitialAccountSeeder` is the only place real personal credentials get
  set, and only via env vars at seed time — never edit it to hardcode a
  password.

## Debugging notes (useful if CI breaks again)

This sandbox cannot fetch raw GitHub Actions logs directly (they redirect
to `blob.core.windows.net`, which isn't reachable). What worked: add a
temporary CI step that runs tests with `set -o pipefail` (important —
without it, `php artisan test | tee out.txt` always "succeeds" because
`tee`'s exit code masks the real one) and, on failure, POSTs the tail of
the output as a commit comment via `api.github.com` using
`secrets.GITHUB_TOKEN` — then fetch that comment via the API. Remove the
step again once done; it's not meant to be permanent.

Real bugs this caught, for reference:
- Missing `package-lock.json` broke `actions/setup-node`'s cache
- Too-narrow PHP extension list broke `composer install`'s platform check
- Composer's advisory-audit blocked the whole `11.9–11.55` Laravel range
- `laravel/tinker` needed v3 for Laravel 13 compatibility
- `Goal` model had no in-memory `status`/`priority` default, so a
  freshly-created (not re-fetched) instance had `null` enums and crashed
  `GoalResource` — fixed via `protected $attributes = [...]` on the model

## Explicitly out of scope / not done

- Calendar view (spec section 29)
- Search across name/description in the API (only in the web UI)
- Dark mode, charts/bar visualizations (spec section 24)
- Code style CI check (Pint configured in `composer.json` dev deps but not
  run in CI)
- Notifications (spec section 34) — intentionally deferred
- AI-driven "falling behind" recommendations — intentionally deferred,
  dashboard "needs attention" logic is simple and deterministic on purpose
