# Custom Endpoints / Rewrite Rules

| URL pattern | Query var | Handler |
|-------------|-----------|---------|
| `^static/(.+)$` | `doc_viewer_matches` | `stwbpb_send_doc_file()` — serves files from `ABSPATH/static-documents/` |
| `^json-comments/?(.+)?$` | `json_comments_custom_matches` | `stwbpb_send_comments_json_from_post()` |
| `^sw-proxy/?$` | `sw_proxy_request` | `stwbpb_proxy_fetch()` |
| `^sw-comment-form/?$` | `sw_comment_form_request` | `stwbpb_handle_comment_form()` |

All rules use priority `top`. After adding or changing rewrite rules, go to **Settings > Permalinks** and click **Save Changes**.

## Static Document Files

Standalone `.hdoc`, `.cdoc`, `.condoc` files can be placed in `ABSPATH/static-documents/` (i.e., the `static-documents` folder in the WordPress site root). They are served inline (not as downloads) via the `/static/filename.ext` URL, which allows the browser extension to intercept and render them.
