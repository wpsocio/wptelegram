<?php
/**
 * Select_Plus_CMB2_Types field type objects
 *
 * This can be used to override the default arguments
 * if you want to render the fields directly
 *
 * @since  1.0.0
 *
 * @method string _id()
 * @method string _name()
 * @method string _desc()
 * @method string _text()
 * @method string concat_attrs()
 */
class Select_Plus_CMB2_Types extends CMB2_Types {

	public function __construct( CMB2_Field $field ) {
		parent::__construct( $field );
	}

	public function select_plus( $args = array() ) {
		return $this->get_new_render_type( __FUNCTION__, 'Select_Plus_CMB2_Type', $args )->render();
	}
}