# DESIGN — Post Insights admin app

> Dev-only document. Implemented in `admin/src/style.scss` (tokens and layout) and `admin/src/components/` (primitives). The chosen mockups are in `docs/design/` (option A of issue #19, chosen on 2026-11-20).

## 1. Direction

A sibling of Secure, Database Cleaner and Social Share: the same shell (gradient app bar, left menu, one screen per section), wp-admin density and the system font stack. Its one accent is **teal**, used for the app bar, the active menu item, the chosen range, the bars and links, so it is related to the others (Social Share is indigo) but not confused with them. No framework, no icon font, no chart library: CSS custom properties and inline SVG only.

The name shown in the UI is **Post Insights**. "NHR" never appears.

## 2. Tokens (`.nhrrob-post-insights-app`, prefix `--ir-`)

| Token | Light | Dark | Use |
|---|---|---|---|
| `--ir-accent` / `--ir-on-accent` | `#0f766e` / `#fff` | `#5eead4` / `#04201c` | links, chosen range, active menu text, focus ring |
| `--ir-accent-soft` | `#e3f3f0` | `#123430` | active menu item, chosen card |
| `--ir-tint` | `#e8f5f2` | `#123430` | the bar behind a ranked row |
| `--ir-grad-a` → `--ir-grad-b` | `#0d5c56` → `#13877b` | `#0c3532` → `#155e57` | app bar |
| `--ir-bar` / `--ir-bar-active` | `#14857a` / `#0b4f4a` | `#2dd4bf` / `#99f6e4` | chart bars and meters, the hovered bar |
| `--ir-up` / `--ir-down` | `#107a4c` / `#b3261e` | `#4ade80` / `#f87171` | a change up or down; always with an arrow, never colour alone |
| `--ir-surface` / `--ir-bg` / `--ir-sunken` | `#fff` / `#f6f8f8` / `#f6f8f8` | `#161c1e` / `#0f1415` / `#11171a` | panel / app / table head |
| `--ir-track` / `--ir-grid` | `#edf1f0` / `#edf1f0` | `#222b2e` / `#1f282a` | empty part of a meter, gridlines |
| `--ir-border` / `-strong` | `#dfe5e4` / `#c3cecc` | `#263033` / `#3a4648` | |
| `--ir-text` / `--ir-muted` | `#1d2327` / `#55606a` | `#e6edf0` / `#9aa6ab` | muted passes 4.5:1 on the surface |

**Dark theme:** the tokens are redefined under `@media (prefers-color-scheme: dark)` guarded by `:not([data-theme="light"])`, and again under `[data-theme="dark"]`, so the app-bar switch wins both ways. `html:has(...)` paints wp-admin's canvas (`#0b0f10`). The choice is kept in `localStorage` (`nhrrob-post-insights-theme`).

**Type:** screen title 22px/600; panel title 15px/600; headline number 28px/600 with tabular figures; labels and notes 13px muted; hosts in the mono stack.

## 3. Layout

- **App bar**: the mark, "Post Insights", the theme switch (44px target).
- **Left menu**, 196px: Overview, Posts, Referrers, then the line "No cookies, no visitor's address. Nothing leaves this site." Below 782px it becomes a row above the content and the line is hidden.
- **Screen head**: the title, with its one-line explanation on the right.
- **Filter row**, the same on every screen: the days as one segmented control (7 days, 30 days, 90 days, All time), Custom dates, Type (when there is more than one type), and on the right what the numbers are compared with ("Compared with Sep 22 – Oct 21", or "Since May 5, 2026, the first day with a view").

## 4. Components

- **Kpi**: label, the number, then the change against the same number of days before ("↑ 8% vs the 30 days before"), or "since …" for all time. Four on Overview: Views, Average time on a post, Posts read, From other sites. One post has three (no Posts read).
- **Panel**: a head (title, a muted note, actions), the body, an optional foot link ("All posts →").
- **DayChart**: a scale of 4 or 5 round steps, dashed gridlines, a solid base line, five dates under it. Each bar is a button: hover or focus shows its number and day in a tip. Up to 90 days a bar is a day; longer a week, past two years a month, and the panel title says which.
- **BarList**: a ranked list with a tinted bar behind each row, as long as its share of the largest: Most read posts, Where readers came from.
- **Change**: the change, green up, red down, muted for "New" or "No change"; a dash in a table when there is nothing to compare.
- **Tag**: a small rounded label for a source's kind (Search, Social, Site, Direct).
- **Grid**: a table with a sunken head row, 48px rows, numbers right-aligned with tabular figures.
- Button, Note, Loading, External link and Toast as before.

## 5. Screens

- **Overview**: the filter row, four Kpis, the views chart, then Most read posts (a row opens that post) and Where readers came from side by side, then **From your other plugins**: the add-on tiles as a list of label and value, kept apart from the range's numbers because an add-on may count all time.
- **Posts**: the table of every published post: Post (title opens it; Details, Edit, View under it), Published, Views with a meter, Change, Average time, then the add-on columns. Search titles in the panel head; pages under it.
- **One post**: "← All posts", the title, published date and View post / Edit, the filter row, three Kpis, the chart and its sources side by side.
- **Referrers**: five cards that narrow the list (All sources, Search, Social, Other sites, No referrer), each with its share and views, then one ranked table: rank, source, kind, share with a meter, views, change. The kind is told from the host name in `admin/src/sources.js`.

## 6. Pro

Pro adds to this app, never a separate UI (the Pro repo's PRD): a "Pro" group in the left menu (Reading, Read next), tabs on one post (Reading map, Read next), columns through the columns hook and a Kpi. Without Pro none of it shows, and nothing in the free plugin mentions Pro. The menu group and the tabs come through the JS filters `nhrrobPostInsights.screens` and `nhrrobPostInsights.postTabs` (`CLAUDE.md`): a group heading in the left menu, and a segmented tab bar under the filter row on one post ("Numbers" first), shown only when there is more than one tab.

## 7. Copy

Plain words from the site owner's side. Say what is counted and what is not. Every number says the days it covers.
