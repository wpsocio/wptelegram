<?php
/**
 * Post Handling functionality of the plugin.
 *
 * @link        https://t.me/WPTelegram
 * @since       1.0.0
 *
 * @package     WPTelegram
 * @subpackage  WPTelegram\Core\modules\p2tg
 */

namespace WPTelegram\Core\modules\p2tg;

use WPTelegram\Core\modules\BaseClass;
use WPTelegram\Core\includes\Utils as MainUtils;
use WPTelegram\BotAPI\API;
use WPTelegram\BotAPI\Response;
use WPTelegram\BotAPI\Client;
use WP_Post;
use WPSocio\WPUtils\Options;

/**
 * The Post Handling functionality of the plugin.
 *
 * @package     WPTelegram
 * @subpackage  WPTelegram\Core\modules\p2tg
 * @author      WP Socio
 */
class PostSender extends BaseClass {

	/**
	 * Bot Token to be used for Telegram API calls
	 *
	 * @since   1.0.0
	 * @access  private
	 * @var     string  Telegram Bot Token.
	 */
	private $bot_token;

	/**
	 * Settings/Options
	 *
	 * @since   1.0.0
	 * @access  private
	 * @var     Options $options Options.
	 */
	private $options;

	/**
	 * Responses prepared from settings
	 *
	 * @since   1.0.0
	 * @access  private
	 * @var     array       $responses
	 */
	private $responses;

	/**
	 * The Telegram API
	 *
	 * @since   1.0.0
	 * @access  private
	 * @var     API $bot_api Telegram API Object
	 */
	private $bot_api;

	/**
	 * The post to be handled
	 *
	 * @var WP_Post $post   Post object.
	 */
	protected $post;

	/**
	 * Whether to send the files (photo etc.) by URL
	 *
	 * @var bool    $send_files_by_url  Send files by URL
	 */
	protected $send_files_by_url = true;

	/**
	 * The post data
	 *
	 * @var PostData   $post_data  Post data.
	 */
	protected $post_data;

	/**
	 * The form data that was submitted.
	 *
	 * @var Options $form_data The form data.
	 */
	protected $form_data;

	/**
	 * The posts processed in the current request
	 * to be used to avoid double posting
	 *
	 * @var array   $processed_posts
	 */
	protected static $processed_posts = [];

	/**
	 * Set up the basics
	 *
	 * @since   2.0.0
	 *
	 * @param WP_Post $post    The post being processed.
	 * @param string  $trigger The trigger source.
	 */
	public function init( $post, $trigger ) {
		$this->post = $post;

		$this->post_data = new PostData( $this->post );

		$this->bot_token = WPTG()->options()->get( 'bot_token' );

		$this->set_form_data();

		do_action( 'wptelegram_p2tg_post_init', $this->post, $trigger );
	}

	/**
	 * Sets the form submission data.
	 *
	 * @since 3.0.0
	 */
	public function set_form_data() {
		// Default data.
		$this->form_data = [
			'send2tg'         => null,
			'override_switch' => false,
		];

		// Form data matters only if post edit switch is enabled.
		if ( ! Admin::show_post_edit_switch() ) {
			return;
		}

		if ( RequestCheck::if_is( RequestCheck::REST_REQUEST ) ) {
			$raw_body = file_get_contents( 'php://input' );

			if ( ! empty( $raw_body ) ) {
				$body = json_decode( $raw_body, true );
				// It has come from Gutenberg.
				if ( ! empty( $body[ Main::PREFIX ] ) ) {
					$data = $body[ Main::PREFIX ];

					$form_data = MainUtils::sanitize( $data );

					if ( isset( $data['message_template'] ) ) {
						// Sanitize the template separately.
						$form_data['message_template'] = MainUtils::sanitize_message_template( $data['message_template'] );
					}

					$form_data['send2tg'] = ! empty( $form_data['send2tg'] ) ? 'yes' : 'no';

					$this->form_data = array_merge( $this->form_data, $form_data );
					// For logging.
					$this->form_data['is_from_gb'] = true;
				}
			}
		} else {

			if ( isset( $_POST[ Main::PREFIX . 'send2tg' ] ) ) { // phpcs:ignore
				// phpcs:ignore
				$this->form_data['send2tg'] = sanitize_text_field( wp_unslash( $_POST[ Main::PREFIX . 'send2tg' ] ) );
			}

			if ( isset( $_POST[ Main::PREFIX . 'override_switch' ] ) ) { // phpcs:ignore
				// phpcs:ignore
				$override_switch = sanitize_text_field( wp_unslash( $_POST[ Main::PREFIX . 'override_switch' ] ) );

				$this->form_data['override_switch'] = 'on' === $override_switch;
			}

			// Override the default options.
			if ( $this->defaults_overridden() ) {

				// if no destination channel is selected.
				if ( empty( $_POST[ Main::PREFIX . 'channels' ] ) ) { // phpcs:ignore
					$this->form_data['channels'] = [];
				} else {
					// override the default channels.
					$this->form_data['channels'] = MainUtils::sanitize( (array) $_POST[ Main::PREFIX . 'channels' ] ); // phpcs:ignore
				}

				// if the template is set.
				if ( isset( $_POST[ Main::PREFIX . 'message_template' ] ) ) { // phpcs:ignore
					// sanitize the template.
					$template = MainUtils::sanitize_message_template( wp_unslash( $_POST[ Main::PREFIX . 'message_template' ] ) ); // phpcs:ignore
					// override the default template.
					$this->form_data['message_template'] = $template;
				}

				// if files included.
				if ( ! empty( $_POST[ Main::PREFIX . 'files' ] ) ) { // phpcs:ignore
					// sanitize the values.
					$files = array_filter( MainUtils::sanitize( (array) $_POST[ Main::PREFIX . 'files' ] ) ); // phpcs:ignore
					if ( ! empty( $files ) ) {
						// add the files to the options.
						$this->form_data['files'] = $files;
					}
				}

				// if delay overridden.
				if ( isset( $_POST[ Main::PREFIX . 'delay' ] ) ) { // phpcs:ignore
					// sanitize the value.
					$this->form_data['delay'] = MainUtils::sanitize( $_POST[ Main::PREFIX . 'delay' ], true ); // phpcs:ignore
				}

				// if notifications are to be disabled.
				if ( isset( $_POST[ Main::PREFIX . 'disable_notification' ] ) ) { // phpcs:ignore
					$this->form_data['disable_notification'] = true;
				}

				// if send featured image.
				if ( isset( $_POST[ Main::PREFIX . 'send_featured_image' ] ) ) { // phpcs:ignore
					$send_featured_image = MainUtils::sanitize( $_POST[ Main::PREFIX . 'send_featured_image' ] ); // phpcs:ignore
					$this->form_data['send_featured_image'] = 'on' === $send_featured_image;
				}
			}
		}

		do_action( 'wptelegram_p2tg_set_form_data', $this->form_data, $this->post );
	}

