=== Static Web Publisher ===
Contributors: kgcoder
Donate link: https://reinventingtheweb.com/donate
Tags: static, web, publish
Requires at least: 5.1
Tested up to: 6.7
Stable tag: 1.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

This plugin lets you publish your website on the Static Web (Web 1.1).

== Description ==

This plugin allows your readers to download pages from your website and store them on their desktop, enhancing audience retention. Web 1.1 is a new concept, and you can read about it on [this website](https://reinventingtheweb.com).

Here’s how the plugin works:

* For each page and post, it creates an additional endpoint.
* For example, if your page URL is: https://example.com/some-page, the plugin generates a new endpoint: https://example.com/sw/some-page
* A download link pointing to the new URL will be placed at the bottom of the original page.
* The link will use a different URL scheme (sws:// instead of https://), allowing users to open the content in apps that support Web 1.1 formats.

Here is a short [video](https://www.youtube.com/watch?v=DX2-G7zy32k) demonstrating how saving of a page works.

The new endpoint provides content in a modified format called HDOC (short for "HTML Document"), which is similar to HTML but does not support scripts.

Example comparison

Standard HTML:
`<html>
    <head>
        <title>My page</title>
    </head>
    <body>
        <h1>My page</h1>
        <p>Content</p>
    </body>
</html>`

HDOC format:
`<hdoc>
    <head>
        <title>My page</title>
    </head>
    <html>
        <h1>My page</h1>
        <p>Content</p>
    </html>
    <panels>...</panels> <!-- Navigational panels (optional) -->
    <connections>...</connections> <!-- Links to related documents (optional) -->
</hdoc>`

== Installation ==

Setting up Static Web Publisher is quick and easy:

1. Go to Plugins in your WordPress dashboard and click Add New.
2. In the "Search plugins..." field, type Static Web Publisher.
3. Find the plugin in the search results, hover over it, and click Install.
4. Once installed, click Activate.
5. Go to the plugin's Settings page and configure the plugin (e.g., top and bottom panels).
6. Enable the info link (optional but important, see below).

*Info link*

Since Web 1.1 is a new concept, most visitors won’t be familiar with it. The info link helps users understand how to use SW links.

* The info link appears as a black button with a question mark next to the orange download button.
* Clicking it takes users to an explanation page.

By default, this feature is disabled because it requires adding an external link. You have two activation options:

* Use the default external link (SEO-safe, uses rel=nofollow).
* Create your own explanation page and link to it from the plugin settings.

If you choose the second option, you can create a page that says:
"To learn about Static Web, click here."

The correct link to use is available in the plugin settings. This way, you minimize external links while still providing necessary information.


== Frequently Asked Questions ==  

No frequently asked questions yet. Feel free to ask!

== Changelog ==  

= 1.0 =  
Initial release.  

== Upgrade Notice ==  

= 1.0 =  
First release. No upgrade steps required. 



