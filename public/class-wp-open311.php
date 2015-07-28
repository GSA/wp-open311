<?php
/**
 * Plugin Name.
 *
 * @package   WP-Open311
 * @author    Philip Ashlock
 * @license   GPL-2.0+
 * @link      http://civicagency.org
 * @copyright 2014 Philip Ashlock
 */


if (!defined('WP_PLUGIN_DIR'))
	define('WP_PLUGIN_DIR','/');

if (!defined('WPOPEN311_PLUGIN_NAME'))
    define('WPOPEN311_PLUGIN_NAME', 'wp-open311');

if (!defined('WPOPEN311_PLUGIN_URL'))
    define('WPOPEN311_PLUGIN_URL', WP_PLUGIN_URL . '/' . WPOPEN311_PLUGIN_NAME . '/');

/**
 * Plugin class. This class should ideally be used to work with the
 * public-facing side of the WordPress site.
 *
 * If you're interested in introducing administrative or dashboard
 * functionality, then refer to `class-plugin-name-admin.php`
 *
 * @TODO: Rename this class to a proper name for your plugin.
 *
 * @package WP-Open311
 * @author  Philip Ashlock
 */
class wp_open311 {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	const VERSION = '1.0.0';

	/**
	 * @TODO - Rename "plugin-name" to the name your your plugin
	 *
	 * Unique identifier for your plugin.
	 *
	 *
	 * The variable name is used as the text domain when internationalizing strings
	 * of text. Its value should match the Text Domain file header in the main
	 * plugin file.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = WPOPEN311_PLUGIN_NAME;

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	var $options;
	var $response;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		$this->options 	= get_option( 'open311_options' );
		$this->response = null;

		// Look for any submitted forms to process
		add_action('init', array($this, 'receive_form'));

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Activate plugin when new blog is added
		add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );

		// Load public-facing style sheet and JavaScript.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		/* Define custom functionality.
		 * Refer To http://codex.wordpress.org/Plugin_API#Hooks.2C_Actions_and_Filters
		 */
		// add_action( '@TODO', array( $this, 'action_method_name' ) );
		// add_filter( '@TODO', array( $this, 'filter_method_name' ) );

		add_shortcode('open311_requests', array($this, 'requests_shortcode'));
		add_shortcode('open311_service', array($this, 'service_shortcode'));


		add_filter('query_vars', array($this, 'open311_queryvars') );

