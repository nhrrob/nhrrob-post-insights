# Design files

The mockups the dashboard is built from: option A of
[#19](https://github.com/nhrrob/nhrrob-post-insights/issues/19), chosen on
2026-11-20 over option B (tabs in the app bar). They are the source files of
a Claude design canvas, kept here so the design is versioned next to the code.
The canvas is private to the owner's Claude account:
<https://claude.ai/artifact/5MVXQwKgzZyRyRGCHZLKTy>.

| File | What it shows |
| --- | --- |
| `Main.dc.html` | Overview: the filter row, four headline numbers with their change, the chart, most read posts, where readers came from, and the numbers other plugins add |
| `Posts.dc.html` | Posts: the table with views, change, average time and add-on columns |
| `Post.dc.html` | One post, with the Pro tabs it gets when Pro is active (Reading map, Read next) |
| `Referrers.dc.html` | Referrers: cards by kind, then one ranked list |

Each file has three switches in the canvas: Pro active, Planned free (the
items under "Free, once there is time" in `.ai/PRD.md`) and Dark. The numbers
are sample data. `.ai/DESIGN.md` is the written rule set; where the two
differ, the code follows `.ai/DESIGN.md`.

The Pro screens (Reading, Read next) belong in the Pro repo's `docs/design/`.
