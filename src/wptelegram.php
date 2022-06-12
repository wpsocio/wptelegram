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
 * Version:           3.1.9
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

define( 'WPTELEGRAM_VER', '3.1.9' );

defined( 'WPTELEGRAM_BASENAME' ) || define( 'WPTELEGRAM_BASENAME', plugin_basename( __FILE__ ) );

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

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wptelegram-activator.php
 */
function activate_wptelegram() {
	\WPTelegram\Core\includes\Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wptelegram-deactivator.php
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

// Fire.
WPTG()->init();

define( 'WPTELEGRAM_LOADED', true );

