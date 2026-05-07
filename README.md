# Joke Fetcher — PHP/Laravel test task

Laravel 12 application that implements all four parts of the PHP developer test task:

1. **Console command** that fetches a joke from `https://official-joke-api.appspot.com/random_joke` every 5 minutes and stores it in the DB.
2. **JSON route** returning the saved jokes.
3. **JS for `testlist.html`** that filters form fields by the selected "Тип" (Type) value.
4. **Bonus** — page-visit counter with IP/city/device collection, hourly + city charts, and an auth-protected stats page.

---

## Stack

- PHP 8.2+
- Laravel 12
- SQLite (file-based, no external DB needed)
- Chart.js (CDN, only on the stats page)

---

## Quick start

```bash
git clone <repo> joke-fetcher
cd joke-fetcher

composer install
cp .env.example .env
php artisan key:generate

# create the sqlite file if it doesn't exist
touch database/database.sqlite

php artisan migrate:fresh --seed --force
php artisan serve
```

The app is now on `http://127.0.0.1:8000`.

The `db:seed` step creates the admin account used by the stats page:

| email                 | password   |
|-----------------------|------------|
| admin@example.com     | 12345678   |

> Change this in [`database/seeders/DatabaseSeeder.php`](database/seeders/DatabaseSeeder.php) before deploying anywhere public.

---

## How to test each task

### Task 1 — Console command (every 5 min)

```bash
php artisan jokes:fetch        # run once, stores a joke
php artisan schedule:list      # confirms: */5 * * * *  php artisan jokes:fetch
php artisan schedule:work      # leave running — auto-fires every 5 min
```

In production, register cron instead of `schedule:work`:

```cron
* * * * * cd /var/www/joke-fetcher && php artisan schedule:run >> /dev/null 2>&1
```

Files:

- Command — [`app/Console/Commands/FetchJokesCommand.php`](app/Console/Commands/FetchJokesCommand.php)
- Schedule registration — [`routes/console.php`](routes/console.php)
- Model — [`app/Models/Joke.php`](app/Models/Joke.php)
- Migration — [`database/migrations/2026_05_07_005645_create_jokes_table.php`](database/migrations/2026_05_07_005645_create_jokes_table.php)

### Task 2 — JSON route

Open [http://127.0.0.1:8000/jokes](http://127.0.0.1:8000/jokes) — returns the latest 50 saved jokes as JSON.

File: [`routes/web.php`](routes/web.php)

### Task 3 — Filter fields by selected "Тип"

Open [http://127.0.0.1:8000/testzz/testlist.html](http://127.0.0.1:8000/testzz/testlist.html).

Change the `Тип` select — only inputs whose `name` attribute *contains* the selected value remain visible. Example: select `company` → `company_name`, `company_inn`, `company_kpp` are shown; everything else is hidden.

Files:

- Page — [`public/testzz/testlist.html`](public/testzz/testlist.html)
- Script — [`public/js/testlist.js`](public/js/testlist.js)

**Algorithm note** (also in the script header): on every change of `#type`, walk every `[name]` element once and toggle a `.hidden` class via `name.indexOf(value) !== -1`. O(N) in the number of fields, runs only on change, no DOM rebuilds.

Alternatives considered:

- Rebuild the form from a JS-side schema on every change (the original approach in `dynamic-fields.js`). Rejected: discards user input on every change, and the task asks us to filter existing markup, not to generate it.
- Use `querySelectorAll('[name*="..."]')` for the visible set. Rejected: requires CSS-escaping the value to be safe and offers no real speed win.
- Pull in jQuery for `:contains`-style filters. Rejected: a one-line `indexOf` check doesn't justify a library dependency.

### Task 4 — Visitor counter (bonus)

**a) Drop the tracker into any HTML page:**

```html
<script src="http://127.0.0.1:8000/js/visitor-tracker.js"></script>
```

The script calls [ipapi.co](https://ipapi.co) for geolocation, classifies the device, and `POST`s `{ip, city, country, device, user_agent}` to `/api/track-visit`.

Manual test from the terminal:

```bash
curl -X POST http://127.0.0.1:8000/api/track-visit \
  -H "Content-Type: application/json" \
  -d '{"ip":"203.0.113.5","city":"Moscow","country":"Russia","device":"Desktop","user_agent":"manual-test"}'
```

**b) View the stats:** open [http://127.0.0.1:8000/stats](http://127.0.0.1:8000/stats).

The browser will prompt for HTTP basic auth — use the admin credentials above. The page shows:

- **Hourly bar chart** — unique visitors (by IP) per hour, last 24 hours.
- **City pie chart** — visit counts by city (top 15).

Files:

- Tracker — [`public/js/visitor-tracker.js`](public/js/visitor-tracker.js)
- API routes — [`routes/api.php`](routes/api.php)
- Controller — [`app/Http/Controllers/VisitorController.php`](app/Http/Controllers/VisitorController.php)
- Model — [`app/Models/Visitor.php`](app/Models/Visitor.php)
- Migration — [`database/migrations/2026_05_07_012930_create_visitors_table.php`](database/migrations/2026_05_07_012930_create_visitors_table.php)
- Stats view — [`resources/views/stats.blade.php`](resources/views/stats.blade.php)
- Stats route + auth — [`routes/web.php`](routes/web.php) (`auth.basic` middleware)

---

## Project layout (relevant files only)

```
app/
  Console/Commands/FetchJokesCommand.php   Task 1 — fetches the joke
  Http/Controllers/VisitorController.php   Task 4 — track + stats data
  Models/Joke.php                          Task 1/2
  Models/Visitor.php                       Task 4
bootstrap/app.php                          registers web + api + console routes
database/
  migrations/...create_jokes_table.php     Task 1
  migrations/...create_visitors_table.php  Task 4
  seeders/DatabaseSeeder.php               admin user for /stats
public/
  js/testlist.js                           Task 3
  js/visitor-tracker.js                    Task 4 frontend
  testzz/testlist.html                     Task 3 page
resources/views/stats.blade.php            Task 4 charts
routes/
  api.php                                  POST /api/track-visit, GET /api/stats/data
  console.php                              Schedule::command('jokes:fetch')->everyFiveMinutes()
  web.php                                  GET /jokes, GET /stats (auth)
```

---

## Endpoints summary

| Method | URL                     | Purpose                              | Auth         |
|--------|-------------------------|--------------------------------------|--------------|
| GET    | `/jokes`                | Task 2 — JSON of saved jokes         | none         |
| GET    | `/testzz/testlist.html` | Task 3 — demo form                   | none         |
| POST   | `/api/track-visit`      | Task 4 — record a page visit         | none         |
| GET    | `/api/stats/data`       | Task 4 — JSON aggregates for charts  | none         |
| GET    | `/stats`                | Task 4 — charts page                 | HTTP basic   |

---

## Deployment notes

- Production needs cron firing `php artisan schedule:run` every minute (see Task 1 above).
- The SQLite file lives at `database/database.sqlite`; ensure the web user has write access to it and to `storage/`.
- Change the admin password in `DatabaseSeeder.php` before public deployment.
- ipapi.co is rate-limited (1k req/day on the free tier). If the site gets real traffic, swap the tracker for a server-side IP→city lookup or a paid plan.
