<?php

/**
 * The file that defines the base of all the modules
 *
 * A class definition that includes attributes and functions used across the modules
 *
 * @link       https://t.me/manzoorwanijk
 * @since      1.0.0
 *
 * @package    WPTelegram
 * @subpackage WPTelegram/modules
 */

/**
 * The module base class.
 *
 * @since      1.0.0
 * @package    WPTelegram
 * @subpackage WPTelegram/modules
 * @author     Manzoor Wani <@manzoorwanijk>
 */
abstract class WPTelegram_Module {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the module.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      WPTelegram_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this module.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $module_name    The string used to uniquely identify this module.
	 */
	protected $module_name;

	/**
	 * Title of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $module_title    Title of the module
	 */
	protected $module_title;

	/**
	 * The module path
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $path    The module path
	 */
	protected $path;

	/**
	 * Define the core functionality of the module.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $module_name, $path, $module_title ) {

		$this->module_name = $module_name;
		$this->path = $path;
		$this->module_title = $module_title;

		$this->loader = new WPTelegram_Loader();
	}

	/**
	 * Load the required dependencies for this module.
	 *
	 * No module can be without dependencies :)
	 *
	 * @since    1.0.0
	 * @access   protected
	 */
	abstract protected function load_dependencies();

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    WPTelegram_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}
}
