<?php

class open311_api {

	var $options;	
	var $filter;
	
	public function __construct($options)
	{
		$this->options =  $options;
	}
	
	public function get_requests($filter = null, $page_size = null, $page = null) {

		$url = $this->options['api_uri'] . 'requests.json'; // '?page_size=' . $page_size;

		$json = wp_remote_get($url);
		
		$reports = json_decode($json['body']);
				
		return $reports;
	}

	public function get_service($id = null) {

		// @TODO: ideally provide some caching here

		$url = $this->options['api_uri'] . 'services.json'; // '?page_size=' . $page_size;
		$json = wp_remote_get($url);	
		$services = json_decode($json['body']);

		$url = $this->options['api_uri'] . 'services/' . $id . '.json'; // '?page_size=' . $page_size;
		$json = wp_remote_get($url);	
		$service_definition = json_decode($json['body']);

		foreach ($services as $service) {
			if ($service->service_code == $id) {
				$service_meta = $service; 
			}
		}

		$complete_service = array('meta' => $service_meta, 'definitions' => $service_definition);

				
		return $complete_service;
	}


	public function post_request($service_code, $attributes) {

		$url = $this->options['api_uri'] . 'requests.json'; 

		

		$parameters = array();
		$parameters['api_key'] 					= $this->options['api_key'];		
		$parameters['service_code']				= $service_code;

		foreach($attributes as $key => $value) {
			$name = "attribute[$key]";
			$parameters[$name] = $value;
		}

		$headers = array( 'Content-Type' => 'application/x-www-form-urlencoded' );
		$post = array( 'method' => 'POST', 'body' => $parameters, 'headers' => $headers );

		$json = wp_remote_get( $url , $post );	

		$response = json_decode($json['body']);
				
		return $response;
	}


}