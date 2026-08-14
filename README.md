# Goal Tracker

A flexible personal goal-tracking system built in Laravel. It supports study
revision, projects, habits, and recurring commitments through one generic
goal model — no goal type is hard-coded into the application logic.

The app does **not** contain a question bank. Study material (e.g. Laravel
or .NET interview questions) lives externally; this app only tracks your
progress: topics covered, time spent, sessions, streaks, and milestones.

## Features

- Generic goals with configurable tracking mode (topics, milestones, count,
  time, boolean, habit, recurring, or manual percentage)
- Topics and milestones with derived (not manually typed) progress
- Activity log as the single source of historical truth — every session,
  workout, or recurring completion is a permanent record
- Time tracking stored as integer minutes, formatted for display
- Habit and recurring-goal streaks calculated from activity history
- Main dashboard and a per-goal dashboard
- REST API secured with Laravel Sanctum tokens
- An MCP-compatible tool-call endpoint so an external AI agent can read and
  adjust your goals under your own scoped, revocable token

## Architecture

```
User → Goals → (Topics | Milestones) → Goal Activities → Dashboards
```

- `App\Models\Goal` — the one goal model for every goal type
- `App\Enums\{GoalType,GoalStatus,GoalPriority,TrackingMode,ActivityType}` —
  labels only; nothing in the app branches on `if ($goal->name === 'Gym')`
- `App\Services\GoalProgressService` — computes 0-100% progress from the
  goal's `tracking_mode`, always clamped, e.g. topics completed / total
- `App\Services\StreakService` — current/longest streak and completion rate,
  derived from `goal_activities`, not cached
- `App\Services\DashboardService` — main dashboard and per-goal dashboard
  aggregates, plus simple deterministic "needs attention" rules
- `App\Services\Mcp\McpToolService` — the 23 MCP tools, called by
  `McpController`

## Requirements

