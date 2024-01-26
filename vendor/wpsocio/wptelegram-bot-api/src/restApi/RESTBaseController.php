<?php
/**
 * WP REST API functionality of the plugin.
 *
 * @link       https://wpsocio.com
 * @since      1.0.0
 *
 * @package    WPTelegram\BotAPI
 * @subpackage WPTelegram\BotAPI\restApi
 */

namespace WPTelegram\BotAPI\restApi;

/**
 * Base class for all the endpoints.
 *
 * @since 1.0.0
 *
 * @package    WPTelegram\BotAPI
 * @subpackage WPTelegram\BotAPI\restApi
 * @author     WPTelegram team
 */
abstract class RESTBaseController extends \WP_REST_Controller {

	/**
	 * The namespace of this controller's route.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	const REST_NAMESPACE = 'wptelegram-bot/v1';

	/**
	 * The base of this controller's route.
	 *
	 * @var string
	 */
	const REST_BASE = '';
}
