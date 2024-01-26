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


$options = get_option( 'wptelegram', '' );

$options = json_decode( $options, true );

/**
 * Cleans up the stale data.
 *
 * @return void
 */
function uninstall_wptelegram() {
	if ( isset( $options['advanced']['clean_uninstall'] ) && false === $options['advanced']['clean_uninstall'] ) {
		return;
	}

	$uninstall_options = [
		'wptelegram',
		'wptelegram_ver',
	];

	$uninstall_options = (array) apply_filters( 'wptelegram_uninstall_options', $uninstall_options );

	foreach ( $uninstall_options as $option ) {
		delete_option( $option );
	}
}

uninstall_wptelegram();
