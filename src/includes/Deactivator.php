<?php
/**
 * Fired during plugin deactivation
 *
 * @link       https://t.me/manzoorwanijk
 * @since      1.0.0
 *
 * @package    WPTelegram
 * @subpackage WPTelegram/includes
 */

namespace WPTelegram\Core\includes;

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    WPTelegram
 * @subpackage WPTelegram/includes
 * @author     Manzoor Wani <@manzoorwanijk>
 */
class Deactivator {

	/**
	 * Clean up the things.
	 *
	 * It cleans the traces left in the database if necessary
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {

		$hooks = [
			'notify_cron_hook',
			'p2tg_process_queue',
		];

		foreach ( $hooks as $hook ) {
			wp_clear_scheduled_hook( 'wptelegram_' . $hook );
		}
	}

}
