<?php
/**
 * Plugin settings endpoint for WordPress REST API.
 *
 * @link       https://wpsocio.com
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
 * @author     WP Socio
 */
class SettingsController extends RESTController {

	/**
	 * Pattern to match Telegram username.
	 *
	 * @var string Pattern.
	 * @since 3.0.0
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
			self::REST_NAMESPACE,
			self::REST_BASE,
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_settings' ],
					'permission_callback' => [ $this, 'settings_permissions' ],
					'args'                => self::get_settings_params( 'view' ),
				],
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'update_settings' ],
					'permission_callback' => [ $this, 'settings_permissions' ],
					'args'                => self::get_settings_params( 'edit' ),
				],
			]
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
		$is_wp_cron_disabled = defined( 'DISABLE_WP_CRON' ) && constant( 'DISABLE_WP_CRON' );

		return [
			'bot_token'    => '',
			'bot_username' => '',
			'p2tg'         => [
				// flag.
				'active'                  => false,
				// Destination.
				'channels'                => [],
				// Rules.
				'send_when'               => [ 'new' ],
				'post_types'              => [ 'post' ],
				'rules'                   => [],
				// Message.
				'message_template'        => '{post_title}' . PHP_EOL . PHP_EOL . '{post_excerpt}' . PHP_EOL . PHP_EOL . '{full_url}',
				'excerpt_source'          => 'post_content',
				'excerpt_length'          => 55,
				'excerpt_preserve_eol'    => true,
				// Image.
				'send_featured_image'     => true,
				'image_position'          => 'before',
				'single_message'          => true,
				// Formatting.
				'cats_as_tags'            => false,
				'parse_mode'              => 'none',
				'link_preview_disabled'   => false,
				'link_preview_url'        => '',
				'link_preview_above_text' => false,
				// Inline button.
				'inline_url_button'       => false,
				'inline_button_text'      => sprintf( '🔗 %s', __( 'View Post', 'wptelegram' ) ),
				'inline_button_url'       => '{full_url}',
				// Misc.
				'plugin_posts'            => false,
				'post_edit_switch'        => true,
				'delay'                   => $is_wp_cron_disabled ? 0 : 0.5,
				'disable_notification'    => false,
				'protect_content'         => false,
			],
			'notify'       => [
				'active'             => false,
				'watch_emails'       => get_option( 'admin_email' ),
				'chat_ids'           => [],
				'user_notifications' => false,
				'message_template'   => '🔔‌<b>{email_subject}</b>🔔' . PHP_EOL . PHP_EOL . '{email_message}',
				'parse_mode'         => 'HTML',
			],
			'proxy'        => [
				'active'       => false,
				'proxy_method' => 'cf_worker',
				'proxy_type'   => 'CURLPROXY_HTTP',
			],
			'advanced'     => [
				'send_files_by_url' => true,
				'enable_logs'       => [],
				'clean_uninstall'   => true,
			],
		];
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
		$params = array_keys( self::get_default_values() );

		$settings = WPTG()->options()->get_data();

		foreach ( $params as $key ) {
			$settings[ $key ] = $request->get_param( $key );
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

		return [
			'bot_token'    => [
				'type'              => 'string',
				'required'          => ( 'edit' === $context ),
				'pattern'           => Utils::enhance_regex( API::BOT_TOKEN_PATTERN, true ),
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
			],
			'bot_username' => [
				'type'              => 'string',
				'pattern'           => Utils::enhance_regex( self::TG_USERNAME_PATTERN, true ),
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
			],
			'p2tg'         => [
				'type'              => 'object',
				'sanitize_callback' => [ __CLASS__, 'sanitize_param' ],
				'validate_callback' => 'rest_validate_request_arg',
				'properties'        => [
					'active'                  => [
						'type' => 'boolean',
					],
					'channels'                => [
						'type'  => 'array',
						'items' => [
							'type' => 'string',
						],
					],
					'send_when'               => [
						'type'  => 'array',
						'items' => [
							'type' => 'string',
							'enum' => [ 'new', 'existing' ],
						],
					],
					'post_types'              => [
						'type'  => 'array',
						'items' => [
							'type' => 'string',
						],
					],
					'rules'                   => [
						'type'  => 'array',
						'items' => [
							'type'  => 'array',
							'items' => [
								'type'       => 'object',
								'properties' => [
									'param'    => [
										'type' => 'string',
									],
									'operator' => [
										'type' => 'string',
										'enum' => [ 'in', 'not_in' ],
									],
									'values'   => [
										'type'  => 'array',
										'items' => [
											'type'       => 'object',
											'properties' => [
												'value' => [
													'type' => 'string',
												],
												'label' => [
													'type' => 'string',
												],
											],
										],
									],
								],
							],
						],
					],
					'message_template'        => [
						'type' => 'string',
					],
					'excerpt_source'          => [
						'type' => 'string',
						'enum' => [ 'post_content', 'before_more', 'post_excerpt' ],
					],
					'excerpt_length'          => [
						'type'    => 'integer',
						'minimum' => 1,
						'maximum' => 300,
					],
					'excerpt_preserve_eol'    => [
						'type' => 'boolean',
					],
					'send_featured_image'     => [
						'type' => 'boolean',
					],
					'image_position'          => [
						'type' => 'string',
						'enum' => [ 'before', 'after' ],
					],
					'single_message'          => [
						'type' => 'boolean',
					],
					'cats_as_tags'            => [
						'type' => 'boolean',
					],
					'parse_mode'              => [
						'type' => 'string',
						'enum' => [ 'none', 'HTML' ],
					],
					'link_preview_disabled'   => [
						'type' => 'boolean',
					],
					'link_preview_url'        => [
						'type' => 'string',
					],
					'link_preview_above_text' => [
						'type' => 'boolean',
					],
					'inline_url_button'       => [
						'type' => 'boolean',
					],
					'inline_button_text'      => [
						'type' => 'string',
					],
					'inline_button_url'       => [
						'type' => 'string',
					],
					'plugin_posts'            => [
						'type' => 'boolean',
					],
					'post_edit_switch'        => [
						'type' => 'boolean',
					],
					'delay'                   => [
						'type'    => 'number',
						'minimum' => 0,
					],
					'disable_notification'    => [
						'type' => 'boolean',
					],
					'protect_content'         => [
						'type' => 'boolean',
					],
				],
			],
			'notify'       => [
				'type'              => 'object',
				'sanitize_callback' => [ __CLASS__, 'sanitize_param' ],
				'validate_callback' => 'rest_validate_request_arg',
				'properties'        => [
					'active'             => [
						'type' => 'boolean',
					],
					'watch_emails'       => [
						'type' => 'string',
					],
					'chat_ids'           => [
						'type'  => 'array',
						'items' => [
							'type' => 'string',
						],
					],
					'user_notifications' => [
						'type' => 'boolean',
					],
					'message_template'   => [
						'type' => 'string',
					],
					'parse_mode'         => [
						'type' => 'string',
						'enum' => [ 'none', 'HTML' ],
					],
				],
			],
			'proxy'        => [
				'type'              => 'object',
				'sanitize_callback' => [ __CLASS__, 'sanitize_param' ],
				'validate_callback' => 'rest_validate_request_arg',
				'properties'        => [
					'active'            => [
						'type' => 'boolean',
					],
					'proxy_method'      => [
						'type' => 'string',
						'enum' => [ 'cf_worker', 'google_script', 'php_proxy' ],
					],
					'cf_worker_url'     => [
						'type'   => 'string',
						'format' => 'url',
					],
					'google_script_url' => [
						'type'   => 'string',
						'format' => 'url',
					],
					'proxy_host'        => [
						'type' => 'string',
					],
					'proxy_port'        => [
						'type' => 'string',
					],
					'proxy_type'        => [
						'type' => 'string',
						'enum' => [
							'CURLPROXY_HTTP',
							'CURLPROXY_SOCKS4',
							'CURLPROXY_SOCKS4A',
							'CURLPROXY_SOCKS5',
							'CURLPROXY_SOCKS5_HOSTNAME',
						],
					],
					'proxy_username'    => [
						'type' => 'string',
					],
					'proxy_password'    => [
						'type' => 'string',
					],
				],
			],
			'advanced'     => [
				'type'              => 'object',
				'sanitize_callback' => [ __CLASS__, 'sanitize_param' ],
				'validate_callback' => 'rest_validate_request_arg',
				'properties'        => [
					'send_files_by_url' => [
						'type' => 'boolean',
					],
					'enable_logs'       => [
						'type'  => 'array',
						'items' => [
							'type' => 'string',
							'enum' => [ 'bot_api', 'p2tg' ],
						],
					],
					'clean_uninstall'   => [
						'type' => 'boolean',
					],
				],
			],
		];
	}

	/**
	 * Sanitize the request param.
	 *
	 * @since 3.0.0
	 *
	 * @param mixed           $value   Value of the param.
	 * @param WP_REST_Request $request WP REST API request.
	 * @param string          $param     The param key.
	 */
	public static function sanitize_param( $value, WP_REST_Request $request, $param ) {
		// First lets make the value safer.
		$safe_value = Utils::sanitize( $value );

		if ( in_array( $param, [ 'p2tg', 'notify' ], true ) ) {
			// Sanitize the template separately.
			$safe_value['message_template'] = Utils::sanitize_message_template( $value['message_template'] );
		}

		// Remove useless rules.
		if ( 'p2tg' === $param && ! empty( $safe_value['rules'] ) ) {
			$rules = [];
			foreach ( (array) $safe_value['rules'] as  $rule_group ) {
				$group = [];

				if ( is_array( $rule_group ) ) {
					foreach ( $rule_group as $rule ) {
						// remove empty values.
						$rule = array_filter( (array) $rule );
						if ( empty( $rule['param'] ) || empty( $rule['operator'] ) || empty( $rule['values'] ) ) {
							continue;
						}
						$group[] = $rule;
					}
				}
				if ( ! empty( $group ) ) {
					$rules[] = $group;
				}
			}
			$safe_value['rules'] = $rules;
		}

		// Remove useless chat_ids.
		if ( 'p2tg' === $param && ! empty( $safe_value['channels'] ) ) {
			$safe_value['channels'] = array_values( array_filter( $safe_value['channels'] ) );
		}
		if ( 'notify' === $param && ! empty( $safe_value['chat_ids'] ) ) {
			$safe_value['chat_ids'] = array_values( array_filter( $safe_value['chat_ids'] ) );
		}

		return $safe_value;
	}
}
