<?php
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
     * Holds the values to be used in the fields callbacks
     */
    public $settings;


    /**
     * Start up
     */
    public function __construct()
    {
        parent::__construct();
        $this->settings =  get_option('mailchimpcampaigns_settings');
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
        add_action( 'update_option', array( $this, 'action_update_option'), 10, 3 ); 
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
                submit_button();
            ?>
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
            __('Custom settings', MCC_TXT_DOMAIN), // Title
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
            __('Custom Post Type\'s name', MCC_TXT_DOMAIN),
            array( $this, 'field_cpt_name_callback' ), // Callback
            'mailchimpcampaign-admin', // Page
            'mailchimpcampaigns_settings_section', // Section     
            array( 'label_for' => 'mailchimpcampaigns_cpt_name' ) // Form label
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
        if( isset( $input['api_key'] ) )
            $new_input['api_key'] = sanitize_text_field( $input['api_key'] );

        if( isset( $input['cpt_name'] ) )
            $new_input['cpt_name'] = sanitize_title( sanitize_text_field( $input['cpt_name'] ) );
     
        return $new_input;
    }

    /** 
     * Print the Section text
     */
    public function print_section_info()
    {
        print __('Enter your Mailchimp settings below:', MCC_TXT_DOMAIN);
    }

    /**
    * Fields
    */
    public function field_api_authname_callback() {
        printf(
            '<input class="code" type="text" id="api_authname" name="mailchimpcampaigns_settings[api_authname]" value="%s" />',
            isset( $this->settings['api_authname'] ) ? esc_attr( $this->settings['api_authname']) : ''
        );
        print '<p class="description">'. __('Let Mailchimp know who you are', MCC_TXT_DOMAIN).' :)</p>';
    }
    public function field_api_key_callback() {
        printf(
            '<input class="code" type="text" id="api_key" name="mailchimpcampaigns_settings[api_key]" value="%s" />',
            isset( $this->settings['api_key'] ) ? esc_attr( $this->settings['api_key']) : ''
        );
        print 
        '<p class="description">' .
            __('Don\'t know how to get a MailChimp API key?', MCC_TXT_DOMAIN).
            '<a href="http://developer.mailchimp.com/documentation/mailchimp/guides/how-to-use-oauth2/#register-your-application">'. 
            ' ' . __('Read the doc', MCC_TXT_DOMAIN) . 
            '</a>
        </p>';
    }
    public function field_cpt_name_callback() {
        $placeholder = __('Default: '. MCC_DEFAULT_CPT, MCC_TXT_DOMAIN);      
        printf(
            '<input class="code" type="text" id="cpt_name" name="mailchimpcampaigns_settings[cpt_name]" value="%s" placeholder="'.$placeholder.'" />',
            isset( $this->settings['cpt_name'] ) ? esc_attr( $this->settings['cpt_name']) : ''
        );
        print 
        '<p class="description">'.
          __('Change this setting only if you want a custom Post Type url.', MCC_TXT_DOMAIN).
          '<br/>'.
          __('This settings must be one lowercase word only with no special character nor space.', MCC_TXT_DOMAIN).
          '<br/>'.
          __('<u>Important</u>: already imported campaigns <strong>are not</strong> deleted automatically.', MCC_TXT_DOMAIN).
        '</p>';
    }
  

    /**
    * Do stuff on option update
    */
    function action_update_option( $option, $old_value, $value ) {
        if( 'mailchimpcampaigns_settings' == $option ) {
            if( ($old_value['cpt_name'] != $value['cpt_name'] ) )
                flush_rewrite_rules(); // If CPT Name has changed
        } 
    }

    /**
    * Miscellaneous
    */
    public function mailchimpcampaigns_button(){   
        $apikey = get_option('mailchimpcampaigns_api_key', false );
        $cpt =  get_option('mailchimpcampaigns_post_type_name', MCC_DEFAULT_CPT); 
        if ( $apikey ) : $button = ''. 
            '<p>'.
                '<a class="button button-secondary" href="/edit.php?post_type='. $cpt.'">'.
                    __('View', MCC_TXT_DOMAIN) .'&nbsp;'. ucfirst($cpt).'&nbsp;<span class="dashicons dashicons-migrate" style="line-height:1.3"></span>'.
                '</a>'.
            '</p>';
        endif;
        return $button;
    }

    public function logo($css){
        return '<img src="https://static.mailchimp.com/web/social/freddie.png" style="'.$css.'" />';
    }

}

// Instanciate our class 
if( is_admin() )
    $MCCAdmin = new MailchimpAdmin();
