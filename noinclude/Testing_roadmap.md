# Testing Roadmap — Static Web Publisher

A phased plan for testing the plugin, written to be picked up incrementally whenever time allows.
Nothing here needs to be done all at once; each phase is useful on its own.

---

## 1. The Big Picture

This project is three testable systems glued together, and each wants a different strategy:

1. **The reader (JS)** — parsers, floating links, viewer UI. Copied from the Visible
   Connections extension; the extension repo is the source of truth for this code.
2. **The WordPress plugin (PHP)** — document builders, endpoints, meta box, settings,
   proxy, comments.
3. **The integration seam** — what WordPress *feeds* the reader: the embedded `#hdoc-data`
   JSON, the PHP-rendered reader template, WP-mangled content (wpautop, stripped block
   comments, YouTube conversion), `window.vcReaderData`, the proxy URL, the comment-form
   iframe.

Most bugs in a system like this live in the seam (#3), and the seam exists **only in the
plugin**. That determines the split below.

### Where frontend testing lives

| Scenario type | Where it is tested | Examples |
|---|---|---|
| Reader core behavior | **Extension repo** (source of truth); smoke-checked in the plugin only after re-syncing `reader/` or rebuilding the bundle | Parsing a valid HDOC, drawing connections, opening right-side tabs, themes, keyboard shortcuts, CDOC pan/zoom |
| Plugin-specific behavior | **Plugin only** — it does not exist in the extension | `doc_in_reader` template with SEO panels rendered in PHP, `window.vcReaderData` wiring, `/sw-proxy/` restrictions, comments JSON pagination, comment form postMessage flow, embedded HDOC detection on a real theme's HTML, minified bundle vs. dev ES modules |

The shared scenario suite gets a **"Runs in"** column: `Extension`, `Plugin`, or `Both`.
`Both` scenarios are executed in the extension every release and only spot-checked in the
plugin when the reader code is re-synced. This avoids doubling the manual workload.

---

## 2. Phase 1 — Test Corpus + Import Script

The highest-value artifact: a versioned set of test documents shared by both projects.
The master copies live in a repo (own repo, or a folder in the extension repo); the test
posts on any WordPress site are **disposable copies** generated from it.

Why files-in-a-repo instead of hand-made pages:

- A test site can be rebuilt with one command instead of an afternoon of clicking.
- Parser unit tests in the extension repo read the exact same fixture files from disk.
- Edge cases (malformed XML, unicode sequences) are precise artifacts; the WP editor
  would silently normalize them, git preserves them exactly.

### Layout

```
test-corpus/
  standalone/          # raw .hdoc / .cdoc / .condoc files
    basic.hdoc
    long-document.hdoc
    all-header-fields.hdoc
    no-header-fields.hdoc
    collage-basic.cdoc
    condoc-basic.condoc
  hostile/             # malformed + injection payloads
    broken-xml.hdoc
    script-in-content.hdoc
    svg-with-script.cdoc
  posts/               # ingredients for WP posts (embedded variants, display modes)
    basic-post/
      content.html     # post body
      meta.json        # _doc_type, _hdoc_display_mode, connections XML, etc.
    cdoc-post/
      content.html
      meta.json        # includes _cdoc_svg
  import.sh            # WP-CLI import script
  README.md            # what each fixture exercises
```

Two consumption modes:

- **`standalone/`** needs no WordPress: parser tests read the files directly; for manual
  testing, copy the folder into `ABSPATH/static-documents/` and each file is instantly
  served at `/static/<name>.<ext>` via the plugin's existing endpoint.
- **`posts/`** only exists as WP posts with meta — the import script turns the
  ingredients into posts.

### The import script

A WP-CLI loop run in the Local site shell — it *creates posts from* the fixture files:

```sh
ID=$(wp post create --post_title="Basic HDOC post" --post_status=publish \
      --post_content="$(cat posts/basic-post/content.html)" --porcelain)
wp post meta update $ID _doc_type HDOC
wp post meta update $ID _hdoc_display_mode doc_in_reader
wp post meta update $ID _static_web_connections_info "$(cat posts/basic-post/connections.xml)"
```

…looped over `posts/`, reading each `meta.json`, plus `cp -r standalone/ <ABSPATH>/static-documents/`.

**Important wrinkle:** connections reference URLs, which differ per site. Fixture files
use a `{{SITE_URL}}` placeholder; `import.sh` substitutes the real URL at import time
(e.g. via `wp option get siteurl` + `sed`). Otherwise connection fixtures only work on
one machine.

### Corpus inventory

Start with 8–10 fixtures; grow it every time a bug reveals a new edge case.

**Starter set (build first):**
- Typical HDOC post, one CDOC post, one CONDOC post
- One post per display mode: `embedded_hdoc`, `embedded_hdoc_forced`, `doc_in_reader`, `standalone_doc`
- Two posts connected to each other (text-range anchors both ends)
- One standalone `.hdoc` file in `static-documents/`
- 2–3 hostile fixtures: broken XML, `<script>`/`onerror=` in content

**Full set (grow toward):**
- Empty content; very long document (10k+ paragraphs); heavy unicode/RTL
- All header fields present vs. all omitted
- Every `republishing_policy` value (global + per-post override + CONDOC exclusion)
- Post with many connections (~50); connection whose target 404s; connection to a
  non-Reader's-Web page
- CDOC with a huge SVG; CDOC connection anchored to a coordinate point
- CONDOC pointing to a slow or unreachable external URL
- Author/date visibility overrides (show/hide/default at post level vs. global)
- Comment fixtures: post with no comments, with paginated comments, with nested replies

**Hostile set (for the security pass, Phase 5):**
- Malformed XML variants
- Script/event-handler injection in HDOC content (DOMPurify must eat these — verify)
- SVG with embedded script in a CDOC
- Proxy calls with a `target_url` **not** in the source post's connections list (must be
  refused — this is the SSRF guard)
