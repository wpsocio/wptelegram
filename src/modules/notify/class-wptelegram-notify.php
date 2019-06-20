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
class WPTelegram_Notify extends WPTelegram_Module {

	/**
	 * Define the core functionality of the module.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $module_name, $path, $module_title ) {

		parent::__construct( $module_name, $path, $module_title );

		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->define_sender_hooks();

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
		require_once $this->path . '/class-wptelegram-notify-admin.php';

		/**
		 * The class responsible for handling save_post hook
		 */
		require_once $this->path . '/class-wptelegram-notify-sender.php';

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$admin = new WPTelegram_Notify_Admin( $this->module_name, $this->module_title );

		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_scripts', 10, 1 );

		$this->loader->add_action( 'cmb2_admin_init', $admin, 'create_options_page' );

		$user_notify = WPTG()->options( 'notify' )->get( 'user_notifications', 'off' );

		if ( 'on' === $user_notify ) {

			$this->loader->add_action( 'cmb2_init', $admin, 'add_user_profile_fields' );

			$this->loader->add_filter( 'user_profile_update_errors', $admin, 'validate_user_profile_fields', 10, 3 );
		}
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_sender_hooks() {

		$sender = new WPTelegram_Notify_Sender( $this->module_name, $this->module_title );

		$this->loader->add_filter( 'wp_mail', $sender, 'handle_wp_mail', 10, 1 );
	}
}
