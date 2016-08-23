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
define( 'MCC_META_MAP', array( 
    'id' => __('ID', MCC_TEXT_DOMAIN),
    'type' => _('Type', MCC_TEXT_DOMAIN),
    'status' => _('Status', MCC_TEXT_DOMAIN),
    'create_time' => _('Created on', MCC_TEXT_DOMAIN),
    'send_time' => _('Sent on', MCC_TEXT_DOMAIN),
    'emails_sent' => _('Emails sent', MCC_TEXT_DOMAIN),
    'delivery_status' => _('Delivery status', MCC_TEXT_DOMAIN),
    // Content
    // mcc_content_plain_text
    // mcc_content_html
    'content_type' => _('Content type', MCC_TEXT_DOMAIN),
    'archive_url' => _('Archive URL', MCC_TEXT_DOMAIN),
    'long_archive_url' => _('Archive URL (long)', MCC_TEXT_DOMAIN),
    // Lists related
    'recipients' => _('Recipients', MCC_TEXT_DOMAIN),
    'list_id' => _('List ID', MCC_TEXT_DOMAIN),
    'list_name' => _('List name', MCC_TEXT_DOMAIN),
    'segment_text' => _('Segment', MCC_TEXT_DOMAIN),
    'recipient_count' => _('Recipients', MCC_TEXT_DOMAIN),
    // Extra campaign settings
    'settings' => _('Settings', MCC_TEXT_DOMAIN),
    'tracking' => _('Tracking', MCC_TEXT_DOMAIN),
    'social_card' => _('Social card', MCC_TEXT_DOMAIN),
    'report_summary' => _('Report summary', MCC_TEXT_DOMAIN),
    // Help related
    '__links' => _('Action links', MCC_TEXT_DOMAIN),
    '_edit_lock' => _('Edit lock', MCC_TEXT_DOMAIN),
    '_edit_last' => _('Edit last', MCC_TEXT_DOMAIN),
));
define( 'MCC_PLUGIN_ROOT_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Enqueue plugin style-file
 */
function mailchimpcampaigns_add_css() {
    wp_register_style( 'mailchimpcampaigns_metaboxes', plugins_url('css/mailchimpcampaigns_metaboxes.css', __FILE__) );
    wp_enqueue_style( 'mailchimpcampaigns_metaboxes' );
}
add_action( 'admin_enqueue_scripts', 'mailchimpcampaigns_add_css' );
add_action( 'wp_enqueue_scripts', 'mailchimpcampaigns_add_css' ); 

require_once( MCC_PLUGIN_ROOT_DIR . 'class/Mailchimp.php');
require_once( MCC_PLUGIN_ROOT_DIR . 'class/MailchimpCustomPostType.php');
$MCCPostType = new MailchimpCustomPostType();

/**
 * Implements hook_init()
 */
function mailchimpcampaigns_init(){
    // Get required files
    if( is_admin() )
    {
        require_once( MCC_PLUGIN_ROOT_DIR . 'class/MailchimpAdmin.php');
        require_once( MCC_PLUGIN_ROOT_DIR . 'class/MailchimpCampaign.php');
        require_once( MCC_PLUGIN_ROOT_DIR . 'class/MailchimpCampaigns.php');
        require_once( MCC_PLUGIN_ROOT_DIR . 'class/MailchimpCampaignMetabox.php');
        require_once( MCC_PLUGIN_ROOT_DIR . 'class/MailchimpPost.php');
        $MCCAdmin = new MailchimpAdmin();       
    } 
}
add_action( 'init', 'mailchimpcampaigns_init' );

/**
 * Add Metaboxes to CPT admin screens
 */
function mailchimpcampaigns_edit_screen(){
    $MCCampaignsMetabox = new MailchimpCampaignMetabox($post);
}
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
    $MCCampaigns = get_transient('mailchimpcampaigns_mcc_campaigns', new MailchimpCampaigns());
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

/**
*
*/
function mailchimpcampaigns_embed_filter() {
    global $post;
    $metas = get_post_meta($post->ID);
    $output = ''.
        $metas['mcc_content_html'][0].
    '';
    echo $output; 
}; 
add_action( 'embed_content', 'mailchimpcampaigns_embed_filter', 10, 0); 