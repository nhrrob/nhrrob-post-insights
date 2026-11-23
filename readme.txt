=== NHR Post Insights ===
Contributors: nhrrob
Tags: analytics, statistics, post views, referrers, privacy
Requires at least: 6.6
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 0.6.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

How many readers each post gets, where they come from and how long they stay. No cookies, and the numbers stay on your site.

== Description ==
NHR Post Insights counts the views of your posts and pages and shows them in the dashboard. It sets no cookie, stores nothing in the visitor's browser and sends nothing to another service.

### What it shows
- **Tools → Post Insights** has three screens, each for the last 7, 30 or 90 days, all time, or a range of days you choose, and for every post type or one.
- The filters are kept in the address, so a filtered view can be bookmarked.
- Each number shows its change against the same number of days before.
- **Overview**: views, the average time on a post, the posts read and the views from other sites, a chart of views per day (per week or month for long ranges), the most read posts and where readers came from.
- **Posts**: every published post with its views, their change and average time, sortable by title, date, views or average time, with a search of the titles. A post opens its own screen with its numbers, chart and sources.
- **Referrers**: the sites readers followed a link from, grouped into search, social, other sites and no referrer, each with its share of the views.
- A light and a dark look, following the system or chosen in the app.

### What is counted
- A view is counted when the page is actually looked at, not when it loads in a background tab.
- Time is counted only while the tab is in view, up to 30 minutes for one view.
- Your own views are not counted while you are logged in as someone who can write posts. Crawlers and link previews are left out.
- Posts that are not published, or that have a password, are not counted.

### What is stored
One small table holds the numbers: per post and day, the views and the time; per post, day and referring site, the views. Of a referrer only the site's name is kept, never the page. No IP address is stored.

== External services ==
This plugin does not connect to any external service.

== Installation ==
1. In the dashboard, go to Plugins → Add New Plugin and search for "NHR Post Insights", or upload the plugin's zip there.
2. Activate it. Counting starts with the next view of a post; nothing needs to be set up.
3. Open Tools → Post Insights, or "View insights" under the plugin on the Plugins screen.

== Frequently Asked Questions ==

= Does it work with a page cache? =
Yes. The view is reported by a small script in the page, so a cached page is counted like any other.

= Do I need a cookie notice for it? =
The plugin sets no cookie and keeps nothing that identifies a visitor. Whether your site needs a notice for other reasons is for you to check.

= Why is the number lower than in another tool? =
Visitors who block scripts are not counted, and neither are crawlers, your own views or tabs that were never looked at.

= What is removed when I delete the plugin? =
Its table and its one option.

= Where is the source of the dashboard's script? =
In the plugin's `admin/src` folder. `admin/build` is made from it with `@wordpress/scripts` (`npm run build`). The script that counts views, `assets/tracker.js`, is not built.

== Screenshots ==

1. Overview: views, average time on a post, posts read and views from other sites, each against the days before, with views per day, the most read posts and where readers came from.
2. Posts: every published post with its views, their change and the average time, sortable and searchable.
3. One post: its own numbers, views per day and sources.
4. Referrers: sources grouped into search, social, other sites and no referrer, each with its share and change.
5. The dark theme.

== Changelog ==

= 0.6.0 =
* The counts are kept in one table instead of two. The rows of the two old tables are moved into it on the first page load after the update, and the old tables are removed. Nothing changes on screen.
* Another plugin can keep counters of its own kind in the same table, instead of adding tables.

= 0.5.2 =
* Changing a filter, opening a post or switching screens no longer empties the screen: the numbers stay, dimmed while new ones load, and the page keeps its height.
* Back on Posts, the table shows at once.
* A menu click scrolls to the top of the screen when it is out of view.

= 0.5.1 =
* The same space on both sides of the screen.
* One database query less on every page, and fewer on the Posts screen.
* Another plugin can add screens to the menu and tabs to one post, with the JS filters `nhrrobPostInsights.screens` and `nhrrobPostInsights.postTabs` and the action `nhrrob_post_insights_enqueue`.

= 0.5.0 =
* New screens: one teal look in light and dark, with a theme switch.
* Overview shows views, average time, posts read and views from other sites, each with its change against the same number of days before, a chart with a scale and the number of each bar, and the most read posts and sources side by side.
* Posts has a Change column; the title opens the post.
* Referrers groups the sources into search, social, other sites and no referrer.
* The stats route returns `read` and `previous`, and each row of the posts route `before`.

= 0.4.0 =
* Filters on every screen: a custom range of days next to 7, 30, 90 days and all time, and the post type.
* Posts has a search of the titles.
* The filters are kept in the address, so a filtered view can be bookmarked.
* The filters `nhrrob_post_insights_stats` and `nhrrob_post_insights_posts` pass the chosen range and type to another plugin.

= 0.3.0 =
* The screen moved to Tools → Post Insights, with a "View insights" link on the Plugins screen. The old address under Dashboard redirects there.
* One post: "Details" in the Posts table opens a post's views, average time, views per day and referring sites.
* "No referrer" on Referrers and on one post counts the views that came without a referring site.
* Sorting Posts by average time uses the whole range, not one day's row.

= 0.2.0 =
* Posts lists every published post, also those without a view, sortable by title, date, views and average time, 20 to a page, with a link to edit each.
* "All time" next to 7, 30 and 90 days on every screen.
* Another plugin can add columns to Posts and tiles to Overview with the filters `nhrrob_post_insights_columns` and `nhrrob_post_insights_posts`.

= 0.1.0 =
* First version: views, average time and referrers per post, with a chart of views per day.
