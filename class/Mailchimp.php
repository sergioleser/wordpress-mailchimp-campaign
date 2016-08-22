<?php
/**
 * MailChimp Campaigns API
 *
 * Inspired by Drew McLellan <drew.mclellan@gmail.com>
 * and his class wrapper for MailChimp API v2.0:
 * https://github.com/drewm/mailchimp-api/
 *
 * @author Matthieu Scarset <m@matthieuscarset.com>
 * @see http://matthieuscarset.com/
 * @version 1.0.0
 */

class Mailchimp {

	// Private settings
	protected $settings;

	// Public settings
	public $is_error = false;
	public $last_error;
	public $last_call;
	public $last_updated;
	public $data;

	/**
	 * Create Mailchimp  instance
 	 * @see http://developer.mailchimp.com/documentation/mailchimp/guides/get-started-with-mailchimp-api-3/#authentication
	 */
	public function __construct()
	{

		$this->settings = (object)get_option('mailchimpcampaigns_settings');
		// Die here if no API Key...
		if( !$this->settings->api_key )
			return new WP_Error('missing api key', __('Mailchimp API Key is missing', MC_TEXT_DOMAIN) );

		list(, $datacentre) = explode('-', $this->settings->api_key);
		$this->settings->api_datacentre = $datacentre;
		$this->settings->api_version =  MCC_API_VERSION;
		$this->settings->api_endpoint = 'https://'. $this->settings->api_datacentre.'.api.mailchimp.com/'.$this->settings->api_version;	
		
		if( ! $this->test() ) 
    	 return;

		$this->last_updated	= current_time( 'mysql' );
	}

	/**
	 * Shortcut for $this->call()
	 * @return boolean False if call raised an error
	 */
	public function test($method = null, $args = false, $timeout = 10)
	{
		return ! $this->call($method, $args, $timeout)->is_error();
	}

	
	/**
		* Determine if a call return an error
		* @param object $ The call's , previously decoded by our custom property $this->decode().
		* @return boolean/array If true, will return an associative array of the error. Otherwise return false directly.
		*/
	public function is_error($data = false)
	{
		$response = ! $data ? $this->last_call : $data;
		
		if( is_wp_error( $response) )
				return true;

		if( isset($response->response) )
				$response = $response->response;

		$code = false;
		if( is_array($response) ) {
				$code = !$code && isset($response->code) ? $response->code : false;
				$message = isset($response->message) ? $response->message : false;
		}
		
		if( !$code || $code == 200 ) return false;

		$link_to_doc = 'http://developer.mailchimp.com/documentation/mailchimp/guides/error-glossary/';
		$error = new stdClass();
		$error->code = $code;
		$error->message = $message;
		$error->link = $link_to_doc . '#' . $code;
		$this->last_error = $error; 
		return $this;
	}

	/**
	 * Call the MailChimp API 
	 * Very easy with WordPress HTTP API :)
	 * @param  string $method The API method to be called
	 * @param  array  $args Associative array of parameters to be passed
	 * @return object The answer to our call, decoded by this->execute()
	 * @see http://developer.mailchimp.com/documentation/mailchimp/guides/get-started-with-mailchimp-api-3/#authentication
	 * @see https://developer.wordpress.org/reference/functions/wp_remote_request/
	 */
	public function call($method = null, $args = false, $timeout = 10)
	{
			$query = $args ? '?'.http_build_query($args) : '';
			$url = $this->settings->api_endpoint.'/'.$method . $query;
			$auth = array(
					'headers' => array(
							'Authorization' => 'Basic ' . base64_encode( $this->settings->auth_name . ':' . $this->settings->api_key )
					)
			);
			$this->last_call = wp_remote_request( $url, $auth );
			return $this;
	}

	/**
 	 * Shortcut to get Mailchimp instance
	 * @param string $prop One property of this class 
	 * @return object property's' value
	 */
	public function get($prop = 'last_call')
	{
		$data = $this->{$prop};
		
		if( isset($data->body) ) {
			$data = $data->body;
		}
		if( isset($data['body']) ) {
			$data = $data['body'];
		}

		// Decode data
		$data = json_decode($data) != null ? json_decode($data) : $data;
		$data = ( is_object($data) || is_array($data) ) ? (object)$data : $data;

		return $data;
	}


}