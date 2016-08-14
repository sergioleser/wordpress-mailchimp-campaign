=== MailChimp Campaigns ===
Contributors: MatthieuScarset
Tags: mailchimp, mailchimp campaign, mailchimp stats, shortcode, shortcodes, newsletter
Requires at least: 4.0.0
Tested up to: 4.5.3
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Display your MailChimp campaigns, the easy way.

== Description ==

This plugin allows you to **display your campaigns in your Post, Page or Custom Post Type using a simple Shortcode**.
Display your campaigns as **HTML**.
Simply requires a free MailChimp API key.

<h4>Features</h4>
* Sync your MailChimp campaigns
* See all your campaigns in WordPress
* Display your newsletters with a simple shortcode
* Support Post, Page and Custom Post Types

<h4>Available Shortcodes</h4>
**[campaign id="1234567890"]**
Display a campaign in your post.


== Installation ==

= Plugin installation =
1. Download this plugin and add it to '/wp-content/plugins/'
1. Activate it from WordPress &raquo; Plugins admin screen
1. Go to Settings and scroll down until 'MailChimp Campaigns' section
1. Save your username and your API Key
1. See your MailChimp campaigns in WordPress in the new menu 'Newsletter'

= Plugin usage =
1. Copy a campaign shortcode (see screenshot)
1. Create a new post/page
1. Insert the shorcode in the content
1. Save or update your post.

== Frequently Asked Questions ==

= How to use this plugin =
1. Copy a campaign shortcode (see screenshot)
1. Create a new post/page
1. Insert the shorcode in the content
1. Save or update your post.

= Available Shortcodes =
**[campaign id="1234567890"]**
Display a campaign in your post.

= How to find my MailChimp API Key ?  =
1. Log into your [MailChimp](http://mailchimp.com/ "MailChimp") account
1. Go to your [Account](https://mailchimp.com/account/ "Account")
1. Click on the [Extra](https://mailchimp.com/account/api/ "Extra") tab and you'll find your API keys ;)


== Screenshots ==

1. This screenshot shows the Setting section where you can save you API key and re-sync your data.
2. This is the WordPress admin screen where your can see all your MailChimp campaigns and grab the shortcode to copy-paste it in any post.
3. This is what you'll see when in case you need to re-sync your data.
4. Campaigns are rendered in a Responsive iframe on Front.


== Changelog ==

= 2.0.2.2 =
* Fix issue in dashboard
* New ajax request for synchronization
* Optimize MC classes
* Improve performance on admin loading
