<?php

class open311_api {

	var $options;	
	var $filter;
	
	public function __construct($options)
	{
		$this->options =  $options;
	}
	
	public function get_requests($filter = null, $page_size = null, $page = null)
	{
		$url = $this->options['api_uri'] . 'requests.json'; // '?page_size=' . $page_size;

		$json = wp_remote_get($url);
		
		$reports = json_decode($json['body']);
				
		return $reports;
	}
}