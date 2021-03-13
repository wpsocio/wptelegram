<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://t.me/manzoorwanijk
 * @since      1.0.0
 *
 * @package    WPTelegram
 * @subpackage WPTelegram\Core\admin
 */

namespace WPTelegram\Core\admin;

use WPTelegram\Core\includes\restApi\SettingsController;
use WPTelegram\Core\includes\BaseClass;
use WPTelegram\Core\includes\Logger;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two hooks to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    WPTelegram\Core
 * @subpackage WPTelegram\Core\admin
 * @author     Manzoor Wani
 */
class Admin extends BaseClass {

	/**
	 * Register WP REST API routes.
	 *
	 * @since x.y.z
	 */
	public function register_rest_routes() {
		$controller = new SettingsController();
		$controller->register_routes();
	}

	/**
	 * Register the admin menu.
	 *
	 * @since x.y.z
	 */
	public function add_plugin_admin_menu() {
		add_menu_page(
			esc_html( $this->plugin->title() ),
			esc_html( $this->plugin->title() ),
			'manage_options',
			$this->plugin->name(),
			array( $this, 'display_plugin_admin_page' ),
			'none',
			80
		);
	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since x.y.z
	 */
	public function display_plugin_admin_page() {
		?>
			<div id="wptelegram-settings"></div>
		<?php
	}

	/**
	 * Add action links to the plugin page.
	 *
	 * @since  1.6.1
	 *
	 * @param array $links The links for the plugin.
	 * @return array
	 */
	public function plugin_action_links( $links ) {
		$settings_link = '<a href="' . menu_page_url( $this->plugin_name, false ) . '">' . esc_html( __( 'Settings', 'wptelegram' ) ) . '</a>';
		array_unshift( $links, $settings_link );

		return $links;
	}

	/**
	 * Initiate logger
	 *
	 * @since    1.0.0
	 */
	public function initiate_logger() {

		$active_logs = WPTG()->options()->get_path( 'advanced.enable_logs', array() );

		$logger = new Logger( $active_logs );
		$logger->hookup();
	}
}
