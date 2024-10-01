<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://wpsocio.com
 * @since      1.0.0
 *
 * @package    WPTelegram
 * @subpackage WPTelegram\Core\includes
 */

namespace WPTelegram\Core\includes;

use WPSocio\WPUtils\ViteWPReactAssets as Assets;
use WPSocio\WPUtils\Options;
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
 * @subpackage WPTelegram\Core\includes
 * @author     WP Socio
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
	 * Whether the dependencies have been initiated.
	 *
	 * @since 3.1.0
	 * @var   bool $initiated Whether the dependencies have been initiated.
	 */
	private static $initiated = false;

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
	}

	/**
	 * Registers the initial hooks.
	 *
	 * @since    3.0.0
	 * @access   public
	 */
	public function init() {
		if ( self::$initiated ) {
			return;
		}
		self::$initiated = true;

		$plugin_upgrade = Upgrade::instance();

		// First lets do the upgrades, if needed.
		add_action( 'plugins_loaded', [ $plugin_upgrade, 'do_upgrade' ], 10 );

		$modules = Modules::instance();
		// Then lets hook everything up.
		add_action( 'plugins_loaded', [ $this, 'hookup' ], 20 );
		add_action( 'plugins_loaded', [ $modules, 'load' ], 20 );

		do_action( 'wptelegram_loaded' );
	}

	/**
	 * Whether an upgrade is going on.
	 *
	 * @since 4.0.19
	 *
	 * @return bool
	 */
	public function doing_upgrade() {
		return defined( 'WPTELEGRAM_DOING_UPGRADE' ) && WPTELEGRAM_DOING_UPGRADE;
	}

	/**
	 * Registers the initial hooks.
	 *
	 * @since    3.0.0
	 * @access   public
	 */
	public function hookup() {

		$plugin_admin = Admin::instance();

		// Ensure that the menu is always there.
		add_action( 'admin_menu', [ $plugin_admin, 'add_plugin_admin_menu' ], 8 );

		if ( $this->doing_upgrade() ) {
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
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new I18n();

		add_action( 'after_setup_theme', [ $plugin_i18n, 'load_plugin_textdomain' ] );
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
		$this->assets = new Assets(
			$this->dir( '/assets/build' ),
			$this->url( '/assets/build' )
		);
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
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = Admin::instance();

		add_action( 'rest_api_init', [ $plugin_admin, 'register_rest_routes' ] );

		add_filter( 'rest_request_before_callbacks', [ Utils::class, 'filter_rest_errors' ], 10, 3 );

		add_filter( 'plugin_action_links_' . WPTELEGRAM_BASENAME, [ $plugin_admin, 'plugin_action_links' ] );

		add_action( 'after_setup_theme', [ $plugin_admin, 'initiate_logger' ] );

		$asset_manager = AssetManager::instance();

		// Ensure that the assets are registered before any other hooks.
		add_action( 'admin_init', [ $asset_manager, 'register_assets' ], 5 );

		add_action( 'admin_enqueue_scripts', [ $asset_manager, 'enqueue_admin_assets' ] );

		// Load the integrations.
		new Integrations();
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
