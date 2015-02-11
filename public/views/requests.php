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

	foreach ($requests as $request) { 	

		$request = (object) $request;

		echo '<div class="open311-request" id="request-' .  $request->service_request_id . '">';
		echo '<div class="open311-id"> Request #' . $request->service_request_id . '</div>';
		echo '<div class="open311-status state-' . $request->status . '">' . $request->status . '</div>';
		
		echo '<div class="open311-requested-datetime">' .  $request->requested_datetime . '</div>';

		echo '<div class="open311-status">';
		echo '<span>' . $request->status_notes . '</span>';
		echo '</div>';		
		echo '<div class="open311-description">' . $request->description . '</div>';
		echo '</div>';
	} 

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


		echo '<div class="open311-request" id="request-' .  $request->service_request_id . '">';
		echo '<div class="open311-id"> Request #' . $request->service_request_id . '</div>';
		echo '<div class="open311-status state-' . $request->status . '">' . $request->status . '</div>';
		
		echo '<div class="open311-requested-datetime">' .  $request->requested_datetime . '</div>';

		echo '<div class="open311-status">';
		echo '<span>' . $request->status_notes . '</span>';
		echo '</div>';		
		echo '<div class="open311-title">Title: ' . $request->title . ' </div>';
		echo '<div class="open311-description">Description: ' . $request->description . ' </div>';
		echo '</div>';
	} 

	return ob_get_clean();
}


?>