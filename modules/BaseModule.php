<?php
/**
 * The file that defines the module
 *
 * A class definition that includes attributes and functions used across the module
 *
 * @link       https://wpsocio.com
 * @since      3.0.0
 *
 * @package    WPTelegram
 * @subpackage WPTelegram\Core\modules;
 */

namespace WPTelegram\Core\modules;

use WPSocio\WPUtils\Options;

/**
 * The module core class.
 *
 * @since      1.0.0
 * @package    WPTelegram
 * @subpackage WPTelegram\Core\modules;
 * @author     WP Socio
 */
abstract class BaseModule {

	/**
	 * The single instance of the class.
	 *
	 * @since 3.0.0
	 * @var   static $instances The instance.
	 */
	protected static $instances = [];

	/**
	 * List of modules which have been initiated.
	 *
	 * @since 3.1.0
	 * @var   array $initiated List of modules which have been initiated.
	 */
	private static $initiated = [];

	/**
	 * The module options
	 *
	 * @since    3.0.0
	 * @access   protected
	 * @var      Options    $options    The module options.
	 */
	protected $options;

	/**
	 * The module name
	 *
	 * @since    3.0.0
	 * @access   protected
	 * @var      string    $module_name    The module name.
	 */
	protected $module_name;

	/**
	 * Main class Instance.
	 *
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @since 3.0.0
	 *
	 * @return static instance.
	 */
	public static function instance() {
		// static::class can be something like "WPTelegram\Core\modules\p2tg\Main".
		// $relative_path becomes "p2tg\Main".
		$relative_path = ltrim( str_replace( __NAMESPACE__, '', static::class ), '\\' );

		// extract module name from ["p2tg", "Main"].
		list( $module_name ) = explode( '\\', $relative_path );

		if ( ! isset( self::$instances[ $module_name ] ) ) {
			self::$instances[ $module_name ] = new static( $module_name );
		}
		return self::$instances[ $module_name ];
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
	}

	/**
	 * Registers the initial hooks.
	 *
	 * @since    3.1.0
	 * @access   public
	 */
	public function init() {
		if ( ! empty( self::$initiated[ $this->module_name ] ) ) {
			return;
		}

		$this->define_necessary_hooks();

		if ( $this->options()->get( 'active' ) ) {
			$this->define_on_active_hooks();
		}

		self::$initiated[ $this->module_name ] = true;
	}

	/**
	 * Set the plugin options
	 *
	 * @since    3.0.0
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
	 * @since    3.0.0
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
