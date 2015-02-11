<?php
/**
 * Represents the view for the public-facing component of the plugin.
 *
 * This typically includes any information, if any, that is rendered to the
 * frontend of the theme when the plugin is activated.
 *
 * @package   Plugin_Name
 * @author    Your Name <email@example.com>
 * @license   GPL-2.0+
 * @link      http://example.com
 * @copyright 2014 Your Name or Company Name
 */


function request_list($requests) {
	ob_start();

	echo '<div class="open311-request-list">';

	foreach ($requests as $request) { 	

		$request = (object) $request;

		// Parse out title from description
		// check to see if there's a line break in the first 50 characters
		// otherwise break out first 50 characters 
		if(!empty($request->description)) {
			if(strpos(substr($request->description, 0, 51), PHP_EOL) !== FALSE) {
				$request->title = substr($request->description, 0, strpos($request->description, PHP_EOL));
				$request->description = substr($request->description, strpos($request->description, PHP_EOL));
			} else {
				$request->title = substr($request->description, 0, 50);

				if (strlen($request->title) == strlen($request->description)) {
					$request->description = '';
				}

				$request->title = $request->title . '...';
			}		
		}

		echo '<div class="open311-request" id="request-' .  $request->service_request_id . '">';
		echo '<h3 class="open311-title"><a href="./request-id/' . $request->service_request_id . '">' . $request->title . '</a></h3>';
		echo '<div class="open311-status state-' . $request->status . '">' . $request->status . '</div>';
		
		echo '<div class="open311-requested-datetime"><a href="./request-id/' . $request->service_request_id . '">Submitted on ' .  date('l F j, Y \a\t g:i a', strtotime($request->requested_datetime)) . '</a></div>';

		echo '</div>';
	} 

	echo '</div>';

	return ob_get_clean();
}

function request_single($requests) {
	ob_start();

	foreach ($requests as $request) { 	

		$request = (object) $request;

		// Parse out title from description
		// check to see if there's a line break in the first 50 characters
		// otherwise break out first 50 characters 
		if(!empty($request->description)) {
			if(strpos(substr($request->description, 0, 51), PHP_EOL) !== FALSE) {
				$request->title = substr($request->description, 0, strpos($request->description, PHP_EOL));
				$request->description = substr($request->description, strpos($request->description, PHP_EOL));
			} else {
				$request->title = substr($request->description, 0, 50);

				if (strlen($request->title) == strlen($request->description)) {
					$request->description = '';
				}

				$request->title = $request->title . '...';
			}		
		}


		if ($request->status == 'new') {
			
		}

		switch ($request->status) {
		    case 'new':
		        $status_icon = 'fa-arrow-circle-o-right';
		        break;
		    case 'open':
		        $status_icon = 'fa-exclamation-circle';
		        break;
		    case 'closed':
		        $status_icon = 'fa-check-circle-o';
		        break;
		}


		echo '<div class="open311-request" id="request-' .  $request->service_request_id . '">';

		echo '<h1 class="open311-title">' . $request->title . '</h1>';

		echo '<h4 class="open311-id"> Request #' . $request->service_request_id . '</h4>';
		echo '<div class="open311-status state-' . $request->status . '"><i class="fa ' . $status_icon . '"></i> ' . $request->status . '</div>';
		
		echo '<div class="open311-requested-datetime">Submitted on ' .  date('l F j, Y \a\t g:i a', strtotime($request->requested_datetime)) . '</div>';

		echo '<div class="open311-description">' . $request->description . ' </div>';

		if (!empty($request->service_notice)) {
			echo '<hr>';
			echo '<div class="open311-status-notes">';	
			echo '<h4 class="status-note-heading">Response</h4>';	
			echo '<div class="status-note">' . $request->service_notice . '</div>';	
			echo '</div>';	
		}
		
		echo '</div>';
	} 

	return ob_get_clean();
}


?>