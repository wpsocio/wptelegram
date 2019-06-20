<?php

/**
 * Class Select_Plus_CMB2_Field
 */
class Select_Plus_CMB2_Field {

	/**
	 * Initialize the plugin by hooking into CMB2
	 */
	public function __construct() {
		// set the Class name to handle the rendering
		add_filter( 'cmb2_render_class_select_plus', array( $this, 'render_class_select_plus' ) );

		// render the actual field
		add_filter( 'cmb2_render_select_plus', array( $this, 'render_select_plus' ), 10, 5 );

		// sanitize the value(s)
		add_filter( 'cmb2_sanitize_select_plus', array( $this, 'sanitize_select_plus' ), 10, 5 );
	}

	public function render_class_select_plus() {
		
		return 'Select_Plus_CMB2_Type';
	}

	public function render_select_plus( $field, $escaped_value, $object_id, $object_type, $field_type_object ) {

		$types = new Select_Plus_CMB2_Types( $field );
		$types->render();
	}

	public function sanitize_select_plus( $check, $meta_value, $object_id, $field_args ) {

		if ( ! is_array( $meta_value ) || ! $field_args['repeatable'] ) {
			return sanitize_text_field( $meta_value );
		}

		foreach ( $meta_value as $key => $val ) {
			$meta_value[ $key ] = array_map( 'sanitize_text_field', $val );
		}

		return $meta_value;
	}
}
