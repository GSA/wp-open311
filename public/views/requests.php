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


function requests_output($requests) {
	ob_start();

	foreach ($requests as $request) { 	
		echo '<div class="open311-request" id="request-' .  $request->service_request_id . '">';
		echo '<div class="open311-id"> Request #' . $request->service_request_id . '</div>';
		echo '<div class="open311-status">';
		echo '<span>' . $request->status_notes . '</span>';
		echo '</div>';		
		echo '<div class="open311-description">' . $request->description . '</div>';
		echo '</div>';
	} 

	return ob_get_clean();
}

?>