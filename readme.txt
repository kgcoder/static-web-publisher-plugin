=== Static Web Publisher ===
Contributors: kgcoder
Donate link: https://reinventingtheweb.com/donate
Tags: reader, connections, hdoc, static, web
Requires at least: 5.2
Tested up to: 7.0
Stable tag: 5.0.1
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Make your WordPress site part of the Reader's Web.

== Description ==

**Static Web Publisher** turns your WordPress site into a publisher on the **Reader's Web** (also called the Static Web or Web 1.1) — a new part of the browsable web where content is separated from presentation and pages can form visible connections with each other.

= What is the Reader's Web? =

Reader's Web is a Web where authors provide content and readers control presentation. Pages from different websites look and feel the same, which improves readability and makes navigation consistent across sites. Standardized pages also enable features previously unavailable on the Web, like visible connections between passages of text in different documents.

The Reader's Web is not the first system where authors provide content without controlling presentation. Social networks already work this way. The difference is that social networks transfer control from authors to platform owners. The Reader's Web transfers control to readers instead.

= What this plugin does =

The plugin can serve your WordPress content in three new document formats and can also host a full Reader UI directly on your site so visitors without a compatible app still get the full experience.

**Document types:**

* **HDOC** — a script-free, style-free XML document for text content. The main format of the Reader's Web.
* **CDOC** — an SVG-based collage document.
* **CONDOC** — a connection-only document that loads a third-party page as its main content and annotates it with visible connections, without modifying the original page.

**Display modes** (configurable globally in Settings, overridable per-post in the editor):

* **Embedded HDOC** — your regular WordPress page is served as normal but includes hidden JSON metadata. Compatible apps detect it and render it as an HDOC when the document is loaded as a connected document. 
* **Embedded HDOC (forced)** — same as above but compatible apps always render the HDOC view, even when the page is the main document (not a connection). Visitors without compatible apps see your normal site.
* **Reader UI** — WordPress serves a full Reader interface instead of your theme. Visitors get the Reader's Web experience directly without needing an app or extension. 
* **Standalone document** — WordPress serves the raw HDOC, CDOC, or CONDOC file at the post's URL with no surrounding HTML. This will be useful if the Reader's Web becomes so popular that browsers start supporting the new document formats.  

= Reader UI =

When a post or page is set to **Reader UI** mode, the plugin replaces your WordPress theme with a built-in reader interface. This is the same interface used by the Visible Connections browser extension, so the experience is consistent regardless of whether the visitor has the extension installed.

= Comments =

Comments on HDOC documents are served as JSON at `/json-comments/?post=ID`, supporting pagination and ordering. A minimal comment submission form is available at `/sw-comment-form/?post=ID` and supports replies. This allows compatible reader apps to display and post comments without loading the full WordPress comment system.

= Proxy endpoint =

When displaying connected documents, the Reader UI fetches pages from other sites through a server-side proxy at `/sw-proxy/`. This proxy is safe to use: before making any request to an external URL, the plugin verifies that the URL is explicitly listed as a connection of the current post. Arbitrary external URLs cannot be fetched through the proxy.

= Static document files =

You can also place standalone `.hdoc`, `.cdoc`, and `.condoc` files directly in a `static-documents` folder in your WordPress site root. They are served inline (not as downloads) at URLs like `https://yoursite.com/static/filename.hdoc`, which lets compatible apps intercept and render them.

= Analytics =

Standard WordPress analytics tools work normally on Embedded HDOC and Reader UI pages. When the Visible Connections extension is active and replaces the page UI, existing analytics scripts continue to run: page view events are already sent before the extension takes over, and session-level tracking continues uninterrupted.

For analytics code that needs to run **after the Reader UI is fully initialized**, the plugin dispatches a `swpReaderReady` event on `document`. You can listen for it in any custom script:

`document.addEventListener('swpReaderReady', function(e) {`
`    // e.detail.url is the current document URL`
`    gtag('event', 'reader_view', { page_location: e.detail.url });`
`});`

The event is fired at most once per page load regardless of whether the reader was initialized by the plugin or the extension.

= Compatible apps =

