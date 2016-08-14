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
define( 'MC_TEXT_DOMAIN', 'mailchimpcampaigns' );
define( 'MC_DEFAULT_CPT',  'newsletter' );
define( 'MC_PLUGIN_ROOT_DIR', plugin_dir_path( __FILE__ ) );
include( MC_PLUGIN_ROOT_DIR . 'mailchimp-admin.php');
include( MC_PLUGIN_ROOT_DIR . 'class/Mailchimp.php');

// Start the fun!
/*
$mc = new Mailchimp();
$mc->call(); 
var_dump($mc->last_call);
var_dump($mc->get('last_call'));
wp_die();
*/