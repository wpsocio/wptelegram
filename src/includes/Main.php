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
 * @subpackage WPTelegram\Core\includes
 */

namespace WPTelegram\Core\includes;

use WPTelegram\Core\admin\Admin;

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
final class Main {

	/**
	 * The single instance of the class.
	 *
	 * @since 1.0.0
	 * @var   Main $instance The instance.
	 */
	protected static $instance = null;

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * Title of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $title    Title of the plugin
	 */
	protected $title;

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
	 * @var      Options    $options    The plugin options
	 */
	protected $options;

	/**
	 * The assets handler.
	 *
	 * @since    x.y.z
	 * @access   protected
	 * @var      string    $assets    The assets handler.
	 */
	protected $assets;

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
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
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

		$this->version     = WPTELEGRAM_VER;
		$this->plugin_name = 'wptelegram';

		$this->load_dependencies();

		$this->load_modules();

		$this->set_locale();

		$this->define_admin_hooks();
		$this->define_other_hooks();

		$this->run();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * Helper functions
		 */
		require_once $this->dir( '/includes/helper-functions.php' );

		/**
		 * The class responsible for loading \WPTelegram\BotAPI library
		 */
		require_once $this->dir( '/includes/wptelegram-bot-api/src/index.php' );

		/**
		 * The library responsible for converting HTML to plain text
		 */
		require_once $this->dir( '/includes/html2text/html2text.php' );

		$this->loader = new Loader();

	}

	/**
	 * Load the active modules
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_modules() {

		$modules = new Modules( $this );

		$this->loader->add_action( 'plugins_loaded', $modules, 'load', 15 );
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

		$plugin_i18n = new I18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Set the plugin options
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_options() {

		$this->options = new Options( $this->plugin_name, true );
	}

	/**
	 * Set the assets handler.
	 *
	 * @since    x.y.z
	 * @access   private
	 */
	private function set_assets() {
		$this->assets = new Assets( $this->dir( '/assets' ), $this->url( '/assets' ) );
	}

	/**
	 * Get the plugin assets handler.
	 *
	 * @since    x.y.z
	 * @access   public
	 *
	 * @return Assets The assets instance.
	 */
	public function assets() {
		if ( ! $this->assets ) {
			$this->set_assets();
		}

		return $this->assets;
	}

	/**
	 * Get the plugin options
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @return Options
	 */
	public function options() {
		if ( ! $this->options ) {
			$this->set_options();
		}
		return $this->options;
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Admin( $this );

		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_admin_menu', 8 );

		$this->loader->add_action( 'rest_api_init', $plugin_admin, 'register_rest_routes' );

		$this->loader->add_filter( 'plugin_action_links_' . WPTELEGRAM_BASENAME, $plugin_admin, 'plugin_action_links' );

		$this->loader->add_action( 'init', $plugin_admin, 'initiate_logger' );

		$asset_manager = new AssetManager( $this );

		$this->loader->add_action( 'admin_enqueue_scripts', $asset_manager, 'enqueue_admin_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $asset_manager, 'enqueue_admin_scripts' );

		$this->loader->add_action( 'enqueue_block_editor_assets', $asset_manager, 'enqueue_block_editor_assets' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_other_hooks() {

		$plugin_upgrade = new Upgrade( $this );

		$this->loader->add_action( 'plugins_loaded', $plugin_upgrade, 'do_upgrade' );
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
	 * The title of the plugin.
	 *
	 * @since     x.y.z
	 * @return    string    The title of the plugin.
	 */
	public function title() {
		// Set here instead of constructor
		// to be able to translate it.
		if ( ! $this->title ) {
			$this->title = __( 'WP Telegram', 'wptelegram' );
		}
		return $this->title;
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     x.y.z
	 * @return    string    The name of the plugin.
	 */
	public function name() {
		return $this->plugin_name;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     x.y.z
	 * @return    string    The version number of the plugin.
	 */
	public function version() {
		return $this->version;
	}

	/**
	 * Retrieve directory path to the plugin.
	 *
	 * @since x.y.z
	 * @param string $path Path to append.
	 * @return string Directory with optional path appended
	 */
	public function dir( $path = '' ) {
		return WPTELEGRAM_DIR . $path;
	}

	/**
	 * Retrieve URL path to the plugin.
	 *
	 * @since 2.1.7
	 * @param string $path Path to append.
	 * @return string URL with optional path appended
	 */
	public function url( $path = '' ) {
		return WPTELEGRAM_URL . $path;
	}
}
