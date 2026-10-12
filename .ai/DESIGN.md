# DESIGN — Report Issue admin app

> Dev-only document. Implemented in `admin/src/style.scss` (tokens) and `admin/src/components/ui.js` (primitives).

## 1. Direction

A sibling of the Secure and Database Cleaner apps: the same shell (gradient app bar, collapsible left nav, one screen per section), the same token structure and the same primitives. Its accent is **graphite**, the colour of GitHub's own chrome, so it is related to the other two but not confused with them, and it stays quiet: the semantic colours carry state, links are blue. wp-admin density and the system font stack. No framework, no icon font, no images: CSS custom properties and inline SVG only.

The name shown in the UI is **Report Issue**. "NHR" never appears.

## 2. Tokens (`.nhrrob-post-insights-app`)

| Token | Light | Dark | Use |
|---|---|---|---|
| `--ir-primary` | `#32383f` | `#adb6c0` | focus ring, panel icons |
| `--ir-primary-600` | `#0969da` | `#58a6ff` | links, soft-button text |
| `--ir-grad-a` → `--ir-grad-b` | `#24292f` → `#57606a` | `#2b3138` → `#4a535d` | app bar, active nav, primary button |
| `--ir-soft` | `#eaeef2` | `#21262d` | soft button, hover |
| `--ir-success` / `--ir-warning` / `--ir-danger` / `--ir-info` (+ `-bg`) | as in Secure | as in Secure | open, token missing, errors, notes |
| `--ir-surface` / `--ir-bg` / `--ir-sunken` | `#fff` / `#f6f8fa` / `#eef1f4` | `#161b22` / `#0d1117` / `#10151c` | panel / page / table head |
| `--ir-border` / `-strong` | `#e1e5ea` / `#cfd6dd` | `#262d36` / `#363e48` | |
| `--ir-text` / `--ir-muted` | `#1f2328` / `#59636e` | `#e6edf3` / `#9198a1` | |

**Dark theme:** the same tokens redefined under `@media (prefers-color-scheme: dark)` guarded by `:not([data-theme="light"])`, and again under `[data-theme="dark"]`, so the app-bar toggle wins both ways. Dark mode darkens the whole screen: `html:has(.nhrrob-post-insights-app…)` paints wp-admin's page canvas and footer. The choice is remembered in `localStorage` (`nhrrob-post-insights-theme`); the nav state in `nhrrob-post-insights-nav`.

## 3. Components

Panel, Pill (`ok | warn | bad | info | off`, always with a dot), Button (`primary | soft | ghost | danger`, `small`), Field (label, control, help), Note (`info | warn`), Grid (table with an uppercase header on a sunken band), External (a link that opens GitHub in a new tab, with an arrow icon), Toast bottom-right (success 3 s, errors 12 s).

## 4. Screens

- **Report**: the form on the left; on the right "Where it goes" (the repository, and a reminder that a public repository is public) and "Similar open issues" (filled when the title field is left). One primary button.
- **Sent**: a table of issues with a state pill and "Check states". The nav item shows the number of open ones.
- **Settings** (separated in the nav): the token with a pill saying where it is stored, and one row per plugin whose repository saves when the field is left. No Save button for repositories.

Below 960px the Report screen is one column; below 782px the nav becomes a row above the content.

## 5. Copy

Plain words from the site owner's side. Say what will be sent and where. Errors say what went wrong and what to do next ("The token cannot open issues in this repository. Check that…").
