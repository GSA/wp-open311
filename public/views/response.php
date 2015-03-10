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


function response_output($response) {
	ob_start();


    if($response['success']) {

        echo "Thanks for your request. ";
        
        if(!empty($response['message']) && is_array($response['message'])) {

            $permalink = get_permalink();
            if (substr($permalink, -1) != '/') {
                $permalink = $permalink . '/';
            }

            foreach ($response['message'] as $message) {
                echo ' <span>The status of the response can be tracked under request ' .  '<a href="' . $permalink . 'request-id/' . $message->service_request_id  . '">#' . $message->service_request_id . '</a></span>';
            }

        }        

    }


    if(!$response['success']) {

        echo "There was an error with your request. ";
        
        if(!empty($response['message']) && is_array($response['message'])) {

            foreach ($response['message'] as $message) {
                echo ' <span>' . $message . '</span>';
            }

        }        

    }




	return ob_get_clean();
}

?>