	/**
	 * Get default options
	 *
	 * @since   2.0.0
	 */
	public static function get_defaults() {
		$array    = [];
		$defaults = [
			'cats_as_tags'            => false,
			'channels'                => $array,
			'delay'                   => 0,
			'disable_notification'    => false,
			'link_preview_disabled'   => false,
			'link_preview_url'        => '',
			'link_preview_above_text' => false,
			'excerpt_length'          => 55,
			'excerpt_preserve_eol'    => false,
			'excerpt_source'          => 'post_content',
			'image_position'          => 'before',
			'inline_button_text'      => '',
			'inline_button_url'       => '',
			'inline_url_button'       => false,
			'message_template'        => '',
			'parse_mode'              => '',
			'plugin_posts'            => false,
			'post_types'              => $array,
			'protect_content'         => false,
			'rules'                   => $array,
			'send_featured_image'     => true,
			'send_when'               => $array,
			'single_message'          => false,
		];

		return (array) apply_filters( 'wptelegram_p2tg_defaults', $defaults );
	}

	/**
	 * Get Post To Telegram options
	 *
	 * @since   2.0.0
	 */
	public function get_saved_options() {

		$saved_options = [];

		foreach ( self::get_defaults() as $key => $default ) {

			$saved_options[ $key ] = $this->module->options()->get( $key, $default );
		}

		// You can add the options dynamically.
		return (array) apply_filters( 'wptelegram_p2tg_saved_options', $saved_options );
	}

	/**
	 * Handle wp_insert_post
	 *
	 * @since   2.0.0
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    The post being processed.
	 */
	public function wp_insert_post( $post_id, $post ) {

		$this->send_post( $post, __FUNCTION__ );
	}

	/**
	 * Handle Scheduled Post.
	 *
	 * @since   2.0.0
	 *
	 * @param WP_Post $post The post being processed.
	 */
	public function future_to_publish( $post ) {

		$send_scheduled_posts = (bool) apply_filters( 'wptelegram_p2tg_send_scheduled_posts', true, $post );

		if ( $send_scheduled_posts ) {

			$this->send_post( $post, __FUNCTION__ );
		}
	}

	/**
	 * Handle Delayed Post.
	 *
	 * @since   2.0.0
	 *
	 * @param int $post_id Post ID.
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
	 * @since   2.0.13
	 *
	 * @param WP_Post $post The post being processed.
	 */
	public function rest_after_insert( $post ) {

		$this->send_post( $post, __FUNCTION__ );
	}

	/**
	 * Make sure the global $post and its data is set
	 *
	 * @since   2.1.5
	 *
	 * @param WP_Post $post    The post being processed.
	 * @param string  $trigger The trigger source.
	 */
	private function may_be_setup_postdata( $post, $trigger ) {
		$previous_post = null;

		// make sure the global $post and its data is set.
		if ( 'delayed_post' === $trigger ) {

			if ( ! empty( $GLOBALS['post'] ) ) {
				$previous_post = $GLOBALS['post'];
			}

			// phpcs:ignore
			$GLOBALS['post'] = $post;

			setup_postdata( $post );
		}

		return $previous_post;
	}

	/**
	 * Make sure the global $post and its data is reset
	 *
	 * @since 2.1.5
	 *
	 * @param WP_Post $previous_post The post being processed.
	 * @param string  $trigger       The trigger source.
	 */
	private function may_be_reset_postdata( $previous_post, $trigger ) {

		if ( 'delayed_post' === $trigger ) {

			// phpcs:ignore
			$GLOBALS['post'] = $previous_post;

			if ( $previous_post ) {
				setup_postdata( $previous_post );
			}
		}
	}

