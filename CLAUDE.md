# Static Web Publisher — CLAUDE.md

## Project Overview

**Static Web Publisher** is a WordPress plugin (plugin slug: `static-web-publisher`, PHP prefix: `stwbpb_`) that makes WordPress sites part of the **Reader's Web** — a new browsable web ecosystem where site owners provide content but readers control styling and layout.

The plugin serves content in several document formats, embeds metadata into regular HTML pages for compatible clients, and can also serve the full Reader UI directly from WordPress.

---

## The Reader's Web

The Reader's Web is a new part of the browsable web where **content is separated from presentation**. It is similar in philosophy to RSS — the site owner provides only the content, while the reader's software decides how to style and display it — but unlike RSS it is part of the browsable web and supports visible connections between pages.

The Reader's Web is also referred to as the **Default Web** or **Web 1.1** in the codebase and specs.

### Document Formats

There are three new standalone document formats and three equivalent embedded variants.

#### Standalone Formats

| Format | Root element | Content | File extension |
|--------|-------------|---------|----------------|
| **HDOC** | `<hdoc>` | HTML or plain text (no scripts, no styles) | `.hdoc` |
| **CDOC** | `<cdoc>` | An SVG image (a collage) | `.cdoc` |
| **CONDOC** | `<condoc>` | A connection-only document that loads another site's page as the main doc | `.condoc` |

**HDOC** is the primary text document type. It is XML-based, script-free, style-free. Structure: `<metadata>`, `<header>`, `<fallback>`, `<content>` (HTML/text), `<panels>`, `<copy-info>`, `<connections>`.

**CDOC** content is an SVG image (a collage). Connections attach to specific coordinate points on the SVG.

**CONDOC** loads an external URL as the left-panel document and connects it with visible connections to pages on the right. It allows annotating any third-party page with connections without modifying it.

#### Embedded Variants

Embedded versions **piggyback on regular HTML pages** — they serve both ordinary visitors and HDOC-aware clients from the same URL:

- **Embedded HDOC** — the HTML page contains a `<div class="hdoc-content">` with the main content and a `<script type="application/json" id="hdoc-data">` block with structured metadata (header, panels, connections, removal-selectors).
- **Embedded CDOC** — the reader template embeds the CDOC source in a `<script type="application/json" id="cdoc-source">` tag.
- **Embedded CONDOC** — same pattern, using `id="condoc-source"`.

#### Visible Connections

Documents connect to each other using **visible connections** (called "floating links" or "flinks" in the code). A connection specifies:
- The **target document URL**
- The **source anchor** (a text range in an HDOC, or an x/y point in a CDOC)
- The **destination anchor** (text range in the target HDOC, or point in a target CDOC)

The main document is shown on the left; any connected documents open in tabs on the right (within the reader UI, not regular browser tabs).

### Specs

Spec files live in [specs/](specs/). Currently present:
- [specs/HDOC_spec.md](specs/HDOC_spec.md) — full HDOC format specification
- [specs/Embedded_HDOC_spec.md](specs/Embedded_HDOC_spec.md) — Embedded HDOC specification
- [specs/Static_comments_spec.md](specs/Static_comments_spec.md) — comments JSON format

Not all document types have specs in this repo yet.

---

## The Browser Extension

The **Visible Connections** Chrome extension (available on the Chrome Web Store) is the primary client that supports these document formats. When the user mentions "extension," they are referring to this extension.

The entire [reader/](reader/) folder is code **copied from the extension**. It provides the same Reader UI that the extension injects into the browser. The plugin uses this code to serve the Reader UI directly as a WordPress template — so visitors without the extension can still experience the Reader's Web.

The reader JS is authored as ES modules. In production (`WP_DEBUG` false) a minified bundle [dist/reader.bundle.min.js](dist/reader.bundle.min.js) is served; in development (`WP_DEBUG` true) the raw ES modules from [reader/](reader/) are served directly (entry point: [reader/readerStartUp.js](reader/readerStartUp.js)).

---

## Plugin Architecture

### Entry Point

[static-web-plugin.php](static-web-plugin.php) — registers all hooks, rewrite rules, query vars, and template overrides. All functions and options use the `stwbpb_` prefix.

### Includes

