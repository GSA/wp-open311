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
?>


<!-- This file is used to markup the public facing aspect of the plugin. -->

<form action="" method="post" role="form">

<h2>
	<?php echo $service['meta']->service_name; ?>
</h2>

<?php foreach ($service['definitions']->attributes as $attribute) { ?>
	<div class="form-group">

		<?php echo generate_html_field($attribute) ?>

	</div>
<?php } ?>

<input type="hidden" name="wp_open311_service_code" value="<?php echo $service['meta']->service_code; ?>">
<button type="submit" class="btn btn-default">Submit</button>


</form>


<?php

/************** Functions **************/

	function generate_html_field($attribute) {

		$label 		= '<label for="' . $attribute->code . '">' . $attribute->description . '</label>';
		$field_type = '<input class="form-control" name="' . $attribute->code . '" id="' . $attribute->code . '" type="text" placeholder="' . $attribute->datatype_description . '">';

		
		$field .= $label;
		$field .= $field_type;
		

		return $field;
	}


?>