<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://t.me/manzoorwanijk
 * @since      1.0.0
 *
 * @package    WPTelegram
 * @subpackage WPTelegram/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    WPTelegram
 * @subpackage WPTelegram/includes
 * @author     Manzoor Wani <@manzoorwanijk>
 */
final class WPTelegram {

	/**
	 * The single instance of the class.
	 *
	 * @since 1.0.0
	 */
	protected static $_instance = null;

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      WPTelegram_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * Title of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_title    Title of the plugin
	 */
	protected $plugin_title;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * The plugin options
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $options    The plugin options
	 */
	protected $options;

	/**
	 * The utility methods
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string    $utils    The utility methods
	 */
	public $utils;

	/**
	 * The helpers methods
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string    $helpers    The helpers methods
	 */
	public $helpers;

	/**
	 * Main class Instance.
	 *
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * 
	 * @return Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone() {}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() {}

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	private function __construct() {

		$this->version		= WPTELEGRAM_VER;
		$this->plugin_title	= 'WP Telegram';
		$this->plugin_name	= strtolower( __CLASS__ );

		$this->load_dependencies();
		$this->set_options();

		$this->set_locale();

		$this->load_modules();

		$this->define_admin_hooks();
		$this->define_public_hooks();

		$this->run();

		$this->utils = WPTelegram_Utils::instance();
		$this->helpers = WPTelegram_Helpers::instance();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - WPTelegram_Loader. Orchestrates the hooks of the plugin.
	 * - WPTelegram_i18n. Defines internationalization functionality.
	 * - WPTelegram_Admin. Defines all hooks for the admin area.
	 * - WPTelegram_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once WPTELEGRAM_DIR . '/includes/class-wptelegram-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once WPTELEGRAM_DIR . '/includes/class-wptelegram-i18n.php';

		/**
		 * The class responsible for plugin options
		 */
		require_once WPTELEGRAM_DIR . '/includes/class-wptelegram-options.php';

		/**
		 * The class responsible for loading modules
		 */
		require_once WPTELEGRAM_DIR . '/includes/class-wptelegram-modules.php';

		/**
		 * The utility methods
		 */
		require_once WPTELEGRAM_DIR . '/includes/class-wptelegram-utils.php';

		/**
		 * The helper methods
		 */
		require_once WPTELEGRAM_DIR . '/includes/class-wptelegram-helpers.php';

		/**
		 * The logger class
		 */
		require_once WPTELEGRAM_DIR . '/includes/class-wptelegram-logger.php';

		/**
		 * CMB2 library responsible for rendering fields
		 */
		if ( file_exists( WPTELEGRAM_DIR . '/includes/cmb2/init.php' ) ) {
			require_once WPTELEGRAM_DIR . '/includes/cmb2/init.php';
		}

		/**
		 * The library responsible for converting HTML to plain text
		 */
		require_once WPTELEGRAM_DIR . '/includes/html2text/html2text.php';

		/**
		 * The class responsible for defining all the common properties and methods
		 */
		require_once WPTELEGRAM_DIR . '/includes/class-wptelegram-core-base.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once WPTELEGRAM_DIR . '/admin/class-wptelegram-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once WPTELEGRAM_DIR . '/admin/partials/class-wptelegram-admin-header.php';

		/**
		 * The class responsible for loading WPTelegram_Bot_API library
		 */
		require_once WPTELEGRAM_DIR . '/includes/wptelegram-bot-api/class-wptelegram-bot-api-loader.php';


		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once WPTELEGRAM_DIR . '/public/class-wptelegram-public.php';

		/**
		 * Helper functions
		 */
		require_once WPTELEGRAM_DIR . '/includes/helper-functions.php';

		$this->loader = new WPTelegram_Loader();

	}

	/**
	 * Set the plugin options
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_options() {

		$this->options['core'] = new WPTelegram_Options( $this->plugin_name );

		$modules = WPTelegram_Modules::get_all_modules();

		foreach ( array_keys( $modules ) as $module ) {
			$this->options[ $module ] = new WPTelegram_Options( $this->plugin_name . '_' . $module );
		}

	}

	/**
	 * Load the active modules
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_modules() {

		$modules = new WPTelegram_Modules();

		$this->loader->add_action( 'plugins_loaded', $modules, 'load' );
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the WPTelegram_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new WPTelegram_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new WPTelegram_Admin( $this->get_plugin_title(), $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles', 10, 1 );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts', 10, 1 );
		$this->loader->add_filter( 'plugin_action_links_' . WPTELEGRAM_BASENAME, $plugin_admin, 'plugin_action_links' );

		$this->loader->add_action( 'plugins_loaded', $plugin_admin, 'load_cmb2_addons' );

		$this->loader->add_action( 'init', $plugin_admin, 'initiate_logger' );

		$this->loader->add_action( 'cmb2_admin_init', $plugin_admin, 'create_options_pages' );

		$this->loader->add_action( 'cmb2_before_form', $plugin_admin, 'render_plugin_header', 10, 4 );

		$this->loader->add_action( 'wptelegram_after_cmb2_form', $plugin_admin, 'render_plugin_sidebar', 10, 1 );

		$this->loader->add_action( 'wp_ajax_wptelegram_test', $plugin_admin, 'ajax_handle_test' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new WPTelegram_Public( $this->get_plugin_title(), $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'after_setup_theme', $plugin_public, 'do_upgrade' );

		// $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		// $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	private function run() {
		$this->loader->run();
	}

	/**
	 * Get the plugin options
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function options( $module = 'core' ) {

		// return core options by default
		if ( array_key_exists( $module, $this->options ) ) {
			return $this->options[ $module ];
		} else {
			return new WPTelegram_Options( $module );
		}		
	}

	/**
	 * The title of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The title of the plugin.
	 */
	public function get_plugin_title() {
		return $this->plugin_title;
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
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

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Retrieve the URL of the plugin directory
	 *
	 * @since     1.0.0
	 * @return    string    plugins_url
	 */
	public function get_url() {
		return WPTELEGRAM_URL;
	}

	/**
	 * Retrieve the path of the plugin directory
	 *
	 * @since     1.0.0
	 * @return    string    plugin_dir_path
	 */
	public function get_dir_path() {
		return WPTELEGRAM_DIR;
	}

	/**
	 * Retrieve the name of the plugin text_domain
	 *
	 * @since     1.0.0
	 * @return    string    text domain
	 */
	public function get_text_domain() {
		return 'wptelegram';
	}

}
