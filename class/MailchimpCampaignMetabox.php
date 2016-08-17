<?php

/**
 * Register a meta box using a class.
 */
class MailchimpCampaignMetabox 
{
 
    // Settings
    public $settings;
    public $post;
    public $post_type;

  /**
    * Constructor.
    */
  public function __construct() {
    global $post;
    $this->post = $post;
    $this->settings = get_option('mailchimpcampaigns_settings', false) ?  (object) get_option('mailchimpcampaigns_settings') : false;
    $this->post_type = empty($this->settings->cpt_name) ? MCC_DEFAULT_CPT : $this->settings->cpt_name;
    $this->init_metabox();
  }
 
  /**
  * Meta box initialization.
  */
  public function init_metabox() {
    add_action( 'add_meta_boxes', array( $this, 'add_metabox'  ) );
    add_action( 'save_post',      array( $this, 'save_metabox' ), 10, 2 );
  }

  /**
  * Adds the meta box.
  */
  public function add_metabox( ) {
    add_meta_box(
        'my-meta-box',
        __( 'My Meta Box', 'textdomain' ),
        array( $this, 'render_metabox' ),
        $this->post_type,
        'advanced',
        'default'
    );

  }

  /**
  * Renders the meta box.
  */
  public function render_metabox( $post ) {
    // Add nonce for security and authentication.
    wp_nonce_field( 'custom_nonce_action', 'custom_nonce' );
    $output =  'test';
    echo $output;
  }

  /**
  * Handles saving the meta box.
  *
  * @param int     $post_id Post ID.
  * @param WP_Post $post    Post object.
  * @return null
  */
  public function save_metabox( $post_id, $post ) {
    // Add nonce for security and authentication.
    $nonce_name   = isset( $_POST['custom_nonce'] ) ? $_POST['custom_nonce'] : '';
    $nonce_action = 'custom_nonce_action';

    // Check if nonce is set.
    if ( ! isset( $nonce_name ) ) {
        return;
    }

    // Check if nonce is valid.
    if ( ! wp_verify_nonce( $nonce_name, $nonce_action ) ) {
        return;
    }

    // Check if user has permissions to save data.
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    // Check if not an autosave.
    if ( wp_is_post_autosave( $post_id ) ) {
        return;
    }

    // Check if not a revision.
    if ( wp_is_post_revision( $post_id ) ) {
        return;
    }
  }
}