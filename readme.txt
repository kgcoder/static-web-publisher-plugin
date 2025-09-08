=== Static Web Publisher ===
Contributors: kgcoder
Donate link: https://reinventingtheweb.com/donate
Tags: static, web, publish
Requires at least: 5.1
Tested up to: 6.8
Stable tag: 2.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

This plugin lets you publish your website on the Static Web (Web 1.1).

== Description ==

This plugin allows your readers to download pages from your website and store them on their desktop, enhancing audience retention. Web 1.1 is a new concept, and you can read about it on [this website](https://reinventingtheweb.com).

Here’s how the plugin works:

* For each page and post, it creates an additional endpoint.
* For example, if your page URL is: https://example.com/some-page, the plugin generates a new endpoint: https://example.com/sw/some-page
* A download link pointing to the new URL will be placed in the head section of the page (invisible on the page). This link may be found using a browser extension. 
* Additionally, you can add a visible download link and an info link to the bottom of each page. 
* The link will use a different URL scheme (sws:// instead of https://), allowing users to open the content in apps that support Web 1.1 formats.

Here is a short [video](https://www.youtube.com/watch?v=DX2-G7zy32k) demonstrating how saving of a page works.

The new endpoint provides content in a modified format called HDOC (short for "Hypertext Document"), which is similar to HTML but does not support scripts and has very limited styling options.

Example comparison

Standard HTML:

    <html>
        <head>
            <title>Page title</title>
        </head>
        <body>
            <h1>Page title</h1>
            <p>Content</p>
        </body>
    </html>


HDOC format:

    <hdoc>
        <head>
            <title>Page title</title>
        </head>
        <html>
            <h1>Page title</h1>
            <p>Content</p>
        </html>
        <panels>...</panels> <!-- Navigational panels (optional) -->
        <connections>...</connections> <!-- Links to related documents (optional) -->
    </hdoc>

== Installation ==

Setting up Static Web Publisher is quick and easy:

1. Go to Plugins in your WordPress dashboard and click Add New.
2. In the "Search plugins..." field, type Static Web Publisher.
3. Find the plugin in the search results, hover over it, and click Install.
4. Once installed, click Activate.
5. Go to the plugin's Settings page and configure top and bottom panels (optional).
6. Add download link and info link (optional).

*Download link*

By default, this link is hidden. You can leave it that way, but then only people who already know about Static Web will be able to access the alternative page version.

If you’d like to spread the word - and encourage new visitors to try downloading pages from your site - enable the download link in the plugin settings.

Since the link alone doesn’t explain much, it’s best to also enable the info link alongside it.


*Info link*

Since Web 1.1 is a new concept, most visitors won’t be familiar with it. The info link helps users understand how to use SW links.

* The info link appears as a black button with a question mark next to the orange download button.
* Clicking it takes users to an explanation page.

By default, this link is disabled. You have two activation options:

* Use the default external link (SEO-safe, uses rel=nofollow).
* Create your own explanation page and link to it from the plugin settings.

If you choose the second option, you can create a page that says:
"To learn about Static Web, click here."

The correct link to use is available in the plugin settings. This way, you minimize external links while still providing necessary information.


== Frequently Asked Questions ==  

No frequently asked questions yet. Feel free to ask!

== Changelog ==  

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



