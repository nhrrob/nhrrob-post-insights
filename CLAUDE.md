# NHR Post Insights — project notes

Slug `nhrrob-post-insights` · display name "NHR Post Insights" · menu label **Post Insights** (Tools → Post Insights; never "NHR" in the UI).

Private repository, version 0.x, submitted to WordPress.org on 2026-11-23 and in review. `.ai/PRD.md` holds the scope and what was left out on purpose: read it before proposing a feature, and ask Robin before building one it does not list.

## Rules that are specific to this plugin

1. **Nothing identifies a visitor.** No cookie, nothing in the browser's storage, no IP address, no user agent and no full referrer address is stored. A referrer is reduced to its host by `Visit::host()` before it reaches the database.
2. **Nothing leaves the site.** The plugin makes no outside request, on the front end or in the dashboard.
3. **The two tracking routes are open to everyone**, because a visitor has no nonce that survives a page cache. All they can do is add to a counter of a published, public post, and they answer `204` whatever happened. Keep it that way: no route that is open may read anything or write anything else.
4. **Counting happens in the browser**, in `assets/tracker.js`, so it works behind a page cache. PHP never counts a view while building a page.
5. **The script is not loaded for someone who can edit posts**, and `Visit::is_bot()` drops crawlers on the server.
6. **`GET /stats` is behind `Rest::can_view()`** (default `manage_options`, filter `nhrrob_post_insights_capability`), with the REST nonce.
7. **The screen is a React app in the style of my other plugins** (`.ai/DESIGN.md`, mockups in `docs/design/`): `@wordpress/scripts` only, no component, icon or chart library. One accent, teal. Light and dark theme.
8. **Identifiers**: namespace `NHRRob\PostInsights\`, class `NHRRob_Post_Insights`, constants `NHRROB_POST_INSIGHTS_*`, option, table and hook prefix `nhrrob_post_insights_`, CSS and HTML id prefix `nhrrob-post-insights-`, CSS tokens `--ir-*`, REST namespace `nhrrob-post-insights/v1`, JS globals `nhrrobPostInsights` (front end) and `nhrrobPostInsightsApp` (dashboard), text domain `nhrrob-post-insights`.

## Layout

```
nhrrob-post-insights.php   main class, constants; Installer and Rest always, Admin in the dashboard, Tracker on the front end
uninstall.php              drops the two tables and deletes the option, per site
includes/
  Installer.php    the two tables, created on activation and when DB_VERSION changes
  Visit.php        which visits count: is_bot(), host(), seconds(), counts()
  Store.php        add_view(), add_seconds(), stats(), posts()
  Filters.php      the dashboard's filters: type, from, to, search; sanitized, the range they resolve to, the WHERE of a read
  Tracker.php      enqueues assets/tracker.js on a single post or page, for visitors
  Rest.php         POST /view and /time (open), GET /stats and /posts (gated)
  Columns.php      the columns and tiles an add-on declares
  Admin.php        Tools → Post Insights: mount point, enqueue, boot data, Plugins row link, old address redirect
