<?php

/**
 *
 */
class MailchimpPost 
{
  
  public $post;
  public $post_metas;

  /**
  *
  */
  public function __construct($post)
  {
    $this->post = $post;
    $this->prepare_post_metas();
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