		// hook add_rewrite_rules function into rewrite_rules_array
		add_filter('rewrite_rules_array', array($this, 'open311_rewrite_rules') );

	}


	public function requests_shortcode($atts) {

		// extract the attributes into variables
		//extract(shortcode_atts(array(
		//	'page_size' => 50,
		//	'page' => 1,
		//), $atts));

		return $this->requests_search($atts);
	}


	function open311_rewrite_rules($aRules) {

		$aNewRules = array('([^/]+)/request-id/([^/]+)/?$' => 'index.php?pagename=$matches[1]&request_id=$matches[2]');
		$aRules = $aNewRules + $aRules;

		return $aRules;
	}




	public function service_shortcode($atts) {
		
		global $wp_query;

		if (!empty($wp_query->query) && !empty($wp_query->query['request_id'])) {

			return $this->requests_search(array('request_id' => $wp_query->query['request_id']));
		}


		if (!empty($this->response) && $this->response['success'] === true) {
			return $this->display_response($this->response);
		}
		else if (!empty($this->response) && $this->response['success'] !== true) {
			return $this->display_response($this->response);			
		} else {
			return $this->assemble_service($atts['id']);
		}
		
	}


	public function requests_search($filter = '') {

		$filter = (empty($filter)) ? array() : $filter;

		$open311_api = $this->get_api();
		global $wp_query;

		$dynamic_filter = $wp_query->query;

		if(!empty($dynamic_filter['request_id']) OR !empty($dynamic_filter['agency_responsible'])) {			
			$filter = array_merge($filter, $dynamic_filter);
		} 

		$requests = $open311_api->get_requests($filter);
		return $this->display_requests_search($requests);

	}


	public function assemble_service($id) {

		$fields = $this->consolidate_fields($id);

		return $this->display_service($fields->standard_fields, $fields->service_fields);

	}

	public function consolidate_fields($id = null, $service_merge = true) {

		$open311_api 	= $this->get_api();
		$open311_model 	= $this->get_model();
		$fields 		= new stdClass();

		$fields->standard_fields = $open311_model->standard_fields();

		// check for standard field definitions via a site-wide override (service_code 0 convention)
		if ($standard_field_override = $open311_api->get_service('0')) {
			if(!empty($standard_field_override->definitions)) {
				$fields->standard_fields = $this->service_override_merge($fields->standard_fields, $standard_field_override, $unset_match = false)->standard_fields;
			}
		} 

		if(isset($id)) {
			$service = $open311_api->get_service($id);	
		}
		
		// check for standard field definitions via service-specific override
		if($service_merge && ($service->meta->metadata == 'true') && !empty($service->definitions->attributes)) {
			$fields = $this->service_override_merge($fields->standard_fields, $service, $unset_match = true);			
		} else {
			$fields->service_fields = $service;
		}

		// namespace standard fields
		$fields->standard_fields->definitions->attributes = $this->namespace_fields($fields->standard_fields->definitions->attributes);

		// namespace service fields
		if (!empty($fields->service_fields->definitions->attributes)) {
			$fields->service_fields->definitions->attributes = $this->namespace_fields($fields->service_fields->definitions->attributes);
		}

		return $fields;

	}

	public function namespace_fields($attributes) {

		$namespace_fields = array();
		foreach ($attributes as $namespace_field) {
			$namespace_field->code = 'wp_open311_' . $namespace_field->code;
			$namespace_fields[] = $namespace_field;
		}
		
		return $namespace_fields;
	}

	public function un_namespace_fields($fields) {

		$namespace = 'wp_open311_';
		$no_namespace = array();

		foreach ($fields as $key => $field) {

			if (substr($key, 0, strlen($namespace)) == $namespace) {
				$new_key = substr($key, strlen($namespace));
			}

			$no_namespace[$new_key] = $field;

			
		}

		return $no_namespace;

	}


	public function service_override_merge($standard_fields, $service_fields, $unset_match = false) {

		$override_merge = new stdClass();	
		$output 		= new stdClass();
		$matches 		= array();

		foreach($standard_fields->definitions->attributes as $match_key => $definition) {

			foreach($service_fields->definitions->attributes as $key => $override) {

				if ($override->code == $match_key) {
					
					$matches[$match_key] = true;


					if($unset_match) {
						//unset($service_fields->definitions->attributes[$key]);
					} else {
						$override_merge->$match_key = $override;
					}
				}
			}
			reset($service_fields->definitions->attributes);

			if (empty($matches[$match_key])) {
				$override_merge->$match_key = $definition;
			}	
			
		}

		$standard_fields->definitions->attributes = $override_merge;

		$output->service_fields  = $service_fields;
		$output->standard_fields = $standard_fields;

		return $output;

	}


	/**
	 * Render the output from the shortcode
	 *
	 * @since    1.0.0
	 */
	public function display_requests_search($requests) {
		include_once( 'views/requests.php' );

		$requests = $this->array_orderby($requests,'service_request_id', SORT_DESC);	
		
		if (count($requests) > 1) {
			return request_list($requests);
		} else if (count($requests) == 1) {
			return request_single($requests);
		} else {
			return "Search returned no results";
		}


		return requests_list($requests);
	}



	/**
	 * Render the output from the shortcode
	 *
	 * @since    1.0.0
	 */
	public function display_service($standard_fields, $service) {
		include_once( 'views/service.php' );

		return service_output($standard_fields, $service);
	}


	public function receive_form() {

		if(isset($_POST['wp_open311_service_code'])) {

			$open311_api 	= $this->get_api();
			$service_code 	= $_POST['wp_open311_service_code'];
			
			$fields = $this->consolidate_fields($service_code, $service_merge = false);

			$combined_fields = $fields->standard_fields->definitions->attributes;

			if($fields->service_fields) {
				$combined_fields = (object) array_merge((array) $combined_fields, (array) $fields->service_fields->definitions->attributes);
			}

			// filter for fields that are defined in service
			$filtered_fields = array();
			$required_fields = array();

			foreach ( $combined_fields as $field) {
				$key = $field->code;

				if (strtolower($field->required) == "true") {
					$required_fields[$key] = true;
				}

				if (isset($_POST[$key]) && !empty($_POST[$key])) {
					$filtered_fields[$key] = $_POST[$key];
				}
			}

			$response 	= array();
			$message 	= array();

			// check for missing required fields 
			$missing = array_diff_key($required_fields, $filtered_fields);
			$missing = array_keys($missing); 

			foreach ($missing as $field) {
				$message[] = 'Missing required field: ' . $field;
			}

			if (!empty($missing)) {
				// return error
				$response['success'] 			= false;
				$response['message'] 			= $message;
			
			} else {

				// Separate standard fields from service 
				$standard_fields_filtered = array();

				foreach ($fields->standard_fields->definitions->attributes as $standard_field) {
					
					if(isset($filtered_fields[$standard_field->code])) {
						$standard_fields_filtered[$standard_field->code] = $filtered_fields[$standard_field->code];
						unset($filtered_fields[$standard_field->code]);
					}
					
				}

				$filtered_fields 			= $this->un_namespace_fields($filtered_fields);
				$standard_fields_filtered	= $this->un_namespace_fields($standard_fields_filtered);

				// This is to provide a pseudo summary field as the first line of the description
				// Inspired by https://github.com/blog/926-shiny-new-commit-styles
				if(!empty($filtered_fields['name'])) {
					$standard_fields_filtered['description'] = $filtered_fields['name'] . "\n\n" . $standard_fields_filtered['description'];
				}

				$api_response = $open311_api->post_request($service_code, $standard_fields_filtered, $filtered_fields);

				// if api response was good:
				$response['success'] = true;
				$response['message'] = $api_response;

			}

			$this->response = $response;
		}
			
	}


	/**
	 * Render the output from the shortcode
	 *
	 * @since    1.0.0
	 */
	public function display_response($response) {
		include_once( 'views/response.php' );

		return response_output($response);
	}	


	public function get_api() {
		require_once( plugin_dir_path( __FILE__ ) . '../public/class-wp-open311-api.php' );
	
		$open311_api = new open311_api($this->options);	
		return $open311_api;
	}


	public function get_model() {
		require_once( plugin_dir_path( __FILE__ ) . '../public/models/open311-model.php' );
	
		$open311_model = new open311_model();	
		return $open311_model;
	}






	public function open311_queryvars($qvars) {
		
		$qvars[] = 'request_id';
		$qvars[] = 'media_url';
		$qvars[] = 'agency_responsible';	

		// Non-open311 variables
		$qvars[] = 'agency_name';			

		return $qvars;
	}

	


	/**
	 * Return the plugin slug.
	 *
	 * @since    1.0.0
	 *
	 * @return    Plugin slug variable.
	 */
	public function get_plugin_slug() {
		return $this->plugin_slug;
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Activate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       activated on an individual blog.
	 */
	public static function activate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide  ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_activate();
				}

				restore_current_blog();

			} else {
				self::single_activate();
			}

		} else {
			self::single_activate();
		}

	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Deactivate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       deactivated on an individual blog.
	 */
	public static function deactivate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_deactivate();

				}

				restore_current_blog();

			} else {
				self::single_deactivate();
			}

		} else {
			self::single_deactivate();
		}

	}

	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @since    1.0.0
	 *
	 * @param    int    $blog_id    ID of the new blog.
	 */
	public function activate_new_site( $blog_id ) {

		if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
			return;
		}

		switch_to_blog( $blog_id );
		self::single_activate();
		restore_current_blog();

	}

	/**
	 * Get all blog ids of blogs in the current network that are:
	 * - not archived
	 * - not spam
	 * - not deleted
	 *
	 * @since    1.0.0
	 *
	 * @return   array|false    The blog ids, false if no matches.
	 */
	private static function get_blog_ids() {

		global $wpdb;

		// get an array of blog ids
		$sql = "SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0'
			AND deleted = '0'";

		return $wpdb->get_col( $sql );

	}

	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 * @since    1.0.0
	 */
	private static function single_activate() {
		// @TODO: Define activation functionality here
	}

	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 */
	private static function single_deactivate() {
		// @TODO: Define deactivation functionality here
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );

	}

	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_slug . '-plugin-styles', WPOPEN311_PLUGIN_URL . 'public/assets/css/chosen.min.css', array(), self::VERSION );
		wp_enqueue_style( $this->plugin_slug . '-plugin-styles-2', WPOPEN311_PLUGIN_URL . 'public/assets/css/public.css', array(), self::VERSION );

	}

	/**
	 * Register and enqueues public-facing JavaScript files.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_slug . '-plugin-script', WPOPEN311_PLUGIN_URL . 'public/assets/js/chosen.jquery.min.js', array( 'jquery' ), self::VERSION );
		wp_enqueue_script( $this->plugin_slug . '-plugin-script-2', WPOPEN311_PLUGIN_URL . 'public/assets/js/public.js', array( 'jquery' ), self::VERSION );
	}

	/**
	 * NOTE:  Actions are points in the execution of a page or process
	 *        lifecycle that WordPress fires.
	 *
	 *        Actions:    http://codex.wordpress.org/Plugin_API#Actions
	 *        Reference:  http://codex.wordpress.org/Plugin_API/Action_Reference
	 *
	 * @since    1.0.0
	 */
	public function action_method_name() {
		// @TODO: Define your action hook callback here
	}

	/**
	 * NOTE:  Filters are points of execution in which WordPress modifies data
	 *        before saving it or sending it to the browser.
	 *
	 *        Filters: http://codex.wordpress.org/Plugin_API#Filters
	 *        Reference:  http://codex.wordpress.org/Plugin_API/Filter_Reference
	 *
	 * @since    1.0.0
	 */
	public function filter_method_name() {
		// @TODO: Define your filter hook callback here
	}

	public function array_orderby() {		
	    $args = func_get_args();
	    $data = array_shift($args);

	    foreach ($data as $key => $row) {
	    	$data[$key] = (array) $row;
	    }

	    foreach ($args as $n => $field) {
	        if (is_string($field)) {
	            $tmp = array();
	            foreach ($data as $key => $row)
	                $tmp[$key] = $row[$field];
	            $args[$n] = $tmp;
	            }
	    }
	    $args[] = &$data;
	    call_user_func_array('array_multisort', $args);
	    return array_pop($args);
	}



}
