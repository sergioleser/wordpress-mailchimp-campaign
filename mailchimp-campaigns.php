<?php
/*
  	Plugin Name: MailChimp Campaigns
    Plugin Script: mailchimp-campaigns.php
    Plugin URI:   http://wordpress.org/extend/plugins/mailchimp-campaigns/
    Description: Display your MailChimp campaigns with simple shortcodes.
    Author: MatthieuScarset 
    Author URI: http://matthieuscarset.com/
    License: GPL
    Version: 1.0.0
    Text Domain: mailchimpcampaigns
    Domain Path: languages/

    Display your MailChimp campaigns with simple shortcodes.
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { exit; }

// Define globals
define( 'MCC_TEXT_DOMAIN', 'mailchimpcampaigns' );
define( 'MCC_DEFAULT_CPT',  'newsletter' );
define( 'MCC_PLUGIN_ROOT_DIR', plugin_dir_path( __FILE__ ) );
include( MCC_PLUGIN_ROOT_DIR . 'class/Mailchimp.php');
include( MCC_PLUGIN_ROOT_DIR . 'class/MailchimpAdmin.php');
include( MCC_PLUGIN_ROOT_DIR . 'class/MailchimpCustomPostType.php');


/*
 * Plugin setting link
 * Displays a direct link to the WordPress plugin admin page
 * @param $links
 * @param $file
 */
function mailchimpcampaigns_settings_link($links, $file) {
    static $this_plugin;
    if (!$this_plugin)
        $this_plugin = plugin_basename(__FILE__);
    if ($file == $this_plugin){
        $settings_link = '<a href="options-general.php?page=mailchimpcampaigns-admin">'.__('Settings', MCC_TXT_DOMAIN).'</a>';
        array_unshift($links, $settings_link);
    }
    return $links;
}
add_filter('plugin_action_links', 'mailchimpcampaigns_settings_link', 10, 2 );

/*
 * Rewrite flush
 * Used on plugin activation hook to register our post type
 */
function mailchimpcampaigns_rewrite_flush() {
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'mailchimpcampaigns_rewrite_flush' );
