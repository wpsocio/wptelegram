<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @link       https://wpsocio.com
 * @since      1.0.0
 *
 * @package    WPTelegram
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) || ! WP_UNINSTALL_PLUGIN || dirname( WP_UNINSTALL_PLUGIN ) !== dirname( plugin_basename( __FILE__ ) ) ) {

	status_header( 404 );
	exit;
}

/**
 * Cleans up the stale data.
 *
 * @return void
 */
function uninstall_wptelegram() {
	$options = get_option( 'wptelegram', '' );

	$options = json_decode( $options, true );

	if ( isset( $options['advanced']['clean_uninstall'] ) && false === $options['advanced']['clean_uninstall'] ) {
		return;
	}

	delete_option( 'wptelegram' );
	delete_option( 'wptelegram_ver' );
}

uninstall_wptelegram();
