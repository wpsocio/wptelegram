<?php
/**
 * Provides logging capabilities for debugging purposes.
 *
 * @package    WPTelegram
 * @subpackage WPTelegram\Core\includes
 */

namespace WPTelegram\Core\includes;

use WPTelegram\BotAPI\Response;
use WPTelegram\BotAPI\API;
use WPTelegram\Core\modules\p2tg\RequestCheck;
use WPTelegram\Core\modules\p2tg\Main as P2TGMain;
use WPSocio\TelegramFormatText\Exceptions\ConverterException;
use ReflectionClass;
use WP_Filesystem_Base;
use WP_Post;

/**
 * WPTelegram_Logger class.
 */
class Logger extends BaseClass {

	/**
	 * Enabled Log types
	 *
	 * @since   1.0.0
	 * @access  private
	 * @var     array       $active_logs    The enabled logs
	 */
	private static $active_logs;

	/**
	 * Whether already hooked or not
	 *
	 * @since   1.0.0
	 * @access  private
	 * @var     array       $hooked_up  The enabled logs
	 */
	private static $hooked_up = false;

	/**
	 * Information about the processed post
	 *
	 * @since   1.0.0
	 * @access  private
	 * @var     array       $p2tg_post_info     The Post info
	 */
	private $p2tg_post_info;

	/**
	 * Set the active logs
	 *
	 * @param array $active_logs The logs to create.
	 *
	 * @return self
	 */
	public function set_active_logs( $active_logs ) {

		self::$active_logs = (array) $active_logs;

		return $this;
	}

	/**
	 * Get the active logs
	 */
	public function get_active_logs() {

		return (array) apply_filters( 'wptelegram_logger_active_logs', self::$active_logs );
	}

	/**
	 * Hook into WP Telegram to create logs
	 */
	public function hookup() {

		add_action( 'init', [ $this, 'view_log' ] );

		// avoid hooking in multiple times.
		if ( ! self::$hooked_up && ! empty( self::$active_logs ) ) {

			$this->hook_it_up();

			self::$hooked_up = true;
		}
	}

	/**
	 * Hook into WP Telegram to create logs
	 */
	protected function hook_it_up() {

		$active_logs = $this->get_active_logs();

		if ( empty( $active_logs ) ) {
			return;
		}

		foreach ( $active_logs as $log_type ) {

			$method = [ $this, "hookup_for_{$log_type}" ];

			if ( is_callable( $method ) ) {
				call_user_func( $method );
			}
		}

		add_action( 'wptelegram_prepare_content_error', [ $this, 'prepare_content_error' ], 10, 3 );
	}

	/**
	 * Get the URL for a log type.
	 *
	 * @param string $type Log type.
	 * @return string
	 */
	public static function get_log_url( $type ) {

		$url = add_query_arg(
			[
				'action' => 'wptelegram_view_log',
				'hash'   => wp_hash( 'log' ),
				'type'   => $type,
			],
			site_url()
		);

		return apply_filters( 'wptelegram_logger_log_url', $url, $type );
	}

	/**
	 * View logs
	 */
	public function view_log() {

		// phpcs:ignore
		if ( isset( $_GET['action'], $_GET['hash'], $_GET['type'] ) && 'wptelegram_view_log' === $_GET['action'] && isset( $_GET['hash'] ) ) {
			$hash = sanitize_text_field( wp_unslash( $_GET['hash'] ) ); // phpcs:ignore
			$type = sanitize_text_field( wp_unslash( $_GET['type'] ) ); // phpcs:ignore

			if ( ! empty( $hash ) && ! empty( $type ) ) {

				$file_path = self::get_log_file_path( $type, $hash );

				if ( self::wp_filesystem()->exists( $file_path ) ) {
					$contents = self::wp_filesystem()->get_contents( $file_path );
				} else {
					$contents = 'Log file not found!';
				}

				header( 'Content-Type: text/plain' );

				exit( $contents ); // phpcs:ignore
			}
		}
	}

	/**
	 * Hook for WPTelegram_Bot_API log
	 */
	protected function hookup_for_bot_api() {

		add_action( 'wptelegram_bot_api_debug', [ $this, 'add_bot_api_debug' ], 10, 2 );
	}

	/**
	 * Hook for Post to Telegram log
	 */
	protected function hookup_for_p2tg() {

		add_action( 'wptelegram_p2tg_before_send_post', [ $this, 'before_p2tg_log' ], 999, 3 );

		add_action( 'wptelegram_p2tg_set_form_data', [ $this, 'set_form_data' ], 999, 2 );

		add_action( 'wptelegram_p2tg_post_sv_check_failed', [ $this, 'add_sv_check' ], 999, 3 );

		add_action( 'wptelegram_p2tg_delay_post', [ $this, 'add_delay_post' ], 999, 3 );

		add_filter( 'wptelegram_p2tg_rules_apply', [ $this, 'add_rules_apply' ], 999, 3 );

		add_filter( 'wptelegram_p2tg_featured_image_source', [ $this, 'add_featured_image_source' ], 999, 4 );

		add_action( 'wptelegram_p2tg_post_finish', [ $this, 'add_post_finish' ], 999, 5 );

		add_action( 'wptelegram_p2tg_after_send_post', [ $this, 'after_p2tg_log' ], 999, 3 );
	}

