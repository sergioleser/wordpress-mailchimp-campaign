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
    public function save()
    {
        foreach($this->campaigns->list as $campaign )
        {
            
        }
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


}
