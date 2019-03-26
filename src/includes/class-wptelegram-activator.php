<?php

/**
 * Fired during plugin activation
 *
 * @link       https://t.me/manzoorwanijk
 * @since      1.0.0
 *
 * @package    WPTelegram
 * @subpackage WPTelegram/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    WPTelegram
 * @subpackage WPTelegram/includes
 * @author     Manzoor Wani <@manzoorwanijk>
 */
class WPTelegram_Activator {

	/**
	 * Enables hooks for the activation process.
	 *
	 * @since	1.0.0
	 * @param	bool	$network_wide	Whether enabled for all network sites or just the current site
	 */
	public static function activate( $network_wide = false ) {
		do_action( 'wptelegram_activated', $network_wide );
	}

}
