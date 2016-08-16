<?php

class MailchimpCampaigns extends Mailchimp
{
    // Properties

    // Constructor
    public function __construct($args = array())
    {
        parent::__construct();
        $this->fetch();
    } 

    /**
     *
     */
    public function get($renew = false)
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
     *
     */
    public function import($renew = true)
    {
        if( $renew )
            $this->fetch();

        $campaigns = $this->get();
        foreach( $campaigns as $i => $campaign){
            $mcc = new MailchimpCampaign($campaign);
            $mcc->set()->save();
            unset($campaigns[$i]); // Remove campaigns from array() just for fun
        }
        // Display result
        $this->admin_notice(__( $this->count() . ' campaigns have been imported.', MCC_TXT_DOMAIN) );
    }

    /**
     *
     */
    public function fetch($args = array())
    {
        // Fetch campaigns
        $default_args = array(
            'count' => 5
            // 'status' => 'sent',
            // 'fields' => array('id', 'type'),
        );
        $args = array_merge_recursive($default_args, $args);
        $this->campaigns = json_decode( $this->call('campaigns', $args)->last_call['body'] );
        
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
            <?php echo __( $message, MCC_TXT_DOMAIN ); ?>
        </p>
    </div>
    <?php }

}
