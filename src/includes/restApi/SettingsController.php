<?php
/**
 * Plugin settings endpoint for WordPress REST API.
 *
 * @link       https://t.me/manzoorwanijk
 * @since      1.7.0
 *
 * @package    WPTelegram\Core
 * @subpackage WPTelegram\Core\includes
 */

namespace WPTelegram\Core\includes\restApi;

use WPTelegram\Core\includes\Utils;
use WPTelegram\BotAPI\API;
use WP_REST_Request;
use WP_REST_Server;

/**
 * Class to handle the settings endpoint.
 *
 * @since 1.7.0
 *
 * @package    WPTelegram\Core
 * @subpackage WPTelegram\Core\includes
 * @author     Manzoor Wani <@manzoorwanijk>
 */
class SettingsController extends RESTController {

	/**
	 * Pattern to match Telegram username.
	 *
	 * @var string Patern.
	 * @since x.y.x
	 */
	const TG_USERNAME_PATTERN = '[a-zA-Z][a-zA-Z0-9_]{3,30}[a-zA-Z0-9]';

	/**
	 * The base of this controller's route.
	 *
	 * @var string
	 */
	const REST_BASE = '/settings';

	/**
	 * The plugin settings/options.
	 *
	 * @var string
	 */
	protected $settings;

	/**
	 * Constructor
	 *
	 * @since 1.7.0
	 */
	public function __construct() {
		$this->settings = WPTG()->options();
	}

