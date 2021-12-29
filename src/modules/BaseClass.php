<?php
/**
 * The base class of the module.
 *
 * @link       https://wpsocio.com
 * @since      3.0.0
 *
 * @package    WPTelegram\Core
 * @subpackage WPTelegram\Core\includes
 */

namespace WPTelegram\Core\modules;

/**
 * The base class of the module.
 *
 * The base class of the module.
 *
 * @package    WPTelegram\Core
 * @subpackage WPTelegram\Core\includes
 * @author     WP Socio
 */
abstract class BaseClass {

	/**
	 * Instances of the class.
	 *
	 * @since  3.1.0
	 * @access protected
	 * @var    self $instances The instances.
	 */
	protected static $instances = [];

	/**
	 * The module class instance.
	 *
	 * @since    3.0.0
	 * @access   protected
	 * @var      BaseModule $module The module class instance.
	 */
	protected $module;

	/**
	 * Base class Instance.
	 *
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @since 3.1.0
	 *
	 * @return static instance.
	 */
	public static function instance() {
		// static::class can be something like "WPTelegram\Core\modules\p2tg\Admin".
		// $relative_path becomes "p2tg\Admin".
		$relative_path = ltrim( str_replace( __NAMESPACE__, '', static::class ), '\\' );

		// extract module name from ["p2tg", "Admin"].
		list( $module_name ) = explode( '\\', $relative_path );

		$main = __NAMESPACE__ . "\\{$module_name}\Main";

		if ( ! isset( self::$instances[ static::class ] ) ) {
			self::$instances[ static::class ] = new static( $main::instance() );
		}
		return self::$instances[ static::class ];
	}

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 3.0.0
	 * @param BaseModule $module The module class instance.
	 */
	protected function __construct( $module ) {

		$this->module = $module;
	}

	/**
	 * Get the instance of the module.
	 *
	 * @since     3.0.11
	 * @return    BaseModule    The module class instance.
	 */
	protected function module() {
		return $this->module;
	}
}
