<?php

/**
 * The file that defines the module
 *
 * A class definition that includes attributes and functions used across the module
 *
 * @link       https://t.me/manzoorwanijk
 * @since      1.0.0
 *
 * @package    WPTelegram
 * @subpackage WPTelegram/modules
 */

/**
 * The module core class.
 *
 * @since      1.0.0
 * @package    WPTelegram
 * @subpackage WPTelegram/modules
 * @author     Manzoor Wani <@manzoorwanijk>
 */
class WPTelegram_Proxy extends WPTelegram_Module {

	/**
	 * Define the core functionality of the module.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $module_name, $path, $module_title ) {

		parent::__construct( $module_name, $path, $module_title );

		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->define_handler_hooks();

	}

	/**
	 * Load the required dependencies for this module.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   protected
	 */
	protected function load_dependencies() {

		/**
		 * The class responsible for handling admin side of the module
		 */
		require_once $this->path . '/class-wptelegram-proxy-admin.php';

		/**
		 * The class responsible for handling save_post hook
		 */
		require_once $this->path . '/class-wptelegram-proxy-handler.php';

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$admin = new WPTelegram_Proxy_Admin( $this->module_name, $this->module_title );
		
		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_scripts', 10, 1 );

		$this->loader->add_action( 'cmb2_admin_init', $admin, 'create_options_page' );

		$this->loader->add_action( 'wptelegram_settings_sidebar_row', $admin, 'add_sidebar_row', 10, 2 );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_handler_hooks() {

		$handler = new WPTelegram_Proxy_Handler( $this->module_name, $this->module_title );

		$this->loader->add_action( 'wptelegram_remote_request_init', $handler, 'configure_proxy' );

		$this->loader->add_action( 'wptelegram_remote_request_finish', $handler, 'remove_proxy' );
	}
}