	/**
	 * Register the routes for the objects of the controller.
	 *
	 * @since 1.7.0
	 */
	public function register_routes() {

		register_rest_route(
			self::NAMESPACE,
			self::REST_BASE,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_settings' ),
					'permission_callback' => array( $this, 'settings_permissions' ),
					'args'                => self::get_settings_params( 'view' ),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'update_settings' ),
					'permission_callback' => array( $this, 'settings_permissions' ),
					'args'                => self::get_settings_params( 'edit' ),
				),
			)
		);
	}

	/**
	 * Check request permissions.
	 *
	 * @since 1.7.0
	 *
	 * @return bool
	 */
	public function settings_permissions() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Get the default values for settings.
	 *
	 * @return array
	 */
	public static function get_default_values() {
		return array(
			'bot_token'    => '',
			'bot_username' => '',
			'p2tg'         => array(
				// flag.
				'active'                   => false,
				// Destination.
				'channels'                 => array(),
				// Rules.
				'send_when'                => array( 'new' ),
				'post_types'               => array( 'post' ),
				'rules'                    => array(),
				// Message.
				'message_template'         => '{post_title}' . PHP_EOL . PHP_EOL . '{post_excerpt}' . PHP_EOL . PHP_EOL . '{full_url}',
				'excerpt_source'           => 'post_content',
				'excerpt_length'           => 55,
				'excerpt_preserve_eol'     => true,
				// Image.
				'send_featured_image'      => true,
				'image_position'           => 'before',
				'single_message'           => true,
				// Formatting.
				'cats_as_tags'             => false,
				'parse_mode'               => 'none',
				'disable_web_page_preview' => false,
				// Inline button.
				'inline_url_button'        => false,
				'inline_button_text'       => sprintf( '🔗 %s', __( 'View Post', 'wptelegram' ) ),
				'inline_button_url'        => '{full_url}',
				// Misc.
				'plugin_posts'             => false,
				'post_edit_switch'         => true,
				'delay'                    => 0.5,
				'disable_notification'     => false,
			),
			'notify'       => array(
				'active'             => false,
				'watch_emails'       => get_option( 'admin_email' ),
				'chat_ids'           => array(),
				'user_notifications' => false,
				'message_template'   => '🔔‌<b>{email_subject}</b>🔔' . PHP_EOL . PHP_EOL . '{email_message}',
				'parse_mode'         => 'HTML',
			),
			'proxy'        => array(
				'active'       => false,
				'proxy_method' => 'google_script',
				'proxy_type'   => 'CURLPROXY_HTTP',
			),
			'advanced'     => array(
				'send_files_by_url' => true,
				'enable_logs'       => array(),
				'clean_uninstall'   => true,
			),
		);
	}

	/**
	 * Get the default settings.
	 *
	 * @return array
	 */
	public static function get_default_settings() {

		$settings = WPTG()->options()->get_data();

		// If we have something saved.
		if ( empty( $settings ) ) {
			$settings = self::get_default_values();
		}

		return $settings;
	}

	/**
	 * Get settings via API.
	 *
	 * @since 1.7.0
	 */
	public function get_settings() {
		return rest_ensure_response( self::get_default_settings() );
	}

	/**
	 * Update settings.
	 *
	 * @since 1.7.0
	 *
	 * @param WP_REST_Request $request WP REST API request.
	 */
	public function update_settings( WP_REST_Request $request ) {

		$settings = WPTG()->options()->get_data();

		foreach ( self::get_settings_params() as $key => $args ) {
			$value = $request->get_param( $key );

			if ( null !== $value || isset( $args['default'] ) ) {

				$settings[ $key ] = null === $value ? $args['default'] : $value;
			}
		}

		WPTG()->options()->set_data( $settings )->update_data();

		return rest_ensure_response( $settings );
	}

	/**
	 * Retrieves the query params for the settings.
	 *
	 * @since 1.7.0
	 *
	 * @param string $context The context for the values.
	 * @return array Query parameters for the settings.
	 */
	public static function get_settings_params( $context = 'edit' ) {

		return array(
			'bot_token'    => array(
				'type'              => 'string',
				'required'          => ( 'edit' === $context ),
				'pattern'           => Utils::enhance_regex( API::BOT_TOKEN_PATTERN, true ),
				'sanitize_callback' => 'sanitize_text_field',
			),
			'bot_username' => array(
				'type'              => 'string',
				'pattern'           => Utils::enhance_regex( self::TG_USERNAME_PATTERN, true ),
				'sanitize_callback' => 'sanitize_text_field',
			),
			'p2tg'         => array(
				'type'              => 'object',
				'sanitize_callback' => array( __CLASS__, 'sanitize_param' ),
				'properties'        => array(
					'active'                   => array(
						'type' => 'boolean',
					),
					'channels'                 => array(
						'type'  => 'array',
						'items' => array(
							'type' => 'string',
						),
					),
					'send_when'                => array(
						'type'  => 'array',
						'items' => array(
							'type' => 'string',
							'enum' => array( 'new', 'existing' ),
						),
					),
					'post_types'               => array(
						'type'  => 'array',
						'items' => array(
							'type' => 'string',
						),
					),
					'rules'                    => array(
						'type'  => 'array',
						'items' => array(
							'type'  => 'array',
							'items' => array(
								'type'       => 'object',
								'properties' => array(
									'param'    => array(
										'type' => 'string',
									),
									'operator' => array(
										'type' => 'string',
										'enum' => array( 'in', 'not_in' ),
									),
									'values'   => array(
										'type'  => 'array',
										'items' => array(
											'type'       => 'object',
											'properties' => array(
												'value' => array(
													'type' => 'string',
												),
												'label' => array(
													'type' => 'string',
												),
											),
										),
									),
								),
							),
						),
					),
					'message_template'         => array(
						'type' => 'string',
					),
					'excerpt_source'           => array(
						'type' => 'string',
						'enum' => array( 'post_content', 'before_more', 'post_excerpt' ),
					),
					'excerpt_length'           => array(
						'type'    => 'integer',
						'minimum' => 1,
						'maximum' => 300,
					),
					'excerpt_preserve_eol'     => array(
						'type' => 'boolean',
					),
					'send_featured_image'      => array(
						'type' => 'boolean',
					),
					'image_position'           => array(
						'type' => 'string',
						'enum' => array( 'before', 'after' ),
					),
					'single_message'           => array(
						'type' => 'boolean',
					),
					'cats_as_tags'             => array(
						'type' => 'boolean',
					),
					'parse_mode'               => array(
						'type' => 'string',
						'enum' => array( 'none', 'Markdown', 'HTML' ),
					),
					'disable_web_page_preview' => array(
						'type' => 'boolean',
					),
					'inline_url_button'        => array(
						'type' => 'boolean',
					),
					'inline_button_text'       => array(
						'type' => 'string',
					),
					'inline_button_url'        => array(
						'type' => 'string',
					),
					'plugin_posts'             => array(
						'type' => 'boolean',
					),
					'post_edit_switch'         => array(
						'type' => 'boolean',
					),
					'delay'                    => array(
						'type'    => 'number',
						'minimum' => 0,
					),
					'disable_notification'     => array(
						'type' => 'boolean',
					),
				),
			),
			'notify'       => array(
				'type'              => 'object',
				'sanitize_callback' => array( __CLASS__, 'sanitize_param' ),
				'properties'        => array(
					'active'             => array(
						'type' => 'boolean',
					),
					'watch_emails'       => array(
						'type' => 'string',
					),
					'chat_ids'           => array(
						'type'  => 'array',
						'items' => array(
							'type' => 'string',
						),
					),
					'user_notifications' => array(
						'type' => 'boolean',
					),
					'message_template'   => array(
						'type' => 'string',
					),
					'parse_mode'         => array(
						'type' => 'string',
						'enum' => array( 'none', 'Markdown', 'HTML' ),
					),
				),
			),
			'proxy'        => array(
				'type'              => 'object',
				'sanitize_callback' => array( __CLASS__, 'sanitize_param' ),
				'properties'        => array(
					'active'            => array(
						'type' => 'boolean',
					),
					'proxy_method'      => array(
						'type' => 'string',
						'enum' => array( 'google_script', 'php_proxy' ),
					),
					'google_script_url' => array(
						'type'   => 'string',
						'format' => 'url',
					),
					'proxy_host'        => array(
						'type' => 'string',
					),
					'proxy_port'        => array(
						'type' => 'string',
					),
					'proxy_type'        => array(
						'type' => 'string',
						'enum' => array(
							'CURLPROXY_HTTP',
							'CURLPROXY_SOCKS4',
							'CURLPROXY_SOCKS4A',
							'CURLPROXY_SOCKS5',
							'CURLPROXY_SOCKS5_HOSTNAME',
						),
					),
					'proxy_username'    => array(
						'type' => 'string',
					),
					'proxy_password'    => array(
						'type' => 'string',
					),
				),
			),
			'advanced'     => array(
				'type'              => 'object',
				'sanitize_callback' => array( __CLASS__, 'sanitize_param' ),
				'properties'        => array(
					'send_files_by_url' => array(
						'type' => 'boolean',
					),
					'enable_logs'       => array(
						'type'  => 'array',
						'items' => array(
							'type' => 'string',
							'enum' => array( 'bot_api', 'p2tg' ),
						),
					),
					'clean_uninstall'   => array(
						'type' => 'boolean',
					),
				),
			),
		);
	}

	/**
	 * Sanitize the request param.
	 *
	 * @since x.y.z
	 *
	 * @param mixed           $value   Value of the param.
	 * @param WP_REST_Request $request WP REST API request.
	 * @param string          $param     The param key.
	 */
	public static function sanitize_param( $value, WP_REST_Request $request, $param ) {
		// First lets make the value safer.
		$safe_value = Utils::sanitize( $value );

		if ( in_array( $param, array( 'p2tg', 'notify' ), true ) ) {
			// Sanitize the template separately.
			$safe_value['message_template'] = Utils::sanitize_message_template( $value['message_template'] );
		}

		return $safe_value;
	}
}
