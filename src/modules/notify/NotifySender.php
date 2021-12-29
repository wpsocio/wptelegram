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

			$parse_mode = Utils::valid_parse_mode( $this->module->options()->get( 'parse_mode', 'HTML' ) );

			$disable_web_page_preview = true;

			$this->responses = [
				[
					'sendMessage' => compact( 'text', 'parse_mode', 'disable_web_page_preview' ),
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

		// email subject.
		$subject = htmlspecialchars( wp_strip_all_tags( $this->wp_mail_args['subject'], true ) );
		$subject = apply_filters( 'wptelegram_notify_email_subject', $subject, $this->wp_mail_args, $this->chats2emails, $this->module->options() );

		// email message (body).
		$message = convert_html_to_text( $this->wp_mail_args['message'], true );
		$message = $this->convert_links_for_parsing( $message );
		$message = apply_filters( 'wptelegram_notify_email_message', $message, $this->wp_mail_args, $this->chats2emails, $this->module->options() );

		$macro_values = [
			'{email_subject}' => $subject,
			'{email_message}' => $message,
		];
		/**
		 * Use this filter to replace your own macros
		 * with the corresponding values
		 */
		$macro_values = (array) apply_filters( 'wptelegram_notify_macro_values', $macro_values, $this->wp_mail_args, $this->chats2emails, $this->module->options() );

		$text = str_replace( array_keys( $macro_values ), array_values( $macro_values ), $template );

		return apply_filters( 'wptelegram_notify_response_text', $text, $template, $this->wp_mail_args, $this->chats2emails, $this->module->options() );
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

				$params['chat_id'] = $chat_id;

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

	/**
	 * [text](url) to <a href="url">text</a>
	 *
	 * @since   1.0.0
	 *
	 * @param string $text The text to convert.
	 */
	private function convert_links_for_parsing( $text ) {

		$parse_mode = Utils::valid_parse_mode( $this->module->options()->get( 'parse_mode', 'HTML' ) );

		if ( 'Markdown' !== $parse_mode ) {
			$text = preg_replace( '/\[([^\]]+?)\]\(([^\)]+?)\)/ui', '<a href="$2">$1</a>', $text );

			if ( 'HTML' !== $parse_mode ) {
				$text = wp_strip_all_tags( $text, false );
			}
		}
		return $text;
	}
}
