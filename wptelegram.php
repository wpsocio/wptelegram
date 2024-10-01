<?php
/**
 * Plugin mainfile.
 *
 * @link              https://wpsocio.com
 * @since             1.0.0
 * @package           WPTelegram
 *
 * @wordpress-plugin
 * Plugin Name:       WP Telegram
 * Plugin URI:        https://t.me/WPTelegram
 * Description:       Integrate your WordPress website perfectly with Telegram. Send posts automatically to Telegram when published or updated, whether to a Telegram Channel, Group or private chat, with full control. Get your email notifications on Telegram.
 * Version:           4.1.16
 * Requires at least: 6.4
 * Requires PHP:      7.4
 * Author:            WP Socio
 * Author URI:        https://wpsocio.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wptelegram
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'WPTELEGRAM_VER', '4.1.16' );

defined( 'WPTELEGRAM_MAIN_FILE' ) || define( 'WPTELEGRAM_MAIN_FILE', __FILE__ );

defined( 'WPTELEGRAM_BASENAME' ) || define( 'WPTELEGRAM_BASENAME', plugin_basename( WPTELEGRAM_MAIN_FILE ) );

define( 'WPTELEGRAM_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );

define( 'WPTELEGRAM_URL', untrailingslashit( plugins_url( '', __FILE__ ) ) );

// Telegram user ID meta key.
if ( ! defined( 'WPTELEGRAM_USER_ID_META_KEY' ) ) {
	// Common for all WP Telegram plugins.
	define( 'WPTELEGRAM_USER_ID_META_KEY', 'wptelegram_user_id' );
}

/**
 * Include autoloader.
 */
require WPTELEGRAM_DIR . '/autoload.php';
require_once dirname( WPTELEGRAM_MAIN_FILE ) . '/vendor/autoload.php';

/**
 * The code that runs during plugin activation.
 */
function activate_wptelegram() {
	\WPTelegram\Core\includes\Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_wptelegram() {
	\WPTelegram\Core\includes\Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wptelegram' );
register_deactivation_hook( __FILE__, 'deactivate_wptelegram' );

/**
 * Begins execution of the plugin and acts as the main instance of WPTelegram.
 *
 * Returns the main instance of WPTelegram to prevent the need to use globals.
 *
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 *
 * @return \WPTelegram\Core\includes\Main
 */
function WPTG() { // phpcs:ignore
	return \WPTelegram\Core\includes\Main::instance();
}

$requirements = new WPTelegram\Core\includes\Requirements( WPTELEGRAM_MAIN_FILE );

if ( $requirements->satisfied() ) {
	// Fire.
	WPTG()->init();

	define( 'WPTELEGRAM_LOADED', true );
} else {
	add_filter( 'after_plugin_row_' . WPTELEGRAM_BASENAME, [ $requirements, 'display_requirements' ] );
}