| File | Purpose |
|------|---------|
| [includes/page-methods.php](includes/page-methods.php) | Per-post meta box (doc type, display mode, author/date visibility, connections, CDOC SVG, CONDOC URL). Helper functions: `stwbpb_get_effective_display_mode()`, `stwbpb_get_effective_doc_type()`, `stwbpb_get_doc_effective_display_mode()`. |
| [includes/hdoc.php](includes/hdoc.php) | `stwbpb_send_hdoc_for_post()` — builds and outputs a standalone HDOC. |
| [includes/cdoc.php](includes/cdoc.php) | `stwbpb_build_cdoc_source()` / `stwbpb_send_cdoc_for_post()` — builds CDOC output. |
| [includes/condoc.php](includes/condoc.php) | `stwbpb_build_condoc_source()` / `stwbpb_send_condoc_for_post()` — builds CONDOC output. |
| [includes/panels.php](includes/panels.php) | `stwbpb_get_panels()` — builds the `<panels>` XML block from global settings. `stwbpb_get_seo_panel_data()` parses panels XML into an array for SEO-friendly PHP rendering in the reader template. `stwbpb_has_comment_section()` — checks if the side panel with comments should be shown. |
| [includes/settings.php](includes/settings.php) | Admin settings page (global defaults for top/bottom panels, display modes, comments labels). Option key: `stwbpb_settings`. |
| [includes/comments-json.php](includes/comments-json.php) | `stwbpb_send_comments_json_from_post()` — serves comments as a JSON array at `/json-comments/?post=ID`. Supports pagination (`page`, `per_page`) and ordering (`order=asc\|desc`). |
| [includes/comment-form.php](includes/comment-form.php) | `stwbpb_handle_comment_form()` — serves and processes a minimal HTML comment form at `/sw-comment-form/?post=ID`. Supports replies via `parent_id`. On successful submission posts `{type:'swp-comment-submitted'}` to the parent frame via `postMessage`. |
| [includes/doc-files.php](includes/doc-files.php) | `stwbpb_send_doc_file()` — serves standalone `.hdoc`, `.cdoc`, `.condoc` files from the `static-documents/` directory in the WordPress root. |
| [includes/proxy.php](includes/proxy.php) | `stwbpb_proxy_fetch()` — a server-side proxy at `/sw-proxy/` that fetches remote documents on behalf of the reader. Access is restricted: the `source_url` must resolve to a known post, and the `target_url` must be in that post's connections list (or, for CONDOCs, match the `_condoc_main_url`). Connection URLs are cached in a transient (`swp_connections_{post_id}`, 5 min TTL, invalidated on `save_post`). |

### Templates

[templates/reader-template.php](templates/reader-template.php) — full Reader UI template, adapted from the extension's HTML. Served when a post/page is in `doc_in_reader` display mode. Contains all the DOM structure the reader JS expects. The template also renders SEO-friendly panel content (logo, site name, top links, bottom sections) in PHP so it is visible without JS.

### Reader (Frontend)

The [reader/](reader/) directory mirrors the extension's frontend codebase:

- **Entry:** [reader/readerStartUp.js](reader/readerStartUp.js) — detects embedded CDOC/CONDOC vs. embedded HDOC and routes to the appropriate parser and loader.
- **Core managers:** `PopupDocumentManager.js`, `ReadingManager.js`, `NoteDivsMethods.js`, `CollageViewer.js`, `CollageDataLoader.js`, `PageInfoManager.js`, `ExportPageManager.js`.
- **Parsers:** `parsers/HDOCParser.js`, `parsers/EmbHDOCParser.js`, `parsers/CDOCParser.js`, `parsers/CondocParser.js`, `parsers/HtmlPageParser.js`, `parsers/PlainTextParser.js`, `parsers/ParsingManager.js`.
- **Models:** `models/FloatingLink.js`, `models/FLEnd.js`, `models/FLTextEnd.js`, `models/FLPointEnd.js`, `models/Line.js`, `models/Crosshair.js`, `models/ImageView.js`, `models/Viewport.js`.
- **Utilities:** `helpers.js`, `constants.js`, `Globals.js`, `NetworkManager.js`, `KeyboardManager.js`, `LocalStorageManager.js`, `HeaderMethods.js`, `MultipleLinksPopupManager.js`, `Icons.js`.
- **Styles:** `reader.css`, `ExportPage.css`, `PageInfo.css`, `hdocStyles.css`, `themes/light.css`, `themes/dark.css`, `themes/sepia.css`, `themes/screenshot-theme.css`.
- **Third-party:** `dompurify/purify.es.mjs` (HTML sanitizer), `hashing/sha256-es/` (SHA-256 for floating link hashing).

Global state lives in [reader/Globals.js](reader/Globals.js): `g.pdm` (PopupDocumentManager), `g.readingManager`, `g.noteDivsManager`.

The reader JS is injected as `type="module"`. A `window.vcReaderData` object is set before the module loads, containing `assetsUrl` (path to `reader/images/`) and `proxyUrl` (the `/sw-proxy/` endpoint URL).

---

## Display Modes

Each post or page can be served in one of four modes, configurable globally in Settings and overridable per-post in the meta box:

