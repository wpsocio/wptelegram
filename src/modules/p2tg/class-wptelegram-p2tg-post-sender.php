<?php

/**
 * Post Handling functionality of the plugin.
 *
 * @link		https://t.me/WPTelegram
 * @since		1.0.0
 *
 * @package		WPTelegram
 * @subpackage	WPTelegram/includes
 */

/**
 * The Post Handling functionality of the plugin.
 *
 * @package		WPTelegram
 * @subpackage	WPTelegram/includes
 * @author		Manzoor Wani <@manzoorwanijk>
 */
class WPTelegram_P2TG_Post_Sender extends WPTelegram_Module_Base {

	/**
	 * Bot Token to be used for Telegram API calls
	 *
	 * @since	1.0.0
	 * @access	private
	 * @var		string	Telegram Bot Token.
	 */
	private $bot_token;

	/**
	 * The prefix for meta data
	 *
	 * @since	1.0.0
	 * @access	private
	 * @var		string	The prefix for meta data
	 */
	private static $prefix = '_wptg_p2tg_';

	/**
	 * Settings/Options
	 *
	 * @since	1.0.0
	 * @access	private
	 * @var		array 		$options 	Options
	 */
	private $options;

	/**
	 * Responses prepared from settings
	 *
	 * @since	1.0.0
	 * @access	private
	 * @var		array		$responses
	 */
	private $responses;

	/**
	 * Meta box override switch
	 *
	 * @since	1.0.0
	 * @access	private
	 * @var		string 		$send2tg 
	 */
	private $send2tg;

	/**
	 * The Telegram API
	 *
	 * @since	1.0.0
	 * @access	private
	 * @var		WPTelegram_Bot_API $bot_api Telegram API Object
	 */
	private $bot_api;

	/**
	 * WP_Error
	 *
	 * @var WP_Error
	 */
	protected $WP_Error;

	/**
	 * The post to be handled
	 *
	 * @var	WP_Post	$post	Post object.
	 */
	protected static $post;

	/**
	 * Whether to send the files (photo etc.) by URL
	 *
	 * @var	bool	$send_files_by_url	Send files by URL
	 */
	protected $send_files_by_url = true;

	/**
	 * The post data
	 *
	 * @var	WPTelegram_P2TG_Post_Data	$post_data	Post data.
	 */
	protected static $post_data;

	/**
	 * The posts processed in the current request
	 * to be used to avoid double posting
	 *
	 * @var	array	$processed_posts
	 */
	protected static $processed_posts;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since	2.0.0
	 */
	public function __construct( $module_name, $module_title ) {

		parent::__construct( $module_name, $module_title );

		self::$processed_posts = array();
	}

	/**
	 * Set up the basics
	 *
	 * @since	2.0.0
	 * 
	 * @param	$post	WP_Post
	 */
	public function init( $post, $trigger ) {

		self::$post = $post;

		self::$post_data = new WPTelegram_P2TG_Post_Data( self::$post );

		$this->bot_token = WPTG()->options()->get( 'bot_token' );

		do_action( 'wptelegram_p2tg_post_init', self::$post, $trigger );
	}

	/**
	 * Get default options
	 *
	 * @since	2.0.0
	 */
	public static function get_defaults() {
		$array    = array();
		$defaults = array(
			'channels'             => '',
			'send_when'            => $array,
			'post_types'           => $array,
			'rules'                => $array,
			'message_template'     => '',
			'excerpt_source'       => 'post_content',
			'excerpt_length'       => 55,
			'excerpt_preserve_eol' => 'off',
			'send_featured_image'  => 'on',
			'image_position'       => 'before',
			'single_message'       => 'off',
			'parse_mode'           => null,
			'cats_as_tags'         => 'off',
			'misc'                 => array(
				// 'disable_web_page_preview',
				// 'disable_notification',
			),
			'delay'                => 0,
			'inline_url_button'    => 'off',
			'inline_button_text'   => '',
		);

		return (array) apply_filters( 'wptelegram_p2tg_defaults', $defaults );
	}

	/**
	 * Get Post To Telegram options
	 *
	 * @since	2.0.0
	 */
	public function get_saved_options() {

		$saved_options = array();

		foreach ( self::get_defaults() as $key => $default ) {

			$saved_options[ $key ] = $this->module_options->get( $key, $default );
		}

		// You can add the options dynamically.
		return (array) apply_filters( 'wptelegram_p2tg_saved_options', $saved_options );
	}

	/**
	 * Handle wp_insert_post
	 *
	 * @since	2.0.0
	 *
	 * @param	$post_id	int
	 * @param	$post		WP_Post
	 */
	public function wp_insert_post( $post_id, $post ) {

		$this->send_post( $post, __FUNCTION__ );
	}

	/**
	 * Handle Scheduled Post
	 *
	 * @since	2.0.0
	 *
	 * @param	$post	WP_Post
	 */
	public function future_to_publish( $post ) {

		$send_scheduled_posts = (bool) apply_filters( 'wptelegram_p2tg_send_scheduled_posts', true, $post );

		if ( $send_scheduled_posts ) {

			$this->send_post( $post, __FUNCTION__ );
		}
	}

	/**
	 * Handle Delayed Post
	 *
	 * @since	2.0.0
	 *
	 * @param	$post_id	string
	 */
	public function delayed_post( $post_id ) {

		$post = get_post( $post_id );

		if ( $post ) {

			$this->send_post( $post, __FUNCTION__ );
		}
	}

	/**
	 * Handle the post published via WP REST API
	 *
	 * @since	2.0.13
	 *
	 * @param	$post	WP_Post
	 */
	public function wp_rest_post( $post ) {

		$this->send_post( $post, __FUNCTION__ );
	}

