<?php
/**
 * The main plugin file.
 *
 * @link              https://t.me/manzoorwanijk
 * @since             1.0.0
 * @package           WPTelegram
 *
 * @wordpress-plugin
 * Plugin Name:       WP Telegram Dev
 * Plugin URI:        https://t.me/WPTelegram
 * Description:       Development Environment for WP Telegram. Versioned high to avoid auto update.
 * Version:           999.999.999
 * Author:            Manzoor Wani
 * Author URI:        https://t.me/manzoorwanijk
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wptelegram
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! defined( 'WPTELEGRAM_DEV' ) ) {
	define( 'WPTELEGRAM_DEV', true );
}

require plugin_dir_path( __FILE__ ) . 'src/wptelegram.php';

register_activation_hook( __FILE__, 'activate_wptelegram' );
register_deactivation_hook( __FILE__, 'deactivate_wptelegram' );
