<?php
/**
 * The main plugin file.
 *
 * @link       https://wpsocio.com
 * @since      1.0.0
 *
 * @package    WPTelegram\BotAPI
 * @subpackage WPTelegram\BotAPI
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

// Initialize REST API.
add_action( 'rest_api_init', [ \WPTelegram\BotAPI\restApi\RESTAPIController::class, 'init' ] );
