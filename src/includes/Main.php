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
	 * @var      Options $options The plugin options
	 */
	protected $options;

	/**
	 * The assets handler.
	 *
	 * @since    3.0.0
	 * @access   protected
	 * @var      Assets $assets The assets handler.
	 */
	protected $assets;

	/**
	 * The asset manager.
	 *
	 * @since    x.y.z
	 * @access   protected
	 * @var      AssetManager $asset_manager The asset manager.
	 */
	protected $asset_manager;

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

		$this->set_locale();

		$this->init();
	}

	/**
	 * Registers the initial hooks.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function init() {

		$plugin_upgrade = new Upgrade( $this );

		$modules = new Modules( $this );

		// First lets do the upgrades, if needed.
		add_action( 'plugins_loaded', [ $plugin_upgrade, 'do_upgrade' ], 10 );

		// Then lets hook everything up.
		add_action( 'plugins_loaded', [ $this, 'hookup' ], 20 );
		add_action( 'plugins_loaded', [ $modules, 'load' ], 20 );
	}

	/**
	 * Registers the initial hooks.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function hookup() {
		// If an upgrade is going on.
		if ( defined( 'WPTELEGRAM_DOING_UPGRADE' ) && WPTELEGRAM_DOING_UPGRADE ) {
			return;
		}
		$this->define_admin_hooks();
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

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new I18n();

		add_action( 'plugins_loaded', [ $plugin_i18n, 'load_plugin_textdomain' ] );
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
	 * Set the assets handler.
	 *
	 * @since    3.0.0
	 * @access   private
	 */
	private function set_assets() {
		$this->assets = new Assets( $this->dir( '/assets' ), $this->url( '/assets' ) );
	}

	/**
	 * Get the plugin assets handler.
	 *
	 * @since    3.0.0
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
	 * Set the asset manager.
	 *
	 * @since    x.y.z
	 * @access   private
	 */
	private function set_asset_manager() {
		$this->asset_manager = new AssetManager( $this );
	}

	/**
	 * Get the plugin assets manager.
	 *
	 * @since    x.y.z
	 * @access   public
	 *
	 * @return AssetManager The asset manager.
	 */
	public function asset_manager() {
		if ( ! $this->asset_manager ) {
			$this->set_asset_manager();
		}

		return $this->asset_manager;
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

		add_action( 'admin_menu', [ $plugin_admin, 'add_plugin_admin_menu' ], 8 );

		add_action( 'rest_api_init', [ $plugin_admin, 'register_rest_routes' ] );

		add_filter( 'rest_request_before_callbacks', [ Utils::class, 'fitler_rest_errors' ], 10, 3 );

		add_filter( 'plugin_action_links_' . WPTELEGRAM_BASENAME, [ $plugin_admin, 'plugin_action_links' ] );

		add_action( 'init', [ $plugin_admin, 'initiate_logger' ] );

		add_action( 'admin_enqueue_scripts', [ $this->asset_manager(), 'enqueue_admin_styles' ] );
		add_action( 'admin_enqueue_scripts', [ $this->asset_manager(), 'enqueue_admin_scripts' ] );
	}

	/**
	 * The title of the plugin.
	 *
	 * @since     3.0.0
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
	 * @since     3.0.0
	 * @return    string    The name of the plugin.
	 */
	public function name() {
		return $this->plugin_name;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     3.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function version() {
		return $this->version;
	}

	/**
	 * Retrieve directory path to the plugin.
	 *
	 * @since 3.0.0
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
