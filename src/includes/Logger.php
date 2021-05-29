<?php
/**
 * Provides logging capabilities for debugging purposes.
 *
 * @package    WPTelegram
 * @subpackage WPTelegram\Core\includes
 */

namespace WPTelegram\Core\includes;

use ReflectionClass;
use WPTelegram\BotAPI\Response;
use WPTelegram\BotAPI\API;
use WP_Post;
use WPTelegram\Core\modules\p2tg\RequestCheck;
use WPTelegram\Core\modules\p2tg\Main as P2TGMain;

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

		foreach ( $this->get_active_logs() as $log_type ) {

			$method = [ $this, "hookup_for_{$log_type}" ];

			if ( is_callable( $method ) ) {
				call_user_func( $method );
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
		return $post->ID . '-' . $post->post_status;
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
	 * Write the log to file.
	 *
	 * @param string $type The log type.
	 * @param string $text The text to write.
	 */
	public function write_log( $type, $text ) {
		$file_path = self::get_log_file_path( $type );

		global $wp_filesystem;

		$contents = '[' . current_time( 'mysql' ) . ']' . PHP_EOL . $text . PHP_EOL . PHP_EOL;

		// Default to 1 MB.
		$max_filesize = apply_filters( 'wptelegram_logger_max_filesize', 1024 ** 2, $type, $file_path );

		// Make sure that the file size remains less than $max_filesize.
		if ( $wp_filesystem->exists( $file_path ) && $wp_filesystem->size( $file_path ) < $max_filesize ) {
			// Append the existing content.
			$contents = $wp_filesystem->get_contents( $file_path ) . $contents;
		}

		$wp_filesystem->put_contents( $file_path, $contents );
	}

	/**
	 * Get log file path.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type Log type.
	 *
	 * @return string
	 */
	public static function get_log_file_path( $type ) {

		require_once ABSPATH . 'wp-admin/includes/file.php';
		WP_Filesystem();

		global $wp_filesystem;

		$file_name = self::get_log_file_name( $type );

		$file_path = $wp_filesystem->wp_content_dir() . $file_name;

		return apply_filters( 'wptelegram_logger_log_file_path', $file_path, $type );
	}

	/**
	 * Get log file name.
	 *
	 * @since 2.2.4
	 *
	 * @param string $type Log type.
	 *
	 * @return string
	 */
	public static function get_log_file_name( $type ) {

		$hash = $type . '-' . wp_hash( 'log' );

		$file_name = "wptelegram-{$hash}.log";

		return apply_filters( 'wptelegram_logger_log_file_name', $file_name, $type, $hash );
	}
}
