<?php

if ( ! class_exists('MailchimpAdmin') ):
/**
 * MailChimp Campaigns API
 *
 * Make use of the great WordPress Settings API 
 * @see https://codex.wordpress.org/Creating_Options_Pages#Example_.232
 *
 * @author Matthieu Scarset <m@matthieuscarset.com>
 * @see http://matthieuscarset.com/
 * @version 1.0.0
 */
class MailchimpAdmin extends Mailchimp
{

    /**
     * Start up
     */
    public function __construct()
    {
        parent::__construct();
        add_action( 'contextual_help', array( $this, 'help_tab' ), 10, 3 );
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
        add_action( 'update_option_mailchimpcampaigns_settings', array( $this, 'action_update_option'), 10, 3 );

        // Register custom AJax call.
        // @example add_action( 'wp_ajax_my_action', 'my_action' );
        // @see https://codex.wordpress.org/AJAX_in_Plugins
        add_action( 'wp_ajax_mailchimpcampaigns_import', array($this, 'import_ajax') );        
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'MailChimp Admin', 
            'MailChimp Campaigns', 
            'manage_options', 
            'mailchimpcampaigns-admin', 
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        ?>
        <div class="wrap">
            <h1><?php echo $this->logo('display: inline-block; height: 20px;'); ?> Mailchimp Campaigns</h1>
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'mailchimpcampaign_option_group' );
                do_settings_sections( 'mailchimpcampaign-admin' );
                
                // Display submit button
                submit_button( 'Save settings', 'primary', 'submit-form', false );

                // Display import button
                if( isset($this->settings['api_key']) && ! empty($this->settings['api_key']))
                    $this->import_button();                                
            ?>
            <div id="mailchimpcampaigns_placeholder"></div>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {        
        register_setting(
            'mailchimpcampaign_option_group', // Option group
            'mailchimpcampaigns_settings', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'mailchimpcampaigns_settings_section', // ID
            __('Custom settings', MCC_TEXT_DOMAIN), // Title
            array( $this, 'print_section_info' ), // Callback
            'mailchimpcampaign-admin' // Page
        );  

        add_settings_field(
            'api_authname',
            'Username',
            array( $this, 'field_api_authname_callback' ), // Callback
            'mailchimpcampaign-admin', // Page
            'mailchimpcampaigns_settings_section', // Section     
            array( 'label_for' => 'mailchimpcampaigns_api_authname' ) // Form label
        );
        add_settings_field(
            'api_key',
            'API Key',
            array( $this, 'field_api_key_callback' ), // Callback
            'mailchimpcampaign-admin', // Page
            'mailchimpcampaigns_settings_section', // Section     
            array( 'label_for' => 'mailchimpcampaigns_api_key' ) // Form label
        );
        add_settings_field(
            'cpt_name',
            __('Custom Post Type\'s name', MCC_TEXT_DOMAIN),
            array( $this, 'field_cpt_name_callback' ), // Callback
            'mailchimpcampaign-admin', // Page
            'mailchimpcampaigns_settings_section', // Section     
            array( 'label_for' => 'mailchimpcampaigns_cpt_name' ) // Form label
        );

        // Lab
        add_settings_field(
            'import_since',
            __('Import Campaigns Since (beta)', MCC_TEXT_DOMAIN),
            array( $this, 'field_import_since_callback' ), // Callback
            'mailchimpcampaign-admin', // Page
            'mailchimpcampaigns_settings_section', // Section
            array( 'label_for' => 'mailchimpcampaigns_import_since' ) // Form label
        );

        // Lab
        add_settings_field(
            'import_before',
            __('Import Campaigns Untill (beta)', MCC_TEXT_DOMAIN),
            array( $this, 'field_import_before_callback' ), // Callback
            'mailchimpcampaign-admin', // Page
            'mailchimpcampaigns_settings_section', // Section
            array( 'label_for' => 'mailchimpcampaigns_import_before' ) // Form label
        );
        
       // Lab
        add_settings_field(
            'show_preview',
            __('Show preview (experimental)', MCC_TEXT_DOMAIN),
            array( $this, 'field_show_preview_callback' ), // Callback
            'mailchimpcampaign-admin', // Page
            'mailchimpcampaigns_settings_section', // Section     
            array( 'label_for' => 'mailchimpcampaigns_show_preview' ) // Form label
        );

    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();

        if( isset( $input['api_authname'] ) )
            $new_input['api_authname'] = sanitize_text_field( $input['api_authname'] );

        if( isset( $input['cpt_name'] ) )
            $new_input['cpt_name'] = sanitize_title( sanitize_text_field( $input['cpt_name'] ) );
        
        // Saving API KEY
        if( isset( $input['api_key'] ) ) 
        {
            $new_input['api_key'] = sanitize_text_field( $input['api_key'] );
            $new_input['api_key'] = $this->check_api_key( $new_input['api_key'] );
        }

        // Import campaigns
        $api_key_current = ( isset($this->settings['api_key']) && !empty($this->settings['api_key']) )? $this->settings['api_key'] : null;
        $api_key_has_changed = isset($new_input['api_key']) && ($new_input['api_key'] != $api_key_current) ? true : false;  
        if( isset( $input['import'] ) && ! $api_key_has_changed ) {
            if( $this->test() )
                $this->import();
        }

        if( isset( $input['import_since'] ) )  {
            $new_input['import_since'] = sanitize_title( sanitize_text_field( $input['import_since'] ) );
            //<Pending> Create check_date validation funtion
            //$new_input['import_since'] = $this->check_date( $new_input['import_since'] );
        }

        if( isset( $input['import_before'] ) )  {
            $new_input['import_before'] = sanitize_title( sanitize_text_field( $input['import_before'] ) );
            //<Pending> Create check_date validation funtion
            //$new_input['import_before'] = $this->check_date( $new_input['import_before'] );
        }

        if( isset( $input['show_preview']) && $input['show_preview'] == '1' )
            $new_input['show_preview'] = true;

        return $new_input;
    }