- Comment submissions containing HTML/XSS payloads; reply with a forged `parent_id`

---

## 3. Phase 2 — Manual Test Suite (the spreadsheet)

One spreadsheet, two sheets.

### Sheet 1: Test cases

Columns:

| ID | Area | Priority | Runs in | Preconditions | Steps | Expected result | Result | Notes |
|----|------|----------|---------|---------------|-------|-----------------|--------|-------|

- **Priority:** `P1` = smoke (pre-release, ~15–25 cases, under an hour to run),
  `P2` = full regression (big releases), `P3` = rare/edge.
- **Runs in:** `Extension` / `Plugin` / `Both` (see §1).
- **Preconditions** can usually just say "corpus imported" thanks to Phase 1.
- Add a new case every time a bug is found. Duplicate the Result/Notes columns per
  release (or copy the sheet per test run).

### Starter P1 smoke cases (plugin side)

1. Regular post in `embedded_hdoc` mode renders normally in a plain browser; page source
   contains `#hdoc-content` wrapper and valid `#hdoc-data` JSON.
2. `embedded_hdoc_forced` post: JSON contains `"forced": true`; extension renders it as
   HDOC even as the main left-side page.
3. `doc_in_reader` post serves the reader template; content readable; SEO panels
   (logo, site name, top links, bottom sections) present in page source with JS disabled.
4. `standalone_doc` post serves raw document with `Content-Type: text/plain`.
5. CDOC post in reader mode: collage displays, pan/zoom works, point-anchored connection
   opens its target.
6. CONDOC post: external main page loads on the left, connections shown on the right.
7. Connection between two corpus posts: clicking opens target in right-side tab, both
   anchors highlighted correctly.
8. `/static/basic.hdoc` serves the file inline (not as a download).
9. `/json-comments/?post=ID`: correct JSON; `page`/`per_page`/`order` respected;
   empty-comments post returns an empty array, not an error.
10. Comment form at `/sw-comment-form/?post=ID`: submission creates the comment and
    posts `swp-comment-submitted` to the parent frame; reply via `parent_id` nests
    correctly.
11. `/sw-proxy/` fetches a target that **is** in the source post's connections;
    **refuses** one that is not.
12. Proxy connection cache: edit a post's connections, save, and the new target is
    allowed immediately (transient invalidated on `save_post`).
13. Meta box: change doc type / display mode / author-date visibility / republishing
    policy on a post, save, verify each takes effect on the front end.
14. Settings page: change top/bottom panel content, default modes, comment labels —
    verify they appear; per-post override still beats the global default.
15. `republishing_policy = prohibit` globally: `<republishing-policy>` tag appears in
    HDOC/CDOC output; `implicit_allow` omits it; CONDOC never gets it.
16. `WP_DEBUG` false: minified `dist/reader.bundle.min.js` is served and the reader
    works; `WP_DEBUG` true: raw ES modules served and the reader works. **Both** paths
    every release — bundle-only bugs are a classic.