	/**
	 * Get the key from post.
	 *
	 * @param WP_Post $post The post being handled.
	 */
	public function get_key( $post ) {
		return $post->post_type . '-' . $post->ID . '-' . $post->post_status;
	}

	/**
	 * Get the current request type.
	 *
	 * @param WP_Post $post    The post being handled.
	 */
	private function get_request_type( $post ) {

		$request_check = new ReflectionClass( RequestCheck::class );

		$constants = $request_check->getConstants();

		foreach ( $constants as $constant => $value ) {
			if ( RequestCheck::if_is( $value, $post ) ) {
				return $constant;
			}
		}
	}

	/**
	 * Handle p2tg before post send action.
	 *
	 * @param mixed   $result  The action result.
	 * @param WP_Post $post    The post being handled.
	 * @param string  $trigger The source trigger.
	 */
	public function before_p2tg_log( $result, $post, $trigger ) {

		// create a an entry from post ID and its status.
		$key = $this->get_key( $post );

		$this->p2tg_post_info[ $key ]['before'] = [
			'trigger'      => $trigger,
			'request_type' => $this->get_request_type( $post ),
		];
	}

	/**
	 * Add form data to the log.
	 *
	 * @param mixed   $form_data The action result.
	 * @param WP_Post $post      The post being handled.
	 */
	public function set_form_data( $form_data, $post ) {

		// create a an entry from post ID and its status.
		$key = $this->get_key( $post );

		// Remove message template to make the logs clean.
		unset( $form_data['message_template'] );

		$this->p2tg_post_info[ $key ]['form_data'] = $form_data;
	}

	/**
	 * Add security and validity info
	 *
	 * @param int     $validity The request validity.
	 * @param WP_Post $post     The post being handled.
	 * @param string  $trigger  The source trigger.
	 */
	public function add_sv_check( $validity, $post, $trigger ) {

		// create a an entry from post ID and its status.
		$key = $this->get_key( $post );

		$this->p2tg_post_info[ $key ]['sv'] = $validity;
	}

	/**
	 * Add delay post info.
	 *
	 * @param float   $delay       Delay in posting.
	 * @param WP_Post $post        The post being handled.
	 * @param array   $result      The result of delay handler.
	 */
	public function add_delay_post( $delay, $post, $result ) {

		// create an entry from post ID and its status.
		$key = $this->get_key( $post );

		$this->p2tg_post_info[ $key ]['delay'] = [
			'duration' => $delay,
			'result'   => $result,
		];
	}

	/**
	 * Add rules_apply info.
	 *
	 * @param boolean $rules_apply The post being handled.
	 * @param Options $options     The post being handled.
	 * @param WP_Post $post        The post being handled.
	 */
	public function add_rules_apply( $rules_apply, $options, $post ) {

		// create a an entry from post ID and its status.
		$key = $this->get_key( $post );

		$this->p2tg_post_info[ $key ]['rules'] = [
			'apply'   => $rules_apply,
			'sent2tg' => get_post_meta( $post->ID, P2TGMain::PREFIX . 'sent2tg', true ),
		];

		return $rules_apply;
	}

	/**
	 * Add rules_apply info.
	 *
	 * @param string  $source            The featured image source.
	 * @param WP_Post $post              The post being handled.
	 * @param Options $options           The post being handled.
	 * @param boolean $send_files_by_url The featured image source.
	 */
	public function add_featured_image_source( $source, $post, $options, $send_files_by_url ) {

		// create a an entry from post ID and its status.
		$key = $this->get_key( $post );

		$this->p2tg_post_info[ $key ]['image_source'] = [
			'send_image'  => $options->get( 'send_featured_image' ),
			'has_image'   => has_post_thumbnail( $post->ID ),
			'send_by_url' => $send_files_by_url,
			'source'      => $source,
		];

		return $source;
	}

	/**
	 * Add post send finish info.
	 *
	 * @param WP_Post $post            The post being handled.
	 * @param string  $trigger         The source trigger.
	 * @param boolean $ok              The featured image source.
	 * @param Options $options         The post being handled.
	 * @param array   $processed_posts The featured image source.
	 */
	public function add_post_finish( $post, $trigger, $ok, $options, $processed_posts ) {

		// create a an entry from post ID and its status.
		$key = $this->get_key( $post );

		$this->p2tg_post_info[ $key ]['finish'] = [
			'ok'        => $ok,
			'processed' => $processed_posts,
		];
	}

