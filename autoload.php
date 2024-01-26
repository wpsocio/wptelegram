<?php
/**
 * Autoloader
 *
 * @link      https://wpsocio.com
 * @since     3.0.0
 *
 * @package WPTelegram
 */

spl_autoload_register( 'wptelegram_autoloader' );

/**
 * Autoloader.
 *
 * @param string $class_name The requested classname.
 * @return void
 */
function wptelegram_autoloader( $class_name ) {

	$namespace = 'WPTelegram\Core';

	if ( 0 !== strpos( $class_name, $namespace ) ) {
		return;
	}

	$class_name = str_replace( $namespace, '', $class_name );
	$class_name = str_replace( '\\', DIRECTORY_SEPARATOR, $class_name );

	$path = WPTELEGRAM_DIR . $class_name . '.php';

	include_once $path;
}
