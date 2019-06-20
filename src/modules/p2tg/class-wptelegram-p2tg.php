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
class WPTelegram_P2TG extends WPTelegram_Module {

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
		require_once $this->path . '/class-wptelegram-p2tg-admin.php';

		/**
		 * The class responsible for handling the metaboxes
		 */
		require_once $this->path . '/class-wptelegram-p2tg-rules.php';

		/**
		 * The class responsible for sending post to Telegram
		 */
		require_once $this->path . '/class-wptelegram-p2tg-post-sender.php';

		/**
		 * The class responsible for fetching post data
		 */
		require_once $this->path . '/class-wptelegram-p2tg-post-data.php';

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$admin = new WPTelegram_P2TG_Admin( $this->module_name, $this->module_title );

		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_styles', 10, 1 );
		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_scripts', 10, 1 );
		$this->loader->add_action( 'wp_ajax_wptg_p2tg_rule_values', $admin, 'ajax_render_rule_values' );

		$this->loader->add_action( 'post_submitbox_misc_actions', $admin, 'add_switch_to_submitbox', 10, 1 );

		$this->loader->add_action( 'edit_form_top', $admin, 'post_edit_form_hidden_input' );
		$this->loader->add_action( 'block_editor_meta_box_hidden_fields', $admin, 'post_edit_form_hidden_input' );

		$this->loader->add_action( 'cmb2_admin_init', $admin, 'create_options_page' );

		$this->loader->add_action( 'cmb2_admin_init', $admin, 'create_override_metabox' );

		$this->loader->add_action( 'admin_notices', $admin, 'admin_notices' );

		$this->loader->add_action( 'wptelegram_settings_sidebar_row', $admin, 'add_sidebar_row', 10, 2 );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_sender_hooks() {

		$post_sender = new WPTelegram_P2TG_Post_Sender( $this->module_name, $this->module_title );

		$this->loader->add_action( 'wp_insert_post', $post_sender, 'wp_insert_post', 20, 2 );

		// scheduled post handler
		$this->loader->add_action( 'future_to_publish', $post_sender, 'future_to_publish', 20, 1 );

		// delay event handler
		$this->loader->add_action( 'wptelegram_p2tg_delayed_post', $post_sender, 'delayed_post', 10, 1 );

		// trigger handler
		$this->loader->add_action( 'wptelegram_p2tg_send_post', $post_sender, 'send_post', 10, 3 );
	}
}
