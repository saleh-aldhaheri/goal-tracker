# Goal Tracker

A flexible personal goal-tracking system built with Laravel 13 and SQLite. It
turns your commitments — study revision, projects, habits, and recurring
routines — into a calm, living garden you can actually watch grow.

The app has two faces that share the same data:

1. **The Homestead** — a data-first dashboard (charts, progress bars,
   statistics) skinned as a quiet farm at night or morning, with a light
   gamification layer (level, XP, gold, streaks, achievements).
2. **The Goal Garden** (`/farm`) — a full-screen, animated garden where each
   goal is a flower that grows with its progress, droops when neglected, and
   blooms when you finish.

The app does **not** contain a question bank. Study material (e.g. Laravel or
.NET interview questions) lives externally; this app only tracks your
*progress*: topics covered, time spent, sessions, streaks, and milestones.

---

## Highlights

- **Generic goals, nothing hard-coded** — one `Goal` model drives study,
  project, habit, recurring, fitness, and custom goals via a `tracking_mode`
  (topics, milestones, count, time, boolean, habit, recurring, percentage).
- **Data-first** — progress and streaks are always *derived* from the
  activity log, never a manually edited number.
- **The Goal Garden** — a living farm with growth-stage flowers, a day/night
  cycle, weather (rain), a house whose windows glow at night, fireflies,
  clouds, and wind, plus sad/happy flower moods tied to how recently you
  worked on each goal.
- **Gamification** — XP earned from real activity, a level curve, gold
  (lifetime XP), streak tracking, and unlockable achievements.
- **Calm theme with dark/light/auto** — a farm-night sky (stars + transparent
  clouds) in dark mode, a morning sky (wind + pollen) in light mode, with a
  smooth crossfade and a translucent-glass interface.
- **Sound** — optional sound effects (on create/log/complete), four
  generative focus tracks, and ambient rain/wind/crickets, each with its own
  volume control, persisted per browser.
- **REST API** secured with scoped Sanctum tokens.
- **MCP-compatible endpoint** so an external AI agent can read and adjust
  your goals under your own scoped, revocable token.

---

## Architecture

```
User → Goals → (Topics | Milestones) → Goal Activities → Dashboards + Farm
```

| Component | Responsibility |
|---|---|
| `App\Models\Goal` | the single goal model for every goal type |
| `App\Enums\*` (`GoalType`, `GoalStatus`, `GoalPriority`, `TrackingMode`, `ActivityType`) | labels only — no `if ($goal->name === 'Gym')` anywhere |
| `App\Services\GoalProgressService` | 0–100% progress from `tracking_mode`, always clamped |
| `App\Services\StreakService` | current/longest streak + completion rate, derived from activities |
| `App\Services\DashboardService` | main + per-goal dashboards, weekly/monthly time, "needs attention" rules |
| `App\Services\GamificationService` | XP, level, gold, streak, and achievements from real activity |
| `App\Http\Controllers\FarmController` | feeds `/farm` with each goal's progress, color, and last-activity age |
| `App\Services\Mcp\McpToolService` | the MCP tools, called by `McpController` |

---

## Requirements

- PHP 8.3+
- Composer
- Node.js 20+ / npm
- SQLite (the `pdo_sqlite` extension, bundled with most PHP installs)

---

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

Visit `http://localhost:8000` and log in. The seeder creates the personal
account:

- Email: `salehaldhaheri09@gmail.com`
- Password: `password`

> Change the password after first login (**Settings → Change password**), or
> set `INITIAL_USER_PASSWORD` in `.env` *before* running the seeder to use a
> different one.

The seeded account starts with four real commitments — **Laravel / PHP
Revision**, **.NET Revision**, **Gym / Fitness**, and **Call Family** — plus
four projects (**Chat App**, **Portfolio**, and two "On Hold" apps). All start
at **zero progress** with no fabricated history: the point of the app is that
*your* activity grows the garden from here.

For a quick look at a populated garden, log some activity (or add topics /
milestones) and the flowers will grow with you.

### Dev mode (hot reload)

```bash
php artisan serve      # terminal 1
npm run dev            # terminal 2, Vite hot reload
```

---

## SQLite setup

The app uses SQLite exclusively (`config/database.php` defaults to
`database/database.sqlite`). Make sure the file exists before migrating:

```bash
touch database/database.sqlite
php artisan migrate
```

Point at a different file with `DB_DATABASE=/absolute/path/to/file.sqlite`.

---

## The Goal Garden (`/farm`)

Each active goal is a flower that grows in stages (seed → sprout → bud →
bloom → full bloom) as its progress rises. A flower droops and sheds a tear
when its goal has been neglected for 5+ days, and bounces cheerfully while
you're active. Hovering a flower shows its progress, last-activity time, and
a little nudge of encouragement; clicking it opens the goal.

- **Time** — Auto (follows your clock) / Day / Night.
- **Weather** — Auto (random showers) / Clear / Rain.
- **Ambience** — wind in the morning, crickets at night, rain while it rains.

---

## Gamification

XP is earned only from real activity — nothing is stored or manually
adjustable:

| Action | XP |
|---|---|
| 1 minute logged | +1 |
| Topic completed | +20 |
| Milestone completed | +50 |
| Goal completed | +200 |
| Daily-chore / streak day | +10 |

