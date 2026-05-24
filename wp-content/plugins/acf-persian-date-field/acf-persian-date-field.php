<?php

/*
Plugin Name: Advanced Custom Fields: Persian Date Field
Plugin URI: http://toranj-co.net
Description: add persian jQuery date picker
Version: 3.2.0
Author: cracki
Author URI: cracki@gmail.com
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/


load_plugin_textdomain( 'acf-persian-date-field', false, dirname( plugin_basename(__FILE__) ) . '/lang/' ); 

function include_field_types_persian_date_field( $version ) {
	include_once('acf-persian-date-field-v5.php');
}
add_action('acf/include_field_types', 'include_field_types_persian_date_field');	


function my_load_custom_wp_admin_style() {
		wp_enqueue_style( 'bootstrap-datepicker', plugins_url( 'css/bootstrap-datepicker.min.css', __FILE__ ) , array(),'1.0.0');
        wp_enqueue_script( 'datepicker-fa', plugins_url( 'js/date-picker-fa.js', __FILE__ ) , '1.0.0' ,true);
		wp_enqueue_script( 'jalali', plugins_url( 'js/bootstrap-datepicker.min.js', __FILE__ ) , '1.0.0' ,true);
		wp_enqueue_script( 'calendar', plugins_url( 'js/bootstrap-datepicker.fa.min.js', __FILE__ ) , '1.0.0' ,true);
}
add_action( 'admin_enqueue_scripts', 'my_load_custom_wp_admin_style',999999 );
?>