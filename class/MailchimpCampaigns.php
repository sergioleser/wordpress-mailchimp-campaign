<?php

if( ! class_exists('MailchimpCampaigns') ):
class MailchimpCampaigns extends Mailchimp
{
    // Properties

    // Constructor
    public function __construct($args = array())
    {
        parent::__construct();
        if( ! $this->test() ) 
            return;

        $this->fetch();
    } 

    /**
     *
     */
    public function campaigns($renew = false)
    {
        if( $renew )
            $this->fetch();
            
        return $this->campaigns->campaigns;
    }

    /**
     *
     */
    public function count()
    {
        return count($this->campaigns->campaigns);
    }

    /**
     * Get total items
     */
    public function getTotal()
    {
        $args = $this->args();
        $results = $this->call('campaigns', $args);
        $total_items = json_decode( $results->last_call['body'] )->total_items;
        return $total_items;
    }

    /**
     *
     */
    public function args( $args = array() ){
        $default_args = array(
            // 'count' => 5,
            // 'status' => 'sent',
            // 'fields' => array('id', 'type'),
        );
        $args = array_merge_recursive($default_args, $args);
        return $args;
    }

    /**
     *
     */
    public function import($renew = true)
    {
        if( $renew )
            $this->fetch();

        $cpt_name = empty($this->settings['cpt_name']) ? MCC_DEFAULT_CPT : $this->settings['cpt_name']; 

        $campaigns = $this->campaigns();
        foreach( $campaigns as $i => $campaign){
            $mcc = new MailchimpCampaign($campaign);
            $mcc->init()->fetch()->save(); // Get content for this campaigns 
            unset($campaigns[$i]); // Remove campaigns from array() just for fun
        }
        // Display result
        $this->admin_notice(__( $this->count() . ' campaigns have been imported.<br/>See the <a href="/wp-admin/edit.php?post_type='.$cpt_name.'">list</a>', MCC_TEXT_DOMAIN) );
    }

    /**
     *
     */
    public function fetch($args = array())
    {
        // Get the total number of items to retrieve 
        $count = $this->getTotal();
        $args = $this->args(array('count'=>$count));
        $results = $this->call('campaigns', $args);
        $this->campaigns = json_decode( $results->last_call['body'] );
        
        // Update the time 
        $this->last_updated = current_time( 'mysql' );
    }

    /**
     * Miscellaneous
     */
    public function admin_notice($message, $status = 'updated') { 
    ?>
    <div class="<?php print $status; ?>">
        <p>
            <?php echo __( $message, MCC_TEXT_DOMAIN ); ?>
        </p>
    </div>
    <?php }

}
endif;