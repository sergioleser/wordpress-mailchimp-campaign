<?php
/**
 * Add all our sections, fields and settings during admin_init
 */
function mailchimpcampaigns_settings_api_init() {
    // Settings
    add_settings_section(
        'mailchimpcampaigns_setting_section',
        'MailChimp Campaigns',
        'mailchimpcampaigns_setting_section_callback_function',
        'general'
    );
    // Settings fields
    add_settings_field(
        'mailchimpcampaigns_api_authname',
        'Username',
        'mailchimpcampaigns_setting_field_api_authname',
        'general',
        'mailchimpcampaigns_setting_section',
        array( 'label_for' => 'mailchimpcampaigns_api_authname' )
    );
    add_settings_field(
        'mailchimpcampaigns_api_key',
        'API key',
        'mailchimpcampaigns_setting_field_api_key',
        'general',
        'mailchimpcampaigns_setting_section',
        array(
            'label_for' => 'mailchimpcampaigns_api_key'
        )
    );
    add_settings_field(
        'mailchimpcampaigns_post_type',
        'Custom Post Type',
        'mailchimpcampaigns_setting_field_post_type',
        'general',
        'mailchimpcampaigns_setting_section',
        array(
            'label_for' => 'mailchimpcampaigns_post_type'
        )
    );

    // Register our settings in wp_options table
    register_setting( 'general', 'mailchimpcampaigns_api_authname');
    register_setting( 'general', 'mailchimpcampaigns_api_key');
    register_setting( 'general', 'mailchimpcampaigns_post_type', 'mailchimpcampaigns_post_type_validation');
}
function mailchimpcampaigns_post_type_validation($value){
    return empty($value) ? MC_DEFAULT_CPT : sanitize_title($value);
}
function mailchimpcampaigns_setting_section_callback_function( $arg ) { 
    echo
    '<div id="'. $arg['id'].'">'.
        // a special phrase can be printed here
    '</div>';
}
add_action( 'admin_init', 'mailchimpcampaigns_settings_api_init' );

/**
 * Fields
 */
function mailchimpcampaigns_setting_field_api_key() {
    echo '<input name="mailchimpcampaigns_api_key" id="mailchimpcampaigns_api_key" type="text" value="'.get_option( 'mailchimpcampaigns_api_key', '' ).'" class="regular-text code" />';
    echo 
    '<p class="description">' .
        __('Don\'t know how to get a MailChimp API key?', MC_TXT_DOMAIN).
        '<a href="http://developer.mailchimp.com/documentation/mailchimp/guides/how-to-use-oauth2/#register-your-application">'. 
        __('Read the doc', MC_TXT_DOMAIN) . 
        '</a>
    </p>';
}
function mailchimpcampaigns_setting_field_api_authname() {
    echo '<input name="mailchimpcampaigns_api_authname" id="mailchimpcampaigns_api_authname" type="text" value="'.get_option( 'mailchimpcampaigns_api_authname', '' ).'" class="regular-text code" />';
}
function mailchimpcampaigns_setting_field_post_type() {
    $cpt =  get_option('mailchimpcampaigns_post_type', MC_DEFAULT_CPT); 
    $placeholder = __('Default: '.$cpt, MC_TXT_DOMAIN);
    echo '<input name="mailchimpcampaigns_post_type" id="mailchimpcampaigns_post_type" type="text" placeholder="'.$placeholder.'" value="'.$cpt.'" class="regular-text code" />';
    if( $cpt ) : 
    echo mailchimpcampaigns_button();
    endif;       
    echo '<br />';
    echo '<p class="description">'.__('Change this setting only if you want a custom Post Type url.', MC_TXT_DOMAIN).'</p>';
    echo '<p class="description">'. __('This settings must be one lowercase word only with no special character nor space.', MC_TXT_DOMAIN).'</p>';
    echo '<p class="description">'.__('<u>Important</u>: already imported campaigns <strong>are not</strong> deleted automatically.', MC_TXT_DOMAIN).'</p>';
}

/**
 * Miscellaneous
 */
function mailchimpcampaigns_button(){   
    $apikey = get_option('mailchimpcampaigns_api_key', false );
    $cpt =  get_option('mailchimpcampaigns_post_type', MC_DEFAULT_CPT); 
    if ( $apikey ) : $button = ''. 
        '<p>'.
            '<a class="button button-secondary" href="/edit.php?post_type='. $cpt.'">'.
                __('View', MC_TXT_DOMAIN) .'&nbsp;'. ucfirst($cpt).'&nbsp;<span class="dashicons dashicons-migrate" style="line-height:1.3"></span>'.
            '</a>'.
        '</p>';
     endif;
     return $button;
}