17. After plugin activation + Permalinks re-save, all four rewrite endpoints resolve
    (no 404s).

### Sheet 2: Environment matrix

Rows = P1 suite runs; columns = environments. Not every cell every release — rotate.

- **Browsers:** Chrome (with and without the extension), Firefox, Safari.
- **Devices:** desktop + **iPad Air on iOS 12.5.7** (declared support target; Safari 12
  has no optional chaining/nullish coalescing — which is why the codebase bans them —
  and quirky scrolling).
- **WordPress:** current WP release; plain vs. pretty permalinks; two popular themes for
  embedded mode (theme markup affects embedded HDOC detection).
- **Plugin build:** `WP_DEBUG` on (ES modules) / off (minified bundle).

---

## 4. Phase 3 & 4 — Automated Tests

Aim for automation where manual testing is tedious and regressions are likely, not for
coverage numbers.

### Phase 3: JS parser unit tests (in the **extension** repo)

Highest protection per hour invested; protects both projects at once.

- **Targets:** `HDOCParser`, `EmbHDOCParser`, `CDOCParser`, `CondocParser`
  (pure input → output).
- **Tooling:** vitest (or plain node test runner) reading corpus files from disk.
- **Cases:** every `standalone/` and `hostile/` fixture; assert extracted header fields,
  content, connections, and that hostile input either parses safely or fails cleanly
  (no throw-through, no script survives sanitization).

### Phase 4: PHP unit + endpoint tests (in **this** repo)

- **Tooling:** PHPUnit with the WP test suite, or `wp-env`.
- **Unit targets** (real logic, easy to break silently):
  - HDOC builder (`stwbpb_send_hdoc_for_post`) and `stwbpb_build_cdoc_source()` /
    `stwbpb_build_condoc_source()` — assert output structure against corpus posts.
  - `stwbpb_get_panels()` and `stwbpb_get_seo_panel_data()`.
  - `stwbpb_strip_wp_tags()`.
  - Resolvers: `stwbpb_get_effective_display_mode()`, `stwbpb_get_effective_doc_type()`,
    `stwbpb_get_effective_republishing_policy()` — the default/override matrix.
- **Endpoint targets** (security- and correctness-critical, boring by hand):
  - `/sw-proxy/`: allowed target passes; unlisted target refused; CONDOC `_condoc_main_url`
    allowed; transient invalidation on save.
  - `/json-comments/`: pagination, ordering, empty post, invalid post ID.
  - Comment form: valid submission, reply nesting, hostile payloads stored/escaped safely.

### Deliberately out of scope (for now)

Browser E2E automation (Playwright) for the reader UI. Big investment; stay manual until
the same UI regressions keep recurring, then revisit.

---

## 5. Phase 5 — Security Pass

One dedicated manual session using the hostile corpus:

1. **Proxy (SSRF):** unlisted `target_url`, `source_url` that resolves to no post,
   redirects from an allowed target, internal/localhost target URLs.
2. **Sanitization (XSS):** hostile HDOC/CDOC fixtures through both the extension and the
   plugin reader; confirm DOMPurify strips scripts/handlers; SVG-in-CDOC script content.
3. **Comment form:** XSS payloads in author/body, CSRF behavior, forged `parent_id`,
   direct POSTs bypassing the form.
4. **Static file endpoint:** path traversal attempts on `/static/../...`.
5. **Meta box / settings:** hostile values in `_cdoc_svg`, connections XML, and panel
   settings — saved by an admin but rendered on the public front end.

Log findings as new spreadsheet cases + hostile fixtures so the pass is repeatable.

---

## 6. Order of Attack

| # | Task | Why first |
|---|------|-----------|
| 1 | Corpus + `import.sh` (starter set) | Enables everything else |
| 2 | Spreadsheet with the P1 smoke set | Immediate pre-release safety net |
| 3 | Parser unit tests in the extension repo | Most protection per hour |
| 4 | PHP unit + endpoint tests | Locks down builders and the proxy |
| 5 | Security pass | Needs the hostile corpus from #1 |

Ongoing habits that keep the system alive:

- Every bug found → a new fixture and/or spreadsheet case before (or with) the fix.
- Run P1 smoke before every release; full P2 suite for big releases.
- Re-sync of `reader/` from the extension or bundle rebuild → run the `Both`-tagged
  scenarios in the plugin.
