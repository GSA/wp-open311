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

	<form class="open311-form" action="" method="post" role="form">

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

		// Set default value
		$hidden_field = false;

		$namespace = 'wp_open311_';
		if (substr($attribute->code, 0, strlen($namespace)) == $namespace) {
			$raw_field = substr($attribute->code, strlen($namespace));
		} else {
			$raw_field = null;
		}

		if ($raw_field == 'name') $raw_field = null;

		if($attribute->datatype == 'string' || $attribute->datatype == 'text' ) {
			if ($passed_value = get_query_var( $raw_field, false )) {
                $passed_value = esc_html($passed_value);
				$placeholder = ' value="' . $passed_value . '" disabled '; 
				$hidden_field = true;
			} else {
				$placeholder = ' placeholder="' . $attribute->datatype_description . '" ';
			}			
		}


		$visibility = attribute_visibility($attribute->code);

		if ($visibility == 'private') {
			$visibility_icon = 'lock';
			$visibility_tooltip = 'This information is private and will only be used to respond to you';
		}

		if ($visibility == 'public') {
			$visibility_icon = 'globe';
			$visibility_tooltip = 'Information provided here will be visible to the public';
		}		

		$required 	= (strtolower($attribute->required) == 'true') ? 'Required' : 'Optional';
		$required_label = '<span class="open311-requirement-label">(' . $required . ')</span>' . "\n";
		$label 		= '<label title="' . $visibility_tooltip . '" for="' . $attribute->code . '"><i class="fa fa-' . $visibility_icon . '"></i> ' . $attribute->description . ' ' . $required_label . '</label>' . "\n";

		if($attribute->datatype == 'string') {			
			
			$field_type = '';
			$field_name = $attribute->code;
			
			if ($hidden_field) {
				$field_type .= '<input type="hidden" name="' . $field_name . '" value="' . $passed_value . '">';
				$field_name .= '_disabled';
			}

			$field_type .= '<input class="form-control" name="' . $field_name . '" id="' . $attribute->code . '" type="text" ' . $placeholder . '>' . "\n";	

		} else if ($attribute->datatype == 'text') {
			$field_type = '<textarea class="form-control" name="' . $attribute->code . '" id="' . $attribute->code . '"></textarea>' . "\n";	
		} else if ($attribute->datatype == 'singlevaluelist') {

			$field_name = $attribute->code;
			$field_type = '';

			if ($passed_value = get_query_var( $raw_field, false )) {
                $passed_value = esc_html($passed_value);
				$disabled = ' disabled ';
				$field_type .= '<input type="hidden" name="' . $field_name . '" value="' . $passed_value . '">';
				$field_name .= '_disabled';				
			} else {
				$passed_value = '';
				$disabled = '';
			}


			$field_type .= '<select class="form-control chosen-select" name="' . $field_name . '" id="' . $attribute->code . '" data-placeholder="Select ' . $attribute->description . '"' . $disabled . '>' . "\n";
		
			$options = $attribute->values;

			$field_type .= '<option value="" disabled ';
			$field_type .= (empty($disabled)) ? 'selected' : '';
			$field_type .= ' ></option>' . "\n";

			foreach ($options as $option) {
				$selected = ($passed_value == $option->key) ? ' selected ' : '';
				$field_type .= '<option value="' . $option->key . '"' . $selected . '>' . $option->name . '</option>' . "\n";
			}

			$field_type .= '</select>' . "\n";

		}
		

		$field = '<div class="form-group ' . strtolower($required) . '">' . "\n";
		$field .= $label;
		$field .= $field_type;
		$field .= '</div>' . "\n";
		

		return $field;
	}

	function attribute_visibility($attribute) {
		
		$private = array(
			'email',
			'device_id',
			'account_id',
			'first_name',
			'last_name',
			'phone');

		$attribute = substr($attribute, strlen('wp_open311_'));

		if (array_search($attribute, $private) !== false) {
			return 'private';
		} else {
			return 'public';
		}


	}


?>