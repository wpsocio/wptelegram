<?php
/**
 * The file that defines the module
 *
 * @link       https://wpsocio.com
 * @since      1.0.0
 *
 * @package    WPTelegram
 * @subpackage WPTelegram\Core\modules\proxy
 */

namespace WPTelegram\Core\modules\proxy;

use WPTelegram\Core\modules\BaseModule;

/**
 * The main module class.
 *
 * @since      1.0.0
 * @package    WPTelegram
 * @subpackage WPTelegram\Core\modules\proxy
 * @author     WP Socio
 */
class Main extends BaseModule {

	/**
	 * Register all of the hooks.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	protected function define_on_active_hooks() {

		$handler = ProxyHandler::instance();

		add_action( 'wptelegram_bot_api_remote_request_init', [ $handler, 'configure_proxy' ] );

		add_action( 'wptelegram_bot_api_remote_request_finish', [ $handler, 'remove_proxy' ] );
	}
}
