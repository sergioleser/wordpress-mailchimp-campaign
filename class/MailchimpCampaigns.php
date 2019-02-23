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
            //'count' => 5,
            // 'status' => 'save',
            // 'fields' => array('id', 'type'),
            'since_create_time' => isset( $this->settings['import_since'] ) ? (empty ($this->settings['import_since']) ? NULL : $this->settings['import_since'] ) : NULL,
            'before_create_time' => isset( $this->settings['import_before'] ) ? (empty ($this->settings['import_before']) ? current_time('Y-m-d') : $this->settings['import_before']) : current_time('Y-m-d'),
            //'since_send_time' => isset( $this->settings['import_since'] ) ? (empty ($this->settings['import_since']) ? NULL : $this->settings['import_since'] ) : NULL,
            //'before_send_time' => isset( $this->settings['import_before'] ) ? (empty ($this->settings['import_before']) ? current_time('Y-m-d') : $this->settings['import_before']) : current_time('Y-m-d'),
        );

        $args = array_merge_recursive($default_args, $args);
        $default_args = NULL;

        return $args;
    }

    /**
     *
     */
    public function import($renew = true)
    {

        $totalCount = $this->getTotal();

        // Establish the number of items to be retrieved at each round

        $countSize = 50;

        // Establish the initial campaign to be imported in each round

        $offsetSize = 0;

        // Controls the number of items retrieved

        $importCount = 0;

        while ($importCount < $totalCount){

           if (($totalCount - $importCount) < $countSize) $countSize = $totalCount - $importCount;
           
           if( $renew )
               $this->fetch($countSize, $offsetSize);

           $cpt_name = empty($this->settings['cpt_name']) ? MCC_DEFAULT_CPT : $this->settings['cpt_name'];

           $campaigns = $this->campaigns();
           foreach( $campaigns as $i => $campaign){
               $mcc = new MailchimpCampaign($campaign);
               $mcc->init()->fetch()->save(); // Get content for this campaigns
               unset($campaigns[$i]); // Remove campaigns from array() just for fun
           }

            $importCount += $this->count();

            $offsetSize += $this->count();

        }

        // Display result
        $this->admin_notice(__( $importCount . ' campaigns have been imported.<br/>See the <a href="/wp-admin/edit.php?post_type='.$cpt_name.'">list</a>', MCC_TEXT_DOMAIN) );


   }

    /**
     *
     */
    public function fetch($countSize = 50, $offsetSize = 0,$args = array())
    {
        // Get the total number of items to retrieve 
        // $count = $this->getTotal();
        $args = $this->args(array('count'=>$countSize,'offset'=>$offsetSize));
        $results = $this->call('campaigns', $args);
        $this->campaigns = json_decode( $results->last_call['body'] );
        
        // Update the time 
        // $this->last_updated = current_time( 'mysql' );
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
