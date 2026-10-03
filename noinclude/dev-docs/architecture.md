# Plugin Architecture

## Entry Point

[static-web-plugin.php](../../static-web-plugin.php) — registers all hooks, rewrite rules, query vars, and template overrides. All functions and options use the `stwbpb_` prefix.

## Includes

| File | Purpose |
|------|---------|
| [includes/page-methods.php](../../includes/page-methods.php) | Per-post meta box (doc type, display mode, author/date visibility, republishing policy, connections, CDOC SVG, CONDOC URL). Helper functions: `stwbpb_get_effective_display_mode()`, `stwbpb_get_effective_doc_type()`, `stwbpb_get_doc_effective_display_mode()`, `stwbpb_get_effective_republishing_policy()`. |
| [includes/hdoc.php](../../includes/hdoc.php) | `stwbpb_send_hdoc_for_post()` — builds and outputs a standalone HDOC. |
| [includes/cdoc.php](../../includes/cdoc.php) | `stwbpb_build_cdoc_source()` / `stwbpb_send_cdoc_for_post()` — builds CDOC output. |
| [includes/condoc.php](../../includes/condoc.php) | `stwbpb_build_condoc_source()` / `stwbpb_send_condoc_for_post()` — builds CONDOC output. |
| [includes/panels.php](../../includes/panels.php) | `stwbpb_get_panels()` — builds the `<panels>` XML block from global settings. `stwbpb_get_seo_panel_data()` parses panels XML into an array for SEO-friendly PHP rendering in the reader template. `stwbpb_has_comment_section()` — checks if the side panel with comments should be shown. |
| [includes/settings.php](../../includes/settings.php) | Admin settings page (global defaults for top/bottom panels, display modes, comments labels). Option key: `stwbpb_settings`. |
| [includes/comments-json.php](../../includes/comments-json.php) | `stwbpb_send_comments_json_from_post()` — serves comments as a JSON object (`{comments, total, page, per_page}`) at `/json-comments/?post=ID`. Supports pagination (`page`, `per_page`) and ordering (`order=asc\|desc`). |
| [includes/comment-form.php](../../includes/comment-form.php) | `stwbpb_handle_comment_form()` — serves and processes a minimal HTML comment form at `/sw-comment-form/?post=ID`. Supports replies via `parent_id`. On successful submission posts `{type:'swp-comment-submitted'}` to the parent frame via `postMessage`. |
| [includes/doc-files.php](../../includes/doc-files.php) | `stwbpb_send_doc_file()` — serves standalone `.hdoc`, `.cdoc`, `.condoc` files from the `static-documents/` directory in the WordPress root. |
| [includes/proxy.php](../../includes/proxy.php) | `stwbpb_proxy_fetch()` — a server-side proxy at `/sw-proxy/` that fetches remote documents on behalf of the reader. Access is restricted: the `source_url` must resolve to a known post, and the `target_url` must be in that post's connections list (or, for CONDOCs, match the `_condoc_main_url`). Connection URLs are cached in a transient (`swp_connections_{post_id}`, 5 min TTL, invalidated on `save_post`). |

The admin JS/CSS in [includes/admin.js](../../includes/admin.js) and [includes/admin.css](../../includes/admin.css) power the settings page UI (dynamic link/section management and the WordPress media uploader for the logo).

## Templates

[templates/reader-template.php](../../templates/reader-template.php) — full Reader UI template, adapted from the extension's HTML. Served when a post/page is in `doc_in_reader` display mode. Contains all the DOM structure the reader JS expects. The template also renders SEO-friendly panel content (logo, site name, top links, bottom sections) in PHP so it is visible without JS.

## Reader (Frontend)

The [reader/](../../reader/) folder is the shared [rw-reader-ui](https://github.com/kgcoder/rw-reader-ui) git submodule, also used by the RW Reader extension. It provides the same Reader UI that the extension injects into the browser, so visitors without the extension can still experience the Reader's Web. Its modules, global state (`g.*`) and document subtypes are documented in [reader/docs/architecture.md](../../reader/docs/architecture.md).

The reader JS is authored as ES modules. In production (`WP_DEBUG` false) a minified bundle [dist/reader.bundle.min.js](../../dist/reader.bundle.min.js) is served; in development (`WP_DEBUG` true) the raw ES modules are served directly. The entry point is [adapter/startup.js](../../adapter/startup.js), which sets `g.hostAdapter` and imports [reader/readerStartUp.js](../../reader/readerStartUp.js). The reader JS is injected as `type="module"`.

## Adapter

[reader/](../../reader/) never talks to the host environment directly. Instead it calls `g.hostAdapter`, whose interface is documented in [reader/docs/host-adapter.md](../../reader/docs/host-adapter.md). This plugin's implementation is [adapter/HostAdapter.js](../../adapter/HostAdapter.js):

- `initReader()` runs on `DOMContentLoaded`. It sets `g.adminBarHeight`, detects an embedded CDOC/CONDOC (`#cdoc-source` / `#condoc-source`) or falls back to the embedded HDOC in the page, applies the admin-configured font set and loads the document.
- `fetchWebPage(url, options)` goes through this plugin's `/sw-proxy/` endpoint.
- `getSetting`/`saveSetting` are stubs: this plugin has no per-visitor storage (theme and font set are site-wide, see the comment in that file).
- DOM ids use a `-rwp` suffix so they don't collide with the extension's reader when the extension takes over a page.

Host-specific reader styles are in [adapter/reader.css](../../adapter/reader.css).

A `window.vcReaderData` object is set before the module loads, containing `assetsUrl` (path to `reader/images/`), `proxyUrl` (the `/sw-proxy/` endpoint URL), `fontSet` and `openInNewTabCommentsLabel`.

## Content Processing

- WordPress block editor comments (`<!-- wp:... -->`) are stripped from HDOC content via `stwbpb_strip_wp_tags()`.
- YouTube embeds are converted to `<iframe>` tags before output.
- The `the_content` filter wraps post content in `<div id="hdoc-content">` for embedded HDOC detection.
- The `template_include` filter swaps in the reader template for `doc_in_reader` mode.
- A promo popup can be optionally shown via the `show_promotion_button` setting.
