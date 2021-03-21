<?php
/**
 * WP REST API functionality of the plugin.
 *
 * @link       https://t.me/manzoorwanijk
 * @since      3.0.0
 *
 * @package    WPTelegram\Core
 * @subpackage WPTelegram\Core\includes
 */

namespace WPTelegram\Core\includes\restApi;

/**
 * Base class for all the endpoints.
 *
 * @since 3.0.0
 *
 * @package    WPTelegram\Core
 * @subpackage WPTelegram\Core\includes
 * @author     Manzoor Wani <@manzoorwanijk>
 */
abstract class RESTController extends \WP_REST_Controller {

	/**
	 * The namespace of this controller's route.
	 *
	 * @var string
	 * @since 3.0.0
	 */
	const NAMESPACE = 'wptelegram/v1';

	/**
	 * The base of this controller's route.
	 *
	 * @var string
	 */
	const REST_BASE = '';
}
