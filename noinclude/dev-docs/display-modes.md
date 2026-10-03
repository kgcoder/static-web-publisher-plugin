# Display Modes

Each post or page can be served in one of five modes, configurable globally in Settings and overridable per-post in the meta box. `embedded_hdoc_forced` is the default. `none` is always the last option in the dropdowns.

| Mode | Behaviour |
|------|-----------|
| `embedded_hdoc` | Regular WordPress HTML page with `#hdoc-content` wrapper and `#hdoc-data` JSON injected into the footer. Compatible clients detect the embedded HDOC automatically. Rendered as an HDOC only when it is loaded as a connected document on the right side. |
| `embedded_hdoc_forced` | Same as above but the `"forced": true` flag in the JSON tells the extension to always render as HDOC, even when the page is the main page shown on the left side. |
| `doc_in_reader` | WordPress serves the full Reader UI template instead of the regular theme. The reader JS loads the embedded doc content directly but also uses `#hdoc-content` and `#hdoc-data` so the extension can extract useful information and show it in its own UI. |
| `standalone_doc` | WordPress serves the raw document (HDOC/CDOC/CONDOC) at the post's URL with `Content-Type: text/plain`. |
| `none` | The page is left completely untouched: no `#hdoc-content` wrapper, no `#hdoc-data` JSON, no reader template or assets. Regular WordPress rendering only. |

CDOC and CONDOC posts always default to `doc_in_reader` unless explicitly set to `standalone_doc` — this includes `none`, which also collapses to `doc_in_reader` for these doc types since they have no plain-page content to fall back to.
