<?php
/**
 * The file that defines the module
 *
 * A class definition that includes attributes and functions used across the module
 *
 * @link       https://wpsocio.com
 * @since      1.0.0
 *
 * @package    WPTelegram
 * @subpackage WPTelegram\Core\modules\notify
 */

namespace WPTelegram\Core\modules\notify;

use WPTelegram\Core\modules\BaseModule;

/**
 * The main module class.
 *
 * @since      1.0.0
 * @package    WPTelegram
 * @subpackage WPTelegram\Core\modules\notify
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

		$sender = NotifySender::instance();

		add_filter( 'wp_mail', [ $sender, 'handle_wp_mail' ], 5, 1 );
	}
}
