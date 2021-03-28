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
	 * The plugin class instance.
	 *
	 * @since    3.0.0
	 * @access   protected
	 * @var      Main $plugin The plugin class instance.
	 */
	protected $plugin;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 3.0.0
	 * @param Main $plugin The plugin class instance.
	 */
	public function __construct( $plugin ) {

		$this->plugin = $plugin;
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
