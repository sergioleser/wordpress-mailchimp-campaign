<?php

/**
 * Register a meta box using a class.
 */
class MailchimpCampaignMetabox 
{
 
    // Settings
    public $settings;
    public $post_type;
    public $post_metas;

  /**
    * Constructor.
    */
  public function __construct() {
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
        'mailchimpcampaigns-preview',
        __( 'Campaign preview', MCC_TXT_DOMAIN ),
        array( $this, 'render_metabox' ),
        $this->post_type,
        'normal',
        'high',
        'preview'
    );
    add_meta_box(
        'mailchimpcampaigns-stats',
        __( 'Campaign statistics', MCC_TXT_DOMAIN ),
        array( $this, 'render_metabox' ),
        $this->post_type,
        'side',
        'high',
        'stats'
    );
    add_meta_box(
        'mailchimpcampaigns-list',
        __( 'Campaign list', MCC_TXT_DOMAIN ),
        array( $this, 'render_metabox' ),
        $this->post_type,
        'side',
        'high',
        'list'
    );

  }

  /**
  * Renders the meta box.
  */
  public function render_metabox( $post, $box ) {
    // Save the $post object now
    // because was not available anytime before
    if( ! $this->post )
      $this->post = $post;

     // Switch over metaboxes ID
    $output = '';
    switch($box['args']){
      default:
        break;
      case 'preview':
        $output = $this->get_meta('content_html', true);
        break;
      case 'stats':
        $this->prepare_post_metas(); // populate metas if not done yet
        if( $this->post_metas ) 
        {
          foreach( $this->post_metas as $meta_key => $meta_value)
          {
            $real_meta_key = $this->get_meta_key($meta_key); 
            $meta_key_label = $this->get_meta_label($meta_key); 
            $stats_keys = array( 'id', 'type', 'create_time', 'archive_url', 'status', 'send_time', 'emails_sent', 'content_type');
            if( in_array($real_meta_key, $stats_keys  ) )
              echo $this->display_meta( $meta_key_label, current($meta_value) );
          }   
        }
        break;
      case 'list':
        $list_data =  $this->get_meta('recipients', true);
        if( $list_data )
        {
          foreach( $list_data as $meta_key => $meta_value)
          {
            echo $this->display_meta( MCC_META_MAP[$meta_key], $meta_value );
          }
        }
        break;
      case 'tracking':
        var_dump( $this->post_metas->tracking );
        break;
    }

    // For security check
    $output .= wp_nonce_field( 'mailchimpcampaigns_nonce_action', 'mailchimpcampaigns_nonce' );

    echo $output;
  }

  /**
  * Handles saving the meta box.
  *
  * @param int     $post_id Post ID.
  * @param WP_Post $post    Post object.
  * @return null
  */
  public function save_metabox( $post_id, $post ) 
  {
    // Add nonce for security and authentication.
    $nonce_name   = isset( $_POST['mailchimpcampaigns_nonce'] ) ? $_POST['mailchimpcampaigns_nonce'] : '';
    $nonce_action = 'mailchimpcampaigns_nonce_action';

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


  /**
  * Save post metadata when needed
  */
  public function prepare_post_metas()
  {
    if( ! $this->post_metas )
      $this->post_metas = (object) get_post_meta($this->post->ID);
  }
  
  /**
  *
  *
  */
  public function get_meta($meta_key, $single = false)
  {
    $this->prepare_post_metas();
    $real_meta_key =   MCC_META_PRE . $meta_key;  
    $meta = isset($this->post_metas->{$real_meta_key}) ? $this->post_metas->{$real_meta_key} : get_post_meta( $this->post->ID, $real_meta_key, $single );
    $meta = maybe_unserialize( current($meta) );
    return $meta;
  }

  /**
  * Remove prefix from meta key
  */
  public function get_meta_key($meta_key)
  {
    $prefix_length = strlen(MCC_META_PRE);
     if( substr($meta_key, 0, $prefix_length ) == MCC_META_PRE )
       $meta_key = substr($meta_key, $prefix_length);
    
    return $meta_key;
  }
  
  /**
  *
  */
  public function get_meta_label($meta_key)
  {
    return MCC_META_MAP[$this->get_meta_key($meta_key)]; 
  }

  /**
  *
  */
  public function display_meta($meta_key = null, $meta_value = null)
  {
    $is_link = (substr($meta_value, 0, 4) == 'http' );
    $echo =  $meta_value;
    if( $is_link )
      $echo = '<a class="mcc__meta-link" href=" '.$meta_value.' " target="_blank">'.$echo.'</a>';

    return 
      '<p class="mcc__metabox">' .
        '<span class="mcc__meta-key">'.$meta_key.'</span>'. 
        '<span class="mcc__meta-value">' . $echo. '</span>'.
      '</p>';
  }

}