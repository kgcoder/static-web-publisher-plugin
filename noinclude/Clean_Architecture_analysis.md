# Clean Architecture Analysis — Reader's Web Publisher

Birds-eye assessment of whether the plugin (PHP) and the reader (JS) should be refactored
toward Clean Architecture (Domain / Application / Presentation / Infrastructure). Written
to support a go/no-go decision, not as an implementation plan. See
[Testing_roadmap.md](Testing_roadmap.md) for the testing plan referenced in the
recommendation below.

---

## 1. Backend — the WordPress plugin (PHP)

### Where the principles are broken

Every request handler is a transaction script: HTTP parsing → business rule → WordPress
I/O → output escaping, all inlined in one function, with no boundary between them.

- **`includes/hdoc.php:22-136`** (`stwbpb_send_hdoc_for_post`) — fetches post data,
  transforms content (YouTube regex, shortcode expansion, WP-tag stripping), decides the
  republishing-policy tag, assembles XML, sanitizes, and `echo`s with `header()` — one
  function doing domain logic + presentation + I/O.
- **`includes/proxy.php:8-56`** (`stwbpb_proxy_fetch`) — the real security rule ("target
  URL must be in the source post's connection list, or match the condoc main URL") is a
  genuine authorization policy, but it's buried inside `$_GET` parsing and
  `wp_remote_get()`. Can't be unit-tested without a running WordPress.
- **`includes/comment-form.php`** — two ~150-line functions each mixing nonce/superglobal
  handling, validation rules (name/email/comment required, parent must belong to the post
  and be approved), raw HTML+CSS string output, and `wp_insert_comment()`.
- **`static-web-plugin.php:153-236`** (`template_redirect`) and **`:342-448`**
  (`stwbpb_output_xml`) — routing dispatch, a DOM-rewriting output-buffer callback, and
  JSON-shape business rules (the `forced` flag, republishing-policy → string mapping,
  duplicated across `hdoc.php`, here, and implicitly in `EmbHDOCParser.js`) all wired
  directly as closures on hooks.
- Settings access (`get_option('stwbpb_settings')`) is repeated ad hoc everywhere with
  duplicated `!empty($x) ? $x : 'default'` fallback logic (e.g. `comment-form.php:27-33`
  and `:158-167` repeat the same pattern) instead of one typed settings object.

Real domain logic does exist and is worth protecting: display-mode/doc-type/
republishing-policy resolution, the proxy authorization rule, connection-URL parsing,
comment validation, and `stwbpb_xml_to_array_with_attributes` (already a pure function).
It's just welded to WordPress calls, superglobals, and `echo`.

### Layer sketch

- **Domain** (pure PHP, zero WP functions): `DisplayMode` / `DocType` /
  `RepublishingPolicy` value objects; `ProxyAuthorizationPolicy`; `CommentValidator`; the
  XML→array transform; the YouTube-embed/WP-tag content transforms.
- **Application (use cases)**: `BuildHdocDocument`, `BuildCdocDocument`,
  `BuildCondocDocument`, `FetchProxyContent`, `SubmitComment`, `ListCommentsPage` — each
  orchestrates domain objects against interfaces (`PostRepository`, `CommentRepository`,
  `SettingsRepository`, `HttpFetcher`, `CacheStore`), returning plain DTOs, not strings.
- **Presentation**: renderers turning DTOs into output — `HdocXmlRenderer`,
  `CommentFormHtmlRenderer`, `HdocDataJsonRenderer`, admin meta-box/settings views,
  `reader-template.php`. All `wp_kses`/`esc_html` escaping lives here, at the edge.
- **Infrastructure**: `WpPostRepository`, `WpCommentRepository`, `WpSettingsRepository`,
  `WpTransientCache`, `WpHttpFetcher` implementing the interfaces via `get_post` /
  `wp_insert_comment` / `get_option` / `wp_remote_get`; `static-web-plugin.php` shrinks to
  a composition root wiring these into use cases and registering hooks.

---

## 2. Frontend — the reader (JS, `reader/` folder)

### Where the principles are broken

Worse than the PHP side in scale (14.6k lines; two files alone are 6,800+ lines), but
with a real domain nucleus already worth protecting.

- **Two god-object managers.** `PopupDocumentManager.js` (3225 lines) and
  `ReadingManager.js` (3614 lines) each do everything: raw DOM query/creation (222 and 54
  calls respectively), canvas-drawing math, event-listener wiring, network orchestration
  via the parsers, and UI state — no seam between "decide what should render" and "render
  it." E.g. `ReadingManager.js`'s `drawFlinksOnMiddleCanvas` (~line 603 on) computes real
  domain logic — arrow direction (`leftStatus`/`rightStatus`), line-style rules
  (`flinkStyle`) — inline inside the method that's also touching the canvas context and
  DOM.
- **A shared mutable global singleton stands in for dependency injection.**
  `Globals.js` exports `g = { pdm, readingManager, noteDivsManager, ... }`, and 14 of ~24
  files reach into it directly — including model classes (`models/Crosshair.js`,
  `models/ImageView.js`, `models/Viewport.js`) that should be pure geometry objects but
  instead pull ambient app state. This inverts the dependency rule: models depend outward
  on global mutable state instead of being handed only what they need.
- **`NetworkManager.js`** (otherwise the cleanest infra-shaped module) still reads
  `g.readingManager.mainDocData` directly (`NetworkManager.js:23,30`) rather than being
  passed the current URL.
- **Parsers leak presentation.** `parsers/EmbHDOCParser.js`, `CDOCParser.js`, etc. are
  otherwise close to pure `(string) → data object` transforms, but call
  `showToastMessage(...)` directly on parse failure — a UI side-effect invoked from what
  should be domain/application code.
- **`helpers.js` is a 1001-line junk drawer** — string sanitization, HTML utilities, DOM
  helpers, and the toast-UI function itself all live in one ungrouped module.

### What's already good

- **`models/FloatingLink.js`, `FLEnd.js`, `FLTextEnd.js`, `FLPointEnd.js`** are genuinely
  clean domain models today: pure serialization/parsing (`getString`,
  `fromExportString`, hash matching for flink stabilization), zero DOM, zero globals.
- Parsers are structurally application/domain-shaped (string in, data object out) apart
  from the toast leak.
- `Icons.js`, `HeaderMethods.js`, `MultipleLinksPopupManager.js`, `PageInfoManager.js`,
  `ExportPageManager.js` are already presentation-scoped; they just need to stop touching
  `g` directly.

### Layer sketch

- **Domain**: the `FloatingLink`/`FLEnd` family (already there); doc-subtype/doc-type
  validation; the flink geometry rules (arrow direction, line-style selection) extracted
  out of `drawFlinksOnMiddleCanvas`.
- **Application**: `LoadDocumentUseCase`, `LoadConnectedDocument`, `ExportPage`,
  `DownloadAllPages` — orchestrate parsers + a fetch port + domain rules, return
  view-state DTOs, touch no DOM.
- **Infrastructure**: `NetworkManager` (once it takes a URL param instead of reading
  `g`), the DOMPurify/sha256 wrappers.
- **Presentation**: `PopupDocumentManager`/`ReadingManager` shrink to "given this
  view-state, create/update these DOM nodes and canvas drawing" — no docSubtype checks,
  no arrow-direction math, no network calls.

### A frontend-specific complication

Per `CLAUDE.md`, `reader/` is code **copied from the Visible Connections Chrome
extension**, which is presumably maintained in its own repo. Any structural refactor done
here either (a) diverges from the extension's copy, creating permanent drift, or (b) has
to be done in the extension repo first and re-copied in. Splitting the two god objects
apart is exactly the kind of change most likely to conflict with future upstream syncs.

---

## 3. Recommendation

**Not worth a full Clean Architecture rewrite on either side, for the same three
reasons:**

1. **No test suite exists anywhere in the project** (confirmed: no `tests/` directory, no
   `composer.json`). CA's main payoff — testing business rules without bootstrapping
   WordPress or a browser — is currently worth $0, because nothing is tested yet. Paying
   the interface/DTO/composition-root tax before collecting that benefit is backwards.
2. **The frameworks fight the pattern.** WordPress hooks are global procedural callbacks
   (`global $post`, superglobals, `get_option`); the reader has no component framework or
   DI container. A strict dependency-rule boundary adds real ceremony against tooling
   that isn't built for it.
3. **Solo-maintained codebase.** Full layering pays off most when it lets separate
   people/teams work in parallel or swap infrastructure — neither applies here. On the
   frontend there's the added complication that `reader/` isn't even the source of truth
   for its own code.

**So yes — testing first is the right call, and it's already the plan.**
[Testing_roadmap.md](Testing_roadmap.md) phases 3 & 4 (parser unit tests in the extension
repo; PHPUnit tests for the HDOC/CDOC/CONDOC builders, the resolvers, and the proxy/
comment endpoints) target almost exactly the same pieces of logic this analysis flags as
the real domain core — that's not a coincidence, it's the same logic being valuable from
both angles. Once those tests exist, two things happen for free:

- You get the actual payoff CA promises (confidence that business rules work,
  fast feedback) without first restructuring anything.
- The extraction work becomes obvious and low-risk: a function you've now got a test
  around is easy to pull out of `hdoc.php` or `ReadingManager.js` into its own
  pure-function module, because the test tells you immediately if the extraction changed
  behavior.

**Practical order:** build the corpus + P1 smoke suite (roadmap §2–3) → PHP unit tests
for the resolvers and builders (roadmap Phase 4) → *then*, opportunistically, pull the
now-tested logic out into small standalone functions/modules as you touch that code
anyway. Skip formal ports/interfaces/composition roots unless a concrete pain point
(e.g. wanting to swap an infrastructure piece, or genuinely needing WP-free unit tests at
scale) shows up later and asks for it.