| Mode | Behaviour |
|------|-----------|
| `embedded_hdoc` | Regular WordPress HTML page with `#hdoc-content` wrapper and `#hdoc-data` JSON injected into the footer. Compatible clients detect the embedded HDOC automatically. Rendered as an HDOC only when it is loaded as a connected document on the right side. |
| `embedded_hdoc_forced` | Same as above but the `"forced": true` flag in the JSON tells the extension to always render as HDOC, even when the page is the main page shown on the left side. |
| `doc_in_reader` | WordPress serves the full Reader UI template instead of the regular theme. The reader JS loads the embedded doc content directly but also uses `#hdoc-content` and `#hdoc-data` so the extension can extract useful information and show it in its own UI. |
| `standalone_doc` | WordPress serves the raw document (HDOC/CDOC/CONDOC) at the post's URL with `Content-Type: text/plain`. |

CDOC and CONDOC posts always default to `doc_in_reader` unless explicitly set to `standalone_doc`.

---

## Custom Endpoints / Rewrite Rules

| URL pattern | Query var | Handler |
|-------------|-----------|---------|
| `^static/(.+)$` | `doc_viewer_matches` | `stwbpb_send_doc_file()` — serves files from `ABSPATH/static-documents/` |
| `^json-comments/?(.+)?$` | `json_comments_custom_matches` | `stwbpb_send_comments_json_from_post()` |
| `^sw-proxy/?$` | `sw_proxy_request` | `stwbpb_proxy_fetch()` |
| `^sw-comment-form/?$` | `sw_comment_form_request` | `stwbpb_handle_comment_form()` |

All rules use priority `top`. After adding or changing rewrite rules, go to **Settings > Permalinks** and click **Save Changes**.

---

## Post Meta Keys

| Meta key | Purpose |
|----------|---------|
| `_doc_type` | `HDOC` (default), `CDOC`, or `CONDOC` |
| `_hdoc_display_mode` | Per-post display mode override (`default`, `embedded_hdoc`, `embedded_hdoc_forced`, `doc_in_reader`, `standalone_doc`) |
| `_hdoc_author_name_display` | `default`, `show`, or `hide` |
| `_hdoc_publish_date_display` | `default`, `show`, or `hide` |
| `_static_web_connections_info` | Raw XML fragment of `<doc>` elements listing outgoing connections |
| `_cdoc_svg` | Raw SVG markup for CDOC posts |
| `_condoc_description` | Description text for CONDOC posts |
| `_condoc_main_url` | The external URL the CONDOC loads as its main document |

---

## Global Settings (option: `stwbpb_settings`)

Stored as a PHP array in the `stwbpb_settings` WordPress option.

Key fields: `page_mode`, `post_mode`, `page_author_name`, `page_publish_date`, `post_author_name`, `post_publish_date`, `removal_selectors`, `side_panel_on_the_left`, `comments_title`, `no_comments_message`, `reply_button_label`, `leave_comment_label`, `top_panel` (main_link, main_title, logo_url, links[]), `bottom_panel` (bottom_message, sections[]), `show_promotion_button`.

---

## Static Document Files

Standalone `.hdoc`, `.cdoc`, `.condoc` files can be placed in `ABSPATH/static-documents/` (i.e., the `static-documents` folder in the WordPress site root). They are served inline (not as downloads) via the `/static/filename.ext` URL, which allows the browser extension to intercept and render them.

---

## Branches

- `master` — main branch
- `comments-posting` — current active branch; adds comment form posting support
- `embedded-hdocs` — previous feature branch for embedded HDOC format
- `reader-for-hdocs` — previous feature branch for the Reader UI template
- `rss-feeds` — previous feature branch (RSS support)

---

## Misc Notes

- The plugin function prefix is `stwbpb_` (static web publisher block/plugin).
- WordPress block editor comments (`<!-- wp:... -->`) are stripped from HDOC content via `stwbpb_strip_wp_tags()`.
- YouTube embeds are converted to `<iframe>` tags before output.
- The `the_content` filter wraps post content in `<div id="hdoc-content">` for embedded HDOC detection.
- The `template_include` filter swaps in the reader template for `doc_in_reader` mode.
- Connection URLs are cached in transients (`swp_connections_{post_id}`) and invalidated on `save_post`.
- The admin JS/CSS in [includes/admin.js](includes/admin.js) and [includes/admin.css](includes/admin.css) power the settings page UI (dynamic link/section management and the WordPress media uploader for the logo).
- A promo popup can be optionally shown via the `show_promotion_button` setting.
- To build code for production use this command (esbuild is installed globally):
esbuild reader/readerStartUp.js --bundle --minify --format=esm --target=safari12 --outfile=dist/reader.bundle.min.js

