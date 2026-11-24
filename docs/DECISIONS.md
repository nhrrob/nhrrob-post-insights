# Decisions

Why NHR Post Insights is the way it is, and whether each choice can be
undone. One row per decision, newest last. A pull request that makes a
decision adds its row; reversing one strikes the old row and adds a new one.

| Date | Decision | Why | Can it be undone? | Who |
| --- | --- | --- | --- | --- |
| 2026-11-24 | One table, `{prefix}nhrrob_post_insights` (day, post, kind, item, views, seconds), instead of the daily and referrers tables; another plugin keeps its counters there under its own kind | Robin's rule for every plugin: no table, or one at most, for a plugin and its add-ons together. The rows of 0.5 are moved in once by `Installer` and the old tables dropped | Hard: moving back needs a migration the other way | Robin |
| 2026-11-24 | Other plugins read through `Read` (totals, popular, hosts, referrals), never the table | Robin: a plugin makes sure the sites using it are not affected by its changes, and migrates its own data; the blog and NHR Social Share had their own SQL on the old tables | Yes, but the functions must stay as they are once used | Robin |
| 2026-11-24 | A pushed tag `v<version>` is the whole release: the tag push deploys to WordPress.org and makes the GitHub release, which is no longer made by hand | Robin: fewer manual steps, the same in every plugin | Yes | Robin |
