<?php
/**
 * MailChimp Campaigns API
 *
 * Make use of the simplistic WordPress Post API 
 * to save Mailchimp campaigns as Custom Posts.
 *
 * @author Matthieu Scarset <m@matthieuscarset.com>
 * @see http://matthieuscarset.com/
 * @version 1.0.0
 */

class MailChimpCampaign
{
  // Settings
  public $campaign;
  public $post;
  public $post_exists = false;
  public $post_type;
  public $post_metas;

  /**
    * Start up
   */
  public function __construct($campaign)
  {
    $this->campaign = $campaign;
    $this->settings = get_option('mailchimpcampaigns_settings', false) ?  (object) get_option('mailchimpcampaigns_settings') : false;
    $this->post_type_previous = empty($this->settings->cpt_name_previous) ? MCC_DEFAULT_CPT : $this->settings->cpt_name_previous;
    $this->post_type = empty($this->settings->cpt_name) ? MCC_DEFAULT_CPT : $this->settings->cpt_name;
    $this->get(); // either get exisitng campaign CPT or an empty Post object
    $this->post_metas = array();
  }  

  /**
   * Populate with the exisitng campaign CPT or an empty Post object
   * @return $this
   */
  public function get()
  {
    $args = array(
        'post_type'  => $this->post_type,
        'posts_per_page'   => 1,
        'meta_query' => array(
            array(
                'key'   => MCC_META_KEY_ID,
                'value' => $this->campaign->id,
            )
        )
    );
    $posts = get_posts( $args );
    if( count( $posts ) > 0 ) {
      $this->post = $posts[0];
      $this->post_exists = true;
    }
    else {
      // Populate an empty post object
      $this->post = new WP_Post((object)array(
        'post_author' => get_current_user_id(),
        'post_type' => $this->post_type,
      ));
    }
    return $this;
  }

  /**
  *
  */
  public function set()
  {
    // Populate required fields
    $title= !empty($this->campaign->settings->title) ? $this->campaign->settings->title : __('Empty title', MCC_TXT_DOMAIN);
    $excerpt= !empty($this->campaign->settings->subject) ? $this->campaign->settings->subject : __('Empty excerpt', MCC_TXT_DOMAIN);

    // Create a new WP_Post
    $this->post->post_type = $this->post_type;
    $this->post->post_name = $title; 
    $this->post->post_title = $title;
    $this->post->post_excerpt = $excerpt;
    $this->post->post_date = str_replace('T', ' ',  $this->campaign->create_time);
    // $this->post->post_date_gmt = str_replace('T', ' ',  $campaign->create_time);
    $this->post->post_content = '';
    $this->post->post_status = MCC_DEFAULT_CPT_STATUS;
    $this->post->comment_status = 'open';
    // $this->post->post_modified = '2016-08-15 14:53:24',
    // $this->post->post_modified_gmt = '2016-08-15 14:53:24';

    // Custom metadata
    foreach($this->campaign as $meta_key => $meta_value){
      $this->post_metas[MCC_META_PRE . $meta_key] = $meta_value;
    }
    
    return $this;
  }

  /**
   * Insert post in database
   */
  public function save()
  {
    // Save || Update post
    $post_id = $this->post_exists ? wp_update_post( $this->post, true) : wp_insert_post( $this->post, true);
    // Save || Update post metas
    foreach( $this->post_metas as $meta_key => $meta_value ){
      $unique = ($meta_key == MCC_META_PRE . 'id') ? true : false;
      $post_metas = $this->post_exists ?
        update_post_meta($post_id, $meta_key, $meta_value, $prev_value) : add_post_meta($post_id, $meta_key, $meta_value, $unique);
    }
    return $this;
  }

  /**
   *
   */
   public function delete()
   {

   }
  
  /**
   *
   */
   public function edit()
   {
   }

  /**
   *
   */
   public function send()
   {

   }

  /**
   *
   */
   public function schedule()
   {

   }

}