<?php
/**
 * CMB2 select_plus field type
 *
 * @since  1.0.0
 */
class Select_Plus_CMB2_Type extends CMB2_Type_Multi_Base {

	public function render() {

		$name = $this->_name();
		$attributes = $this->field->args( 'attributes' );

		if ( isset( $attributes['multiple'] ) && 'multiple' == $attributes['multiple'] ) {
			$name .= '[]';
		}

		$a = $this->parse_args( 'select_plus', array(
			'class'   => 'cmb2_select',
			'name'    => $name,
			'id'      => $this->_id(),
			'desc'    => $this->_desc( true ),
			'options' => $this->concat_items(),
		) );

		$attrs = $this->concat_attrs( $a, array( 'desc', 'options' ) );

		return $this->rendered(
			sprintf( '<select%s>%s</select>%s', $attrs, $a['options'], $a['desc'] )
		);
	}

	/**
	 * Generates html for concatenated items
	 */
	public function concat_items( $args = array() ) {
		$field = $this->field;

		$method = isset( $args['method'] ) ? $args['method'] : 'select_option';
		unset( $args['method'] );

		$value = null !== $field->escaped_value()
			? $field->escaped_value()
			: $field->get_default();

		// convert to array for multiselect
		$value = (array) CMB2_Utils::normalize_if_numeric( $value );

		$concatenated_items = '';

		$options = (array) $field->options();

		// check if it has optgroup (2 level array)
		$optgroup = is_array( reset( $options ) ) || false;

		if ( $option_none = $field->args( 'show_option_none' ) ) {

			$a['value'] = '';
			$a['label'] = $option_none;
			if ( in_array( '', $value ) ) {
				$a['checked'] = 'checked';
			}

			$concatenated_items .= $this->$method( $a );
		}

		foreach ( $options as $key => $val ) {

			if ( $optgroup ) {

				$concatenated_items .= sprintf( '<optgroup label="%s">', $key );
				
				if ( is_array( $val ) ) {

					foreach ( $val as $opt_value => $opt_label ) {

						// Clone args & modify for just this item
						$a = $args;

						$a['value'] = $opt_value;
						$a['label'] = $opt_label;

						// Check if this option is the value of the input
						if ( in_array( CMB2_Utils::normalize_if_numeric( $opt_value ), $value ) ) {
							$a['checked'] = 'checked';
						}

						$concatenated_items .= $this->$method( $a );
					}
				}
				
				$concatenated_items .= '</optgroup>';
			} else {
				// Clone args & modify for just this item
				$a = $args;

				$a['value'] = $key;
				$a['label'] = $val;

				// Check if this option is the value of the input
				if ( in_array( CMB2_Utils::normalize_if_numeric( $key ), $value ) ) {
					$a['checked'] = 'checked';
				}

				$concatenated_items .= $this->$method( $a );
			}
		}

		return $concatenated_items;
	}
}