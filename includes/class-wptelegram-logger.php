<?php
/**
 * Provides logging capabilities for debugging purposes.
 *
 * @class          WPTelegram_Logger
 * @package        WPTelegram/includes
 */

defined( 'ABSPATH' ) || exit;

/**
 * WPTelegram_Logger class.
 */
class WPTelegram_Logger {

	/**
	 * Enabled Log types
	 *
	 * @since	1.0.0
	 * @access	private
	 * @var		array 		$active_logs 	The enabled logs
	 */
	private static $active_logs;

	/**
	 * Whether already hooked or not
	 *
	 * @since	1.0.0
	 * @access	private
	 * @var		array 		$hooked_up 	The enabled logs
	 */
	private static $hooked_up = false;

	/**
	 * Information about the processed post
	 *
	 * @since	1.0.0
	 * @access	private
	 * @var		array 		$p2tg_post_info 	The Post info
	 */
	private $p2tg_post_info;

	/**
	 * Constructor for the logger.
	 */
	public function __construct( $active_logs = array() ) {

		$this->set_active_logs( $active_logs );
	}

	/**
	 * Set the active logs
	 */
	public function set_active_logs( $active_logs ) {

		self::$active_logs = (array) $active_logs;
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

		// avoid hooking in multiple times
		if ( ! self::$hooked_up && ! empty( self::$active_logs ) ) {

			$this->_hookup();

			self::$hooked_up = true;
		}
	}

	/**
	 * Hook into WP Telegram to create logs
	 */
	protected function _hookup() {

		foreach ( $this->get_active_logs() as $log_type ) {

			$method = array( $this, "hookup_for_{$log_type}" );
			
			if ( is_callable( $method ) ) {
				call_user_func( $method );
			}
		}
	}

	/**
	 * Hook for WPTelegram_Bot_API log
	 */
	protected function hookup_for_bot_api() {

		add_action( 'wptelegram_bot_api_debug', array( $this, 'hangle_bot_api_debug' ), 10, 2 );
	}

	/**
	 * Hook for Post to Telegram log
	 */
	protected function hookup_for_p2tg() {

		add_action( 'wptelegram_p2tg_before_send_post', array( $this, 'before_p2tg_log' ), 10, 3 );

		add_action( 'wptelegram_p2tg_post_sv_check_failed', array( $this, 'add_sv_check' ), 10, 3 );

		add_filter( 'wptelegram_p2tg_rules_apply', array( $this, 'add_rules_apply' ), 10, 3 );

		add_filter( 'wptelegram_p2tg_featured_image_source', array( $this, 'add_featured_image_source' ), 10, 4 );

		add_action( 'wptelegram_p2tg_post_finish', array( $this, 'add_post_finish' ), 10, 5 );

		add_action( 'wptelegram_p2tg_after_send_post', array( $this, 'after_p2tg_log' ), 10, 3 );
	}

	/**
	 * Get the key from post
	 */
	public function get_key( $post ) {
		return $post->ID . '-' . $post->post_status;
	}

	/**
	 * Handle the debug action
	 */
	public function before_p2tg_log( $result, $post, $trigger ) {

		// create a an entry from post ID and its status
		$key = $this->get_key( $post );

		$this->p2tg_post_info[ $key ][] = array(
			'hook'		=> 'before',
			'trigger'	=> $trigger,
		);
	}

	/**
	 * Add security and validity info
	 */
	public function add_sv_check( $validity, $post, $trigger ) {

		// create a an entry from post ID and its status
		$key = $this->get_key( $post );

		$this->p2tg_post_info[ $key ][] = array(
			'hook'		=> 'sv',
			'validity'	=> $validity,
		);
	}

	/**
	 * Add rules_apply info
	 */
	public function add_rules_apply( $rules_apply, $options, $post ) {

		// create a an entry from post ID and its status
		$key = $this->get_key( $post );

		$this->p2tg_post_info[ $key ][] = array(
			'hook'		=> 'rules',
			'apply'		=> $rules_apply,
		);

		return $rules_apply;
	}

	/**
	 * Add rules_apply info
	 */
	public function add_featured_image_source( $source, $post, $options, $send_files_by_url ) {

		// create a an entry from post ID and its status
		$key = $this->get_key( $post );

		$this->p2tg_post_info[ $key ][] = array(
			'hook'			=> 'image_source',
			'send_image'	=> $options->get( 'send_featured_image' ),
			'has_image'		=> has_post_thumbnail( $post->ID ),
			'send_by_url'	=> $send_files_by_url,
			'source'		=> $source,
		);

		return $source;
	}

	/**
	 * Add rules_apply info
	 */
	public function add_post_finish( $post, $trigger, $ok, $options, $processed_posts ) {

		// create a an entry from post ID and its status
		$key = $this->get_key( $post );

		$this->p2tg_post_info[ $key ][] = array(
			'hook'		=> 'finish',
			'ok'		=> $ok,
			'processed'	=> $processed_posts,
		);
	}

	/**
	 * Handle the debug action
	 */
	public function after_p2tg_log( $result, $post, $trigger ) {

		// create a an entry from post ID and its status
		$key = $this->get_key( $post );

		$this->p2tg_post_info[ $key ][] = array(
			'hook'		=> 'after',
			'result'	=> $result,
		);

		$text = WPTG()->utils->json_encode( $this->p2tg_post_info/*, 128*/ );

        $this->write_log( 'p2tg', $text );
	}

	/**
	 * Handle the debug action
	 */
	public function hangle_bot_api_debug( $response, $tg_api ) {

		$res = $tg_api->get_last_response();
        // add the method and request params
        $text = 'Method: ' . $tg_api->get_request()->get_api_method() . PHP_EOL . 'Params: ' . json_encode( $tg_api->get_request()->get_params() ) . PHP_EOL . '--------------------------------' . PHP_EOL;

        // add the response
        if ( is_wp_error( $res ) ) {
            $text .= 'WP_Error: ' . $res->get_error_code() . ' ' . $res->get_error_message();
        } else{
            $text .= 'Response: ' . $res->get_body();
        }

        $this->write_log( 'bot-api', $text );
	}

	/**
	 * Write the log to file
	 */
	public function write_log( $type, $text ) {

		$filename = WP_CONTENT_DIR . "/wptelegram-{$type}.log";
        $filename = apply_filters( "wptelegram_logger_{$type}_log_filename", $filename );

        $data = '[' . current_time( 'mysql' ) . ']' . PHP_EOL . $text . PHP_EOL . PHP_EOL;

        file_put_contents( $filename, $data, FILE_APPEND );
	}
}