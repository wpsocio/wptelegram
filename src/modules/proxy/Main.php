<?php
/**
 * The file that defines the module
 *
 * A class definition that includes attributes and functions used across the module
 *
 * @link       https://manzoorwani.dev
 * @since      1.0.0
 *
 * @package    WPTelegram
 * @subpackage WPTelegram/modules
 */

namespace WPTelegram\Core\modules\proxy;

use WPTelegram\Core\modules\BaseModule;

/**
 * The main module class.
 *
 * @since      1.0.0
 * @package    WPTelegram
 * @subpackage WPTelegram/modules
 * @author     Manzoor Wani <@manzoorwanijk>
 */
class Main extends BaseModule {

	/**
	 * The single instance of the class.
	 *
	 * @since 3.0.0
	 * @var   Main $instance The instance.
	 */
	protected static $instance = null;

	/**
	 * Register all of the hooks.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	protected function define_on_active_hooks() {

		$handler = new ProxyHandler( $this );

		add_action( 'wptelegram_bot_api_remote_request_init', [ $handler, 'configure_proxy' ] );

		add_action( 'wptelegram_bot_api_remote_request_finish', [ $handler, 'remove_proxy' ] );
	}
}
