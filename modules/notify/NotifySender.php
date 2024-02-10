<?php
/**
 * The Notification Sending functionality of the plugin.
 *
 * @link       https://t.me/WPTelegram
 * @since      1.0.0
 *
 * @package    WPTelegram
 * @subpackage WPTelegram\Core\modules\notify
 */

namespace WPTelegram\Core\modules\notify;

use WPTelegram\Core\modules\BaseClass;
use WPTelegram\BotAPI\API as BotApi;
use WPTelegram\Core\includes\Utils;
use WP_User;

/**
 * The Notification Sending functionality of the plugin.
 *
 * @package    WPTelegram
 * @subpackage WPTelegram\Core\modules\notify
 * @author     WP Socio
 */
class NotifySender extends BaseClass {

	/**
	 * Bot Token to be used for Telegram API calls
	 *
	 * @since   1.0.0
	 * @access  private
	 * @var     string  Telegram Bot Token.
	 */
	private $bot_token;

	/**
	 * Args for wp_mail()
	 *
	 * @since   1.0.0
	 * @access  private
	 * @var     array
	 */
	private $wp_mail_args;

	/**
	 * Array of email headers containing chat_ids and emails
	 *
	 * @since   1.0.0
	 * @access  private
	 * @var     array
	 */
	private $chats2emails;

	/**
	 * Prepared Responses to be sent to Telegram
	 *
	 * @since   1.0.0
	 * @access  private
	 * @var     array  $responses
	 */
	private $responses;

	/**
	 * Set up the basics
	 *
	 * @since    1.0.0
	 *
	 * @param   array $args   A compacted array of wp_mail() arguments,
	 * including the "to" email, subject, message, headers, and attachments values.
	 */
	private function init( $args ) {

		$this->wp_mail_args = $args;

		$this->bot_token = WPTG()->options()->get( 'bot_token' );

		do_action( 'wptelegram_notify_init', $args );
	}

	/**
	 * Filters the wp_mail() arguments
	 *
	 * @since 1.0.0
	 *
	 * @param   array $args   A compacted array of wp_mail() arguments.
	 * including the "to" email, subject, message, headers, and attachments values.
	 */
	public function handle_wp_mail( $args ) {
		$this->init( $args );

		if ( empty( $this->bot_token ) || ! is_array( $args ) ) {
			return $this->wp_mail_args;
		}

		$_watch_emails = $this->module->options()->get( 'watch_emails' );
		$watch_emails  = array_map( 'trim', explode( ',', $_watch_emails ) );
		$watch_emails  = array_map( 'strtolower', $watch_emails );

		$notify_chat_ids = array_map( 'trim', $this->module->options()->get( 'chat_ids', [] ) );
		$user_notify     = $this->module->options()->get( 'user_notifications' );

		$this->chats2emails = [];
		$no_chat_emails     = [];

		if ( ! apply_filters( 'wptelegram_notify_send_notification', true, $args ) ) {
			return $args;
		}

		$to = $args['to'];

		if ( ! is_array( $to ) ) {
			$to = explode( ',', $to );
		}
		$to = array_map( 'trim', $to );

		foreach ( (array) $to as $recipient ) {
			// Break $recipient into name and address parts if in the format "Foo <bar@baz.com>".
			if ( preg_match( '/(.*)<(.+)>/', $recipient, $matches ) && count( $matches ) === 3 ) {
				$email = $matches[2];
			} else {
				$email = $recipient;
			}

			// for comparisons.
			$email = strtolower( $email );

			$chat_id = $this->get_user_chat_id( $email );

			if ( $user_notify && $chat_id ) {

				$this->chats2emails[ $chat_id ] = $email;

			} elseif ( 'any' === $_watch_emails || in_array( $email, $watch_emails, true ) ) {

				foreach ( $notify_chat_ids as $chat_id ) {
					$this->chats2emails[ $chat_id ] = $email;
				}
			} else {
				$no_chat_emails[] = $recipient; // User not having Chat ID.
			}
		}

		if ( ! empty( $this->chats2emails ) ) {

			$this->prepare_default_responses();

			if ( ! empty( $this->responses ) ) {

				$this->send_responses();
			}
		}

		do_action( 'wptelegram_notify_finish', $args, $this->module->options() );

		if ( apply_filters( 'wptelegram_notify_abort_email', false, $args ) ) {
			$args['to'] = $no_chat_emails;
		}
		return $args;
	}

	/**
	 * Prepare the text to be sent to Telegram.
	 *
	 * @since   1.0.0
	 *
	 * @access  private
	 */
	private function prepare_default_responses() {

		$this->responses = [];

		$template = $this->get_message_template();

		$text = $this->get_response_text( $template );

		if ( ! empty( $text ) ) {

			$options = $this->get_prepare_content_options( Utils::get_max_text_length( 'text' ) );

			$this->responses = [
				[
					'sendMessage' => [
						'text'                 => Utils::prepare_content( $text, $options ),
						'parse_mode'           => $options['format_to'],
						'link_preview_options' => [ 'is_disabled' => true ],
					],
				],
			];
		}

		$this->responses = apply_filters( 'wptelegram_notify_default_responses', $this->responses, $this->wp_mail_args, $this->chats2emails, $this->module->options() );
	}

