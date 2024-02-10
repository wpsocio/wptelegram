<?php
/**
 * Request class.
 *
 * @link       https://wpsocio.com
 * @since      1.0.0
 *
 * @package    WPTelegram\BotAPI
 * @subpackage WPTelegram\BotAPI
 */

namespace WPTelegram\BotAPI;

if ( ! class_exists( __NAMESPACE__ . '\Request', false ) ) :
	/**
	 * Request class.
	 *
	 * @since  1.0.1
	 *
	 * @category  WordPress_Plugin Addon
	 * @package   WPTelegram\BotAPI
	 * @author    WPTelegram team
	 * @license   GPL-2.0+
	 * @link      https://t.me/WPTelegram
	 */
	class Request {
		/**
		 * The API token.
		 *
		 * @since  1.0.0
		 *
		 * @var string|null The bot access token to use for this request.
		 */
		protected $bot_token;

		/**
		 * The API method name.
		 *
		 * @since  1.0.5
		 *
		 * @var string The API api_method for this request.
		 */
		protected $api_method;

		/**
		 * The method params.
		 *
		 * @since  1.0.0
		 *
		 * @var array The parameters to send with this request.
		 */
		protected $params = [];

		/**
		 * Creates a new Request
		 *
		 * @param string $bot_token  The API token.
		 * @param string $api_method The API method name.
		 * @param array  $params     The method params.
		 */
		public function __construct( string $bot_token = '', string $api_method = '', array $params = [] ) {
			$this->set_bot_token( $bot_token );
			$this->set_api_method( $api_method );
			$this->set_params( $params );
		}

		/**
		 * Set the bot token for this request.
		 *
		 * @since  1.0.0
		 *
		 * @param string $bot_token The API token.
		 *
		 * @return Request
		 */
		public function set_bot_token( string $bot_token ) {
			$this->bot_token = $bot_token;

			return $this;
		}

		/**
		 * Return the bot token for this request.
		 *
		 * @since  1.0.0
		 *
		 * @return string|null
		 */
		public function get_bot_token() {
			return $this->bot_token;
		}

		/**
		 * Set the api_method for this request.
		 *
		 * @since  1.0.5
		 *
		 * @param string $api_method The API method name.
		 *
		 * @return Request The current request.
		 */
		public function set_api_method( string $api_method ) {
			$this->api_method = $api_method;
			return $this;
		}

		/**
		 * Return the API Endpoint for this request.
		 *
		 * @since  1.0.5
		 *
		 * @return string
		 */
		public function get_api_method() {
			return $this->api_method;
		}

		/**
		 * Set the params for this request.
		 *
		 * @since  1.0.0
		 *
		 * @param array $params The method params.
		 *
		 * @return Request
		 */
		public function set_params( array $params = [] ) {
			$this->params = array_merge( $this->params, $params );

			return $this;
		}

		/**
		 * Return the params for this request.
		 *
		 * @since  1.0.0
		 *
		 * @return array
		 */
		public function get_params() {
			return $this->params;
		}
	}
endif;
