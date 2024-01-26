<?php
/**
 * Loads and includes all the active modules
 *
 * @link       https://wpsocio.com
 * @since     1.0.0
 *
 * @package WPTelegram
 * @subpackage WPTelegram\Core\includes
 */

namespace WPTelegram\Core\includes;

/**
 * Loads and includes all the active modules
 *
 * @package WPTelegram
 * @subpackage WPTelegram\Core\includes
 * @author   WP Socio
 */
class Modules extends BaseClass {

	/**
	 * Retrieve all modules.
	 *
	 * @since   1.0.0
	 * @return array
	 */
	public static function get_all_modules() {
		return [ 'p2tg', 'notify', 'proxy' ];
	}

	/**
	 * Load the active modules
	 *
	 * @since   1.0.0
	 * @access   public
	 */
	public function load() {
		// If an upgrade is going on.
		if ( defined( 'WPTELEGRAM_DOING_UPGRADE' ) && WPTELEGRAM_DOING_UPGRADE ) {
			return;
		}

		$namespace = 'WPTelegram\Core\modules';

		foreach ( self::get_all_modules() as $module ) {

			$main = "{$namespace}\\{$module}\Main";

			$main::instance()->init();

			define( 'WPTELEGRAM_' . strtoupper( $module ) . '_LOADED', true );
		}
	}
}
