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

## What's built and pushed (commits `f5c3c2a`..`66b272c`)

- Laravel skeleton, config, auth (register/login/logout, policies)
- Goal/Topic/Milestone/Activity CRUD, web UI, progress + streak calc
- Main + per-goal dashboards
- REST API (`/api/goals`, `/api/goals/{goal}/topics`, `/activities`,
  `/dashboard`) — all Sanctum + ability gated
- MCP tool endpoint, all 23 tools from the original spec section 38
- API/MCP token issuance UI at `/settings/tokens` (Sanctum abilities,
  revocable, plaintext shown once)
- Self-service password change at `/settings/password`
- Question-coverage tracking as an optional per-goal metric
  (`target_unit === 'questions'`, summed from `question_review` activities)
- `DemoSeeder` — generic placeholder demo data, NOT Saleh's real data
- `InitialAccountSeeder` — Saleh's real 5 goals, run explicitly, never
  fabricates progress/history (see full description below)
- Tests: auth, goal management, tracking, unit progress-calc, API, MCP,
  question coverage, password change, initial-account seeder — all
  genuinely passing (verified via a real CI failure readout, not assumed)
- GitHub Actions CI (`.github/workflows/tests.yml`) — verified green
- README with install/API/MCP/seeding docs
- **`Dockerfile`** — authored directly by the repo owner (not this agent)
  while this session was in progress; PHP 8.3-fpm base, builds via
  Composer + npm, runs `php artisan migrate --force` then
  `php artisan serve` on port 8080. Not yet wired to any hosting platform.
- A defensive extra `sessions` table migration
  (`0001_01_01_000004_create_sessions_table.php`, guarded with
  `Schema::hasTable()`) was added by the repo owner independently — it's
  redundant with the sessions table already created in
  `0001_01_01_000000_create_users_table.php`, but harmless (no-ops if the
  table exists). Left as-is rather than removed, to avoid fighting the
  owner's own commits.

**Note on `composer.lock`:** deliberately *not* committed (gitignored).
The repo owner committed a real one directly via GitHub at one point, but
it was generated against an older `composer.json` (pre-Laravel-13 bump)
and broke `composer install` in CI once merged. It was removed again. If
you regenerate one from a real `composer install` against the *current*
`composer.json`, committing it is fine and generally good practice — just
make sure it's actually in sync first (`composer validate` or a clean
`composer install --no-interaction` with no lock-mismatch warnings).

## Debugging notes (useful if CI breaks again)

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
- `StreakService::completionRate()` called `Carbon::toDateImmutable()`,
  which doesn't exist (real method is `toImmutable()`) — only triggered
  when a goal has no `start_date`, which is exactly Saleh's Gym/recurring
  goals, so it would have broken in production on day one if untested

## Explicitly out of scope / not done

- Calendar view (spec section 29)
- Search across name/description in the API (only in the web UI)
- Dark mode, charts/bar visualizations (spec section 24)
- Code style CI check (Pint configured in `composer.json` dev deps but not
  run in CI)
- Notifications (spec section 34) — intentionally deferred
- AI-driven "falling behind" recommendations — intentionally deferred,
  dashboard "needs attention" logic is simple and deterministic on purpose
