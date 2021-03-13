<?php
/**
 * The base class of the plugin.
 *
 * @link       https://t.me/manzoorwanijk
 * @since      x.y.z
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
	 * @since    x.y.z
	 * @access   protected
	 * @var      Main $plugin The plugin class instance.
	 */
	protected $plugin;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since x.y.z
	 * @param Main $plugin The plugin class instance.
	 */
	public function __construct( $plugin ) {

		$this->plugin = $plugin;
	}
}
