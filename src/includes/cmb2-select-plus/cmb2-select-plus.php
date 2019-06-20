<?php

if ( ! function_exists( 'cmb2_select_plus_autoload_classes' ) ) {
	function cmb2_select_plus_autoload_classes( $class_name ) {
		
		if ( 0 !== strpos( $class_name, 'Select_Plus_CMB2' ) ) {
			return;
		}

		$path = dirname( __FILE__ ) . '/includes';

		$file = "$path/{$class_name}.php";

		if ( file_exists( $file ) ) {

			include_once( $file );
		}
	}
}

spl_autoload_register( 'cmb2_select_plus_autoload_classes' );