	/**
	 * Get the message template
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	private function get_message_template() {

		$template = $this->module->options()->get( 'message_template', '' );

		return apply_filters( 'wptelegram_notify_message_template', $template, $this->wp_mail_args, $this->chats2emails, $this->module->options() );
	}

	/**
	 * Get the text based response
	 *
	 * @since 1.0.0
	 *
	 * @param string $template The message template.
	 *
	 * @return string
	 */
	private function get_response_text( $template ) {

		$macro_keys = [ 'email_subject', 'email_message' ];
		// Use this filter to add your own macros.
		$macro_keys = (array) apply_filters( 'wptelegram_notify_macro_keys', $macro_keys, $this->wp_mail_args, $this->chats2emails, $this->module->options() );

		$macro_values = [];

		foreach ( $macro_keys as $macro_key ) {

			$macro = '{' . $macro_key . '}';

			// get the value only if it's in the template.
			if ( false !== strpos( $template, $macro ) ) {

				$macro_values[ $macro ] = $this->get_macro_value( $macro_key );
			}
		}

		/**
		 * Use this filter to replace your own macros
		 * with the corresponding values
		 */
		$macro_values = (array) apply_filters( 'wptelegram_notify_macro_values', $macro_values, $this->wp_mail_args, $this->chats2emails, $this->module->options() );

		$text = str_replace( array_keys( $macro_values ), array_values( $macro_values ), $template );

		return apply_filters( 'wptelegram_notify_response_text', $text, $template, $this->wp_mail_args, $this->chats2emails, $this->module->options() );
	}

	/**
	 * Get the text for the given macro.
	 *
	 * @param string $macro The macro to get the text for.
	 *
	 * @return string The text for the given macro.
	 */
	private function get_macro_value( $macro ) {

		$value = '';

		$options = $this->get_prepare_content_options();

		switch ( $macro ) {
			case 'email_message':
				$value = $this->prepare_email_message( $this->wp_mail_args['message'], $this->wp_mail_args['headers'] );
				$value = Utils::prepare_content( $value, $options );
				break;

			case 'email_subject':
				$value = wp_strip_all_tags( $this->wp_mail_args['subject'], true );
				$value = Utils::prepare_content( $value, $options );
				break;
		}

		$value = apply_filters( 'wptelegram_notify_macro_value', $value, $macro, $this->wp_mail_args, $this->module->options() );

		return apply_filters( "wptelegram_notify_macro_{$macro}_value", $value, $this->wp_mail_args, $this->module->options() );
	}


	/**
	 * Get the options for prepare_content
	 *
	 * @since 4.0.7
	 *
	 * @param int $limit The limit.
	 *
	 * @return array
	 */
	private function get_prepare_content_options( $limit = 0 ) {
		$parse_mode = Utils::valid_parse_mode( $this->module->options()->get( 'parse_mode', 'HTML' ) );

		$options = [
			'format_to'       => $parse_mode,
			'id'              => 'notify',
			'limit'           => $limit,
			'limit_by'        => 'chars',
			'text_hyperlinks' => 'retain',
			'images_in_links' => [
				'title_or_alt'    => 'retain',
				'lone_image_link' => 'retain',
			],
		];

		return apply_filters( 'wptelegram_notify_prepare_content_options', $options, $limit, $this->wp_mail_args, $this->chats2emails, $this->module->options() );
	}

	/**
	 * Prepare the email message.
	 *
	 * The function:
	 * 1. Converts the quoted-printable message to an 8 bit string
	 *    if "Content-Transfer-Encoding" is "quoted-printable"
	 *
	 * @since 4.0.0
	 *
	 * @param string       $message The email message.
	 * @param string|array $headers The email headers.
	 *
	 * @return string
	 */
	private function prepare_email_message( $message, $headers ) {

		$headers_str = is_array( $headers ) ? implode( "\n", $headers ) : $headers;

		if ( preg_match( '/Content-Transfer-Encoding:\s*?quoted-printable/i', $headers_str ) ) {
			$message = quoted_printable_decode( $message );
		}

		return apply_filters( 'wptelegram_notify_prepare_email_message', $message, $headers, $this->wp_mail_args, $this->module->options() );
	}

	/**
	 * Send the messages
	 *
	 * @since 1.0.0
	 */
	public function send_responses() {

		$tg_api = new BotApi( $this->bot_token );

		do_action( 'wptelegram_notify_before_send_responses', $this->wp_mail_args, $this->chats2emails, $this->module->options(), $this->responses );

		// loop through instance destination channels.
		foreach ( $this->chats2emails as $chat_id => $email ) {

			// loop through the prepared responses.
			foreach ( $this->responses as $response ) {

				$params = reset( $response );
				$method = key( $response );

				// Remove note added to the chat id after "|".
				$chat_id = preg_replace( '/\s*\|.*?$/u', '', $chat_id );

				list( $params['chat_id'], $params['message_thread_id'] ) = array_pad( explode( ':', $chat_id ), 2, '' );

				if ( ! $params['message_thread_id'] ) {
					unset( $params['message_thread_id'] );
				}

				$params = apply_filters( 'wptelegram_notify_api_method_params', $params, $method, $this->wp_mail_args, $this->chats2emails, $this->module->options() );

				$api_res = call_user_func( [ $tg_api, $method ], $params );

				do_action( 'wptelegram_notify_api_response', $api_res, $tg_api, $response, $email, $this->wp_mail_args, $this->chats2emails, $this->module->options() );
			}
		}

		do_action( 'wptelegram_notify_after_send_responses', $this->wp_mail_args, $this->chats2emails, $this->module->options(), $this->responses, $this );
	}

	/**
	 * Get Telegram Chat ID from email address
	 *
	 * @since   1.0.0
	 *
	 * @param   string $email  Email ID of the user.
	 * @return  int
	 */
	private function get_user_chat_id( $email ) {
		$chat_id = 0;

		$user = get_user_by( 'email', $email );

		if ( $user instanceof WP_User ) {

			$chat_id = $user->{WPTELEGRAM_USER_ID_META_KEY};
		}
		return apply_filters( 'wptelegram_notify_user_chat_id', $chat_id, $email, $this->wp_mail_args );
	}
}
