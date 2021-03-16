<?php
/**
 * The file that defines the module
 *
 * A class definition that includes attributes and functions used across the module
 *
 * @link       https://t.me/manzoorwanijk
 * @since      x.y.z
 *
 * @package    WPTelegram
 * @subpackage WPTelegram/modules
 */

namespace WPTelegram\Core\modules;

use WPTelegram\Core\includes\Options;

/**
 * The module core class.
 *
 * @since      1.0.0
 * @package    WPTelegram
 * @subpackage WPTelegram/modules
 * @author     Manzoor Wani <@manzoorwanijk>
 */
abstract class BaseModule {

	/**
	 * The single instance of the class.
	 *
	 * @since x.y.z
	 * @var   Main $instance The instance.
	 */
	protected static $instance = null;

	/**
	 * The module options
	 *
	 * @since    x.y.z
	 * @access   protected
	 * @var      Options    $options    The module options.
	 */
	protected $options;

	/**
	 * The module name
	 *
	 * @since    x.y.z
	 * @access   protected
	 * @var      Options    $options    The module name.
	 */
	protected $module_name;

	/**
	 * Main class Instance.
	 *
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @since x.y.z
	 *
	 * @param string $module_name The module name.
	 *
	 * @return Main instance.
	 */
	public static function instance( $module_name ) {
		if ( is_null( static::$instance ) ) {
			static::$instance = new static( $module_name );
		}
		return static::$instance;
	}

	/**
	 * Define the core functionality of the module.
	 *
	 * @param string $module_name The module name.
	 *
	 * @since    1.0.0
	 */
	protected function __construct( $module_name ) {

		$this->module_name = $module_name;

		$this->define_necessary_hooks();

		if ( $this->options()->get( 'active' ) ) {
			$this->define_on_active_hooks();
		}
	}

	/**
	 * Set the plugin options
	 *
	 * @since    x.y.z
	 * @access   private
	 */
	protected function set_options() {
		$data = WPTG()->options()->get( $this->module_name );

		$this->options = new Options();

		$this->options->set_data( (array) $data );
	}


	/**
	 * Get the plugin options
	 *
	 * @since    x.y.z
	 * @access   public
	 *
	 * @return Options
	 */
	public function options() {
		if ( ! $this->options ) {
			$this->set_options();
		}
		return $this->options;
	}

	/**
	 * Register all of the hooks.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	protected function define_necessary_hooks() {}

	/**
	 * Register all of the hooks.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	protected function define_on_active_hooks() {}
}