- PHP 8.2+
- Composer
- Node.js 20+ / npm
- SQLite (bundled with PHP's `pdo_sqlite` extension)

## Installation (local)

```bash
git clone https://github.com/saleh-aldhaheri/goal-tracker.git
cd goal-tracker
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

Visit `http://localhost:8000`. The seeder creates a demo account:

- Email: `demo@goal-tracker.test`
- Password: `password`

The demo goals (Laravel/PHP revision, .NET revision, a project, gym, and a
weekly family call) are clearly-marked seed data — the app also works
correctly with a completely empty database, and you can delete the demo
goals from the UI at any time.

## Running locally in dev mode

```bash
php artisan serve      # terminal 1
npm run dev             # terminal 2, for Tailwind/JS hot reload
```

## SQLite setup

The app uses SQLite exclusively (`config/database.php` defaults to
`database/database.sqlite`). Make sure the file exists and is writable
before running migrations:

```bash
touch database/database.sqlite
php artisan migrate
```

Set a different path with `DB_DATABASE=/absolute/path/to/file.sqlite` in
`.env`.

## Running tests

```bash
php artisan test
```

Tests run against an in-memory SQLite database (`phpunit.xml`), so no setup
is required beyond `composer install`.

## Building frontend assets

```bash
npm run build
```

Outputs to `public/build`, referenced via the `@vite` directive in
`resources/views/components/layouts/app.blade.php`.

## API documentation

All endpoints are under `/api` and require a Sanctum bearer token:

```
Authorization: Bearer <token>
```

Create a token from the UI at **Settings -> API & MCP tokens**, choosing one
or more abilities: `goals:read`, `goals:write`, `activities:read`,
`activities:write`, `dashboard:read`. Every route checks the specific
ability it needs — a read-only token cannot write.

| Method | Endpoint | Ability |
|---|---|---|
| GET | `/api/goals` | `goals:read` |
| POST | `/api/goals` | `goals:write` |
| GET | `/api/goals/{goal}` | `goals:read` |
| PUT/PATCH | `/api/goals/{goal}` | `goals:write` |
| DELETE | `/api/goals/{goal}` | `goals:write` |
| GET | `/api/goals/{goal}/topics` | `goals:read` |
| POST | `/api/goals/{goal}/topics` | `goals:write` |
| GET | `/api/goals/{goal}/activities` | `activities:read` |
| POST | `/api/goals/{goal}/activities` | `activities:write` |
| GET | `/api/dashboard` | `dashboard:read` |
| GET | `/api/goals/{goal}/dashboard` | `dashboard:read` |

A request for another user's goal returns `404`, not `403` — the API never
confirms that a goal ID belonging to someone else exists.

## MCP setup

There's no single, independently-verifiable "the" Laravel MCP package as of
this build, so rather than depend on one sight-unseen, the app exposes a
small, self-contained MCP-compatible tool-call endpoint that any MCP
bridge/adapter can wrap:

```
GET  /api/mcp/tools                 -> list available tool names
POST /api/mcp/tools/{tool}          -> invoke a tool, JSON body = arguments
```

Example — log 2 hours of Laravel study:

```bash
curl -X POST https://your-app.example/api/mcp/tools/log_goal_activity \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{"goal_id": 1, "type": "study_session", "duration_minutes": 120}'
```

Every tool call is authenticated by the same Sanctum token as the REST API
and checked against the same abilities, so an AI agent can never see or
modify another user's data, and a read-only token can't call a write tool.

### Full tool list (`app/Services/Mcp/McpToolService.php`)

Read tools (`goals:read` / `activities:read` / `dashboard:read`):
`list_goals`, `get_goal`, `list_goal_topics`, `list_goal_milestones`,
`get_goal_activity`, `get_goal_progress`, `get_goal_statistics`,
`get_dashboard`, `get_time_summary`, `get_streak`

Write tools (`goals:write` / `activities:write`):
`create_goal`, `update_goal`, `delete_goal`, `pause_goal`, `resume_goal`,
`complete_goal`, `create_goal_topic`, `update_goal_topic`,
`complete_goal_topic`, `create_goal_milestone`, `update_goal_milestone`,
`complete_goal_milestone`, `log_goal_activity`

### Suggested AI workflow (spec section 40)

1. `get_goal` + `get_goal_statistics` to read current state and history
2. Analyze in the AI's own reasoning — the app does no automatic AI analysis
3. Propose a change to the person, e.g. a later `target_date`
4. On confirmation, call `update_goal` to apply it

The application remains the source of truth throughout; the AI only ever
acts through these same validated, scoped, user-owned operations.

## Deployment

Deployment was intentionally left out of this build at the user's request.
The app is deployment-agnostic: it needs PHP 8.2+, Composer, Node for the
asset build, and a writable path for `database/database.sqlite` that
persists across restarts (a bind-mounted volume, not container-ephemeral
storage). A `Dockerfile` was not added since no specific platform was
chosen — see "Known limitations" below.

## Database backup

Back up `database/database.sqlite` directly (e.g. `cp` it somewhere, or
`sqlite3 database/database.sqlite ".backup backup.sqlite"` for a
consistent snapshot while the app is running). Never expose this file
publicly — keep it outside `public/`.

## Troubleshooting

- **"could not find driver" on migrate** — install/enable `pdo_sqlite`.
- **419 Page Expired on login/forms** — usually a stale session cookie
  after `APP_KEY` changed; clear cookies or re-run `php artisan key:generate`.
- **Assets not loading (`Vite manifest not found`)** — run `npm run build`,
  or `npm run dev` while developing.
- **MCP tool returns 403** — the token is missing the ability that tool
  requires; issue a new token with the right abilities in Settings.

## Known limitations

- No live deployment configuration is included yet (see "Deployment" above).
- The MCP endpoint is a custom, minimal implementation rather than a
  published Laravel MCP package integration — functionally equivalent, but
  worth revisiting if a de facto standard package matures.
- Notifications (spec section 34) are intentionally not built; the schema
  and services don't preclude adding them later.
- AI-driven "goals falling behind" recommendations are intentionally not
  built — dashboard "needs attention" logic is simple and deterministic.
