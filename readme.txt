=== Static Web Publisher ===
Contributors: kgcoder
Donate link: https://reinventingtheweb.com/donate
Tags: static, web, publish
Requires at least: 5.1
Tested up to: 6.8
Stable tag: 4.0.0
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
        <metadata>
            <title>Page title</title>
        </metadata>

        <header>
            <h1>Page title</h1>
            <author>John Doe</author>
            <date>October 1, 2025</date>
            ...
        </header>

        <content>
            <p>Content</p>
        </content>

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



== Frequently Asked Questions ==  

No frequently asked questions yet. Feel free to ask!

== Changelog ==  

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



