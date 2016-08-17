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
define( 'MCC_API_VERSION', '3.0' );
define( 'MCC_DEFAULT_CPT',  'newsletter' );
define( 'MCC_DEFAULT_CPT_STATUS',  'publish' );
define( 'MCC_TEXT_DOMAIN', 'mailchimpcampaigns' );
define( 'MCC_META_PRE', 'mcc_' );
define( 'MCC_META_KEY_ID', MCC_META_PRE .'id' );
define( 'MCC_PLUGIN_ROOT_DIR', plugin_dir_path( __FILE__ ) );

// Get required files
require_once( MCC_PLUGIN_ROOT_DIR . 'class/Mailchimp.php');
require_once( MCC_PLUGIN_ROOT_DIR . 'class/MailchimpCustomPostType.php');
new MailchimpCustomPostType();

/**
 * Include required files
 */
function mailchimpcampaigns_include_files(){
    require_once( MCC_PLUGIN_ROOT_DIR . 'class/MailchimpAdmin.php');
    require_once( MCC_PLUGIN_ROOT_DIR . 'class/MailchimpCampaign.php');
    require_once( MCC_PLUGIN_ROOT_DIR . 'class/MailchimpCampaigns.php');
    require_once( MCC_PLUGIN_ROOT_DIR . 'class/MailchimpCampaignMetabox.php');
}

/**
 * Implements hook init
 */
function mailchimpcampaigns_init(){
    // Include file on init
    mailchimpcampaigns_include_files();

    // Load our classes
    if( is_admin() ) {
        $MCCAdmin = new MailchimpAdmin();
        $MCCampaigns = new MailchimpCampaigns();
    }
}
add_action( 'init', 'mailchimpcampaigns_init' );

/**
 * Add Metaboxes to CPT admin screens
 */
function mailchimpcampaigns_edit_screen(){
    $MCCampaignsMetabox = new MailchimpCampaignMetabox();
}
// add_action( 'edit_form_top', 'mailchimpcampaigns_edit_form_action' );
add_action('load-post.php', 'mailchimpcampaigns_edit_screen', 10, 2);
add_action('load-post-new.php', 'mailchimpcampaigns_edit_screen', 10, 2);

/**
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

/**
 * Rewrite flush
 * Used on plugin activation hook to register our post type
 */
function mailchimpcampaigns_rewrite_flush() {
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'mailchimpcampaigns_rewrite_flush' );

/**
 * Syncronize CPT with Mailchimp
 */
function mailchimpcampaigns_sync(){
    $MCCampaigns = new MailchimpCampaigns();
    $MCCampaigns->save();
}

/*
 * Compatibility issue management
 * Aims to solve issues with previous versions
 * @return void
 */
function mailchimpcampaigns_compatibilty() {
    // Save the previous API Key and delete it
    $old_api_key = get_option('ola_mccp_api_key', false) || get_option('olalaweb_mailchimp_api_key', false);
    $settings = array( 'api_key' => $old_api_key); 
    if ( $old_api_key ) {
        // Delete previous option entries
        if ( get_option('ola_mccp_api_key', false) ) 
            delete_option('ola_mccp_api_key');
        if ( get_option('olalaweb_mailchimp_api_key', false) )
            delete_option('olalaweb_mailchimp_api_key');
        // Save new settings
        add_option('mailchimpcampaigns_settings', $settings ); // return nothing it already exists
        update_option('mailchimpcampaigns_settings', $settings );// update it just in case
    }
}
register_activation_hook( __FILE__, 'mailchimpcampaigns_rewrite_flush' );