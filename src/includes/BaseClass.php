<?php
/**
 * The base class of the plugin.
 *
 * @link       https://manzoorwani.dev
 * @since      3.0.0
 *
 * @package    WPTelegram\Core
 * @subpackage WPTelegram\Core\includes
 */

namespace WPTelegram\Core\includes;

/**
 * The base class of the plugin.
 *
 * The base class of the plugin.
 *
 * @package    WPTelegram\Core
 * @subpackage WPTelegram\Core\includes
 * @author     Manzoor Wani <@manzoorwanijk>
 */
abstract class BaseClass {

	/**
	 * Instances of the class.
	 *
	 * @since  x.y.z
	 * @access protected
	 * @var    self $instances The instances.
	 */
	protected static $instances = [];

	/**
	 * The plugin class instance.
	 *
	 * @since    3.0.0
	 * @access   protected
	 * @var      Main $plugin The plugin class instance.
	 */
	protected $plugin;

	/**
	 * Base class Instance.
	 *
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @since x.y.z
	 *
	 * @return static instance.
	 */
	public static function instance() {
		if ( ! isset( self::$instances[ static::class ] ) ) {
			self::$instances[ static::class ] = new static();
		}
		return self::$instances[ static::class ];
	}

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 3.0.0
	 */
	protected function __construct() {

		$this->plugin = Main::instance();
	}

	/**
	 * Get the instance of the plugin.
	 *
	 * @since     3.0.0
	 * @return    Main    The plugin class instance.
	 */
	protected function plugin() {
		return $this->plugin;
	}
}