    /**
     * Check API KEY format
     */
    public function check_api_key($key)
    {
        if( strpos($key, '-') === false ) {
            return;
        } 
        else {
            return $key;
        }
    }

    /** 
     * Print the Section text
     */
    public function print_section_info()
    {
        print __('Enter your Mailchimp settings below:', MCC_TEXT_DOMAIN);
    }
    
    /**
    * Fields
    */
    public function field_api_authname_callback() {
        printf(
            '<input class="code" type="text" required id="api_authname" name="mailchimpcampaigns_settings[api_authname]" value="%s" />',
            isset( $this->settings['api_authname'] ) ? esc_attr( $this->settings['api_authname']) : ''
        );
        print '<p class="description">'. __('Let Mailchimp knows who you are', MCC_TEXT_DOMAIN).' :)</p>';
    }
    public function field_api_key_callback() {
        printf(
            '<input class="code" type="text" required id="api_key" name="mailchimpcampaigns_settings[api_key]" value="%s" />',
            isset( $this->settings['api_key'] ) ? esc_attr( $this->settings['api_key']) : ''
        );
        print 
        '<p class="description">' .
            __('Don\'t know how to get a MailChimp API key?', MCC_TEXT_DOMAIN).
            '<a href="http://developer.mailchimp.com/documentation/mailchimp/guides/how-to-use-oauth2/#register-your-application">'. 
            ' ' . __('Read the doc', MCC_TEXT_DOMAIN) . 
            '</a>
        </p>';
    }
    public function field_cpt_name_callback() {
        $placeholder = __('Default: '. MCC_DEFAULT_CPT, MCC_TEXT_DOMAIN);      
        printf(
            '<input class="code" type="text" id="cpt_name" name="mailchimpcampaigns_settings[cpt_name]" value="%s" placeholder="'.$placeholder.'" />',
            isset( $this->settings['cpt_name'] ) ? esc_attr( $this->settings['cpt_name']) : ''
        );
        print 
        '<p class="description">'.
          __('Lowercase only with no special character nor space.', MCC_TEXT_DOMAIN).
          '<br/>'.
          __('Refresh permalinks after change (<a href="options-permalink.php">Permalinks</a> > Click save).', MCC_TEXT_DOMAIN).
        '</p>';
    }
    public function field_import_since_callback() {
        printf(
            '<input class="code" type="text" id="import_since" name="mailchimpcampaigns_settings[import_since]" value="%s" />',
            isset( $this->settings['import_since'] ) ? esc_attr( $this->settings['import_since']) : ''
        );
        print '<p class="description">'. __('Import campaigns since this date', MCC_TEXT_DOMAIN).' (Use YYYY-MM-DD Format) </p>';
    }
    public function field_import_before_callback() {
        printf(
            '<input class="code" type="text" id="import_before" name="mailchimpcampaigns_settings[import_before]" value="%s" />',
            isset( $this->settings['import_before'] ) ? esc_attr( $this->settings['import_before']) : ''
        );
        print '<p class="description">'. __('Import campaigns since this date', MCC_TEXT_DOMAIN).' (Use YYYY-MM-DD Format) </p>';
    }
    public function field_show_preview_callback() {
        $checked = (isset($this->settings['show_preview']) && $this->settings['show_preview'] === true) ? ' checked' : '';
        echo '<input type="checkbox" id="show-preview" name="mailchimpcampaigns_settings[show_preview]" value="1"'.$checked.' />' .
            ' Activate campaigns preview in admin screens';
    }
  
    /*
    * Help tab for admin screens
    */
    public function help_tab($contextual_help, $screen_id, $screen ){
        $cpt = isset($this->settings['cpt_name']) && ! empty($this->settings['cpt_name']) ? $this->settings['cpt_name'] : MCC_DEFAULT_CPT;
        if ( $cpt == $screen->id || $screen_id == 'settings_page_mailchimpcampaigns-admin') {
            $screen = get_current_screen();
            $screen->add_help_tab( array(
                'id' => $screen->id,
                'title' => __('Help'),
                'content' => __('You can import your mailchimp campaigns from the settings page (Settings > Mailchimp Campaign).', MCC_TEXT_DOMAIN),
            ));
        }
    }

    /**
    * Do stuff on option update
    */
    public function action_update_option(  $old_value, $value, $option ) {
         if( $option == 'mailchimpcampaigns_settings') {
            $has_changed = ($old_value['cpt_name'] != $value['cpt_name']);
            if( $has_changed )
                flush_rewrite_rules(); // If CPT Name has changed
        }
    }

    /**
     * Import Mailchimp Campaigns
     */
    public function import(){
        // $MCCampaigns = get_transient('mailchimpcampaigns_mcc_campaigns', new MailchimpCampaigns()) ;
        $MCCampaigns = new MailchimpCampaigns();
        return $MCCampaigns->test() ? $MCCampaigns->import(true) : new WP_Error('error on import', __('Error on import. Try again later.', MCC_TEXT_DOMAIN));
    }

    /**
    * Ajax button
    * wp_die() is required to terminate immediately and return a proper response
    */
    public function import_ajax()
    {
        $data = $this->import(); 
        // echo $return;
        wp_die(); 
    }

    /** 
     * Import button
     */
    public function import_button() {
      // Call our custom action on submit.
      submit_button( 'Import', 'primary', 'mailchimpcampaigns_import', false );     
    }

    public function logo($css){
        return '<img src="https://static.mailchimp.com/web/social/freddie.png" style="'.$css.'" />';
    }

}
endif;