	/**
	 * Add after send post info.
	 *
	 * @param mixed   $result  The action result.
	 * @param WP_Post $post    The post being handled.
	 * @param string  $trigger The source trigger.
	 */
	public function after_p2tg_log( $result, $post, $trigger ) {

		// create a an entry from post ID and its status.
		$key = $this->get_key( $post );

		if ( is_array( $result ) ) {
			$result = array_map(
				function ( $responses ) {
					if ( is_array( $responses ) ) {
						return array_map(
							function ( $response ) {
								if ( $response instanceof Response ) {
									return $response->get_decoded_body();
								}
								return $response;
							},
							$responses
						);
					}
					return $responses;
				},
				$result
			);
		}

		$this->p2tg_post_info[ $key ]['after'] = [
			'result' => $result,
		];

		$text = wp_json_encode( [ $key => $this->p2tg_post_info[ $key ] ] /*, 128*/ );

		$this->write_log( 'p2tg', $text );

		unset( $this->p2tg_post_info[ $key ] );
	}

	/**
	 * Handle the debug action.
	 *
	 * @param Response $response  The API response.
	 * @param API      $tg_api    The post being handled.
	 */
	public function add_bot_api_debug( $response, $tg_api ) {

		$res = $tg_api->get_last_response();
		// add the method and request params.
		$text = 'Method: ' . $tg_api->get_request()->get_api_method() . PHP_EOL . 'Params: ' . wp_json_encode( $tg_api->get_request()->get_params() ) . PHP_EOL . '--------------------------------' . PHP_EOL;

		// add the response.
		if ( is_wp_error( $res ) ) {
			$text .= 'WP_Error: ' . $res->get_error_code() . ' ' . $res->get_error_message() . PHP_EOL;

			$base_url = $tg_api->get_client()->get_base_url();
			// redact the worker name if present.
			$base_url = preg_replace( '/(?<=https:\/\/)[^\.]+?(?=\.)/', '***', $base_url );

			$text .= 'URL: ' . $base_url;
		} else {
			$text .= 'Response: ' . $res->get_body();
		}

		$this->write_log( 'bot-api', $text );
	}

	/**
	 * Handle prepare content error.
	 *
	 * @param ConverterException $exception The exception thrown.
	 * @param string             $content The content that was being prepared.
	 * @param array              $options The options passed to prepare_content.
	 *
	 * @return void
	 */
	public function prepare_content_error( $exception, $content, $options ) {
		$text  = 'Error: ' . $exception . PHP_EOL;
		$text .= 'Options: ' . wp_json_encode( $options ) . PHP_EOL;
		$text .= 'Content: ' . $content;

		$this->write_log( 'converter', $text );
	}

	/**
	 * Write the log to file.
	 *
	 * @param string $type The log type.
	 * @param string $text The text to write.
	 */
	public function write_log( $type, $text ) {
		$file_path = self::get_log_file_path( $type );

		$bot_token_regex = '/' . \WPTelegram\BotAPI\API::BOT_TOKEN_PATTERN . '/';

		$text = preg_replace( $bot_token_regex, '**********', $text );

		$filesystem = self::wp_filesystem();

		// Default to 1 MB.
		$max_filesize = apply_filters( 'wptelegram_logger_max_filesize', 1024 ** 2, $type, $file_path );

		$contents  = $filesystem->is_readable( $file_path ) ? $filesystem->get_contents( $file_path ) : '';
		$contents .= '[' . current_time( 'mysql' ) . ']' . PHP_EOL . $text . PHP_EOL . PHP_EOL;

		// Make sure that the file size remains less than $max_filesize.
		while ( mb_strlen( $contents ) > $max_filesize ) {
			$content_pieces = preg_split( "/[\n\r]{2}/", $contents );

			// Remove the first piece.
			array_shift( $content_pieces );

			$contents = implode( "\n\n", $content_pieces );
		}

		$filesystem->put_contents( $file_path, $contents );
	}

	/**
	 * Get an instance of WP_Filesystem.
	 *
	 * @return WP_Filesystem_Base The global WP_Filesystem object.
	 */
	public static function wp_filesystem() {

		require_once ABSPATH . 'wp-admin/includes/file.php';
		WP_Filesystem();

		global $wp_filesystem;

		return $wp_filesystem;
	}

	/**
	 * Get log file path.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type Log type.
	 * @param string $hash The hash to use in file name.
	 *
	 * @return string
	 */
	public static function get_log_file_path( $type, $hash = '' ) {

		$file_name = self::get_log_file_name( $type, $hash );

		$file_path = self::wp_filesystem()->wp_content_dir() . $file_name;

		return apply_filters( 'wptelegram_logger_log_file_path', $file_path, $type );
	}

	/**
	 * Get log file name.
	 *
	 * @since 2.2.4
	 *
	 * @param string $type Log type.
	 * @param string $hash The hash to use in file name.
	 *
	 * @return string
	 */
	public static function get_log_file_name( $type, $hash = '' ) {

		$hash = $hash ? $hash : wp_hash( 'log' );

		$file_name = "wptelegram-{$type}-{$hash}.log";

		return apply_filters( 'wptelegram_logger_log_file_name', $file_name, $type, $hash );
	}
}
