<?php
/**
 *
 * @link              https://t.me/manzoorwanijk
 * @since             1.0.0
 * @package           WPTelegram
 *
 * @wordpress-plugin
 * Plugin Name:       WP Telegram
 * Plugin URI:        https://t.me/WPTelegram
 * Description:       Integrate your WordPress website perfectly with Telegram. Send posts automatically to Telegram when published or updated, whether to a Telegram Channel, Group, Supergroup or private chat, with full control. Get your email notifications on Telegram.
 * Version:           2.1.6
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

define( 'WPTELEGRAM_VER', '2.1.6' );

define( 'WPTELEGRAM_BASENAME', plugin_basename( __FILE__ ) );

define( 'WPTELEGRAM_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );

define( 'WPTELEGRAM_URL', untrailingslashit( plugins_url( '', __FILE__ ) ) );

define( 'WPTELEGRAM_MODULES_DIR', WPTELEGRAM_DIR . '/modules' );

define( 'WPTELEGRAM_MODULES_URL', WPTELEGRAM_URL . '/modules' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wptelegram-activator.php
 */
function activate_wptelegram() {
	require_once WPTELEGRAM_DIR . '/includes/class-wptelegram-activator.php';
	WPTelegram_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wptelegram-deactivator.php
 */
function deactivate_wptelegram() {
	require_once WPTELEGRAM_DIR . '/includes/class-wptelegram-deactivator.php';
	WPTelegram_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wptelegram' );
register_deactivation_hook( __FILE__, 'deactivate_wptelegram' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require WPTELEGRAM_DIR . '/includes/class-wptelegram.php';

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
 */
function WPTG() {

	return WPTelegram::instance();
}

// Fire
WPTG();

define( 'WPTELEGRAM_LOADED', true );

