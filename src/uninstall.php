<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * @link       https://t.me/manzoorwanijk
 * @since      1.0.0
 *
 * @package    WPTelegram
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) || ! WP_UNINSTALL_PLUGIN || dirname( WP_UNINSTALL_PLUGIN ) != dirname( plugin_basename( __FILE__ ) ) ) {
	
	status_header( 404 );
	exit;
}

wptelegram_uninstall();

function wptelegram_uninstall() {

	$options = get_option( 'wptelegram' );

	if ( isset( $options['clean_uninstall'] ) && 'on' !== $options['clean_uninstall'] ) {
		return;
	}

	$uninstall_options = array(
		'wptelegram',
		'wptelegram_notify',
		'wptelegram_p2tg',
		'wptelegram_proxy',
		'wptelegram_ver',
	);

	$uninstall_options = (array) apply_filters( 'wptelegram_uninstall_options', $uninstall_options );

	foreach ( $uninstall_options as $option ) {
		delete_option( $option );
	}
}