	/**
	 * Make sure the global $post and its data is set
	 *
	 * @since	2.1.5
	 *
	 * @param	WP_Post	$post		The post to be handled
	 * @param	string	$trigger	The name of the source trigger hook
	 */
	private function may_be_setup_postdata( $post, $trigger ) {
		$previous_post = null;

		// make sure the global $post and its data is set
		if ( 'delayed_post' === $trigger ) {


			if ( ! empty( $GLOBALS['post'] ) ) {
				$previous_post = $GLOBALS['post'];
			}

			$GLOBALS['post'] = $post;

			setup_postdata( $post );
		}

		return $previous_post;
	}

	/**
	 * Make sure the global $post and its data is reset
	 *
	 * @since	2.1.5
	 *
	 * @param	WP_Post|null	$previous_post	The post to be handled
	 * @param	string			$trigger		The name of the source trigger hook
	 */
	private function may_be_reset_postdata( $previous_post, $trigger ) {

		if ( 'delayed_post' === $trigger ) {

			$GLOBALS['post'] = $previous_post;

			if ( $previous_post ) {
				setup_postdata( $previous_post );
			}
		}
	}

	/**
	 * May be send the post to Telegram, if rules apply
	 *
	 * @since	2.0.0
	 *
	 * @param	WP_Post	$post		The post to be handled
	 * @param	string	$trigger	The name of the source trigger hook
	 * @param	bool	$force		Whether to bypass the custom rules
	 */
	public function send_post( WP_Post $post, $trigger = 'non_wp', $force = false ) {

		$previous_post = $this->may_be_setup_postdata( $post, $trigger );

		$result = null;

		do_action( 'wptelegram_p2tg_before_send_post', $result, $post, $trigger, $force );

		$result = $this->send_the_post( $post, $trigger, $force );

		do_action( 'wptelegram_p2tg_after_send_post', $result, $post, $trigger, $force );

		$this->may_be_reset_postdata( $previous_post, $trigger );
	}

	/**
	 * May be send the post to Telegram, if the rules apply
	 *
	 * This method is not intended to be used directly, although it can be.
	 * Use wptelegram_p2tg_send_post() instead.
	 * Relying on this method is not safe as it may change in future
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post $post    The post to be handled.
	 * @param string  $trigger The name of the source trigger hook.
	 * @param bool    $force   Whether to bypass the custom rules.
	 */
	public function send_the_post( WP_Post $post, $trigger, $force ) {

		$this->init( $post, $trigger );

		if ( empty( $this->bot_token ) ) {
			return __LINE__;
		}

		// if already processed in the current/recent request.
		if ( in_array( $post->ID, self::$processed_posts ) ) {
			return __LINE__;
		}

		$ok = true;

		// if the post is published/updated using WP REST API.
		$is_rest = ( defined( 'REST_REQUEST' ) && REST_REQUEST );

		// if WP 5+ and not doing "rest_after_insert_{$post_type}" action.
		if ( $is_rest && WPTG()->utils->wp_at_least( '5.0' ) && current_filter() != ( $tag = 'rest_after_insert_' . self::$post->post_type ) ) {

			// avoid the Gutenberg mess.
			if ( ! WPTG()->utils->is_gutenberg_post( $post ) ) {
				// come back later.
				add_action( $tag, array( $this, 'wp_rest_post' ), 10, 1 );
			}

			$ok = false;
		}

		/**
		 * if the security check failed
		 * returned int (the line number) or boolean (false)
		 */
		if ( $ok && true !== ( $validity = $this->security_and_validity_check() ) ) {

			$ok = false;

			/**
			 * Fires after the security check fails
			 * Can be used to determine which condition actually failed
			 * by checking for the integer value of $validity - the line number
			 *
			 * @since 1.0.0
			 *
			 * @param WP_Post  $post     The current post
			 * @param int|bool $validity The validity status
			 */
			do_action( 'wptelegram_p2tg_post_sv_check_failed', $validity, self::$post, $trigger );
		}

		if ( $ok ) {

			$this->set_options();

			if ( ! $this->options->get( 'channels' ) ) {
				$this->send2tg = 'no';
			}

			if ( 'no' === $this->send2tg ) {
				$ok = false;
			}
		}

		if ( 'no' === $this->send2tg && $this->is_valid_status() ) {
			$this->clear_scheduled_hook();
		}

		$result = __LINE__;

		// if some rules should be bypassed.
		if ( $ok && 'non_wp' === $trigger ) {
			$this->bypass_rules( $force );
		}

		$rules_apply = $ok && $this->rules_apply();

		$apply_rules_before_delay = apply_filters( 'wptelegram_p2tg_apply_rules_before_delay', true, $this->options, self::$post );

		if ( $this->is_status_of_type( 'non_live' ) || ( $ok && ( $delay = $this->delay_in_posting( $trigger ) ) ) ) {

			$this->may_be_save_options();

			if ( $delay && ( ! $apply_rules_before_delay || $rules_apply ) ) {

				$this->delay_post( $delay );

				// for logging.
				$result = "delayed {$delay}";
			}

			$ok = false;

		} elseif ( $this->is_status_of_type( 'live' ) ) {

			self::clean_up();
		}

		if ( $ok && $rules_apply ) {

			// Everything looks good.
			$result = $this->process();

			// add the post ID to the processed array.
			self::$processed_posts[] = $post->ID;
		}

		do_action( 'wptelegram_p2tg_post_finish', self::$post, $trigger, $ok, $this->options, self::$processed_posts );

		return $result;
	}

	/**
	 * The post statuses that are valid/allowed.
	 *
	 * @since 2.1.2
	 */
	public function get_valid_post_statuses() {
		$valid_statuses = array(
			'live'     => array( // The ones that are live/visible.
				'publish',
				'private',
			),
			'non_live' => array( // The that are not yet live for the audience.
				'future',
				'draft',
				'pending',
			),
		);
		return (array) apply_filters( 'wptelegram_p2tg_valid_post_statuses', $valid_statuses, self::$post );
	}

