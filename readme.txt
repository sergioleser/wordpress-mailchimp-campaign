=== MailChimp Campaigns ===
Contributors: matthieuscarset-1, olalaweb
Tags: mailchimp, mailchimp campaign, mailchimp stats, shortcode, shortcodes, newsletter
Requires at least: 4.0.0
Tested up to: 4.6
Stable tag: 3.0.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Display your MailChimp campaigns, the easy way.

== Description ==

This plugin allows you to **display your Mailchim campaigns in your WordPress site with simple [embed](https://codex.wordpress.org/Embeds) content**.

Import your campaigns in WordPress as custom posts and display them anywhere you want with just a copy/paste of the internal URL.
 
You can display your campaigns as **HTML** in any Post, Widget, Page or Custom Post Type. 

Simply requires a free MailChimp API key. 

<h4>Features</h4>
* Import your MailChimp campaigns in WordPress
* List all your campaigns with their statistics  in WordPress
* Display your newsletter as HTML anywhere you want

== Installation ==

= Plugin installation =
1. Download this plugin and add it to '/wp-content/plugins/'
1. Activate it from WordPress &raquo; Plugins admin screen
1. Go to Settings and scroll down until 'MailChimp Campaigns' section
1. Save your username and your API Key

= Plugin usage =
1. Import your campaigns in WordPress from the settings screen
1. Copy/paste a new imported campaign post's url in a Post or a Page of your site

== Frequently Asked Questions ==

= How to find my MailChimp API Key ?  =
1. Log into your [MailChimp](http://mailchimp.com/ "MailChimp") account
1. Go to your [Account](https://mailchimp.com/account/ "Account")
1. Click on the [Extra](https://mailchimp.com/account/api/ "Extra") tab and you'll find your API keys ;)


== Screenshots ==

1. This screenshot shows the Setting section where you can save you API key and import your data.
2. This is the WordPress admin screen where your can see all your imported MailChimp campaigns
3. Campaigns are rendered in a Responsive iframe on front.
4. Campaigns are rendered in a Responsive iframe on front.


== Changelog ==

= 3.0.3 =
* Fix activation issue (thank to @yochanan.g) @see [this ticket](https://wordpress.org/support/topic/wordpress-46-failure?replies=3#post-8794293)
* Clean uninstall with options deletion

= 3.0.2 =
* Fix minor bugs

= 3.0.1 =
* Provide previous shortcodes compatibility
* Save custom post URL on campaign import to display them on Front
* FIx missing tag version

= 3.0.0 =
* New version to support WordPress 4.5.3
* Use of the WordPress core Embed functionnality
* Optimize MC classes
* Improve performance

= 2.0.2.2 =
* Fix issue in dashboard
* New ajax request for synchronization
* Optimize MC classes
* Improve performance on admin loading

= 2.0.2.1 =
* Fix issue in setting panel

= 2.0.2 =
* Fix issue with require_once for our classes file
* Correct an error in the [campaign] shortcode preventing email from being displayed

= 0.2.0.1 =
* Major release. We know make use of the new MailChimp API v3.0.
* Metabox deprecated (dropdown menu is no longer available from the Post edit screen)
* For your ease of use, your previous API Key is used when reactivating this plugin
* New admin menu with the full list of your MailChimp campaigns
* New simpler shortcodes [campaign id="123"]
* New "Sync" button available in Settings and in the admin screen Help tab.
* New use of the WordPress cache to save your synced campaigns
* New screenshots

= 0.1.2.1 =
* Update the way we handle is_single() in order to fix this issue : https://wordpress.org/support/topic/single-post-or-page?replies=3#post-6208830

= 0.1.2 =
* New screenshots, banner and icon
* New menu screen to easily copy-paste shortcodes
* Change plugin's title to MailChimp Campaigns Displayer
* Changes to readme.txt
* Fix to statistics list in front-end


= 0.1.1 =
* Changes to readme.txt
* Fix statistics in admin screen
* Change menu title to MailChimp Campaigns

= 0.1.0 =
* Fix issue with user permission

= 0.0.1 =
* First version ever


== Upgrade Notice ==
= 3.0.1 =
This version is an important update. Campaigns retrieval and display have been entirely rework for performance and ease of use.

= 0.1.2 =
This version fixes an issue with statistics not showing correctly on front-end and add a new widget to the settings admin screen to easily use shortcodes.

= 0.1.1 =
This version fixes an issue with statistics that were not displayed in the admin screen under Tools &raquo; MailChimp Campaigns.


= 0.1.0 =
This version fixes an issue with user permission and display of the admin screen.