	/**
	 * May be send the post to Telegram, if rules apply
	 *
	 * @since   2.0.0
	 *
	 * @param WP_Post $post    The post to be handled.
	 * @param string  $trigger The name of the source trigger hook.
	 * @param bool    $force   Whether to bypass the custom rules.
	 */
	public function send_post( $post, $trigger = 'non_wp', $force = false ) {

		if ( empty( $post ) ) {
			return __LINE__;
		}

		$previous_post = $this->may_be_setup_postdata( $post, $trigger );

		$result = __LINE__;

		do_action( 'wptelegram_p2tg_before_send_post', $result, $post, $trigger, $force );

		$result = $this->send_the_post( $post, $trigger, $force );

		do_action( 'wptelegram_p2tg_after_send_post', $result, $post, $trigger, $force );

		$this->may_be_reset_postdata( $previous_post, $trigger );

		return $result;
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
	public function send_the_post( $post, $trigger, $force ) {

		$this->init( $post, $trigger );

		if ( empty( $this->bot_token ) ) {
			return __LINE__;
		}

		// if already processed in the current/recent request.
		if ( in_array( $post->ID, self::$processed_posts, true ) ) {
			return __LINE__;
		}

		$ok = true;
		// for logging.
		$result = __LINE__;

		// if not doing "rest_after_insert_{$post_type}" action.
		if ( RequestCheck::if_is( RequestCheck::REST_PRE_INSERT, $this->post ) ) {

			// come back later.
			add_action( 'rest_after_insert_' . $this->post->post_type, [ $this, 'rest_after_insert' ], 10, 1 );

			$ok = false;

			$result .= ':' . __LINE__;
		}

		/**
		 * If the security check failed.
		 * returned int (the line number) or boolean (false)
		 */
		if ( $ok && true !== ( $validity = $this->security_and_validity_check() ) ) { //phpcs:ignore

			$ok = false;

			$result .= ':' . __LINE__;

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
			do_action( 'wptelegram_p2tg_post_sv_check_failed', $validity, $this->post, $trigger );
		}

		$this->set_options();

		if ( $ok ) {

			if ( ! $this->options->get( 'channels' ) ) {
				$this->form_data['send2tg'] = 'no';
			}

			if ( 'no' === $this->form_data['send2tg'] ) {
				$ok = false;

				$result .= ':' . __LINE__;
			}
		}

		if ( 'no' === $this->form_data['send2tg'] && $this->is_valid_status() ) {
			$this->clear_scheduled_hook();

			$result .= ':' . __LINE__;
		}

		// if some rules should be bypassed.
		if ( $ok && 'non_wp' === $trigger ) {
			$this->bypass_rules( $force );
		}

		$rules_apply = $ok && $this->rules_apply();

		if ( $this->is_status_of_type( 'non_live' ) || ( $ok && ( $delay = $this->delay_in_posting( $trigger ) ) ) ) { //phpcs:ignore

			$this->may_be_save_options();

			$apply_rules_before_delay = apply_filters( 'wptelegram_p2tg_apply_rules_before_delay', true, $this->options, $this->post );

			$result .= ':' . __LINE__;

			if ( ! empty( $delay ) && ( ! $apply_rules_before_delay || $rules_apply ) ) {

				$this->delay_post( $delay );

				// for logging.
				$result = "delayed {$delay}";
			}

			$ok = false;

		} else {
			$result .= ':' . __LINE__;
			$this->may_be_clean_up();
		}

		if ( $ok && $rules_apply ) {

			// Everything looks good.
			$result = $this->process();

			// add the post ID to the processed array.
			self::$processed_posts[] = $post->ID;
		}

		do_action( 'wptelegram_p2tg_post_finish', $this->post, $trigger, $ok, $this->options, self::$processed_posts );

		return $result;
	}

	/**
	 * The post statuses that are valid/allowed.
	 *
	 * @since 2.1.2
	 */
	public function get_valid_post_statuses() {
		$valid_statuses = [
			'live'     => [ // The ones that are live/visible.
				'publish',
				'private',
			],
			'non_live' => [ // The that are not yet live for the audience.
				'future',
				'draft',
				'pending',
			],
		];
		return (array) apply_filters( 'wptelegram_p2tg_valid_post_statuses', $valid_statuses, $this->post );
	}

	/**
	 * If it's a valid status that the should be handled.
	 *
	 * @since 2.0.11
	 */
	public function is_valid_status() {

		$valid_statuses = call_user_func_array( 'array_merge', array_values( $this->get_valid_post_statuses() ) );

		return in_array( $this->post->post_status, $valid_statuses, true );
	}

	/**
	 * If it's a live/non_live status.
	 *
	 * @param string $type The status type.
	 *
	 * @since 2.1.2
	 */
	public function is_status_of_type( $type ) {

		$valid_statuses = $this->get_valid_post_statuses();

		return in_array( $this->post->post_status, $valid_statuses[ $type ], true );
	}

	/**
	 * Clear an existing scheduled event.
	 *
	 * @since 2.1.2
	 *
	 * @param string $hook The cron hook name.
	 *
	 * @return bool|int
	 */
	public function clear_scheduled_hook( $hook = '' ) {

		$hook = $hook ? $hook : 'wptelegram_p2tg_delayed_post';

		// cast to match the exact event.
		$args = [ (string) $this->post->ID ];

		// clear the previous event, if set.
		return wp_clear_scheduled_hook( $hook, $args );
	}

	/**
	 * Set the post for delay.
	 *
	 * @since 1.0.0
	 *
	 * @param string $delay Delay in posting.
	 */
	public function delay_post( $delay ) {

		$hook  = 'wptelegram_p2tg_delayed_post';
		$delay = absint( $delay * MINUTE_IN_SECONDS );
		// cast to match the exact event.
		$args = [ (string) $this->post->ID ];

		$cleared = $this->clear_scheduled_hook();

		$scheduled = wp_schedule_single_event( time() + $delay, $hook, $args );

		$result = [
			'cleared'   => $cleared,
			'scheduled' => $scheduled,
		];

		do_action( 'wptelegram_p2tg_delay_post', $delay, $this->post, $result );
	}

	/**
	 * Delay posts by minutes.
	 *
	 * @since   1.0.0
	 *
	 * @param string $trigger The source trigger.
	 * @return  int
	 */
	public function delay_in_posting( $trigger = '' ) {

		// avoid infinite loop.
		if ( 'delayed_post' === $trigger ) {
			return 0;
		}

		$delay = $this->options->get( 'delay' ); // minutes.

		$delay = apply_filters( 'wptelegram_p2tg_delay_in_posting', $delay, $this->post, $trigger, $this->options );

		return abs( (float) $delay );
	}

	/**
	 * Save if the settings have been overridden
	 *
	 * @since   1.0.0
	 */
	private function may_be_save_options() {

		// if options need to be saved.
		if ( $this->defaults_overridden() && 'no' !== $this->form_data['send2tg'] ) {
			$this->save_options_to_meta();
		}

		// if it's a future or draft post and override switch is used.
		if ( $this->form_data['send2tg'] ) {
			if ( ! add_post_meta( $this->post->ID, Main::PREFIX . 'send2tg', $this->form_data['send2tg'], true ) ) {

				update_post_meta( $this->post->ID, Main::PREFIX . 'send2tg', $this->form_data['send2tg'] );
			}
		}
	}

	/**
	 * Save options meta for the scheduled post
	 *
	 * @since   1.0.0
	 */
	private function save_options_to_meta() {

		// get all options as array.
		$options = $this->options->get();

		// add slashes to avoid stripping of backslashes.
		$options = addslashes( wp_json_encode( $options ) );

		if ( ! add_post_meta( $this->post->ID, Main::PREFIX . 'options', $options, true ) ) {
			update_post_meta( $this->post->ID, Main::PREFIX . 'options', $options );
		}
	}

	/**
	 * Add the required filters to bypass some rules.
	 *
	 * Post type rule will not be bypassed.
	 *
	 * @since   1.0.0
	 *
	 * @param bool $force  Whether to bypass the custom rules.
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
	 * @since   2.0.0
	 */
	private function security_and_validity_check() {

		if ( 'no' === $this->form_data['send2tg'] ) {
			return __LINE__;
		}

		// If it's the block editor metabox submission.
		if ( RequestCheck::if_is( RequestCheck::IS_GB_METABOX ) ) {
			return __LINE__;
		}

		$send_if_importing = (bool) apply_filters( 'wptelegram_p2tg_send_if_importing', false, $this->post );

		// if importing.
		if ( RequestCheck::if_is( RequestCheck::WP_IMPORTING ) && ! $send_if_importing ) {
			return __LINE__;
		}

		$send_if_bulk_edit = (bool) apply_filters( 'wptelegram_p2tg_send_if_bulk_edit', false, $this->post );

		// if bulk edit.
		if ( RequestCheck::if_is( RequestCheck::BULK_EDIT ) && ! $send_if_bulk_edit ) {
			return __LINE__;
		}

		$send_if_quick_edit = (bool) apply_filters( 'wptelegram_p2tg_send_if_quick_edit', false, $this->post );

		// if quick edit.
		if ( RequestCheck::if_is( RequestCheck::QUICK_EDIT ) && ! $send_if_quick_edit ) {
			return __LINE__;
		}

		// Is the post created via wp-admin.
		if ( RequestCheck::if_is( RequestCheck::FROM_WEB ) && Admin::show_post_edit_switch() ) {

			$nonce = MainUtils::nonce();

			// Check for nonce.
			if ( ! isset( $_POST[ $nonce ] ) ) {
				return __LINE__;
			}

			// Verify nonce.
			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ $nonce ] ) ), $nonce ) ) {
				return __LINE__;
			}
		}

		// if it's an AUTOSAVE.
		if ( RequestCheck::if_is( RequestCheck::DOING_AUTOSAVE ) ) {
			return __LINE__;
		}

		// if it's a post revision.
		if ( RequestCheck::if_is( RequestCheck::POST_REVISION, $this->post ) ) {
			return __LINE__;
		}

		// If not a valid status.
		if ( ! $this->is_valid_status() ) {
			return __LINE__;
		}

		// if the post is published/updated using WP CRON.
		$is_cron = RequestCheck::if_is( RequestCheck::DOING_CRON );

		// if the post is published/updated via WP-CLI.
		$is_cli = RequestCheck::if_is( RequestCheck::WP_CLI );

		$plugin_posts = $this->module->options()->get( 'plugin_posts', true );

		// No need to check for user permissions when WP-CLI or Cron.
		if ( ! $is_cli && ! $is_cron && ! $plugin_posts ) {

			$user_has_permission = false;
			// Allow custom code to control authentication.
			// Especially for front-end submissions.
			$user_has_permission = (bool) apply_filters( 'wptelegram_p2tg_current_user_has_permission', $user_has_permission, $this->post );

			if ( ! $user_has_permission ) {

				if ( 'page' === $this->post->post_type && ! current_user_can( 'edit_page', $this->post->ID ) ) { // if user has not permissions to edit pages.
					return __LINE__;
				} elseif ( ! current_user_can( 'edit_post', $this->post->ID ) ) { // if user has not permissions to edit posts.
					return __LINE__;
				}
			}
		}

		// final control in your hands.
		// pass a false value to avoid posting.
		if ( ! apply_filters( 'wptelegram_p2tg_filter_post', $this->post ) ) {
			return __LINE__;
		}

		return true;
	}

	/**
	 * Whether the default options have been overridden
	 * on the post edit page
	 *
	 * @since 1.0.0
	 */
	public function defaults_overridden() {

		return $this->form_data['override_switch'];
	}

	/**
	 * Clean up the meta table etc.
	 *
	 * @since 3.0.3
	 */
	public function may_be_clean_up() {
		$is_gb_metabox = RequestCheck::if_is( RequestCheck::IS_GB_METABOX );

		$is_initial_rest_request = RequestCheck::if_is( RequestCheck::REST_PRE_INSERT, $this->post );

		if ( $this->is_status_of_type( 'live' ) && ! $is_gb_metabox && ! $is_initial_rest_request ) {
			delete_post_meta( $this->post->ID, Main::PREFIX . 'options' );
			delete_post_meta( $this->post->ID, Main::PREFIX . 'send2tg' );
		}
	}

	/**
	 * Set the options
	 *
	 * @since 1.0.0
	 */
	public function set_options() {

		$options = $this->get_options();

		if ( ! $this->form_data['send2tg'] ) {
			$send2tg = get_post_meta( $this->post->ID, Main::PREFIX . 'send2tg', true );
			if ( $send2tg ) {
				$this->form_data['send2tg'] = $send2tg;
			}
		}

		$this->options = new Options();
		$this->options->set_data( $options );
	}

	/**
	 * Fetch the options from the settings and user override options.
	 *
	 * @since 1.0.0
	 *
	 * @return  array
	 */
	public function get_options() {

		// try to get the options from meta.
		$saved_options = (string) get_post_meta( $this->post->ID, Main::PREFIX . 'options', true );

		// if there is nothing.
		if ( empty( $saved_options ) ) {

			$saved_options = $this->get_saved_options();
		} else {
			$saved_options = json_decode( $saved_options, true );
		}

		// clone.
		$options = $saved_options;

		// Override the default options.
		if ( $this->defaults_overridden() ) {
			$options['channels'] = $this->form_data['channels'];

			// if the template is set.
			if ( isset( $this->form_data['message_template'] ) ) {
				$options['message_template'] = $this->form_data['message_template'];
			}

			// if files included.
			if ( ! empty( $this->form_data['files'] ) ) {
				$options['files'] = $this->form_data['files'];
			} else {
				unset( $options['files'] );
			}

			// if delay overridden.
			if ( isset( $this->form_data['delay'] ) ) {
				$options['delay'] = $this->form_data['delay'];
			}

			// if send_featured_image overridden.
			if ( isset( $this->form_data['send_featured_image'] ) ) {
				$options['send_featured_image'] = $this->form_data['send_featured_image'];
			}

			// if notifications to be disabled.
			$options['disable_notification'] = ! empty( $this->form_data['disable_notification'] );
		}

		return (array) apply_filters( 'wptelegram_p2tg_options', $options, $saved_options, $this->post );
	}

	/**
	 * Process
	 *
	 * @since   1.0.0
	 */
	private function process() {

		do_action( 'wptelegram_p2tg_before_process', $this->post, $this->options );

		$this->responses = $this->get_responses();

		$responses = $this->send_responses();

		do_action( 'wptelegram_p2tg_after_process', $this->post, $this->options, $this->responses );

		return $responses;
	}

	/**
	 * Prepare responses from options
	 *
	 * @since   1.0.0
	 *
	 * @return  array
	 */
	private function get_responses() {

		$responses = [];

		// For text message.
		$template = $this->get_message_template();
		$text     = '';

		if ( ! empty( $template ) ) {

			$text = $this->get_response_text( $template );
		}

		$this->send_files_by_url = MainUtils::send_files_by_url();

		// For Photo.
		$image_source = $this->get_featured_image_source();

		$responses = $this->get_default_responses( $text, $image_source );

		$files = $this->options->get( 'files' );

		if ( ! empty( $files ) ) {

			$file_responses = $this->get_file_responses( $files );

			$responses = array_merge( $responses, $file_responses );
		}

		return (array) apply_filters( 'wptelegram_p2tg_responses', $responses, $this->post, $this->options );
	}

	/**
	 * Check if the rules apply to the post
	 *
	 * @since   1.0.0
	 *
	 * @return  bool
	 */
	private function rules_apply() {

		// check if the rules apply to the post.
		$rules_apply = $this->check_for_rules();

		return (bool) apply_filters( 'wptelegram_p2tg_rules_apply', $rules_apply, $this->options, $this->post );
	}

	/**
	 * Check if the rules apply to the post
	 *
	 * @since   1.0.0
	 *
	 * @return  bool
	 */
	private function check_for_rules() {

		$bypass_date_rules      = false;
		$bypass_post_type_rules = false;

		if ( 'yes' === $this->form_data['send2tg'] ) {

			$bypass_date_rules = true;
		}

		$bypass_date_rules = (bool) apply_filters( 'wptelegram_p2tg_bypass_post_date_rules', $bypass_date_rules, $this->post, $this->options );

		if ( ! $bypass_date_rules ) {

			$send_when = $this->options->get( 'send_when' );

			$is_new = Utils::is_post_new( $this->post );
			$is_new = (bool) apply_filters( 'wptelegram_p2tg_rules_is_new_post', $is_new, $this->post, $this->options );

			$send_new = in_array( 'new', $send_when, true );
			$send_new = (bool) apply_filters( 'wptelegram_p2tg_rules_send_new_post', $send_new, $this->post, $this->options );

			// if sending new posts is disabled and is new post.
			if ( $is_new && ! $send_new ) {
				return false;
			}

			$send_existing = in_array( 'existing', $send_when, true );
			$send_existing = (bool) apply_filters( 'wptelegram_p2tg_rules_send_existing_post', $send_existing, $this->post, $this->options );

			// if sending existing posts is disabled and is existing post.
			if ( ! $is_new && ! $send_existing ) {

				return false;
			}
		}

		$bypass_post_type_rules = (bool) apply_filters( 'wptelegram_p2tg_bypass_post_type_rules', $bypass_post_type_rules, $this->post, $this->options );

		if ( ! $bypass_post_type_rules ) {
			// Check for Post type.
			$post_types = $this->options->get( 'post_types' );

			$send_post_type = in_array( $this->post->post_type, $post_types, true );
			$send_post_type = (bool) apply_filters( 'wptelegram_p2tg_rules_send_post_type', $send_post_type, $this->post, $this->options );
			// post type specific filter.
			$send_post_type = (bool) apply_filters( 'wptelegram_p2tg_rules_send_' . $this->post->post_type, $send_post_type, $this->post, $this->options );

			if ( ! $send_post_type ) {

				return false;
			}
		}

		// finally check for custom rules.
		$bypass_custom_rules = (bool) apply_filters( 'wptelegram_p2tg_bypass_custom_rules', false, $this->post, $this->options );

		if ( $bypass_custom_rules ) {

			return true;
		}

		$rules = new Rules();

		return $rules->rules_apply( $this->options->get( 'rules' ), $this->post );
	}

	/**
	 * Get the link preview options
	 *
	 * @return array
	 */
	protected function get_link_preview_options() {
		$link_preview_options = [
			'is_disabled' => $this->options->get( 'link_preview_disabled' ),
		];

		if ( ! $link_preview_options['is_disabled'] ) {

			unset( $link_preview_options['is_disabled'] );

			$link_preview_url = $this->options->get( 'link_preview_url' );

			if ( $link_preview_url ) {

				$parser = new TemplateParser( $this->post, $this->options );

				$url = $parser->parse( $link_preview_url );

				if ( $url ) {
					$link_preview_options['url'] = $url;
				}
			}

			$link_preview_options['show_above_text'] = $this->options->get( 'link_preview_above_text', false );
		}

		return apply_filters( 'wptelegram_p2tg_link_preview_options', $link_preview_options, $this->post, $this->options );
	}

	/**
	 * Create responses based on the text and image source
	 *
	 * @since   1.0.0
	 *
	 * @param string $text         The text.
	 * @param string $image_source Image source.
	 *
	 * @return  array
	 */
	private function get_default_responses( $text, $image_source ) {

		$parse_mode = MainUtils::valid_parse_mode( $this->options->get( 'parse_mode' ) );

		$link_preview_options = $this->get_link_preview_options();
		$disable_notification = $this->options->get( 'disable_notification' );
		$protect_content      = $this->options->get( 'protect_content' );

		$limit_to_one_message = apply_filters( 'wptelegram_p2tg_limit_text_to_one_message', true, $this->post, $this->options, $text, $image_source );

		$text_options = [
			'format_to' => $parse_mode,
			'id'        => 'p2tg',
			'limit'     => $limit_to_one_message ? MainUtils::get_max_text_length( 'text' ) : 0,
			'limit_by'  => 'chars',
		];

		$caption_options = array_merge( $text_options, [ 'limit' => MainUtils::get_max_text_length( 'caption' ) ] );

		$method_params = [
			'sendPhoto'   => compact(
				'disable_notification',
				'parse_mode',
				'protect_content'
			),
			'sendMessage' => compact(
				'disable_notification',
				'link_preview_options',
				'parse_mode',
				'protect_content'
			),
		];

		if ( ! empty( $image_source ) ) {

			$image_position = $this->options->get( 'image_position' );
			$single_message = $this->options->get( 'single_message' );
			$caption        = '';

			if ( $single_message ) {
				// if only caption is to be sent.
				if ( 'before' === $image_position ) {

					// remove sendMessage.
					unset( $method_params['sendMessage'] );

					$caption = MainUtils::smart_trim_excerpt( $text, $caption_options );

				} elseif ( 'after' === $image_position && '' !== $parse_mode ) {

					$text = $this->add_hidden_image_url( $text, $parse_mode );

					// Remove "sendPhoto".
					unset( $method_params['sendPhoto'] );
				}
			} elseif ( 'after' === $image_position ) {

				$method_params = array_reverse( $method_params );
			}

			if ( isset( $method_params['sendPhoto'] ) ) {

				$caption = apply_filters( 'wptelegram_p2tg_post_image_caption', $caption, $this->post, $this->options, $text, $image_source );

				$method_params['sendPhoto']['photo']   = $image_source;
				$method_params['sendPhoto']['caption'] = $caption;
			}
		} else {
			unset( $method_params['sendPhoto'] );
		}

		$additional_text_responses = [];

		if ( isset( $method_params['sendMessage'] ) ) {

			if ( $limit_to_one_message ) {

				$text = MainUtils::smart_trim_excerpt( $text, $text_options );

			} else {
				$text_parts = MainUtils::split_content( $text, $parse_mode );
				// Extract the first piece.
				$text = array_shift( $text_parts );

				// Create additional responses for the remaining pieces.
				foreach ( $text_parts as $text_part ) {
					$additional_text_responses[] = [
						'sendMessage' => array_merge(
							$method_params['sendMessage'],
							[
								'text' => $text_part,
							]
						),
					];
				}
			}

			$method_params['sendMessage']['text'] = $text;
		}

		$method_params = (array) apply_filters( 'wptelegram_p2tg_method_params', $method_params, $this->post, $this->options, $text, $image_source );

		// passed by reference.
		$this->add_reply_markup( $method_params );

		$default_responses = [];

		foreach ( $method_params as $method => $params ) {
			$default_responses[] = [
				$method => $params,
			];
		}

		$default_responses = array_merge( $default_responses, $additional_text_responses );

		return apply_filters( 'wptelegram_p2tg_default_responses', $default_responses, $this->post, $this->options, $text, $image_source );
	}

	/**
	 * Create responses based on the files included.
	 *
	 * @since   1.0.0
	 *
	 * @param array $files The files.
	 *
	 * @return  array
	 */
	private function get_file_responses( $files ) {

		$file_responses = [];

		$caption = $this->post_data->get_field( 'post_title' );

		$size_limit = MainUtils::get_image_size_limit();

		foreach ( $files as $id => $url ) {

			$caption = apply_filters( 'wptelegram_p2tg_file_caption', $caption, $this->post, $id, $url, $this->options );

			$media_path = MainUtils::get_attachment_by_filesize( $id, $size_limit );

			if ( ! $media_path ) {
				continue;
			}

			$type = MainUtils::guess_file_type( $id, $media_path );

			$file_responses[] = [
				'send' . ucfirst( $type ) => [
					$type     => $media_path,
					'caption' => $caption,
				],
			];
		}

		return apply_filters( 'wptelegram_p2tg_file_responses', $file_responses, $this->post, $this->options, $files, $this->send_files_by_url );
	}

	/**
	 * Get the message template
	 *
	 * @since   1.0.0
	 *
	 * @return  string
	 */
	private function get_message_template() {

		$template = $this->options->get( 'message_template' );

		return apply_filters( 'wptelegram_p2tg_message_template', $template, $this->post, $this->options );
	}

	/**
	 * May by add reply_markup to the message
	 *
	 * @param array $method_params Methods and Params passed by reference.
	 */
	private function add_reply_markup( &$method_params ) {

		$inline_keyboard = $this->get_inline_keyboard( $method_params );

		if ( ! empty( $inline_keyboard ) ) {

			$reply_markup = [ 'inline_keyboard' => $inline_keyboard ];

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
	 * Get inline_keyboard
	 *
	 * @param array $method_params Methods and Params.
	 *
	 * @return  array|false
	 */
	public function get_inline_keyboard( $method_params ) {

		$inline_url_button  = $this->options->get( 'inline_url_button' );
		$inline_button_text = $this->options->get( 'inline_button_text' );
		$inline_button_url  = $this->options->get( 'inline_button_url' );

		if ( ! $inline_url_button || ! $inline_button_text || ! $inline_button_url ) {
			return false;
		}

		$url = self::get_parsed_button_url( $inline_button_url, $this->post->ID );

		if ( ! $url ) {
			return false;
		}

		$default_button = [
			'text' => $inline_button_text,
			'url'  => $url,
		];

		$default_button = (array) apply_filters( 'wptelegram_p2tg_default_inline_button', $default_button, $this->post, $method_params );

		$inline_keyboard = [
			[ $default_button ],
		];
		return (array) apply_filters( 'wptelegram_p2tg_inline_keyboard', $inline_keyboard, $this->post, $method_params );
	}

	/**
	 * Parse the tags/macros in a button URL.
	 *
	 * @since 3.0.0
	 *
	 * @param string $url_template The dynamic url template.
	 * @param int    $post_id      The post ID.
	 *
	 * @return string
	 */
	public static function get_parsed_button_url( $url_template, $post_id ) {
		$parser = new TemplateParser( $post_id );

		$button_url_filter = function ( $macro_values, $template, $post ) {
			return (array) apply_filters_deprecated(
				'wptelegram_p2tg_button_url_macro_values',
				[ $macro_values, $template, $post->ID ],
				'4.1.0',
				'wptelegram_p2tg_template_macro_values'
			);
		};

		add_filter( 'wptelegram_p2tg_template_macro_values', $button_url_filter, 10, 3 );

		$url = $parser->parse( $url_template );

		remove_filter( 'wptelegram_p2tg_template_macro_values', $button_url_filter, 10, 3 );

		return apply_filters( 'wptelegram_p2tg_parsed_button_url', $url, $url_template, $post_id );
	}

	/**
	 * Get the text based response
	 *
	 * @since   1.0.0
	 *
	 * @param string $template The message template.
	 *
	 * @return  string
	 */
	private function get_response_text( $template ) {

		$parser = new TemplateParser( $this->post, $this->options );

		$text = $parser->parse( $template );

		return apply_filters( 'wptelegram_p2tg_response_text', $text, $template, $this->post, $this->options );
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

		if ( $send_image && has_post_thumbnail( $this->post->ID ) ) {

			if ( $this->send_files_by_url ) {

				// featured image URL.
				$source = $this->post_data->get_field( 'featured_image_url' );

			} else {

				// featured image path.
				$source = $this->post_data->get_field( 'featured_image_path' );
			}
		}

		return apply_filters( 'wptelegram_p2tg_featured_image_source', $source, $this->post, $this->options, $this->send_files_by_url );
	}

	/**
	 * Send the responses
	 */
	private function send_responses() {

		// Remove query variable, if present.
		remove_query_arg( Main::PREFIX . 'error' );
		// Remove error transient.
		delete_transient( 'wptelegram_p2tg_errors' );

		$this->bot_api = new API( $this->bot_token );

		$api_responses = [];

		do_action( 'wptelegram_p2tg_before_send_responses', $this->responses, $api_responses, $this->post, $this->options, $this->bot_api );

		// if modify curl for WP Telegram.
		if ( ! $this->send_files_by_url ) {
			// modify curl.
			add_action( 'http_api_curl', [ $this, 'modify_http_api_curl' ], 10, 3 );
		}

		$channels = $this->options->get( 'channels', [] );
		$channels = (array) apply_filters( 'wptelegram_p2tg_send_to_channels', $channels, $this->responses, $this->post, $this->options, $this->bot_api );

		$message_as_reply = (bool) apply_filters( 'wptelegram_p2tg_send_message_as_reply', true, $this->post, $this->options );

		// loop through destination channels.
		foreach ( $channels as $channel ) {
			/**
			 * The api response.
			 *
			 * @var Response
			 */
			$res = false;

			// loop through the prepared responses.
			foreach ( $this->responses as $response ) {

				$params = reset( $response );
				$method = key( $response );

				// Remove note added to the chat id after "|".
				$channel = preg_replace( '/\s*\|.*?$/u', '', $channel );

				list( $params['chat_id'], $params['message_thread_id'] ) = array_pad( explode( ':', $channel ), 2, '' );

				if ( ! $params['message_thread_id'] ) {
					unset( $params['message_thread_id'] );
				}

				if ( $message_as_reply && $this->bot_api->is_success( $res ) ) {

					$result = $res->get_result();
					// send next message in reply to the previous one.
					if ( ! empty( $result['message_id'] ) ) {
						$params['reply_parameters'] = [
							'allow_sending_without_reply' => true,
							'message_id'                  => $result['message_id'],
						];
					}
				}

				/**
				 * Filters the params for the Telegram API method
				 * It can be used to modify the behavior in a number of ways
				 * You can use it to change the text based on the channel/chat
				 *
				 * @since   1.0.0
				 */
				$params = apply_filters( 'wptelegram_p2tg_api_method_params', $params, $method, $this->responses, $this->post, $this->options );

				$res = call_user_func( [ $this->bot_api, $method ], $params );

				$api_responses[ $channel ][] = $res;

				do_action( 'wptelegram_p2tg_api_response', $res, $this->responses, $this->post, $this->options, $this->bot_api );

				if ( is_wp_error( $res ) ) {
					$this->handle_wp_error( $res, $channel );
				}
			}
		}

		// remove cURL modification.
		remove_action( 'http_api_curl', [ $this, 'modify_http_api_curl' ], 10, 3 );

		// update post meta if the message was successful.
		$this->update_post_meta( $api_responses );

		do_action( 'wptelegram_p2tg_after_send_responses', $this->responses, $api_responses, $this->post, $this->options, $this->bot_api );

		return $api_responses;
	}

	/**
	 * Update post meta if the message was successful.
	 *
	 * @param Response[] $api_responses The array of responses.
	 *
	 * @since 1.0.0
	 */
	private function update_post_meta( $api_responses ) {

		foreach ( $api_responses as $responses ) {

			foreach ( $responses as $res ) {
				// if any of the responses is successful.
				if ( $this->bot_api->is_success( $res ) ) {

					$current_time = current_time( 'mysql' );

					if ( ! add_post_meta( $this->post->ID, Main::PREFIX . 'sent2tg', $current_time, true ) ) {

						update_post_meta( $this->post->ID, Main::PREFIX . 'sent2tg', $current_time );

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
	 * @param \CurlHandle $handle  The cURL handle (passed by reference).
	 * @param array       $r       The HTTP request arguments.
	 * @param string      $url     The request URL.
	 *
	 * @return void
	 */
	public function modify_http_api_curl( &$handle, $r, $url ) {

		$telegram_api_client = new Client();

		// If it's a request to Telegram API base URL.
		$to_telegram = 0 === strpos( $url, $telegram_api_client->get_base_url() );

		$by_wptelegram = ! empty( $r['headers']['wptelegram_bot'] );
		// if the request is sent to Telegram by WP Telegram.
		if ( $to_telegram && $by_wptelegram ) {

			/**
			 * Modify for files
			 */
			if ( ! $this->send_files_by_url ) {

				$types = [ 'animation', 'photo', 'audio', 'video', 'document' ];

				foreach ( $types as $type ) {

					if ( ! empty( $r['body'][ $type ] ) && file_exists( $r['body'][ $type ] ) ) {

						$r['body'][ $type ] = curl_file_create( $r['body'][ $type ] ); // phpcs:ignore
						curl_setopt( $handle, CURLOPT_POSTFIELDS, $r['body'] ); // phpcs:ignore
						break;
					}
				}
			}
		}
	}

	/**
	 * Handle WP_Error of wp_remote_post()
	 *
	 * @since   1.0.0
	 *
	 * @param WP_Error $wp_error The error.
	 * @param string   $channel  Chat ID.
	 * @return void
	 */
	private function handle_wp_error( $wp_error, $channel ) {

		$transient = 'wptelegram_p2tg_errors';

		$p2tg_errors = array_filter( (array) get_transient( $transient ) );

		$p2tg_errors[ $channel ][ $wp_error->get_error_code() ] = $wp_error->get_error_message();

		set_transient( $transient, $p2tg_errors, 60 );

		add_filter( 'redirect_post_location', [ $this, 'add_admin_notice_query_var' ], 99 );
	}

	/**
	 * Add query variable upon error.
	 *
	 * @param string $location Redirect location.
	 *
	 * @since   1.0.0
	 */
	public function add_admin_notice_query_var( $location ) {

		remove_filter( 'redirect_post_location', [ $this, __FUNCTION__ ], 99 );

		return add_query_arg( [ Main::PREFIX . 'error' => true ], $location );
	}

	/**
	 * Add hidden URL at the beginning of the text
	 *
	 * @since   1.0.0
	 *
	 * @param string $text       The message text.
	 * @param string $parse_mode The parse mode.
	 *
	 * @return string
	 */
	private function add_hidden_image_url( $text, $parse_mode ) {

		$image_url = $this->post_data->get_field( 'featured_image_url' );

		$string = '';

		if ( 'HTML' === $parse_mode ) {
			// Add Zero Width Non Joiner as the anchor text.
			$string = '<a href="' . $image_url . '">&#8204;</a>';
		}

		// if text starts with a hashtag, add a space separator.
		$separator = preg_match( '/^#/', $text ) ? ' ' : '';

		return $string . $separator . $text;
	}
}
