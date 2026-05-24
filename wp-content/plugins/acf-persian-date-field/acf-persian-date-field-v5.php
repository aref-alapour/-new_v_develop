<?php

if( ! class_exists('acf_field_persian_date_field') ) :

class acf_field_persian_date_field extends acf_field {
	
	
	function __construct() {
		
		// vars
		$this->name = 'date_picker_fa';
		$this->label = __("Persian Date Picker",'acf-persian-date-field');
		$this->category = 'jquery';
		$this->defaults = array(
			'display_format'	=> 'd/m/Y',
			'return_format'		=> 'd/m/Y',
			'first_day'			=> 6
		);

    	parent::__construct();
	}

	

	
	function render_field( $field ) {
		
		// vars
		$e = '';
		$div = array(
			
			'class'					=> 'acf-date_picker_fa acf-input-wrap',
			'data-display_format'	=> acf_convert_date_to_js($field['display_format']),
			'data-first_day'		=> $field['first_day'],
		);
		$input = array(
			'class' 				=> 'input-alt acf-date_picker_fa_db',
			'type'					=> 'hidden',
			'name'					=> $field['name'],
			'value'					=> $field['value'],
		);
			

		// html
		$e .= '<div ' . acf_esc_attr($div) . '>';
			$e .= '<input ' . acf_esc_attr($input). '/>';
			$e .= '<input type="text" value="" class="input acf-persian-date-picker" />';
		$e .= '</div>';
		
		
		// return
		echo $e;
	}
	


	function render_field_settings( $field ) {
		
		// global
		global $wp_locale;
		
		
		// display_format
		acf_render_field_setting( $field, array(
			'label'			=> __('Display format','acf'),
			'instructions'	=> __('The format displayed when editing a post','acf'),
			'type'			=> 'radio',
			'name'			=> 'display_format',
			'other_choice'	=> 1,
			'choices'		=> array(
				'd/m/Y'			=> jdate('d/m/Y'),
				'm/d/Y'			=> jdate('m/d/Y'),
				'F j, Y'		=> jdate('F j, Y'),
			)
		));
				
		
		// return_format
		acf_render_field_setting( $field, array(
			'label'			=> __('Return format','acf'),
			'instructions'	=> __('The format returned via template functions','acf'),
			'type'			=> 'radio',
			'name'			=> 'return_format',
			'other_choice'	=> 1,
			'choices'		=> array(
				'd/m/Y'			=> jdate('d/m/Y'),
				'm/d/Y'			=> jdate('m/d/Y'),
				'F j, Y'		=> jdate('F j, Y'),
				'Ymd'			=> jdate('Ymd'),
			)
		));
		
		
		// first_day
		/* acf_render_field_setting( $field, array(
			'label'			=> __('Week Starts On','acf'),
			'instructions'	=> '',
			'type'			=> 'select',
			'name'			=> 'first_day',
			'choices'		=> array_values( $wp_locale->weekday )
		)); */
		
	}

	
}

new acf_field_persian_date_field();

endif;

?>