	/**
	 * If it's a valid status that the should be handled.
	 *
	 * @since 2.0.11
	 */
	public function is_valid_status() {

		$valid_statuses = call_user_func_array( 'array_merge', $this->get_valid_post_statuses() );

		return in_array( self::$post->post_status, $valid_statuses, true );
	}

	/**
	 * If it's a live/non_live status .
	 *
	 * @since 2.1.2
	 */
	public function is_status_of_type( $type ) {

		$valid_statuses = $this->get_valid_post_statuses();

		return in_array( self::$post->post_status, $valid_statuses[ $type ], true );
	}

	/**
	 * Clear an existing scheduled event.
	 *
	 * @since 2.1.2
	 *
	 */
	public function clear_scheduled_hook( $hook = '' ) {

		$hook = $hook ? $hook : 'wptelegram_p2tg_delayed_post';

		$args = array( strval( self::$post->ID ) );

		// clear the previous event, if set.
		wp_clear_scheduled_hook( $hook, $args );
	}


	/**
	 * Set the post for delay.
	 *
	 * @since 1.0.0
	 */
	public function delay_post( $delay ) {

		$hook  = 'wptelegram_p2tg_delayed_post';
		$delay = absint( $delay * MINUTE_IN_SECONDS );
		$args  = array( strval( self::$post->ID ) ); // strval to match the exact event.

		$this->clear_scheduled_hook();

		wp_schedule_single_event( time() + $delay, $hook, $args );
	}

	/**
	 * Delay posts by minutes.
	 *
	 * @since	1.0.0
	 *
	 * @return  int
	 */
	public function delay_in_posting( $trigger = '' ) {

		// avoid infinite loop.
		if ( 'delayed_post' === $trigger ) {
			return 0;
		}

		$delay = $this->options->get( 'delay' ); // minutes.

		$delay = apply_filters( 'wptelegram_p2tg_delay_in_posting', $delay, self::$post, $trigger, $this->options );

		return abs( (float) $delay );
	}

	/**
	 * Save if the settings have been overridden
	 *
	 * @since	1.0.0
	 *
	 */
	private function may_be_save_options() {

		// if options need to be saved.
		if ( $this->defaults_overridden() && 'no' !== $this->send2tg ) {

			$this->save_options_to_meta();
		}

		// if it's a future or draft post and override switch is used.
		if ( $this->send2tg ) {
			if ( ! add_post_meta( self::$post->ID, self::$prefix . 'send2tg', $this->send2tg, true ) ) {

				update_post_meta( self::$post->ID, self::$prefix . 'send2tg', $this->send2tg );
			}
		}
	}

	/**
	 * Save options meta for the scheduled post
	 *
	 * @since	1.0.0
	 *
	 */
	private function save_options_to_meta() {

		// get all options as array.
		$options = $this->options->get();

		// add slashes to the template to avoid stripping of backslashes.
		$options['message_template'] = addslashes( $options['message_template'] );

		if ( ! add_post_meta( self::$post->ID, self::$prefix . 'options', $options, true ) ) {
			update_post_meta ( self::$post->ID, self::$prefix . 'options', $options );
		}
	}

	/**
	 * Add the required filters to bypass some rules
	 * 
	 * Post type rule will not be bypassed
	 *
	 * @since	1.0.0
	 *
	 * @param	bool	$force	Whether to bypass the custom rules
	 *
	 */
	private function bypass_rules( $force = false ) {

		// override the default saved option.
		add_filter( 'wptelegram_p2tg_bypass_post_date_rules', '__return_true' );

		// if forced to bypass the custom rules.
		if ( $force ) {
			add_filter( 'wptelegram_p2tg_bypass_custom_rules', '__return_true' );
		}
	}

	/**
	 * Security checks
	 *
	 * This function was actually a requirement
	 * to check which condition actually failed
	 *
	 * @since	2.0.0
	 *
	 */
	private function security_and_validity_check() {

		$send_if_importing = (bool) apply_filters( 'wptelegram_p2tg_send_if_importing', false, self::$post );

		// if importing.
		if ( defined( 'WP_IMPORTING' ) && WP_IMPORTING && ! $send_if_importing ) {
			return __LINE__;
		}

		$send_if_bulk_edit = (bool) apply_filters( 'wptelegram_p2tg_send_if_bulk_edit', false, self::$post );

		// if bulk edit.
		if ( isset( $_GET['bulk_edit'] ) && ! $send_if_bulk_edit ) {
			return __LINE__;
		}

		$send_if_quick_edit = (bool) apply_filters( 'wptelegram_p2tg_send_if_quick_edit', false, self::$post );

		// if quick edit.
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX && isset( $_REQUEST['action'] ) && 'inline-save' == $_REQUEST['action'] && ! $send_if_quick_edit ) {
			return __LINE__;
		}

		$is_post_request = isset( $_SERVER['REQUEST_METHOD'] ) && 'post' == strtolower( $_SERVER['REQUEST_METHOD'] );

		// Is the post created via wp-admin.
		$from_web = $is_post_request && ( isset( $_POST[ self::$prefix . 'from_web'] ) || isset( $_POST[ self::$prefix . 'send2tg'] ) );

		$from_web = (bool) apply_filters( 'wptelegram_p2tg_is_from_web', $from_web, self::$post );

		$post_edit_switch = $this->module_options->get( 'post_edit_switch', 'on' );

		// use strict check to avoid type conversion.
		if ( $from_web && 'on' === $post_edit_switch ) {

			$nonce = self::get_nonce_name();

			// Check for nonce.
			if ( ! isset( $_POST[ $nonce ] ) ) {
				return __LINE__;
			}

			// Verify nonce.
			if ( ! wp_verify_nonce( $_POST[ $nonce ], $nonce ) ) {
				return __LINE__;
			}

			// check for override switch.
			if ( isset( $_POST[ self::$prefix . 'send2tg' ] ) ) {

				$this->send2tg = WPTG()->utils->sanitize( $_POST[ self::$prefix . 'send2tg' ] );
			}

			if ( 'no' === $this->send2tg ) {
				return __LINE__;
			}
		}

