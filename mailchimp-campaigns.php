<?php
/*
  	Plugin Name: MailChimp Campaigns
    Plugin Script: mailchimp-campaigns.php
    Plugin URI:   http://wordpress.org/extend/plugins/mailchimp-campaigns/
    Description: Display your MailChimp campaigns with simple shortcodes.
    Author: MatthieuScarset 
    Author URI: http://matthieuscarset.com/
    License: GPL
    Version: 3.0.6
    Text Domain: mailchimpcampaigns
    Domain Path: languages/

    Display your MailChimp campaigns with simple shortcodes.
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { exit; }

// Define globals
define( 'MCC_VERSION', '3.0.6' );
define( 'MCC_API_VERSION', '3.0' );
define( 'MCC_DEFAULT_CPT',  'newsletter' );
define( 'MCC_DEFAULT_CPT_STATUS',  'publish' );
define( 'MCC_TEXT_DOMAIN', 'mailchimpcampaigns' );
define( 'MCC_META_PRE', 'mcc_' );
define( 'MCC_META_KEY_ID', MCC_META_PRE .'id' );
define( 'MCC_PLUGIN_ROOT_DIR', plugin_dir_path( __FILE__ ) );

function mailchimpcampaigns_register_labels(){
    $labels = array( 
        'id' => __('ID', MCC_TEXT_DOMAIN),
        'type' => __('Type', MCC_TEXT_DOMAIN),
        'status' => __('Status', MCC_TEXT_DOMAIN),
        'create_time' => __('Created on', MCC_TEXT_DOMAIN),
        'send_time' => __('Sent on', MCC_TEXT_DOMAIN),
        'emails_sent' => __('Emails sent', MCC_TEXT_DOMAIN),
        'delivery_status' => __('Delivery status', MCC_TEXT_DOMAIN),
        // Content
        // mcc_content_plain_text
        // mcc_content_html
        'content_type' => __('Content type', MCC_TEXT_DOMAIN),
        'archive_url' => __('Archive URL', MCC_TEXT_DOMAIN),
        'long_archive_url' => __('Archive URL (long)', MCC_TEXT_DOMAIN),
        // Lists related
        'recipients' => __('Recipients', MCC_TEXT_DOMAIN),
        'list_id' => __('List ID', MCC_TEXT_DOMAIN),
        'list_name' => __('List name', MCC_TEXT_DOMAIN),
        'segment_text' => __('Segment', MCC_TEXT_DOMAIN),
        'recipient_count' => __('Recipients', MCC_TEXT_DOMAIN),
        // Extra campaign settings
        'settings' => __('Settings', MCC_TEXT_DOMAIN),
        'tracking' => __('Tracking', MCC_TEXT_DOMAIN),
        'social_card' => __('Social card', MCC_TEXT_DOMAIN),
        'report_summary' => __('Report summary', MCC_TEXT_DOMAIN),
        // Help related
        '__links' => __('Action links', MCC_TEXT_DOMAIN),
        '_edit_lock' => __('Edit lock', MCC_TEXT_DOMAIN),
        '_edit_last' => __('Edit last', MCC_TEXT_DOMAIN),
    );
    if( get_option('mailchimpcampaigns_labels', false) ) {
        update_option('mailchimpcampaigns_labels', $labels);
    } else {
        add_option('mailchimpcampaigns_labels', $labels);
    }
    return $labels; 
}
register_activation_hook( __FILE__, 'mailchimpcampaigns_register_labels' );
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
require_once( MCC_PLUGIN_ROOT_DIR . 'class/MailchimpPost.php');
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
        $MCCAdmin = new MailchimpAdmin();       
    } 
}
add_action( 'init', 'mailchimpcampaigns_init' );

/**
 * Add Metaboxes to CPT admin screens
 */
function mailchimpcampaigns_edit_screen(){
    global $post;
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
        $settings_link = '<a href="options-general.php?page=mailchimpcampaigns-admin">'.__('Settings', MCC_TEXT_DOMAIN).'</a>';
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

/**
 * Shortcodes
 */
function  mailchimpcampaigns_shutdown_shortcode(){return;}
function  mailchimpcampaigns_compatibilty_shortcode(){
    // re-register old shortcodes with a dummy return function
    // for back-compatibility with v1.0.0
    $old_shortcodes = array(
        'campaign-title',
        'campaign-stats-list',
        'campaign-stats-table',
        'campaign-html',
        'campaign-text',
        'campaign-id',
        'cid'
    );
    foreach($old_shortcodes as $shortcode){
        add_shortcode ( $shortcode, 'mailchimpcampaigns_shutdown_shortcode' );
    }
}
function mailchimpcampaigns_campaign_shortcode( $atts ) {
    $content = '';
    $settings = get_option('mailchimpcampaigns_settings', false);
    $cpt = ! empty( $settings['cpt'] ) ? $settings['cpt'] : MCC_DEFAULT_CPT;
    // Attributes
    extract( shortcode_atts( array( 'id' => '', ), $atts ) );
    // Code
    if ( isset( $id ) ) {
        // Query the dabatase
        $args = array(
            'post_type'  => $cpt,
            'posts_per_page'   => 1,
            'meta_query' => array(
                array(
                    'key'   => MCC_META_KEY_ID,
                    'value' => $id,
                )
            )
        );
        // Get post
        $posts = get_posts( $args );
        if( isset($posts[0]) )
            $content = get_post_embed_html( '600', '800', $posts[0]);
        return $content;
    }
}
add_shortcode( 'campaign', 'mailchimpcampaigns_campaign_shortcode' );