- **Level** rises on an increasing curve (each level needs a little more XP
  than the last).
- **Gold** is just your lifetime XP total — a fun counter, not a second
  currency.
- **Streak** is your longest current streak across all goals.
- **Achievements** unlock automatically: First Step, On Fire (7-day streak),
  Harvest (5 goals), Scholar (20 topics), Builder (10 milestones), Farmer
  (level 20).

---

## Sound

A floating 🔊 button (bottom-right) opens the sound panel on every page:

- **Music** — four generative tracks (Calm Day / Focus / Rainy / Night) with
  a volume slider.
- **SFX** — a gentle chime on create/log/complete, with a volume slider.
- **Ambience** — wind / rain / crickets, with a volume slider.

All settings persist in `localStorage`. Audio starts after your first click
or keypress (browser autoplay rules).

---

## Running tests

```bash
php artisan test
```

Tests run against an in-memory SQLite database (`phpunit.xml`); no setup is
needed beyond `composer install`.

---

## Building frontend assets

```bash
npm run build
```

Outputs to `public/build`, referenced via the `@vite` directive.

---

## API documentation

All endpoints are under `/api` and require a Sanctum bearer token:

```
Authorization: Bearer <token>
```

Create a token from **Settings → API & MCP tokens**, choosing abilities:
`goals:read`, `goals:write`, `activities:read`, `activities:write`,
`dashboard:read`. Every route checks the ability it needs — a read-only token
cannot write.

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

---

## MCP (Model Context Protocol)

The app ships a **real MCP server** that runs over stdio, so any MCP client
(Claude Desktop, Cursor, Zed, …) can connect to it directly. It speaks
JSON-RPC 2.0 with full tool schemas — `initialize`, `tools/list`, and
`tools/call`.

### 1. Issue a token

Go to **Settings → API & MCP tokens** and create a token. Give it the
abilities the AI should have — for a full assistant, select all five:
`goals:read`, `goals:write`, `activities:read`, `activities:write`,
`dashboard:read`. A read-only token can read but never modify your goals.

### 2. Connect an MCP client

Register the server with the command:

```
php /absolute/path/to/artisan mcp:serve
```

and set `MCP_TOKEN` to the token you created. Example Claude Desktop config
(`claude_desktop_config.json`):

```json
{
  "mcpServers": {
    "goal-tracker": {
      "command": "php",
      "args": ["/absolute/path/to/goal-tracker/artisan", "mcp:serve"],
      "env": { "MCP_TOKEN": "your-token-here" }
    }
  }
}
```

You can also pass the token directly with `php artisan mcp:serve --token=…`.

### Tools

The server exposes **23 tools**, each mapping to a goal, topic, milestone,
activity, or dashboard operation — authenticated by your token and gated by
its abilities, so an AI agent can never see or modify another user's data and
a read-only token can never write.

Read tools: `list_goals`, `get_goal`, `list_goal_topics`,
`list_goal_milestones`, `get_goal_activity`, `get_goal_progress`,
`get_goal_statistics`, `get_dashboard`, `get_time_summary`, `get_streak`

Write tools: `create_goal`, `update_goal`, `delete_goal`, `pause_goal`,
`resume_goal`, `complete_goal`, `create_goal_topic`, `update_goal_topic`,
`complete_goal_topic`, `create_goal_milestone`, `update_goal_milestone`,
`complete_goal_milestone`, `log_goal_activity`

The application remains the source of truth throughout; the AI only ever
acts through these validated, scoped, user-owned operations.

### HTTP fallback

A plain HTTP tool-call endpoint is also available for non-MCP callers (same
auth and ability checks):

```
GET  /api/mcp/tools                 -> list available tool names
POST /api/mcp/tools/{tool}          -> invoke a tool, JSON body = arguments
```

```bash
curl -X POST https://your-app.example/api/mcp/tools/log_goal_activity \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{"goal_id": 1, "type": "study_session", "duration_minutes": 120}'
```

---

## Deployment

The app is deployment-agnostic: it needs PHP 8.3+, Composer, Node for the
asset build, and a writable path for `database/database.sqlite` that
persists across restarts (a bind-mounted volume, not container-ephemeral
storage). Run `npm run build` (or build in CI) before serving.

---

## Database backup

Back up `database/database.sqlite` directly:

```bash
cp database/database.sqlite database/database.sqlite.bak
```

or, for a consistent snapshot while running:

```bash
sqlite3 database/database.sqlite ".backup backup.sqlite"
```

Never expose this file publicly — keep it outside `public/`.

---

## Troubleshooting

- **"could not find driver" on migrate** — install/enable `pdo_sqlite`.
- **419 Page Expired on login/forms** — stale session cookie after `APP_KEY`
  changed; clear cookies or re-run `php artisan key:generate`.
- **Assets not loading (`Vite manifest not found`)** — run `npm run build`,
  or `npm run dev` while developing.
- **No sound** — click anywhere on the page first (browser autoplay rules),
  then check the 🔊 panel toggles and volumes.
- **MCP tool returns 403** — the token is missing the ability that tool
  requires; issue a new token with the right abilities in Settings.
he dashboard is simple and deterministic — no
  AI recommendation engine.
