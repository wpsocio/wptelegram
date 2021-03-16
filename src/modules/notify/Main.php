<?php
/**
 * The file that defines the module
 *
 * A class definition that includes attributes and functions used across the module
 *
 * @link       https://t.me/manzoorwanijk
 * @since      1.0.0
 *
 * @package    WPTelegram
 * @subpackage WPTelegram/modules
 */

namespace WPTelegram\Core\modules\notify;

use WPTelegram\Core\modules\BaseModule;

/**
 * The module core class.
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
	 * @since x.y.z
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

		add_filter( 'wp_mail', array( $sender, 'handle_wp_mail' ), 10, 1 );
	}
}