assets/tracker.js  reports the view and the time in view; plain script, not built
admin/src/         React source: index.js, app.js, api.js, format.js, sources.js (a referrer's kind), components/ (AppShell, Filters, ui, DayChart, toasts), screens/ (Overview, Posts, Post, Referrers), style.scss
docs/design/       the chosen mockups (Claude design canvas files); not shipped
docs/wporg/        icon.html and banner.html, the sources of the WordPress.org icon and banners; render.mjs (Playwright) writes them to .wordpress-org/
.wordpress-org/    WordPress.org listing: icon, banners, screenshot-1 to 5 (captioned in readme.txt's == Screenshots ==); not shipped
admin/build/       compiled output (committed, ships)
tests/             PHPUnit + WP_Mock
.github/workflows/php.yml   PHPCS, then per PHP version: syntax, unit tests, smoke test
.github/workflows/plugin-check.yml  WordPress.org's Plugin Check on the zip's files, on every pull request
.github/workflows/release.yml  when Version changes on main: tag v<version>, a GitHub release with readme.txt's changelog, and the version sent to WordPress.org; a change to readme.txt or .wordpress-org/ alone updates the listing
.github/ci/smoke.php        calls every GET route inside a real WordPress, fails on any PHP notice
```

## Stored data

| Where | Holds |
|---|---|
| table `{prefix}nhrrob_post_insights_daily` | `day`, `post_id`, `views`, `seconds`: one row per post per day |
| table `{prefix}nhrrob_post_insights_referrers` | `day`, `post_id`, `host`, `views`: one row per post, day and referring site |
| option `nhrrob_post_insights_db_version` | the layout version of the tables, autoloaded (read on every request); 2 since 0.5.1 only to switch autoload on |

A day is the site's own date (`current_time()`). Average time is `seconds / views` of the same rows.

## REST routes (`nhrrob-post-insights/v1`)

- `POST /view`: `post`, `referrer` → `204`. Counts one view today, and the referring host when it is another site.
- `POST /time`: `post`, `seconds` → `204`. Adds seconds, at most `Visit::MAX_SECONDS` per request.
- Filters, on both GET routes: `type` (a public post type that is counted, empty for all), `from` and `to` (Y-m-d; a custom range used only when both are given and in order, ending today at the latest and spanning at most `Filters::MAX_DAYS`, otherwise `days` applies). On `/posts` also `search` (words in the title, at most 100 characters). An unknown value is ignored, never an error. Category, tag and author filters are planned as free (`.ai/PRD.md`).
- `GET /stats?days=30&post=0`: `days` (each with `day`, `views`, `seconds`, empty days included), `views`, `seconds`, `direct` (views with no referrer: the views less the referred views, never below zero), `read` (posts with a view), `posts` (top 50), `referrers` (top 50), `previous` (the same number of days right before: `views`, `seconds`, `direct`, `read`, and `referrers` as views by host for the hosts listed; null for all time). `days=0` is all time, from the first day with a view. `post` narrows every number to one post; 0 is the whole site.
- `GET /posts?days=30&orderby=views&order=desc&page=1`: one page (`Store::PER_PAGE`, 20) of every published, public post, also those without a view: `posts` (each with `id`, `title`, `link`, `edit`, `date`, `views`, `seconds`, `before` (views in the days right before, null for all time), `extra`), `total`, `pages`. `orderby` is `title`, `date`, `views` or `average`. Behind `Rest::can_view()`.

## Hooks for an add-on

- filter `nhrrob_post_insights_capability`: who may see the numbers.
- filter `nhrrob_post_insights_counts`: whether views of a post are counted.
- action `nhrrob_post_insights_view`: after a view was counted, with the post ID and the referring host.
- filter `nhrrob_post_insights_stats`: the numbers before they go to the app, with the days, the post (0 for the whole site) and the resolved filters (`from`, `to`, `type`, `search`, and `custom`: whether the days are a custom range), so an add-on's tiles can follow a custom range or a type. An add-on's tile values go in `extra`, by key; one post's screen shows no add-on tiles.
- filter `nhrrob_post_insights_columns`: `[ 'columns' => [...], 'tiles' => [...] ]`, each entry `key`, `label`, `kind` (`number`, `time` in seconds, or `text`). `Columns::get()` drops a malformed entry and passes the rest to the app as `nhrrobPostInsightsApp.extra`.
- filter `nhrrob_post_insights_posts`: the rows of one page of `GET /posts`, with the days and the resolved filters; fill a declared column in each row's `extra`, by key. Only that page's rows, so an add-on never loads every post.
- action `nhrrob_post_insights_enqueue`: on the app's screen only, after the app's script (handle passed). Enqueue the add-on's script here, depending on `wp-hooks` and `wp-element`; the app starts on `DOMContentLoaded`, so a footer script is in time.
- JS filter `nhrrobPostInsights.screens`: the left menu's screens, `[ { id, label, component } ]`, optionally `group` (a heading above the add-on's items) and `icon` (an element; without it a placeholder icon). A screen gets the same props as Overview (`data` of `/stats`, `range`, `against`, `query`, `extra`, `onNavigate`, `onOpen`). An entry without an id, label or component, or with an id already used, is dropped.
- JS filter `nhrrobPostInsights.postTabs`: the tabs of one post's screen, `[ { id, label, component } ]`, with the post as the second argument. The first tab is the post's own numbers; a tab gets the screen's props (`data`, `post`, `range`, `against`, `query`). One tab shows no tab bar.

## Commands

```
npm run build               # admin/build
npm run lint:js
composer phpcs
composer test:unit
```

After a change to `admin/src`, run the lint and the build and commit `admin/build`.

## Releases

Raise `Version` in the header (and the class constant) and `Stable tag`, and add a `= x.y.z =` section under `== Changelog ==` in `readme.txt`. Merging that into `main` runs `release.yml`, which tags `v<version>` and makes the GitHub release. Once the `SVN_USERNAME` and `SVN_PASSWORD` secrets are set (after WordPress.org approves the plugin), the same run sends the version to WordPress.org; until then those steps are skipped. The first time, run the workflow by hand to send the current version. The blog (`nhrrob/nazmulrobin_blog`) requires the plugin by these tags with Composer, so a release reaches it in Dependabot's next pull request.
