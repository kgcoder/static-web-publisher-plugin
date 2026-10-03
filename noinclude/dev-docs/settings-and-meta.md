# Settings and Post Meta

## Post Meta Keys

| Meta key | Purpose |
|----------|---------|
| `_doc_type` | `HDOC` (default), `CDOC`, or `CONDOC` |
| `_hdoc_display_mode` | Per-post display mode override (`default`, `embedded_hdoc`, `embedded_hdoc_forced`, `doc_in_reader`, `standalone_doc`, `none`) |
| `_hdoc_author_name_display` | `default`, `show`, or `hide` |
| `_hdoc_publish_date_display` | `default`, `show`, or `hide` |
| `_republishing_policy` | Per-post republishing policy override (`default`, `implicit_allow`, `explicit_allow`, `prohibit`). Resolved by `stwbpb_get_effective_republishing_policy()`. Not applied to CONDOC posts. |
| `_static_web_connections_info` | Raw XML fragment of `<doc>` elements listing outgoing connections |
| `_cdoc_svg` | Raw SVG markup for CDOC posts |
| `_condoc_description` | Description text for CONDOC posts |
| `_condoc_main_url` | The external URL the CONDOC loads as its main document |

Connection URLs are cached in transients (`swp_connections_{post_id}`) and invalidated on `save_post`.

## Global Settings (option: `stwbpb_settings`)

Stored as a PHP array in the `stwbpb_settings` WordPress option.

Key fields: `page_mode`, `post_mode`, `republishing_policy` (`implicit_allow` / `explicit_allow` / `prohibit`; default `implicit_allow`), `page_author_name`, `page_publish_date`, `post_author_name`, `post_publish_date`, `removal_selectors`, `side_panel_on_the_left`, `comments_title`, `no_comments_message`, `reply_button_label`, `leave_comment_label`, `top_panel` (main_link, main_title, logo_url, links[]), `bottom_panel` (bottom_message, sections[]), `show_promotion_button`.

## Republishing Policy

The `republishing_policy` setting controls whether a `<republishing-policy>` tag is included inside `<metadata>` in HDOC and CDOC output. `implicit_allow` omits the tag entirely (default Reader's Web behaviour). Per-post overrides are stored in `_republishing_policy` meta. CONDOC posts never receive the tag regardless of settings. For embedded HDOCs the value is passed as `"republishing-policy"` in the `#hdoc-data` JSON and injected into the reconstructed XML by `EmbHDOCParser.js`.

A draft license governing republishing (Reader's Web Republishing License, RWRL) is being worked out — see [../legal/RWRL-1.0-DRAFT.md](../legal/RWRL-1.0-DRAFT.md). It currently covers only the connection-stabilization use case (`implicit_allow` / `explicit_allow`, both the same scope). A broader `allow-aggregation`-style policy value, permitting inclusion in feeds/aggregation services, has been discussed but is deliberately deferred — not useful until multiple independent aggregation services exist; a single early service can rely on a private opt-in/signup relationship with sites instead, which needs no public policy value at all. Do not add an aggregation policy tier without revisiting that draft's §5.
