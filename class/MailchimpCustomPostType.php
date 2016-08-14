<?php
   
 class MailchimpCustomPostType extends Mailchimp
 {

    /**  
     * Holds the values to be used in the fields callbacks
     */
    public $post_type_name;

    /**
     * Start up
     */
    public function __construct()
    {
        parent::__construct();
        $this->post_type_name = isset( $this->settings['cpt_name'] ) && !empty($this->settings['cpt_name']) ? $this->settings['cpt_name'] : MCC_DEFAULT_CPT;
        add_action( 'init', array( $this, 'register_post_type') );
        add_action( 'contextual_help', array( $this, 'help_tab' ), 10, 3 );
    }

 
    /**
    * Custom Post Type
    */
    public function register_post_type()
    {
        $cpt = $this->post_type_name;
        $labels = array(
            'name' => _x( ucfirst($cpt), 'Post type general name', MCC_TXT_DOMAIN ),
            'singular_name' => _x( $cpt, 'Post type singular name', MCC_TXT_DOMAIN ),
            'menu_name' => _x( ucfirst($cpt), 'Admin Menu text', MCC_TXT_DOMAIN ),
            'name_admin_bar' => _x( ucfirst($cpt), 'Add New on Toolbar', MCC_TXT_DOMAIN ),
            'add_new' => __( 'Add New', MCC_TXT_DOMAIN ),
            'add_new_item' => __( 'Add New ' . $cpt, MCC_TXT_DOMAIN ),
            'new_item' => __( 'New ' . $cpt, MCC_TXT_DOMAIN ),
            'edit_item'  => __( 'Edit ' . $cpt, MCC_TXT_DOMAIN ),
            'view_item' => __( 'View ' . $cpt, MCC_TXT_DOMAIN ),
            'all_items' => __( 'All ' . $cpt, MCC_TXT_DOMAIN ),
            'search_items' => __( 'Search ' . $cpt, MCC_TXT_DOMAIN ),
            'parent_item_colon' => __( 'Parent ' . $cpt . ':', MCC_TXT_DOMAIN ),
            'not_found' => __( 'No '. $cpt .' found.', MCC_TXT_DOMAIN ),
            'not_found_in_trash' => __( 'No ' .  $cpt .' found in Trash.', MCC_TXT_DOMAIN ),
            'featured_image' => _x( ucfirst($cpt) . ' cover image', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', MCC_TXT_DOMAIN ),
            'set_featured_image' => _x( 'Set cover image', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', MCC_TXT_DOMAIN ),
            'remove_featured_image' => _x( 'Remove cover image', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', MCC_TXT_DOMAIN ),
            'use_featured_image' => _x( 'Use as cover image', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', MCC_TXT_DOMAIN ),
            'archives' => _x( ucfirst($cpt) . ' archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', MCC_TXT_DOMAIN ),
            'insert_into_item' => _x( 'Insert into ' . $cpt, 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', MCC_TXT_DOMAIN ),
            'uploaded_to_this_item' => _x( 'Uploaded to this ' . $cpt, 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', MCC_TXT_DOMAIN ),
            'filter_items_list' => _x( 'Filter '.$cpt.' list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', MCC_TXT_DOMAIN ),
            'items_list_navigation' => _x( ucfirst($cpt) . ' list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', MCC_TXT_DOMAIN ),
            'items_list' => _x( ucfirst($cpt) . ' list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', MCC_TXT_DOMAIN ),
        );

        $args = array(
            'labels' => $labels,
            'public' =>  true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'query_var' => true,
            'rewrite' => array( 'slug' => $cpt ),
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => false,
            'menu_icon' => 'dashicons-email',
            'supports' => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments', 'custom-fields' ),
        );
        register_post_type( $cpt, $args);
    }

    /*
    * Help tab for admin screens
    */
    public function help_tab($contextual_help, $screen_id, $screen ){
        $cpt = $this->post_type_name;
        if ( 'edit-'.$cpt == $screen->id ) {
            $screen = get_current_screen();
            $screen->add_help_tab( array(
                'id' => $screen->id,
                'title' => __('Synchronization'),
                'content' => __('Help tab', MCC_TXT_DOMAIN),
            ));
        }
    }

}

// Instanciate our class 
$MCCCustomPostTYpe = new MailchimpCustomPostType();
