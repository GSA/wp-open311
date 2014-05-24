<?php

class open311_model {

	var $request;	
	
	public function __construct() {
		$this->request =  $this->request_model();
	}
	
	public function request_model() {

		$request = new stdClass();

		$request->lat 				= null;
		$request->long 				= null;
		$request->address_string 	= null;
		$request->address_id 		= null;
		$request->email 			= null;
		$request->device_id 		= null;
		$request->account_id 		= null;
		$request->first_name 		= null;
		$request->last_name 		= null;
		$request->phone 			= null;
		$request->description 		= null;
		$request->media_url 		= null;

		return $request;

	}

	public function standard_fields() {

		$fields = array(
						'first_name', 
						'last_name', 
						'email', 
						'description'
						);

		$required = array(
						'description' => true
						);


		
		$order 			 = 1;
		$standard_fields = new stdClass();

		foreach ($fields as $field) {

			$standard_field = new stdClass();

			$required	 = (!empty($required[$field])) ? "true" : "false";
			$description = ucwords(str_replace('_', ' ', $field));

			$standard_field->variable 		= "true";			
			$standard_field->code 			= $field;
			$standard_field->datatype 		= "string";
			$standard_field->required 		= $required;
			$standard_field->order 			= $order;			
			$standard_field->description 	= $description;
			$standard_field->values 		= null;			

			$order++;

			$standard_fields->$field = $standard_field;
		}

		$standard_service = new stdClass();
		$standard_service->attributes = $standard_fields;

		$standard_fields_as_service = new stdClass();
		$standard_fields_as_service->definitions =  $standard_service;

		return $standard_fields_as_service;

	}

}

?>