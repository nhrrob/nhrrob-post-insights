# NHR Post Insights: scope

## Purpose

Show a site owner how many readers each post gets, where they come from and how long they stay, without a cookie, an outside service or anything that identifies a visitor.

## Version 0.1

- Views per post per day, counted by a script when the page is looked at.
- Average time on a post, counted while the tab is in view, capped at 30 minutes a view.
- Referring sites, by host name only.
- Dashboard → Post Insights: Overview (totals and views per day), Posts, Referrers, each for 7, 30 or 90 days.
- Not counted: logged-in users who can edit posts, crawlers, unpublished and password-protected posts.

## Version 0.2

- Posts is the table of every published post, sortable and paged by the route, with the post's date and a link to edit it.
- "All time" as a range on every screen.
- An add-on declares columns for Posts and tiles for Overview, and fills them; the app draws them by kind (number, time or text), so the add-on needs no script.

## Version 0.3

- One post: "Details" in the Posts table opens its views, average time, views per day and referring sites, for the chosen days.
- "No referrer" on Referrers and on one post: the views that came without a referring site.
- The screen is under Tools → Post Insights, with a "View insights" link on the Plugins screen. The old address under Dashboard redirects there.

## Version 0.4

- Filters, free: the post type, a custom date range (from and to, at most about five years, ending today at the latest) and a title search on Posts. They sit in the one row above the numbers and narrow Overview, Posts, Referrers and one post alike, and they are kept in the address, so a filtered view can be bookmarked. An unknown value is ignored, not an error.

## Version 0.5

- The screens rebuilt to a design (#19, option A, `.ai/DESIGN.md`): one accent, four headline numbers on Overview, each with its change against the same number of days before, a chart with a scale, gridlines and hover values that switches to weeks past 90 days and months past two years, most read posts and where readers came from as ranked lists, and the add-on tiles in their own panel.
- Posts gains a Change column; Referrers gains cards by kind (search, social, other sites, no referrer, told from the host name) and a change per source.

## Left out on purpose

- **Unique visitors.** Telling two views apart needs something that identifies the visitor for a day, a hash of the address at least. Left until there is a way that keeps rule 1 of `CLAUDE.md`; #4 decides (see below).
- **A settings screen.** Nothing needs a setting yet.
- **Protection against a script inflating a counter.** The open routes only add to counters of public posts. A rate limit would need an identifier per visitor.
- **Deleting old rows.** One row per post per day stays small for years.

## Once there is time

Not planned in a version yet. Each of these is free in other WordPress plugins too.

- **Category, tag and author filters**, next to the post type (free in Independent Analytics and WP Statistics). This reverses the call on #6.
- **A view count block and a "most read" block** (free in Koko, Post Views Counter, Top 10).
- **"Read to the end"**: how many views reached the end of the post, counted in the browser (free in Semantic Journey).
- **Export as CSV** of what a screen shows (free in Independent Analytics, WP Statistics).
- **A weekly email** of the week's numbers (free in Post Views Counter, Burst).
- **Campaign links**: views by `utm_source` and `utm_campaign`, read from the address in the browser (free in SlimStat, WP Statistics).
- **Devices**: phone, tablet or desktop from the screen width in the browser, never the user agent (free in Independent Analytics, Burst).
- **Countries** only if it can be done on the site without a GeoIP download (rule 2). Free in Independent Analytics, Burst and others.
- **Readers, not only views**: free in Koko, Independent Analytics, Burst, WP Statistics and SlimStat. Decided on #4: a daily salted hash.
