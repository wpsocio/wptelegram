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

		$sender = new NotifySender( $this );

		add_filter( 'wp_mail', [ $sender, 'handle_wp_mail' ], 10, 1 );
	}
}
