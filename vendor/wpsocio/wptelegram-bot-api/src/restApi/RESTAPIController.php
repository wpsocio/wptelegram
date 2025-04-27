<?php
/**
 * Plugin bot API endpoint for WordPress REST API.
 *
 * @link       https://wpsocio.com
 * @since      1.0.0
 *
 * @package    WPTelegram\BotAPI
 * @subpackage WPTelegram\BotAPI\restApi
 */

namespace WPTelegram\BotAPI\restApi;

use WP_REST_Request;
use WP_REST_Response;
use WPTelegram\BotAPI\API;

/**
 * Class to handle the bot API endpoint.
 *
 * @since 1.0.0
 *
 * @package    WPTelegram\BotAPI
 * @subpackage WPTelegram\BotAPI\restApi
 * @author     WPTelegram team
 */
class RESTAPIController extends RESTBaseController {

	/**
	 * The base of this controller's route.
	 *
	 * @var string
	 */
	const REST_BASE = '/(?P<api_method>[a-zA-Z]+)';

	/**
	 * Whether the dependencies have been initiated.
	 *
	 * @since 1.0.0
	 * @var   bool $initiated Whether the dependencies have been initiated.
	 */
	private static $initiated = false;

	/**
	 * Initialize the REST routes.
	 *
	 * @return void
	 */
	public static function init() {
		if ( self::$initiated ) {
			return;
		}
		self::$initiated = true;

		$controller = new self();
		$controller->register_routes();
	}

	/**
	 * Register the routes.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_routes() {

		register_rest_route(
			self::REST_NAMESPACE,
			self::REST_BASE,
			[
				[
					'methods'             => 'GET, POST',
					'callback'            => [ $this, 'handle_request' ],
					'permission_callback' => [ $this, 'permissions_for_request' ],
					'args'                => self::get_test_params(),
				],
			]
		);
	}

	/**
	 * Check request permissions.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request WP REST API request.
	 *
	 * @return boolean
	 */
	public function permissions_for_request( WP_REST_Request $request ) {
		$permission = current_user_can( 'manage_options' );

		return apply_filters( 'wptelegram_bot_api_rest_permission', $permission, $request );
	}

	/**
	 * Handle the request.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request WP REST API request.
	 *
	 * @return WP_REST_Response The response.
	 */
	public function handle_request( WP_REST_Request $request ) {

		$bot_token  = $request->get_param( 'bot_token' );
		$api_method = $request->get_param( 'api_method' );
		$api_params = $request->get_param( 'api_params' );

		$body = [];
		$code = 200;

		$bot_api = new API( $bot_token );

		if ( empty( $api_params ) ) {
			$api_params = [];
		}

		$res = call_user_func( [ $bot_api, $api_method ], $api_params );

		if ( is_wp_error( $res ) ) {

			$body = [
				'ok'          => false,
				'error_code'  => 500,
				'description' => $res->get_error_code() . ' - ' . $res->get_error_message(),
			];
			$code = $body['error_code'];

		} else {

			$body = $res->get_decoded_body();
			// When using proxy, error_code may be in body.
			$code = ! empty( $body['error_code'] ) ? $body['error_code'] : $res->get_response_code();
		}

		return new WP_REST_Response( $body, $code );
	}

	/**
	 * Retrieves the query params for the settings.
	 *
	 * @since 1.0.0
	 *
	 * @return array Query parameters for the settings.
	 */
	public static function get_test_params() {
		return [
			'bot_token'  => [
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => [ __CLASS__, 'validate_param' ],
			],
			'api_params' => [
				'type'              => 'object',
				'sanitize_callback' => [ __CLASS__, 'sanitize_params' ],
				'validate_callback' => 'rest_validate_request_arg',
			],
		];
	}

	/**
	 * Validate params.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed           $value   Value of the param.
	 * @param WP_REST_Request $request WP REST API request.
	 * @param string          $key     Param key.
	 *
	 * @return boolean
	 */
	public static function validate_param( $value, WP_REST_Request $request, string $key ) {
		switch ( $key ) {
			case 'bot_token':
				$pattern = API::BOT_TOKEN_REGEX;
				break;
		}

		return (bool) preg_match( $pattern, $value );
	}

	/**
	 * Sanitize params.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed           $value   Value of the param.
	 * @param WP_REST_Request $request WP REST API request.
	 * @param string          $key     Param key.
	 *
	 * @return mixed
	 */
	public static function sanitize_params( $value, WP_REST_Request $request, string $key ) {
		$safe_value = self::sanitize_input( $value );

		return apply_filters( 'wptelegram_bot_api_rest_sanitize_params', $safe_value, $value, $request, $key );
	}

	/**
	 * Sanitize params.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $input Value of the param.
	 *
	 * @return mixed
	 */
	public static function sanitize_input( $input ) {
		$raw_input = $input;
		if ( is_array( $input ) ) {
			foreach ( $input as $key => $value ) {
				$input[ sanitize_text_field( $key ) ] = self::sanitize_input( $value );
			}
		} else {
			$input = sanitize_text_field( $input );
		}

		return apply_filters( 'wptelegram_bot_api_rest_sanitize_input', $input, $raw_input );
	}
}
