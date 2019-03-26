<?php

/**
 * Loads and includes all the active modules
 *
 * @link	   https://t.me/manzoorwanijk
 * @since	  1.0.0
 *
 * @package	WPTelegram
 * @subpackage WPTelegram/includes
 */

/**
 * Loads and includes all the active modules
 *
 * @package	WPTelegram
 * @subpackage WPTelegram/includes
 * @author	 Manzoor Wani <@manzoorwanijk>
 */
class WPTelegram_Modules {

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->load_dependencies();

	}

	/**
	 * Load the required dependencies
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for defining all the common basics of modules
		 */
		require_once WPTELEGRAM_MODULES_DIR . '/class-wptelegram-module.php';

		/**
		 * The class responsible for defining the basics
		 */
		require_once WPTELEGRAM_MODULES_DIR . '/class-wptelegram-module-base.php';
	}

	/**
	 * Retrieve all modules
	 *
	 * @since	1.0.0
	 */
	public static function get_all_modules() {

		return array(
			'p2tg'		=> array(
				'title'	=> __( 'Post to Telegram', 'wptelegram' ),
				'desc'	=> __( 'Send the posts automatically to a Telegram Channel or group.', 'wptelegram' ),
			),
			'notify'	=> array(
				'title'	=> __( 'Private Notifications', 'wptelegram' ),
				'desc'	=> __( 'Send your email notifications to Telegram.', 'wptelegram' ),
			),
			'proxy'		=> array(
				'title'	=> __( 'Proxy', 'wptelegram' ),
				'desc'	=> __( 'Bypass the ban on Telegram by making use of proxy.', 'wptelegram' ),
			),
		);
	}

	/**
	 * Load the active modules
	 *
	 * @since	1.0.0
	 * @access   private
	 */
	public function load() {
		
		$all_modules	= self::get_all_modules();
		$active_modules	= WPTG()->helpers->get_active_modules();

		if ( empty( $active_modules ) ) {
			return;
		}

		foreach ( $active_modules as $_module ) {

			$module = str_replace( '_', '-', $_module );

			$path = WPTELEGRAM_MODULES_DIR . '/' . $module;

			$file = $path . '/class-wptelegram-' . $module . '.php';

			if ( file_exists( $file ) ) {
				/**
				 * The class responsible for loading the module
				 */
				require_once $file;
				
				$module = WPTG()->utils->ucwords( $_module, '_' );

				$class = "WPTelegram_{$module}";

				if ( class_exists( $class ) ) {

					$module = new $class( $_module, $path, $all_modules[ $_module ]['title'] );

					$module->run();

					define( strtoupper( $class ) . '_LOADED', true );
				}
			}
		}
	}
}
