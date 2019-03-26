<?php
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;
if( !class_exists( 'CMB2_Switch_Button' ) ) {
	/**
	 * Class CMB2_Radio_Image
	 */
	class CMB2_Switch_Button {

		/**
		 * Whether already hooked up or not
		 *
		 * @since 1.0.0
		 */
		protected static $hooked_up = false;

		public function __construct() {

			$this->hook_up();
		}
		
		public function hook_up(){
			if ( ! self::$hooked_up ) {
				add_action( 'cmb2_render_switch', array( $this, 'callback' ), 10, 5 );
				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );

				self::$hooked_up = true;
			}
		}
		public function callback($field, $escaped_value, $object_id, $object_type, $field_type_object) {
		   $field_name = $field->_name();
		   
		   $args = array(
						'type'  => 'checkbox',
						'id'	=> $field_name,
						'name'  => $field_name,
						'desc'	=> '',
						'value' => 'on',
					);
		   if($escaped_value == 'on'){
			  $args['checked'] = 'checked';
		   }

		   echo '<label class="cmb2-switch">';
		   echo $field_type_object->input($args);
		   echo '<span class="cmb2-slider round"></span>';
		   echo '</label>';
		   $field_type_object->_desc( true, true );
		}

		public function enqueue_styles() {
			// Use minified libraries if SCRIPT_DEBUG is turned off
			$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
			wp_enqueue_style( 'cmb2-switch', plugins_url( '', __FILE__ ) . '/style' . $suffix . '.css', array(), false, 'all' );
		}
	}
}
