<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://wpsocio.com
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
 * @author     WP Socio
 */
class Admin extends BaseClass {

	/**
	 * Register WP REST API routes.
	 *
	 * @since 3.0.0
	 */
	public function register_rest_routes() {
		$controller = new SettingsController();
		$controller->register_routes();
	}

	/**
	 * Register the admin menu.
	 *
	 * @since 3.0.0
	 */
	public function add_plugin_admin_menu() {
		add_menu_page(
			esc_html( $this->plugin->title() ),
			esc_html( $this->plugin->title() ),
			'manage_options',
			$this->plugin->name(),
			[ $this, 'display_plugin_admin_page' ],
			'none',
			80
		);
	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since 3.0.0
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
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			menu_page_url( $this->plugin->name(), false ),
			esc_html( __( 'Settings', 'wptelegram' ) )
		);

		array_unshift( $links, $settings_link );

		return $links;
	}

	/**
	 * Fires up plugin version upgrade by sending a non-blocking request to home page
	 * immediately after the plugin is upgraded to a new version.
	 *
	 * @since  3.1.2
	 *
	 * @param mixed $upgrader WP Upgrader instance.
	 * @param array $args     Array of bulk item update data.
	 */
	public function fire_plugin_version_upgrade( $upgrader, $args ) {
		if ( 'update' === $args['action'] && 'plugin' === $args['type'] && ! empty( $args['plugins'] ) ) {
			foreach ( $args['plugins'] as $basename ) {
				if ( WPTELEGRAM_BASENAME === $basename ) {
					wp_remote_get(
						site_url(),
						[
							'timeout'   => 0.01,
							'blocking'  => false,
							'sslverify' => false,
						]
					);
					break;
				}
			}
		}
	}

	/**
	 * Initiate logger
	 *
	 * @since    1.0.0
	 */
	public function initiate_logger() {

		$active_logs = WPTG()->options()->get_path( 'advanced.enable_logs', [] );

		Logger::instance()->set_active_logs( $active_logs )->hookup();
	}
}
