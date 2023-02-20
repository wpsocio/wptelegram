<?php
/**
 * The main plugin file.
 *
 * @link              https://wpsocio.com
 * @since             1.0.0
 * @package           WPTelegram
 *
 * @wordpress-plugin
 * Plugin Name:       WP Telegram Dev
 * Plugin URI:        https://t.me/WPTelegram
 * Description:       ❌ DO NOT DELETE ❌ Development Environment for WP Telegram. Versioned high to avoid auto update.
 * Version:           999.999.999
 * Requires at least: 5.8
 * Requires PHP:      7.2
 * Author:            WP Socio
 * Author URI:        https://t.me/WPTelegram
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wptelegram
 * Domain Path:       /languages
 * Update URI:        false
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! defined( 'WPTELEGRAM_DEV' ) ) {
	define( 'WPTELEGRAM_DEV', true );
}

define( 'WPTELEGRAM_PLUGIN_MAIN_FILE', __FILE__ );

define( 'WPTELEGRAM_BASENAME', plugin_basename( WPTELEGRAM_PLUGIN_MAIN_FILE ) );

require plugin_dir_path( __FILE__ ) . 'src/wptelegram.php';

register_activation_hook( __FILE__, 'activate_wptelegram' );
register_deactivation_hook( __FILE__, 'deactivate_wptelegram' );
