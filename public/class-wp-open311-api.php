<?php

class open311_api {

	var $options;	
	var $filter;
	
	public function __construct($options)
	{
		$this->options =  $options;
	}
	
	public function get_requests($filter = null, $page_size = null, $page = null) {

		if (empty($filter['request_id'])) {
			$url = $this->options['api_uri'] . 'requests.json'; // '?page_size=' . $page_size;

			if (!empty($filter['service_code'])) {
				$url = $url . '?service_code=' . $filter['service_code'];
			}

			if (!empty($filter['agency_responsible'])) {
				$separator = (strpos($url, '?') !== false) ? '&' : '?';
				$url = $url . $separator . 'agency_responsible=' . $filter['agency_responsible'];
			}			

		} else {
			$url = $this->options['api_uri'] . 'requests/' . $filter['request_id'] . '.json'; 
		}

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

		$complete_service = new stdClass();
		
		$complete_service->meta = $service_meta;
		$complete_service->definitions = $service_definition;

				
		return $complete_service;
	}


	public function post_request($service_code, $standard_fields, $attributes) {

		$url = $this->options['api_uri'] . 'requests.json'; 

	
		$parameters = $standard_fields;
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