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
	private $api_key;

	// Public settings
	public $settings;
	public $api_authname;
	public $api_datacentre;
	public $api_endpoint;
	public $api_version = '3.0';
	public $is_error = false;
	public $last_error;
	public $last_call;
	public $data;

	/**
	 * Create Mailchimp  instance
 	 * @see http://developer.mailchimp.com/documentation/mailchimp/guides/get-started-with-mailchimp-api-3/#authentication
	 */
	public function __construct()
	{
		$this->settings = get_option('mailchimpcampaigns_settings');
		$this->api_authname = isset($this->settings['api_authname']) ? $this->settings['api_authname'] : false;
		$this->api_key = isset($this->settings['api_key']) ? $this->settings['api_key'] : false;

		// Die here if no API Key...
		if( !$this->api_key)
			return new WP_Error('missing api key', __('Mailchimp API Key is missing', MC_TEXT_DOMAIN) );
		
		// ...or continue if we have an API Key
		list(, $datacentre) = explode('-', $this->api_key);
		$this->api_datacentre = $datacentre;
		$this->api_endpoint = 'https://'. $this->api_datacentre.'.api.mailchimp.com/'.$this->api_version;	

	}

	/**
	 * Shortcut for $this->call()
	 * @return boolean False if call raised an error
	 */
	public function init($test = ''){
		return ! $this->call($test)->is_error();
	}

	/**
	 * Shortcut to get Mailchimp instance
	 * @param string $prop One property of this class 
	 * @return object property's' value
		 */
	 public function get($prop)
	 {
		 $data = $this->{$prop}; 
		 return ( is_object($data) || is_array($data) ) ? (object)$data : $data;
	 }

	/*
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
			$url = $this->api_endpoint.'/'.$method . $query;
			$auth = array(
					'headers' => array(
							'Authorization' => 'Basic ' . base64_encode( $this->auth_name . ':' . $this->api_key )
					)
			);
			$this->last_call = wp_remote_request( $url, $auth );
			return $this;
	}

}

// Instanciate our class 
$MCC = new Mailchimp();