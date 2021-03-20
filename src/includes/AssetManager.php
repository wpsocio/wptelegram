<?php
/**
 * The assets manager of the plugin.
 *
 * @link       https://t.me/manzoorwanijk
 * @since      3.0.0
 *
 * @package    WPTelegram\Core
 * @subpackage WPTelegram\Core\includes
 */

namespace WPTelegram\Core\includes;

use WPTelegram\Core\includes\restApi\RESTController;

/**
 * The assets manager of the plugin.
 *
 * Loads the plugin assets.
 *
 * @package    WPTelegram\Core
 * @subpackage WPTelegram\Core\includes
 * @author     Manzoor Wani <@manzoorwanijk>
 */
class AssetManager extends BaseClass {

	const ADMIN_MAIN_JS_HANDLE         = 'wptelegram--main';
	const ADMIN_P2TG_GB_JS_HANDLE      = 'wptelegram--p2tg-gb';
	const ADMIN_P2TG_CLASSIC_JS_HANDLE = 'wptelegram--p2tg-classic';

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    3.0.0
	 * @param string $hook_suffix The current admin page.
	 */
	public function enqueue_admin_styles( $hook_suffix ) {

		wp_enqueue_style(
			$this->plugin->name() . '-menu',
			$this->plugin->assets()->url( sprintf( '/css/admin-menu%s.css', wp_scripts_get_suffix() ) ),
			array(),
			$this->plugin->version(),
			'all'
		);

		$entrypoint = self::ADMIN_MAIN_JS_HANDLE;

		// Load only on settings page.
		if ( $this->is_settings_page( $hook_suffix ) && $this->plugin->assets()->has_asset( $entrypoint, Assets::ASSET_EXT_CSS ) ) {
			wp_enqueue_style(
				$entrypoint,
				$this->plugin->assets()->get_asset_url( $entrypoint, Assets::ASSET_EXT_CSS ),
				array(),
				$this->plugin->assets()->get_asset_version( $entrypoint, Assets::ASSET_EXT_CSS ),
				'all'
			);
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    3.0.0
	 * @param string $hook_suffix The current admin page.
	 */
	public function enqueue_admin_scripts( $hook_suffix ) {
		// Load only on settings page.
		if ( $this->is_settings_page( $hook_suffix ) ) {
			$entrypoint = self::ADMIN_MAIN_JS_HANDLE;

			wp_enqueue_script(
				$entrypoint,
				$this->plugin->assets()->get_asset_url( $entrypoint ),
				$this->plugin->assets()->get_asset_dependencies( $entrypoint ),
				$this->plugin->assets()->get_asset_version( $entrypoint ),
				true
			);

			// Pass data to JS.
			$data = $this->get_dom_data();

			wp_add_inline_script(
				$entrypoint,
				sprintf( 'var wptelegram = %s;', json_encode( $data ) ), // phpcs:ignore WordPress.WP.AlternativeFunctions
				'before'
			);
		}

		// Load Post to Telegram js for classic editor if CMB2 is loaded.
		if ( $this->is_post_edit_page( $hook_suffix ) && did_action( 'cmb2_init' ) && ! did_action( 'enqueue_block_editor_assets' ) ) {
			$entrypoint = self::ADMIN_P2TG_CLASSIC_JS_HANDLE;

			wp_enqueue_script(
				$entrypoint,
				$this->plugin->assets()->get_asset_url( $entrypoint ),
				$this->plugin->assets()->get_asset_dependencies( $entrypoint ),
				$this->plugin->assets()->get_asset_version( $entrypoint ),
				true
			);
		}
	}

	/**
	 * Enqueue assets for the Gutenberg block
	 *
	 * @since    3.0.0
	 */
	public function enqueue_block_editor_assets() {
		$add_gb_detection_code = $this->plugin->options()->get_path( 'p2tg.active' );
		$add_gb_detection_code = apply_filters( 'wptelegram_p2tg_add_gb_detection_code', $add_gb_detection_code );

		if ( $add_gb_detection_code ) {
			$entrypoint = self::ADMIN_P2TG_GB_JS_HANDLE;
			wp_enqueue_script(
				$entrypoint,
				$this->plugin->assets()->get_asset_url( $entrypoint ),
				$this->plugin->assets()->get_asset_dependencies( $entrypoint ),
				$this->plugin->assets()->get_asset_version( $entrypoint ),
				true
			);

			// Pass data to JS.
			$data = $this->get_dom_data( 'BLOCKS' );

			wp_add_inline_script(
				$entrypoint,
				sprintf( 'var wptelegram = %s;', json_encode( $data ) ), // phpcs:ignore WordPress.WP.AlternativeFunctions
				'before'
			);
		}
	}

	/**
	 * Get the common DOM data.
	 *
	 * @param string $for The domain for which the DOM data is to be rendered.
	 * possible values: 'SETTINGS_PAGE' | 'BLOCKS'.
	 *
	 * @return array
	 */
	private function get_dom_data( $for = 'SETTINGS_PAGE' ) {
		$data = array(
			'pluginInfo' => array(
				'title'       => $this->plugin->title(),
				'name'        => $this->plugin->name(),
				'version'     => $this->plugin->version(),
				'description' => __( 'With this plugin, you can send posts to Telegram and receive notifications and do lot more :)', 'wptelegram' ),
			),
			'api'        => array(
				'admin_url'      => admin_url(),
				'nonce'          => wp_create_nonce( 'wptelegram' ),
				'use'            => 'SERVER', // or may be 'BROWSER'?
				'rest_namespace' => RESTController::NAMESPACE,
				'wp_rest_url'    => esc_url_raw( rest_url() ),
			),
			'uiData'     => array(
				'debug_info' => $this->get_debug_info(),
			),
			'i18n'       => Utils::get_jed_locale_data( 'wptelegram' ),
		);

		if ( 'SETTINGS_PAGE' === $for ) {
			$data['assets'] = array(
				'logoUrl'        => $this->plugin->assets()->url( '/icons/icon-128x128.png' ),
				'tgIconUrl'      => $this->plugin->assets()->url( '/icons/tg-icon.svg' ),
				'editProfileUrl' => get_edit_profile_url( get_current_user_id() ),
				'p2tgLogUrl'     => content_url( Logger::get_log_file_name( 'p2tg' ) ),
				'botApiLogUrl'   => content_url( Logger::get_log_file_name( 'bot-api' ) ),
			);
		}

		// Not to expose bot token to non-admins.
		if ( 'SETTINGS_PAGE' === $for && current_user_can( 'manage_options' ) ) {
			$data['savedSettings'] = \WPTelegram\Core\includes\restApi\SettingsController::get_default_settings();
		}

		return apply_filters( 'wptelegram_assets_dom_data', $data, $for, $this->plugin );
	}

	/**
	 * Get debug info.
	 */
	public function get_debug_info() {

		$info  = 'PHP: ' . PHP_VERSION . PHP_EOL;
		$info .= 'WP: ' . get_bloginfo( 'version' ) . PHP_EOL;
		$info .= 'WP Telegram: ' . $this->plugin->version();

		return $info;
	}

	/**
	 * Whether the current page is the plugin settings page.
	 *
	 * @since 3.0.0
	 *
	 * @param string $hook_suffix The current admin page.
	 */
	public function is_settings_page( $hook_suffix ) {
		return 'toplevel_page_' . $this->plugin->name() === $hook_suffix;
	}

	/**
	 * Whether the current page is the post edit page.
	 *
	 * @since 3.0.0
	 *
	 * @param string $hook_suffix The current admin page.
	 */
	public function is_post_edit_page( $hook_suffix ) {
		return 'post-new.php' === $hook_suffix || 'post.php' === $hook_suffix;
	}
}
