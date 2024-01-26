<?php
/**
 * Client class.
 *
 * @link       https://wpsocio.com
 * @since      1.0.0
 *
 * @package    WPTelegram\BotAPI
 * @subpackage WPTelegram\BotAPI
 */

namespace WPTelegram\BotAPI;

use WP_Error;

if ( ! class_exists( __NAMESPACE__ . '\Client', false ) ) :
	/**
	 * Client class.
	 *
	 * @since  1.0.1
	 *
	 * @category  WordPress_Plugin Addon
	 * @package   WPTelegram\BotAPI
	 * @author    WPTelegram team
	 * @license   GPL-2.0+
	 * @link      https://t.me/WPTelegram
	 */
	class Client {
		/**
		 * The API base URL.
		 *
		 * @since 1.0.0
		 * @var string Telegram Bot API URL.
		 */
		const BASE_URL = 'https://api.telegram.org/bot';

		/**
		 * Returns the base URL of the Bot API.
		 *
		 * @since  1.0.0
		 *
		 * @return string
		 */
		public function get_base_url() {
			return apply_filters( 'wptelegram_bot_api_base_url', self::BASE_URL );
		}

		/**
		 * Prepares the API request for sending to the client
		 *
		 * @since  1.0.0
		 *
		 * @param Request $request The request instance.
		 *
		 * @return array
		 */
		public function prepare_request( Request $request ) {
			$url = $this->get_base_url() . $request->get_bot_token() . '/' . $request->get_api_method();

			return apply_filters(
				'wptelegram_bot_api_prepare_request',
				[
					$url,
					$request->get_params(),
				],
				$request
			);
		}

		/**
		 * Send an API request and process the result.
		 *
		 * @since  1.0.0
		 *
		 * @param Request $request The request instance.
		 *
		 * @return WP_Error|Response
		 */
		public function sendRequest( Request $request ) {
			list( $url, $params ) = $this->prepare_request( $request );

			$args = [
				'timeout' => 20, // seconds.
				'headers' => [ 'wptelegram_bot' => true ],
				'body'    => $params,
			];

			foreach ( $args as $argument => $value ) {
				$args[ $argument ] = apply_filters( "wptelegram_bot_api_request_arg_{$argument}", $value, $request );
			}

			$url = apply_filters( 'wptelegram_bot_api_request_url', $url );

			$args = apply_filters( 'wptelegram_bot_api_remote_post_args', $args, $request );

			$remote_request = empty( $params ) ? 'wp_remote_get' : 'wp_remote_post';

			// send the request.
			$raw_response = $remote_request( $url, $args );

			if ( ! is_wp_error( $raw_response ) ) {
				return $this->get_response( $request, $raw_response );
			}

			return $raw_response;
		}

		/**
		 * Creates response object.
		 *
		 * @since  1.0.0
		 *
		 * @param Request $request      The request instance.
		 * @param mixed   $raw_response The response.
		 *
		 * @return Response
		 */
		protected function get_response( Request $request, $raw_response ) {
			return new Response( $request, $raw_response );
		}
	}
endif;
