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

- The screen is under Tools → Post Insights, with a "View insights" link on the Plugins screen. The old address under Dashboard redirects there.

## Left out on purpose

- **Unique visitors.** Telling two views apart needs something that identifies the visitor for a day, a hash of the address at least. Left until there is a way that keeps rule 1 of `CLAUDE.md`.
- **Numbers shown to readers.** A view count on the post itself. The data is there; it is a display question.
- **A settings screen.** Nothing needs a setting yet.
- **Protection against a script inflating a counter.** The open routes only add to counters of public posts. A rate limit would need an identifier per visitor.
- **Deleting old rows.** One row per post per day stays small for years.

## Candidates for a paid version

- Unique visitors and returning readers, with a daily rotating hash.
- Views per category, tag, author and series; countries; devices.
- Scroll depth and "read to the end".
- A view count block for the front end, and a "most read" block.
- A weekly email, and export as CSV.
- Campaign links (`utm_*`) and goals.
