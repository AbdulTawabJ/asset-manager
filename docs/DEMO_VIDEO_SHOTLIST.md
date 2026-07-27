# Demo Video Shot-List (no narration)

A tight **60–90 second** silent screen recording for LinkedIn. Keep the cursor deliberate, pause ~1.5s on each result so viewers can read. Record at 1080p, light mode. Add soft background music and short on-screen text captions (suggested below) in your editor.

> Tip: record each numbered block as its own clip, then stitch. Re-seed first (`php artisan migrate:fresh --seed`) so the data looks clean.

| # | Duration | On-screen caption | Actions to perform |
|---|----------|-------------------|--------------------|
| 1 | 0:00–0:06 | *"TMF Asset Manager — IT asset tracking, built with Laravel"* | Show the login page (logo visible). Type the admin email, click login. |
| 2 | 0:06–0:18 | *"Role-based dashboard"* | Land on the admin dashboard. Slowly scroll the asset table. Hover the search box, type a keyword (e.g. `Laptop`), show filtered results. |
| 3 | 0:18–0:30 | *"Full CRUD + audit trail"* | Click *Add Asset*, fill a couple of fields, save, show the success toast and the new row. Then open an asset's *Shift* action, pick a new owner/location, save. |
| 4 | 0:30–0:42 | *"Advanced multi-condition query"* | Open Advanced Query. Add two conditions (e.g. `type = Laptop` AND `region = South`). Run it. Show results. Click *Export CSV*. |
| 5 | 0:42–0:54 | *"Guided reports with cascading filters"* | Open the Report Generator. Demonstrate the Region → Branch → Office → Department dropdowns cascading. Generate the report (printable view). |
| 6 | 0:54–1:06 | *"Separate IT review workflow"* | Log out, log in as the IT officer. Show the review queue. Open a pending item, set status = Working, add a remark, submit. Show it clear from the queue. |
| 7 | 1:06–1:15 | *"Laravel 12 · Tailwind · MySQL/SQLite — github.com/AbdulTawabJ/asset-manager"* | End card: the repo URL + your name. (Make this a static frame in your editor.) |

**Export settings:** MP4, H.264, 1080p, 30fps. LinkedIn autoplays muted — the on-screen captions carry the story without sound.
