# PRD — Post Insights PRO (add-on)

Status: **plan only — nothing built** · Owner: Nazmul Hasan Robin (nhrrob)
Last revised: 2026-11-10 · Repo: [nhrrob/nhrrob-post-insights-pro](https://github.com/nhrrob/nhrrob-post-insights-pro) · Settled in [Pro #1](https://github.com/nhrrob/nhrrob-post-insights-pro/issues/1)

> Dev-only document. Excluded from distribution (`.distignore` + `.gitattributes export-ignore`).
> Free/core scope: **[PRD.md](./PRD.md)**. Market check: `nazmulrobin_products/docs/market/post-insights.md`.

## 0. The free-vs-PRO rule (non-negotiable)

1. **If a feature is free in any other WordPress plugin, it is free here.**
2. **If a feature is listed in the free PRD, it is free.**
3. **PRO carries major features only.** No padding ("longer retention", "more rows").
4. **Re-check before building.** Every candidate below is marked *verify*: its market status comes from the 2026-11-10 check, which can show a feature is free somewhere but not prove it is free nowhere.

## 1. Honest starting point

- The free plugin is at 0.4.0, private, and used on one site (the blog). It is not on WordPress.org. There is nobody to sell to yet.
- The niche is crowded with strong free leaders: Burst (200k+), Independent Analytics (100k+), Koko (70k+), WP Statistics (500k+). They give away visitors, countries, devices, campaigns, CSV, emails and "most read" blocks. Of the twelve candidates checked, **ten are free elsewhere** and move to the free PRD (§4).
- What survives is specific to **reading posts**, not general web analytics. That is also the best story for the free plugin: insights for writers, not traffic for marketers.
- **Launch trigger: ~1,000 active installs**, same rule as the Database Cleaner and Secure add-ons. Until then all effort goes into the free plugin. This document exists so the free architecture leaves room (§6, step 1).

## 2. Locked decisions

| Decision | Choice |
|---|---|
| Delivery | Separate add-on plugin. PHP through the free plugin's filters (`nhrrob_post_insights_columns`, `_stats`, `_posts`, `_view`); the dashboard through a JS screens filter that the free plugin must add first (§6) |
| Licensing/billing | Our own store, store.nazmulrobin.com (Laravel, Paddle), as Database Cleaner Pro. Not Freemius |
| Free-side discoverability | None, as in the other plugins, unless nazmulrobin_products#15 decides otherwise |
| Privacy | Pro keeps the free plugin's three rules: nothing identifies a visitor, nothing leaves the site, counting happens in the browser. Both candidates below are plain counts |
| Look | The same app: Pro adds screens and tabs to the free shell (`.ai/DESIGN.md`), never a separate UI |

## 3. Candidate features (pick at most three; all *verify*)

Only two survived the market check. A third slot is open; nothing else passed rule 1.

### 3.1 Reading Map
Where in a post readers stop.
- For each post, the share of views that reached each **heading** (or, without headings, each tenth of the text), drawn as a bar beside the post's own outline: "62% reached *Setting up*, 18% reached *Conclusion*".
- The biggest drop between two headings is marked, so a writer sees which section loses readers.
- Site-wide: the posts read furthest and the posts most often left early, for the chosen range.
- Counted in `tracker.js` with IntersectionObserver on the post's headings: one counter per post, day and heading reached, the furthest heading per view only. No pixels, no timestamps per reader.

*Market:* scroll heatmaps are paid in SlimStat Pro; scroll depth is a paid hosted feature in Plausible; Reading Progress Tracker limits its average-depth report to 10 pages unless paid. **Free elsewhere:** milestone counts (started, middle, end) in Semantic Journey, so plain **"read to the end" goes to the free PRD**. The section-by-section map was not found free. **Verify** before build.

### 3.2 Read Next
What a reader does after a post.
- For each post: the posts readers opened next from it, as counts, and the share of its views that led to no other post (its views less those next views).
- Site-wide: the strongest post-to-post paths, and posts that send no one anywhere (a hint to add links).
- Counted from the next view's referrer when it is a page **of this site**: its path is reduced to a post ID before storage, one counter per day, from-post and to-post. No visitor is linked across views.

*Market:* internal link click counts are free (Link Tracker), so a plain click counter is not Pro. Page-to-page journeys are paid and per visitor (Independent Analytics Pro "User journeys", SlimStat Pro "User overview"). An aggregate post-to-post count was not found. **Verify** before build.

*Privacy note:* the free plugin keeps only the referring **host**. Read Next keeps one more thing: which of our own posts a reader came from, as a post ID. It names a page of the site, not the visitor, so rule 1 holds, but `readme.txt` ("Of a referrer only the site's name is kept") must say so when Pro is active.

## 4. Considered and rejected for PRO

| Idea | Why not |
|---|---|
| Unique visitors, returning readers (daily salted hash) | Free in Koko, Independent Analytics, Burst, WP Statistics, SlimStat. Free or not at all: free #4 decides |
| Views per category, tag and author | Free in Independent Analytics (author, post type) and WP Statistics (author, categories) → free PRD. Reverses the call on free #6 |
| Countries | Free in Independent Analytics, Burst, WP Statistics, SlimStat, Jetpack. Needs a GeoIP lookup, which breaks rule 2 if downloaded → free PRD, "left out" unless a local way is found |
| Devices | Free in Independent Analytics, Burst, WP Statistics → free PRD |
| View count block, "most read" block | Free in Koko, Post Views Counter, Top 10, Independent Analytics → free PRD |
| Weekly email | Free in Post Views Counter, Burst, WP Statistics → free PRD |
| CSV export | Free in Independent Analytics, WP Statistics → free PRD |
| Campaign links (`utm_*`) | Free in SlimStat, WP Statistics → free PRD |
| Goals | Free in Burst, SlimStat (one goal); little use on a reading site → free PRD, not planned |
| Traffic change alerts | Free in Post Views Counter ("Traffic Signals") |
| Real-time visitors, user journeys, heatmaps of clicks | Need a per-visitor identity or session; break rule 1 |
| Search terms (Search Console) | Needs an outside request; breaks rule 2 |
| Longer history, more rows, more posts in a list | Padding (rule 3) |

## 5. Pricing (placeholder until launch)

The house grid: $39 / $79 / $149 a year (1 / 5 / unlimited sites), with a lifetime tier. Reference points from 2026-11-10, one site a year: Burst Creator €43.61, Independent Analytics $49, SlimStat $49, Koko €59, Post Views Counter $59, WP Statistics $119, MonsterInsights $199. Re-check at launch.

## 6. Order of work

1. **Free plugin, before WordPress.org:** a JS extension point in the dashboard app (`@wordpress/hooks`, a `nhrrobPostInsights.screens` filter as in Secure, and a slot for an add-on's tab on one post's screen), so the free code does not need reshaping later.
2. Free 1.0 on WordPress.org, with the free-side moves from §4 that are cheap and expected (category, tag and author filters; a view count block; read-to-the-end counts).
3. Grow to ~1,000 installs.
4. Re-run §3 market checks.
5. Build the surviving features, Reading Map first (one post's screen already exists, and it is the easier demo).
