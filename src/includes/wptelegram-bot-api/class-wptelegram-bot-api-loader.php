<?php

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'WPTelegram_Bot_API_Loader_121', false ) ) {
	/**
	 * Handles checking for and loading the newest version of WPTelegram_Bot_API
	 * 
	 * Inspired from CMB2 loading technique
	 * to ensure that only the latest version is loaded
	 * @see https://github.com/CMB2/CMB2/blob/v2.3.0/init.php
	 *
	 * @since  1.0.1
	 *
	 * @category  WordPress_Plugin Addon
	 * @package   WPTelegram_Bot_API
	 * @author    WPTelegram team
	 * @license   GPL-2.0+
	 * @link      https://t.me/WPTelegram
	 */
	class WPTelegram_Bot_API_Loader_121 {

		/**
		 * Current version number
		 *
		 * @var   string
		 * @since 1.0.1
		 */
		const VERSION = '1.2.1';

		/**
		 * Current version hook priority.
		 * Will decrement with each release
		 *
		 * @var   int
		 * @since 1.0.1
		 */
		const PRIORITY = 9987;

		/**
		 * Single instance of the WPTelegram_Bot_API_Loader_121 object
		 *
		 * @var WPTelegram_Bot_API_Loader_121
		 */
		public static $single_instance = null;

		/**
		 * Creates/returns the single instance WPTelegram_Bot_API_Loader_121 object
		 *
		 * @since  1.0.1
		 * @return WPTelegram_Bot_API_Loader_121 Single instance object
		 */
		public static function initiate() {
			if ( null === self::$single_instance ) {
				self::$single_instance = new self();
			}
			return self::$single_instance;
		}

		/**
		 * Starts the version checking process.
		 * Creates WPTelegram_Bot_API_LOADED definition for early detection by other scripts
		 *
		 * Hooks WPTelegram_Bot_API inclusion to the after_setup_theme hook on a high priority which decrements
		 * (increasing the priority) with each version release.
		 *
		 * @since 1.0.1
		 */
		private function __construct() {
			/**
			 * A constant you can use to check if WPTelegram_Bot_API is loaded
			 * for your plugins/themes with WPTelegram_Bot_API dependency
			 */
			if ( ! defined( 'WPTELEGRAM_API_LOADED' ) ) {
				define( 'WPTELEGRAM_API_LOADED', self::PRIORITY );
			}

			/**
			 * use after_setup_theme hook instead of init
			 * to make the API library available during init
			 */
			add_action( 'after_setup_theme', array( $this, 'include_wptelegram_bot_api' ), self::PRIORITY );
		}

		/**
		 * A final check if WPTelegram_Bot_API exists before kicking off our WPTelegram_Bot_API loading.
		 * WPTELEGRAM_API_VERSION constant is set at this point.
		 *
		 * @since  1.0.1
		 */
		public function include_wptelegram_bot_api() {
			if ( class_exists( 'WPTelegram_Bot_API', false ) ) {
				return;
			}

			if ( ! defined( 'WPTELEGRAM_API_VERSION' ) ) {
				define( 'WPTELEGRAM_API_VERSION', self::VERSION );
			}

			if ( ! defined( 'WPTELEGRAM_API_DIR' ) ) {
				define( 'WPTELEGRAM_API_DIR', dirname( __FILE__ ) );
			}

			// Now kick off the class autoloader.
			spl_autoload_register( array( __CLASS__, 'wptelegram_bot_api_autoload_classes' ) );
		}
		
		/**
		 * Autoloads files with WPTelegram_Bot_API classes when needed
		 *
		 * @since  1.0.1
		 * @param  string $class_name Name of the class being requested
		 */
		public static function wptelegram_bot_api_autoload_classes( $class_name ) {
			if ( 0 !== strpos( $class_name, 'WPTelegram_Bot_API' ) ) {
				return;
			}
			$path = WPTELEGRAM_API_DIR . '/classes';

			$class_name = strtolower( $class_name );

			include_once( "{$path}/class-{$class_name}.php" );
		}
	}
	WPTelegram_Bot_API_Loader_121::initiate();
}