		// if it's an AUTOSAVE.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return __LINE__;
		}

		// if it's a post revision.
		if ( wp_is_post_revision( self::$post ) ) {
			return __LINE__;
		}

		// If not a valid status.
		if ( ! $this->is_valid_status() ) {
			return __LINE__;
		}

		// if the post is published/updated using WP CRON.
		$is_cron = ( defined( 'DOING_CRON' ) && DOING_CRON );

		// if the post is published/updated via WP-CLI.
		$is_cli = ( defined( 'WP_CLI' ) && WP_CLI );

		// No need to check for user permissions when WP-CLI or Cron.
		if ( ! $is_cli && ! $is_cron ) {

			$user_has_permission = false;
			// Allow custom code to control authentication.
			// Especially for front-end submissions.
			$user_has_permission = (bool) apply_filters( 'wptelegram_p2tg_current_user_has_permission', $user_has_permission, self::$post );

			if ( ! $user_has_permission ) {

				if ( 'page' == self::$post->post_type && ! current_user_can( 'edit_page', self::$post->ID ) ) { // if user has not permissions to edit pages.
					return __LINE__;
				} elseif ( ! current_user_can( 'edit_post', self::$post->ID ) ) { // if user has not permissions to edit posts.
					return __LINE__;
				}
			}
		}

		// final control in your hands.
		// pass a false value to avoid posting.
		if ( ! apply_filters( 'wptelegram_p2tg_filter_post', self::$post ) ) {
			return __LINE__;
		}

		return true;
	}

	/**
	 * Get nonce name for nonce verification
	 *
	 * @since	1.0.0
	 */
	public static function get_nonce_name() {

		// hardcode by default so as not to require CMB2 active here.
		$nonce = 'nonce_CMB2phpwptelegram_p2tg_override';

		// if CMB2 is active.
		if ( defined( 'CMB2_LOADED' ) && CMB2_LOADED && function_exists( 'cmb2_get_metabox' ) ) {

			$cmb = cmb2_get_metabox( 'wptelegram_p2tg_override' );

			if ( is_callable( array( $cmb, 'nonce' ) ) ) {
				$nonce = $cmb->nonce();
			}
		}

		return $nonce;
	}

	/**
	 * Whether the default options have been overridden
	 * on the post edit page
	 *
	 * @since 1.0.0
	 */
	public function defaults_overridden() {

		// allow override only if enabled.
		$post_edit_switch = $this->module_options->get( 'post_edit_switch', 'on' );

		// default off.
		$override_switch = 'off';

		if ( 'on' === $post_edit_switch && isset( $_POST[ self::$prefix . 'override_switch'] ) ) {

			$override_switch = WPTG()->utils->sanitize( $_POST[ self::$prefix . 'override_switch'] );
		}

		if ( 'on' === $override_switch ) {
			return true;
		}

		return false;
	}

	/**
	 * Clean up the meta table etc.
	 *
	 * @since 2.0.0
	 */
	public static function clean_up() {

		delete_post_meta( self::$post->ID, self::$prefix . 'options' );
		delete_post_meta( self::$post->ID, self::$prefix . 'send2tg' );
	}

	/**
	 * Set the options
	 *
	 * @since 1.0.0
	 */
	public function set_options() {

		$options = $this->get_options();

		if ( ! $this->send2tg && ( $send2tg = get_post_meta( self::$post->ID, self::$prefix . 'send2tg', true ) ) ) {
			$this->send2tg = $send2tg;
		}		

		$this->options = new WPTelegram_Options();
		$this->options->set_data( $options );
	}

	/**
	 * fetch the options from the settings
	 * and user override options.
	 *
	 * @since 1.0.0
	 *
	 * @return  array
	 */
	public function get_options() {

		// try to get the options from meta.
		$saved_options = get_post_meta( self::$post->ID, self::$prefix . 'options', true );

		// if there is nothing.
		if ( empty( $saved_options ) ) {

			$saved_options = $this->get_saved_options();
		}

		// clone.
		$options = $saved_options;

		// Override the default options.
		if ( $this->defaults_overridden() ) {

			// if no destination channel is selected.
			if ( empty( $_POST[ self::$prefix . 'channels'] ) ) {

				$options['channels'] = '';

			} else {
				// sanitize the values.
				$channels = WPTG()->utils->sanitize( (array) $_POST[ self::$prefix . 'channels'] );

				// override the default channels.
				$options['channels'] = implode( ',', $channels );
			}

			// if the template is set.
			if ( isset( $_POST[ self::$prefix . 'message_template'] ) ) {

				// sanitize the template.
				$template = WPTG()->helpers->sanitize_message_template( $_POST[ self::$prefix . 'message_template'], false );

				// override the default template.
				$options['message_template'] = $template;
			}

			// if files included.
			if ( ! empty( $_POST[ self::$prefix . 'files'] ) ) {

				// sanitize the values.
				$files = array_filter( WPTG()->utils->sanitize( (array) $_POST[ self::$prefix . 'files'] ) );

				if ( ! empty( $files ) ) {
					// add the files to the options.
					$options['files'] = $files;
				}
			} else {
				unset( $options['files'] );
			}

			// if delay overridden.
			if ( isset( $_POST[ self::$prefix . 'delay'] ) ) {

				// sanitize the value.
				$options['delay'] = (int) WPTG()->utils->sanitize( $_POST[ self::$prefix . 'delay'], true );
			}

			// if notifications to be disabled.
			if ( isset( $_POST[ self::$prefix . 'disable_notification'] ) && ! in_array( 'disable_notification', (array) $options['misc'] ) ) {
				$options['misc'][] = 'disable_notification';

			} elseif ( ( $key = array_search( 'disable_notification', (array) $options['misc'] ) ) !== false ) {

				unset( $options['misc'][ $key ] );
			}
		}

		return (array) apply_filters( 'wptelegram_p2tg_options', $options, $saved_options, self::$post );
	}

	/**
	 * Process
	 *
	 * @since	1.0.0
	 */
	private function process() {

		do_action( 'wptelegram_p2tg_before_process', self::$post, $this->options );

		$this->responses = $this->get_responses();

		$responses = $this->send_responses();

		do_action( 'wptelegram_p2tg_after_process', self::$post, $this->options, $this->responses );

		return $responses;
	}

	/**
	 * Prepare responses from options
	 *
	 * @since	1.0.0
	 *
	 * @return	array
	 */
	private function get_responses() {

		$responses = array();

		// For text message.
		$template = $this->get_message_template();
		$text = '';

		if ( ! empty( $template ) ) {

			$text = $this->get_response_text( $template );
		}

		$this->send_files_by_url = ( 'on' === WPTG()->options()->get( 'send_files_by_url', 'on' ) );

		/* TO BE REMOVED IN FUTURE */
		// only for backward compatibility.
		$this->send_files_by_url = apply_filters( 'wptelegram_send_image_by_url', $this->send_files_by_url, self::$post );
		$this->send_files_by_url = apply_filters( 'wptelegram_send_file_by_url', $this->send_files_by_url );
		/* TO BE REMOVED IN FUTURE */

		/**
		 * Pass false to upload the file
		 * instead of sending as URL
		 */
		$this->send_files_by_url = (bool) apply_filters( 'wptelegram_p2tg_send_files_by_url', $this->send_files_by_url, self::$post, $this->options );

		// For Photo.
		$image_source = $this->get_featured_image_source();

		$responses = $this->get_default_responses( $text, $image_source );

		$files = $this->options->get( 'files' );

		if ( ! empty( $files ) ) {

			$file_responses = $this->get_file_responses( $files );

			$responses = array_merge( $responses, $file_responses );
		}

		return (array) apply_filters( 'wptelegram_p2tg_responses', $responses, self::$post, $this->options );
	}

	/**
	 * Check if the rules apply to the post
	 *
	 * @since	1.0.0
	 *
	 * @return	bool
	 */
	private function rules_apply() {

		// check if the rules apply to the post.
		$rules_apply = $this->check_for_rules();

		return (bool) apply_filters( 'wptelegram_p2tg_rules_apply', $rules_apply, $this->options, self::$post );
	}

	/**
	 * Check if the rules apply to the post
	 *
	 * @since	1.0.0
	 *
	 * @return	bool
	 */
	private function check_for_rules() {

		$bypass_date_rules = false;
		$bypass_post_type_rules = false;

		if ( 'yes' === $this->send2tg ) {

			$bypass_date_rules = true;
		}

		$bypass_date_rules = (bool) apply_filters( 'wptelegram_p2tg_bypass_post_date_rules', $bypass_date_rules, self::$post, $this->options );

		if ( ! $bypass_date_rules ) {

			$send_when = $this->options->get( 'send_when' );

			// don't use post_date by default.
			$use_post_date = (bool) apply_filters( 'wptelegram_p2tg_rules_use_post_date', false, self::$post, $this->options );

			if ( $use_post_date ) {
				// New/existing.
				$is_new = ( self::$post->post_date_gmt === self::$post->post_modified_gmt );

			} else {

				// whether the post has already been sent to Telegram.
				$sent2tg = get_post_meta( self::$post->ID, self::$prefix . 'sent2tg', true );
				// if the meta value is empty - it's new.
				$is_new = empty( $sent2tg );
			}

			$is_new = (bool) apply_filters( 'wptelegram_p2tg_rules_is_new_post', $is_new, self::$post, $this->options );

			$send_new = in_array( 'new', $send_when );
			$send_new = (bool) apply_filters( 'wptelegram_p2tg_rules_send_new_post', $send_new, self::$post, $this->options );

			// if sending new posts is disabled and is new post.
			if ( $is_new && ! $send_new ) {

				return false;
			}

			$send_existing = in_array( 'existing', $send_when );
			$send_existing = (bool) apply_filters( 'wptelegram_p2tg_rules_send_existing_post', $send_existing, self::$post, $this->options );

			// if sending existing posts is disabled and is existing post.
			if ( ! $is_new && ! $send_existing ) {

				return false;
			}
		}

		$bypass_post_type_rules = (bool) apply_filters( 'wptelegram_p2tg_bypass_post_type_rules', $bypass_post_type_rules, self::$post, $this->options );

		if ( ! $bypass_post_type_rules ) {
			// Check for Post type.
			$post_types = $this->options->get( 'post_types' );

			$send_post_type = in_array( self::$post->post_type, $post_types );
			$send_post_type = (bool) apply_filters( 'wptelegram_p2tg_rules_send_post_type', $send_post_type, self::$post, $this->options );
			// post type specific filter.
			$send_post_type = (bool) apply_filters( 'wptelegram_p2tg_rules_send_' . self::$post->post_type, $send_post_type, self::$post, $this->options );

			if ( ! $send_post_type ) {

				return false;
			}
		}

		// finally check for custom rules.

		$bypass_custom_rules = (bool) apply_filters( 'wptelegram_p2tg_bypass_custom_rules', false, self::$post, $this->options );

		if ( $bypass_custom_rules ) {

			return true;
		}

		$rules = new WPTelegram_P2TG_Rules();

		return $rules->rules_apply( $this->options->get( 'rules' ), self::$post );
	}

	/**
	 * Create responses based on the text and image source
	 *
	 * @since	1.0.0
	 *
	 * @param	$text			string
	 * @param	$image_source	string
	 *
	 * @return	array
	 */
	private function get_default_responses( $text, $image_source ) {

		$parse_mode	= WPTG()->helpers->valid_parse_mode( $this->options->get( 'parse_mode' ) );

		$misc_opts	= $this->options->get( 'misc' );

		$disable_web_page_preview	= in_array( 'disable_web_page_preview', $misc_opts );

		$disable_notification		= in_array( 'disable_notification', $misc_opts );

		$method_params = array(
			'sendPhoto'		=> compact(
				'parse_mode',
				'disable_notification'
			),
			'sendMessage'	=> compact(
				'parse_mode',
				'disable_notification',
				'disable_web_page_preview'
			),
		);

		if ( ! empty( $image_source ) ) {

			$image_position	= $this->options->get( 'image_position' );
			$single_message	= $this->options->get( 'single_message' );
			$caption = '';

			if ( 'on' === $single_message ) {
				// if only caption is to be sent.
				if ( 'before' === $image_position ) {

					// remove sendMessage.
					unset( $method_params['sendMessage'] );

					// use regex instead of mb_substr to preserve words.
					preg_match( '/.{1,1024}(?=\s|$)/us', $text, $match );
					$caption = $match[0];

				} elseif ( 'after' === $image_position && NULL !== $parse_mode ) {

					$text = $this->add_hidden_image_url( $text, $image_source, $parse_mode );

					// Remove "sendPhoto".
					unset( $method_params['sendPhoto'] );
				}

			} elseif ( 'after' === $image_position ) {

				$method_params = array_reverse( $method_params );
			}

			if ( isset( $method_params['sendPhoto'] ) ) {

				$caption = apply_filters( 'wptelegram_p2tg_post_image_caption', $caption, self::$post, $this->options, $text, $image_source );

				$method_params['sendPhoto']['photo'] = $image_source;
				$method_params['sendPhoto']['caption'] = $caption;
			}
		} else {
			unset( $method_params['sendPhoto'] );
		}

		if ( isset( $method_params['sendMessage'] ) ) {

			$method_params['sendMessage']['text'] = $text;
		}

		$method_params = (array) apply_filters( 'wptelegram_p2tg_method_params', $method_params, self::$post, $this->options, $text, $image_source );

		// passed by reference.
		$this->add_reply_markup( $method_params );

		$default_responses = array();

		foreach ( $method_params as $method => $params ) {
			$default_responses[] = array(
				$method	=> $params,
			);
		}

		return apply_filters( 'wptelegram_p2tg_default_responses', $default_responses, self::$post, $this->options, $text, $image_source );
	}

	/**
	 * Create responses based on the files included
	 *
	 * @since	1.0.0
	 *
	 * @param	$files	array
	 *
	 * @return	array
	 */
	private function get_file_responses( $files ) {

		$file_responses = array();

		$caption = self::$post_data->get_field( 'post_title' );

		foreach ( $files as $id => $url ) {

			$caption = apply_filters( 'wptelegram_p2tg_file_caption', $caption, self::$post, $id, $url, $this->options );

			$type = WPTG()->utils->guess_file_type( $id, $url );

			$file_responses[] = array(
				'send' . ucfirst( $type )	=> array(
					$type		=> $this->send_files_by_url ? $url : get_attached_file( $id ),
					'caption'	=> $caption,
				),
			);
		}

		return apply_filters( 'wptelegram_p2tg_file_responses', $file_responses, self::$post, $this->options, $files, $this->send_files_by_url );
	}

	/**
	 * Get the message template
	 *
	 * @since	1.0.0
	 *
	 * @return	string
	 */
	private function get_message_template() {

		$template = $this->options->get( 'message_template' );

		$template = stripslashes( json_decode( $template ) );

		return apply_filters( 'wptelegram_p2tg_message_template', $template, self::$post, $this->options );
	}

	/**
	 * May by add reply_markup to the message
	 * 
	 * @param array &$method_params Methods and Params passed by reference
	 *
	 * @return  array
	 */
	private function add_reply_markup( &$method_params ) {

		$inline_keyboard = $this->get_inline_keyboard( $method_params );

		if ( ! empty( $inline_keyboard ) ) {

			$reply_markup = json_encode( compact( 'inline_keyboard' ) );

			if ( isset( $method_params['sendMessage'] ) ) {

				$method_params['sendMessage']['reply_markup'] = $reply_markup;
			} else {
				// add to the last Method.
				end( $method_params );
				$method = key( $method_params );

				$method_params[ $method ]['reply_markup'] = $reply_markup;
			}
		}
	}

	/**
	 * get inline_keyboard 
	 * 
	 * @return  array
	 */
	public function get_inline_keyboard( $method_params ) {

		$inline_url_button = $this->options->get( 'inline_url_button' );
		$inline_button_text = $this->options->get( 'inline_button_text' );

		if ( 'on' !== $inline_url_button ) {
			return false;
		}

		$default_button = array(
			'text'	=> '🔗 ' . $inline_button_text,
			'url'	=> self::$post_data->get_field( 'full_url' ),
		);

		$default_button = (array) apply_filters( 'wptelegram_p2tg_default_inline_button', $default_button, self::$post, $method_params );

		$inline_keyboard[][] = $default_button;

		return (array) apply_filters( 'wptelegram_p2tg_inline_keyboard', $inline_keyboard, self::$post, $method_params );
	}

	/**
	 * Get the text based response
	 *
	 * @since	1.0.0
	 * 
	 * @param	$template	string
	 *
	 * @return	string
	 */
	private function get_response_text( $template ) {
		
		// Remove wpautop() from the `the_content` filter
		// to preserve newlines.
		$priority = has_filter( 'the_content', 'wpautop' );
		if ( false !== $priority ) {
			remove_filter( 'the_content', 'wpautop', $priority );
			add_filter( 'the_content', array( $this, '_restore_wpautop_hook' ), $priority + 1 );
		}

		$excerpt_source = $this->options->get( 'excerpt_source' );
		$excerpt_length = (int) $this->options->get( 'excerpt_length' );
		$excerpt_preserve_eol = $this->options->get( 'excerpt_preserve_eol' );
		$cats_as_tags   = $this->options->get( 'cats_as_tags' );
		$parse_mode		= WPTG()->helpers->valid_parse_mode( $this->options->get( 'parse_mode' ) );

		// replace {tags} and {categories} with taxonomy names.
		$replace = array( '{terms:post_tag}', '{terms:category}' );

		// use {tags} and {categories} for WooCommerce.
		if ( class_exists( 'woocommerce' ) && 'product' == self::$post->post_type ) {

			$replace = array( '{terms:product_tag}', '{terms:product_cat}' );

		}

		// modify the template.
		$template = str_replace( array( '{tags}', '{categories}' ), $replace, $template );

		$macro_keys = array(
			'ID',
			'post_title',
			'post_date',
			'post_date_gmt',
			'post_author',
			'post_excerpt',
			'post_content',
			'short_url',
			'full_url',
		);

		// for post excerpt.
		$params = compact( 'excerpt_source', 'excerpt_length', 'excerpt_preserve_eol', 'cats_as_tags' );

		$macro_values = array();

		foreach ( $macro_keys as $macro_key ) {

			// get the value only if it's in the template.
			if ( false !== strpos( $template, '{' . $macro_key . '}' ) ) {

				$macro_values['{' . $macro_key . '}'] = self::$post_data->get_field( $macro_key, $params );
			}
		}

		// if it's something unusual.
		if ( preg_match_all( '/(?<=\{)(terms|cf):([^\}]+?)(?=\})/iu', $template, $matches ) ) {

			foreach ( $matches[0] as $field ) {

				$macro_values['{' . $field . '}'] = self::$post_data->get_field( $field, $params );
			}
		}

		/**
		 * Use this filter to replace your own macros
		 * with the corresponding values
		 */
		$macro_values = (array) apply_filters( 'wptelegram_p2tg_macro_values', $macro_values, self::$post, $this->options );

		if ( 'Markdown' === $parse_mode ) {
			$callback = array( WPTG()->helpers,'esc_markdown' );
		} else {
			$callback = 'stripslashes'; // to remove unwanted slashes.
		}

		// apply the callback to each value.
		$macro_values = array_map( $callback, $macro_values );

		// lets replace the conditional macros.
		$template = $this->process_template_logic( $template, $macro_values );

		// replace the lone macros with values.
		$text = str_replace( array_keys( $macro_values ), array_values( $macro_values ), $template );

		// decode all HTML entities.
		$text = html_entity_decode( $text, ENT_QUOTES, 'UTF-8' );

		// fix the malformed text.
		$text = WPTG()->helpers->filter_text_for_parse_mode( $text, $parse_mode );

		return apply_filters( 'wptelegram_p2tg_response_text', $text, $template, self::$post, $this->options );
	}

	/**
	 * Resolve the conditional macros in the template
	 *
	 * @since	2.0.17
	 *
	 * @return	string
	 */
	private function process_template_logic( $template, $macro_values ) {

		$raw_template = $template;

		$pattern = '/\[if\s*?	# Conditional block starts
			(\{[^\}]+?\})		# Conditional expression, a macro
		\]						# Conditional block ends
		\[						# Consequence block starts
			([^\]]+?)			# Consequence expression
		\]						# Consequence block ends
		(?:						# non-capturing alternative block
			\[					# Alternative block starts
				([^\]]*?)		# Alternative expression
			\]					# Alternative block ends
		)?						# Make alternative block optional
		/ix';

		preg_match_all( $pattern, $template, $matches );

		// loop through the conditional expressions.
		foreach ( $matches[1] as $key => $macro ) {

			// if expression is false, take from alternative.
			$index = empty( $macro_values[ $macro ] ) ? 3 : 2;

			$replace = str_replace( array_keys( $macro_values ), array_values( $macro_values ), $matches[ $index ][ $key ] );

			$template = str_replace( $matches[0][ $key ], $replace, $template );
		}

		// remove the ugly empty lines.
		$template = preg_replace( '/(?:\A|[\n\r]).*?\{remove_line\}.*/u', '', $template );

		return apply_filters( 'wptelegram_p2tg_process_template_logic', $template, $macro_values, $raw_template, self::$post, $this->options );
	}

	/**
	 * Re-add wp_autop() to the `the_content` filter.
	 *
	 * @access public
	 *
	 * @since 2.1.3
	 *
	 * @param string $content The post content running through this filter.
	 * @return string The unmodified content.
	 */
	public function _restore_wpautop_hook( $content ) {
		$current_priority = has_filter( 'the_content', array( $this, '_restore_wpautop_hook' ) );

		add_filter( 'the_content', 'wpautop', $current_priority - 1 );
		remove_filter( 'the_content', array( $this, '_restore_wpautop_hook' ), $current_priority );

		return $content;
	}

	/**
	 * Get the featured image URL or file location
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	private function get_featured_image_source() {

		$send_image = $this->options->get( 'send_featured_image' );

		$source = '';

		if ( 'on' === $send_image && has_post_thumbnail( self::$post->ID ) ) {

			if ( $this->send_files_by_url ) {

				// featured image URL.
				$source = self::$post_data->get_field( 'featured_image_url' );

			} else {

				// featured image path.
				$source = self::$post_data->get_field( 'featured_image_path' );
			}
		}

		return apply_filters( 'wptelegram_p2tg_featured_image_source', $source, self::$post, $this->options, $this->send_files_by_url );
	}

	/**
	 * Send the responses
	 */
	private function send_responses() {

		// Remove query variable, if present.
		remove_query_arg( self::$prefix . 'error' );
		// Remove error transient.
		delete_transient( 'wptelegram_p2tg_errors' );

		$this->bot_api = new WPTelegram_Bot_API( $this->bot_token );

		$api_responses = array();

		do_action( 'wptelegram_p2tg_before_send_responses', $this->responses, $api_responses, self::$post, $this->options, $this->bot_api );

		// if modify curl for WP Telegram.
		if ( ! $this->send_files_by_url ) {
			// modify curl.
			add_action( 'http_api_curl', array( $this, 'modify_http_api_curl' ), 10, 3 );
		}

		$channels = explode( ',', $this->options->get( 'channels', '' ) );
		$channels = (array) apply_filters( 'wptelegram_p2tg_send_to_channels', $channels, $this->responses, self::$post, $this->options, $this->bot_api );

		$message_as_reply = (bool) apply_filters( 'wptelegram_p2tg_send_message_as_reply', true, self::$post, $this->options );

		// loop through destination channels.
		foreach ( $channels as $channel ) {
			$res = false;

			// loop through the prepared responses.
			foreach ( $this->responses as $response ) {

				$params = reset( $response );
				$method = key( $response );

				$params['chat_id'] = $channel;

				if ( $message_as_reply && $this->bot_api->is_success( $res ) ) {

					$result = $res->get_result();
					$params['reply_to_message_id'] = $result ? $result['message_id'] : null;
				}

				/**
				 * Filters the params for the Telegram API method
				 * It can be used to modify the behavior in a number of ways
				 * You can use it to change the text based on the channel/chat
				 * 
				 * @since	1.0.0
				 */
				$params = apply_filters( 'wptelegram_p2tg_api_method_params', $params, $method, $this->responses, self::$post, $this->options );

				$res = call_user_func( array( $this->bot_api, $method ), $params );
				$api_responses[ $channel ][] = $res;

				do_action( 'wptelegram_p2tg_api_response', $res, $this->responses, self::$post, $this->options, $this->bot_api );

				if ( is_wp_error( $res ) ) {
					$this->handle_wp_error( $res, $channel );
				}
			}
		}

		// remove cURL modification.
		remove_action( 'http_api_curl', array( $this, 'modify_http_api_curl' ), 10, 3 );

		// update post meta if the message was successful.
		$this->update_post_meta( $api_responses );

		do_action( 'wptelegram_p2tg_after_send_responses', $this->responses, $api_responses, self::$post, $this->options, $this->bot_api );

		return $api_responses;
	}

	/**
	 * Update post meta if the message was successful
	 *
	 * @since 1.0.0
	 */
	private function update_post_meta( $api_responses ) {

		foreach ( $api_responses as $responses ) {

			foreach ( $responses as $res ) {
				// if any of the responses is successful.
				if ( $this->bot_api->is_success( $res ) ) {

					$current_time = current_time( 'mysql' );

					if ( ! add_post_meta( self::$post->ID, self::$prefix . 'sent2tg', $current_time, true ) ) {

						update_post_meta( self::$post->ID, self::$prefix . 'sent2tg', $current_time );

						return;
					}
				}
			}
		}
	}

	/**
	 * Modify cURL handle
	 * The method is not used by default
	 * but can be used to modify
	 * the behavior of cURL requests
	 *
	 * @since 1.0.0
	 *
	 * @param resource $handle  The cURL handle (passed by reference).
	 * @param array    $r       The HTTP request arguments.
	 * @param string   $url     The request URL.
	 *
	 * @return string
	 */
	public function modify_http_api_curl( &$handle, $r, $url ) {

		$to_telegram   = ( 0 === strpos( $url, 'https://api.telegram.org/bot' ) );
		$by_wptelegram = ( isset( $r['headers']['wptelegram_bot'] ) && $r['headers']['wptelegram_bot'] );
		// if the request is sent to Telegram by WP Telegram.
		if ( $to_telegram && $by_wptelegram ) {

			/**
			 * Modify for files
			 */
			if ( ! $this->send_files_by_url ) {

				$types = array( 'photo', 'audio', 'video', 'document' );

				foreach ( $types as $type ) {

					if ( isset( $r['body'][ $type ] ) && file_exists( $r['body'][ $type ] ) ) {

						// PHP >= 5.5
						if ( function_exists( 'curl_file_create' ) ) {
							$r['body'][ $type ] = curl_file_create( $r['body'][ $type ] );
						} else {
							// Create a string with file data.
							$r['body'][ $type ] = '@' . $r['body'][ $type ] . ';type=' . mime_content_type( $r['body'][ $type ] ) . ';filename=' . basename( $r['body'][ $type ] );
						}

						curl_setopt( $handle, CURLOPT_POSTFIELDS, $r['body'] );
					}
				}
			}
		}
	}

	/**
	 * Handle WP_Error of wp_remote_post()
	 *
	 * @since	1.0.0
	 */
	private function handle_wp_error( $wp_error, $channel ) {

		$transient = 'wptelegram_p2tg_errors';

		$p2tg_errors = array_filter( (array) get_transient( $transient ) );

		$p2tg_errors[ $channel ][ $wp_error->get_error_code() ] = $wp_error->get_error_message();

		set_transient( $transient, $p2tg_errors, 60 );

		add_filter( 'redirect_post_location', array( $this, 'add_admin_notice_query_var' ), 99 );
	}

	/**
	 * Add query variable upon error
	 *
	 * @since	1.0.0
	 */
	public function add_admin_notice_query_var( $location ) {

		remove_filter( 'redirect_post_location', array( $this, __FUNCTION__ ), 99 );

		return add_query_arg( array( self::$prefix . 'error' => true ), $location );
	}

	/**
	 * Add hidden URL at the beginning of the text
	 *
	 * @since	1.0.0
	 *
	 * @param 	string	$text
	 * @param 	string	$image_url
	 * @param 	string 	$parse_mode
	 *
	 * @return string
	 */
	private function add_hidden_image_url( $text, $image_url, $parse_mode ) {

		if ( 'HTML' == $parse_mode ) {
			// Add Zero Width Non Joiner &#8204; as the anchor text.
			$string = '<a href="' . $image_url . '">&#8204;</a>';
		} else {
			// Add hidden Zero Width Non Joiner between "[" and "]".
			$string = '[‌](' . $image_url . ')';
		}
		return $string . '‌' . $text; // something magical in the middle.
	}
}