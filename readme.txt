=== Static Web Publisher ===
Contributors: kgcoder
Donate link: https://reinventingtheweb.com/donate
Tags: static, web, publish
Requires at least: 5.1
Tested up to: 6.9
Stable tag: 4.1.1
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Support visible connections between web pages by making your site part of the emerging Static Web (Web 1.1) ecosystem.

== Description ==

Static Web Publisher adds a small block of JSON metadata to each post and page. This metadata allows compatible apps and browser extensions to generate reader-friendly, static representations of your content — versions that can visually highlight connections to other pages across the Web.

Visitors without supporting software will see your site exactly as before. Nothing about its appearance or behavior changes for regular users.

The Static Web is a new approach to building a more interconnected Web. You can learn more about it [here](reinventingtheweb.com).

= Background =
This project is inspired by the long-standing ideas of hypertext pioneer Ted Nelson.

= Current support =
The new formats (HDOCs) this plugin works with are currently supported by:

[Visible Connections](https://chromewebstore.google.com/detail/visible-connections/hlckcdbgknflkkciojgdbhomdnegimbm)
 — a Chrome extension that shows visible connections between web pages in your browser,

[LZ Desktop](https://reinventingtheweb.com/download)
 — a standalone app for exploring and creating Web 1.1 content.

= Looking ahead =
More apps — especially reader apps, and eventually browsers — will be able to support Static Web data formats in the future.


== Installation ==

Setting up Static Web Publisher is quick and easy:

1. Go to Plugins in your WordPress dashboard and click Add New.
2. In the "Search plugins..." field, type Static Web Publisher.
3. Find the plugin in the search results, hover over it, and click Install.
4. Once installed, click Activate.
5. Go to the plugin's Settings page and configure top and bottom panels (optional).
6. In the main folder of your website (usually named public), create a folder named static-documents. Place your standalone HDOCs, CDOCs, and CONDOCs inside this folder. They will be accessible via URLs like: https://yourwebsite.com/static/your_file.hdoc. Files in this folder are served directly in the browser rather than downloaded, which allows compatible extensions to handle them properly.

== Frequently Asked Questions ==  

No frequently asked questions yet. Feel free to ask!

== Changelog ==  

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



