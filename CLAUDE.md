# NHR Post Insights — project notes

Slug `nhrrob-post-insights` · display name "NHR Post Insights" · menu label **Post Insights** (Dashboard → Post Insights; never "NHR" in the UI).

Private repository, version 0.x, not on WordPress.org. `.ai/PRD.md` holds the scope, what was left out on purpose and the candidates for a paid version: read it before proposing a feature.

## Rules that are specific to this plugin

1. **Nothing identifies a visitor.** No cookie, nothing in the browser's storage, no IP address, no user agent and no full referrer address is stored. A referrer is reduced to its host by `Visit::host()` before it reaches the database.
2. **Nothing leaves the site.** The plugin makes no outside request, on the front end or in the dashboard.
3. **The two tracking routes are open to everyone**, because a visitor has no nonce that survives a page cache. All they can do is add to a counter of a published, public post, and they answer `204` whatever happened. Keep it that way: no route that is open may read anything or write anything else.
4. **Counting happens in the browser**, in `assets/tracker.js`, so it works behind a page cache. PHP never counts a view while building a page.
5. **The script is not loaded for someone who can edit posts**, and `Visit::is_bot()` drops crawlers on the server.
6. **`GET /stats` is behind `Rest::can_view()`** (default `manage_options`, filter `nhrrob_post_insights_capability`), with the REST nonce.
7. **The screen is a React app in the style of my other plugins** (`.ai/DESIGN.md`): `@wordpress/scripts` only, no component, icon or chart library. Light and dark theme.
8. **Identifiers**: namespace `NHRRob\PostInsights\`, class `NHRRob_Post_Insights`, constants `NHRROB_POST_INSIGHTS_*`, option, table and hook prefix `nhrrob_post_insights_`, CSS and HTML id prefix `nhrrob-post-insights-`, CSS tokens `--ir-*`, REST namespace `nhrrob-post-insights/v1`, JS globals `nhrrobPostInsights` (front end) and `nhrrobPostInsightsApp` (dashboard), text domain `nhrrob-post-insights`.

## Layout

```
nhrrob-post-insights.php   main class, constants; Installer and Rest always, Admin in the dashboard, Tracker on the front end
uninstall.php              drops the two tables and deletes the option, per site
includes/
  Installer.php    the two tables, created on activation and when DB_VERSION changes
  Visit.php        which visits count: is_bot(), host(), seconds(), counts()
  Store.php        add_view(), add_seconds(), stats()
  Tracker.php      enqueues assets/tracker.js on a single post or page, for visitors
  Rest.php         POST /view and /time (open), GET /stats (gated)
  Admin.php        Dashboard → Post Insights: mount point, enqueue, boot data
assets/tracker.js  reports the view and the time in view; plain script, not built
admin/src/         React source: index.js, app.js, api.js, format.js, components/ (AppShell, ui, DayChart, toasts), screens/ (Overview, Posts, Referrers), style.scss
admin/build/       compiled output (committed, ships)
tests/             PHPUnit + WP_Mock
.github/workflows/php.yml   PHPCS, then per PHP version: syntax, unit tests, smoke test
.github/ci/smoke.php        calls every GET route inside a real WordPress, fails on any PHP notice
```

## Stored data

| Where | Holds |
|---|---|
| table `{prefix}nhrrob_post_insights_daily` | `day`, `post_id`, `views`, `seconds`: one row per post per day |
| table `{prefix}nhrrob_post_insights_referrers` | `day`, `post_id`, `host`, `views`: one row per post, day and referring site |
| option `nhrrob_post_insights_db_version` | the layout version of the tables, not autoloaded |

A day is the site's own date (`current_time()`). Average time is `seconds / views` of the same rows.

## REST routes (`nhrrob-post-insights/v1`)

- `POST /view`: `post`, `referrer` → `204`. Counts one view today, and the referring host when it is another site.
- `POST /time`: `post`, `seconds` → `204`. Adds seconds, at most `Visit::MAX_SECONDS` per request.
- `GET /stats?days=30`: `days` (each with `day`, `views`, `seconds`, empty days included), `views`, `seconds`, `posts` (top 50), `referrers` (top 50).

## Hooks for an add-on

- filter `nhrrob_post_insights_capability`: who may see the numbers.
- filter `nhrrob_post_insights_counts`: whether views of a post are counted.
- action `nhrrob_post_insights_view`: after a view was counted, with the post ID and the referring host.
- filter `nhrrob_post_insights_stats`: the numbers before they go to the app.

## Commands

```
npm run build               # admin/build
npm run lint:js
composer phpcs
composer test:unit
```

After a change to `admin/src`, run the lint and the build and commit `admin/build`.
