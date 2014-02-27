<?php
/**
 * Represents the view for the administration dashboard.
 *
 * This includes the header, options, and other information that should provide
 * The User Interface to the end user.
 *
 * @package   Plugin_Name
 * @author    Your Name <email@example.com>
 * @license   GPL-2.0+
 * @link      http://example.com
 * @copyright 2014 Your Name or Company Name
 */
?>

<div class="wrap">

	<?php
    	// Set class property
    	$this->options = get_option( 'open311_options' );
    ?>

	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

	<!-- @TODO: Provide markup for your options page here. -->

    <form method="post" action="options.php">
    <?php
        // This prints out all hidden setting fields
        settings_fields( 'open311_options_group' );   
        do_settings_sections( 'wp-open311' );
        submit_button(); 
    ?>
    </form>
        


	

</div>
