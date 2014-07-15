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




function service_output($standard_fields, $service) {
	ob_start();
?>

	<form action="" method="post" role="form">

	<fieldset class="open311-service">
		<legend><?php echo $service->meta->service_name; ?></legend>
		
		<?php if(!empty($service->definitions->attributes)): ?> 

			<?php foreach ($service->definitions->attributes as $attribute) { ?>
				<?php echo generate_html_field($attribute) ?>
			<?php } ?>

		<?php endif; ?>		
		
	</fieldset>

	<fieldset class="open311-core">
		<legend>Information about your Request</legend>
		<?php foreach ($standard_fields->definitions->attributes as $standard_field) { ?>
			<?php echo generate_html_field($standard_field) ?>
		<?php } ?>
	</fieldset>


	<input type="hidden" name="wp_open311_service_code" value="<?php echo $service->meta->service_code; ?>">
	<button type="submit" class="btn btn-primary">Submit</button>


	</form>


<?php
	return ob_get_clean();
}



/************** Functions **************/

	function generate_html_field($attribute) {

		$required 	= (strtolower($attribute->required) == 'true') ? 'Required' : 'Optional';
		$required_label = '<span class="open311-requirement-label">(' . $required . ')</span>' . "\n";
		$label 		= '<label for="' . $attribute->code . '">' . $attribute->description . ' ' . $required_label . '</label>' . "\n";

		if($attribute->datatype == 'string') {
			$field_type = '<input class="form-control" name="' . $attribute->code . '" id="' . $attribute->code . '" type="text" placeholder="' . $attribute->datatype_description . '">' . "\n";	
		} else if ($attribute->datatype == 'text') {
			$field_type = '<textarea class="form-control" name="' . $attribute->code . '" id="' . $attribute->code . '"></textarea>' . "\n";	
		} else if ($attribute->datatype == 'singlevaluelist') {
			$field_type = '<select class="form-control chosen-select" name="' . $attribute->code . '" id="' . $attribute->code . '" data-placeholder="Select ' . $attribute->description . '">' . "\n";
		
			$options = $attribute->values;

			$field_type .= '<option value="" disabled selected></option>' . "\n";

			foreach ($options as $option) {
				$field_type .= '<option value="' . $option->key . '">' . $option->name . '</option>' . "\n";
			}

			$field_type .= '</select>' . "\n";

		}
		

		$field .= '<div class="form-group ' . strtolower($required) . '">' . "\n";
		$field .= $label;
		$field .= $field_type;
		$field .= '</div>' . "\n";
		

		return $field;
	}


?>