* [Visible Connections](https://chromewebstore.google.com/detail/visible-connections/hlckcdbgknflkkciojgdbhomdnegimbm) — a Chrome extension that shows visible connections between pages in your browser and renders Embedded HDOCs in a reader view.
* [LZ Desktop](https://reinventingtheweb.com/download) — a standalone app for exploring and creating Reader's Web documents.

Visitors without any compatible app will see your content normally on Embedded HDOC pages, or through the built-in Reader UI if you enable Reader UI mode.

= Background =

The Reader's Web is inspired by Ted Nelson's long-standing vision of hypertext. You can read more about the project at [reinventingtheweb.com](https://reinventingtheweb.com).


== Installation ==

1. Go to **Plugins > Add New** in your WordPress dashboard and search for **Static Web Publisher**.
2. Install and activate the plugin.
3. Go to **Static Web Publisher** in the sidebar to configure panels, display modes, and comments labels.
4. Set the default display mode for posts and pages. **Embedded HDOC** is a safe starting point — it adds hidden metadata without changing anything visitors see.
5. To use **Reader UI** mode, set the display mode to **Reader UI** either globally or per-post. The plugin will replace your theme with the built-in reader interface for those posts.
6. To serve standalone document files, create a folder named `static-documents` in the root of your WordPress installation (the same folder that contains `wp-config.php`). Place `.hdoc`, `.cdoc`, or `.condoc` files inside it. They will be accessible at `https://yoursite.com/static/filename.hdoc`.
7. After changing any plugin settings, go to **Settings > Permalinks** and click **Save Changes** to ensure all custom endpoints are registered.


== Frequently Asked Questions ==

= Do visitors need to install anything to use the Reader UI? =

No. When a post is set to **Reader UI** mode, the plugin serves the full reader interface as a regular web page. Any visitor can use it. Visitors who do have the Visible Connections extension installed will also benefit from it on Embedded HDOC pages.

= Will this break my site's appearance for regular visitors? =

No. In **Embedded HDOC (forced)** mode (the default) your site looks identical to regular visitors. Only compatible apps detect and use the hidden metadata. **Reader UI** mode replaces your theme for those posts, which is intentional — it is meant for content you want to present in the reader layout for all visitors.

= Is the proxy endpoint safe? =

Yes. The proxy at `/sw-proxy/` only fetches URLs that are explicitly listed as connections of the post being viewed. If a URL is not in the current post's connection list, the request is rejected. The allowed URLs are cached and invalidated whenever you update the post.

= Can I use my existing analytics with this plugin? =

Yes. Standard WordPress analytics tools work on all pages. On **Reader UI** pages they load via the normal WordPress head hooks. On **Embedded HDOC** pages visited through the Visible Connections extension, analytics scripts initialize before the extension takes over the UI and continue tracking the session afterward. For analytics that need to run after the reader finishes rendering, listen for the `swpReaderReady` event on `document`.

= What is the difference between Embedded HDOC and Embedded HDOC (forced)? =

In regular **Embedded HDOC** mode, compatible apps render the HDOC view only when the document is opened as a connected document on the right side of the reader. In **Embedded HDOC (forced)** mode, the HDOC view is always used, even when the page is the main document shown on the left side.

= Can I mix display modes across posts and pages? =

Yes. You can set a global default in Settings and override it per post or page in the editor sidebar.


== Changelog ==

= 5.0.1 =
Bug fix

= 5.0.0 =
* Added Reader UI display mode: the plugin can now serve a full built-in reader interface as a WordPress template, so visitors without the extension get the complete Reader's Web experience.
* Added support for embedded CDOC and embedded CONDOC document types.
* Added a server-side proxy endpoint (/sw-proxy/) for fetching connected documents from other sites. The proxy validates all URLs against the current post's connection list before making any request.
* Added a comment submission form endpoint (/sw-comment-form/) supporting replies, with postMessage integration for reader apps.
* Added the `swpReaderReady` DOM event, dispatched after the reader finishes initializing. Useful for analytics and other post-render integrations.
* Added Embedded HDOC (forced) display mode.
* Reader UI includes SEO-friendly PHP rendering of panel content.

= 4.1.2 =
Bug fix

= 4.1.1 =
Content type of standalone files changed to text/html to allow HTML in fallback messages.

= 4.1.0 =
Some unnecessary functionality removed.

= 4.0.0 =
Introduced the embedded HDOC format.  
The plugin no longer creates additional endpoints for serving HDOCs.  
Support for custom URL schemes has been removed.

= 3.0.0 =
Big changes in HDOC format. 

= 2.1.0 =
It's now possible to order comments with 'order' parameter. Values: asc, desc. Title for comments section and 'no comments yet' message can now be specified in the Settings.

= 2.0.0 =
Color configuration of panels was removed from Settings. 
Now a link to the original page can be added to the panels. The same link was removed from the bottom of each page. 
Comments are now exported in JSON format.  

= 1.2.0 =
The site name and link are automatically added to the HDOC top panel after the plugin is activated. This ensures the top panel is populated even if you don’t configure panels after installation.


= 1.1.1 =
Bugfix

= 1.1.0 =
* Download buttons are now optional and are not visible by default.
* An alternate link is added to the head of each page. This way sw:// and sws:// links can be found on the page using a browser extension. That's why download buttons are now optional.
* Minor improvements

= 1.0 =
Initial release.


== Upgrade Notice ==

= 5.0.1 =
Minor changes. No action required.

= 5.0.0 =
After upgrading, go to Settings > Permalinks and click Save Changes to register new endpoints (/sw-proxy/ and /sw-comment-form/). Review your display mode settings — new options are available.

= 4.1.2 =
Minor changes. No action required.

= 4.1.1 =
Minor changes. No action required.

= 4.1.0 =
After upgrading, go to Settings > Permalinks and click Save Changes to update endpoints. To use standalone static files, create a 'static-documents' folder in your website's main directory.

= 4.0.0 =
You may have to update links to old HDOC pages because now they have the same URLs as the original pages.

= 3.0.0 =
Big changes in HDOC format.

= 2.1.0 =
It's now possible to order comments with 'order' parameter. Values: asc, desc. Title for comments section and 'no comments yet' message can now be specified in the Settings.

= 2.0.0 =
After the upgrade go to Settings > Permalinks and press 'Save Changes' to update your enpoints (json-comments endpoint was added). 

= 1.2.0 =
Sitename and link are added on the top panel of HDOC automatically after activation of the plugin.

= 1.1.1 =
Bugfix

= 1.1.0 =
Important: In this version, the download button is optional. By default, all download buttons are hidden, but you can re-enable them anytime from the plugin settings page.

= 1.0 =  
First release. No upgrade steps required. 



