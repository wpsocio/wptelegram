<?php
/**
 * Response class.
 *
 * @link       https://wpsocio.com
 * @since      1.0.0
 *
 * @package    WPTelegram\BotAPI
 * @subpackage WPTelegram\BotAPI
 */

namespace WPTelegram\BotAPI;

if ( ! class_exists( __NAMESPACE__ . '\Response', false ) ) :
	/**
	 * Response class.
	 *
	 * @since  1.0.1
	 *
	 * @category  WordPress_Plugin Addon
	 * @package   WPTelegram\BotAPI
	 * @author    WPTelegram team
	 * @license   GPL-2.0+
	 * @link      https://t.me/WPTelegram
	 */
	class Response {
		/**
		 * Whether the response is valid JSON.
		 *
		 * @since  1.0.0
		 *
		 * @var bool
		 */
		protected $valid_json;

		/**
		 * The HTTP response code from API.
		 *
		 * @since  1.0.0
		 *
		 * @var null|int
		 */
		protected $response_code;

		/**
		 * The HTTP response message from API.
		 *
		 * @since  1.0.0
		 *
		 * @var null|int
		 */
		protected $response_message;

		/**
		 * The headers returned from API request.
		 *
		 * @since  1.0.0
		 *
		 * @var array
		 */
		protected $headers = null;

		/**
		 * The raw body of the response from API request.
		 *
		 * @since  1.0.0
		 *
		 * @var string
		 */
		protected $body = null;

		/**
		 * The decoded body of the API response.
		 *
		 * @since  1.0.0
		 *
		 * @var array
		 */
		protected $decoded_body = [];

		/**
		 * API Endpoint used to make the request.
		 *
		 * @since  1.0.0
		 *
		 * @var string
		 */
		protected $api_method;

		/**
		 * The original request that returned this response.
		 *
		 * @since  1.0.0
		 *
		 * @var Request
		 */
		protected $request;

		/**
		 * The original response from wp_remote_post.
		 *
		 * @since   1.0.0
		 *
		 * @var array
		 */
		protected $raw_response;

		/**
		 * Gets the relevant data from the client.
		 *
		 * @since   1.0.0
		 *
		 * @param Request $request      The original request that returned this response.
		 * @param mixed   $raw_response The original response from wp_remote_post.
		 */
		public function __construct( Request $request, $raw_response ) {

			$this->set_properties( $raw_response );

			$this->valid_json = $this->decode_body();

			$this->request      = $request;
			$this->raw_response = $raw_response;
			$this->api_method   = (string) $request->get_api_method();
		}

		/**
		 * Sets the class properties
		 *
		 * @since   1.0.0
		 *
		 * @param mixed $raw_response The original response from wp_remote_post.
		 * @return void
		 */
		public function set_properties( $raw_response ) {
			$properties = [
				'response_code',
				'response_message',
				'body',
				'headers',
			];
			foreach ( $properties as $property ) {
				$this->$property = call_user_func( 'wp_remote_retrieve_' . $property, $raw_response );
			}
		}

		/**
		 * Return the original request that returned this response.
		 *
		 * @since   1.0.0
		 *
		 * @return Request
		 */
		public function get_request() {
			return $this->request;
		}

		/**
		 * Gets the original HTTP response.
		 *
		 * @since   1.0.0
		 *
		 * @return array
		 */
		public function get_raw_response() {
			return $this->raw_response;
		}

		/**
		 * Gets the HTTP response code.
		 *
		 * @since   1.0.0
		 *
		 * @return null|integer
		 */
		public function get_response_code() {
			return $this->response_code;
		}

		/**
		 * Returns the value of valid_json
		 *
		 * @since   1.0.0
		 *
		 * @return boolean
		 */
		public function is_valid_json() {
			return $this->valid_json;
		}

		/**
		 * Gets the HTTP response message.
		 *
		 * @since   1.0.0
		 *
		 * @return null|string
		 */
		public function get_response_message() {
			return $this->response_message;
		}

		/**
		 * Gets the Request Endpoint used to get the response.
		 *
		 * @since   1.0.0
		 *
		 * @return string
		 */
		public function get_api_method() {
			return $this->api_method;
		}

		/**
		 * Return the bot access token that was used for this request.
		 *
		 * @since   1.0.0
		 *
		 * @return string|null
		 */
		public function get_bot_token() {
			return $this->request->get_bot_token();
		}

		/**
		 * Return the HTTP headers for this response.
		 *
		 * @since   1.0.0
		 *
		 * @return array
		 */
		public function get_headers() {
			return $this->headers;
		}

		/**
		 * Return the raw body response.
		 *
		 * @since   1.0.0
		 *
		 * @return string
		 */
		public function get_body() {
			return $this->body;
		}

		/**
		 * Return the decoded body response.
		 *
		 * @since   1.0.0
		 *
		 * @return array
		 */
		public function get_decoded_body() {
			return $this->decoded_body;
		}

		/**
		 * Helper function to return the payload of a successful response.
		 *
		 * @since   1.0.0
		 *
		 * @return mixed
		 */
		public function get_result() {
			$body = $this->get_decoded_body();

			return isset( $body['result'] ) ? $body['result'] : null;
		}

		/**
		 * Converts raw API response to proper decoded response.
		 *
		 * @since   1.0.0
		 *
		 * @return boolean
		 */
		public function decode_body() {
			$this->decoded_body = json_decode( $this->body, true );
			// check for PHP < 5.3.
			if ( function_exists( 'json_last_error' ) && defined( 'JSON_ERROR_NONE' ) ) {
				return ( json_last_error() === JSON_ERROR_NONE );
			}
			return true;
		}
	}
endif;
