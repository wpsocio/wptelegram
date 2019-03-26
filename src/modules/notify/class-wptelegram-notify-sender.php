<?php

/**
 * The Notification Sending functionality of the plugin.
 *
 * @link       https://t.me/WPTelegram
 * @since      1.0.0
 *
 * @package    WPTelegram
 * @subpackage WPTelegram/includes
 */

/**
 * The Notification Sending functionality of the plugin.
 *
 * @package    WPTelegram
 * @subpackage WPTelegram/includes
 * @author     Manzoor Wani <@manzoorwanijk>
 */
class WPTelegram_Notify_Sender extends WPTelegram_Module_Base {

    /**
	 * Bot Token to be used for Telegram API calls
	 *
	 * @since  	1.0.0
	 * @access 	private
     * @var		string	Telegram Bot Token.
     */
    private $bot_token;

	/**
	 * wp_mail arguments
	 *
	 * @since  	1.0.0
	 * @access 	private
	 * @var  	array
	 */
	private $wp_mail_args;

	/**
	 * Array of email headers containing chat_ids and emails
	 *
	 * @since  	1.0.0
	 * @access 	private
	 * @var  	array
	 */
	private $chats2emails;

	/**
	 * Prepared Responses to be sent to Telegram
	 *
	 * @since	1.0.0
	 * @access	private
	 * @var		array		$responses
	 */
	private $responses;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $module_name, $module_title ) {

		parent::__construct( $module_name, $module_title );
	}

	/**
	 * Set up the basics
	 *
	 * @since    1.0.0
	 * 
	 * @param	array	$args	A compacted array of wp_mail() arguments,
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
	 * @since	1.0.0
	 *
	 * @param	array	$args	A compacted array of wp_mail() arguments,
	 * including the "to" email, subject, message, headers, and attachments values.
	 */
	public function handle_wp_mail( $args ) {

		$this->init( $args );

		if ( empty( $this->bot_token ) || ! is_array( $args ) ) {
			return $this->wp_mail_args;
		}

		$_watch_emails = $this->module_options->get( 'watch_emails' );
		$watch_emails = array_map( 'trim', explode( ',', $_watch_emails ) );
		$watch_emails = array_map( 'strtolower', $watch_emails );

		$notify_chat_ids = explode( ',', $this->module_options->get( 'chat_ids', '' ) );
		$user_notify = $this->module_options->get( 'user_notifications' );

		$this->chats2emails = array();
		$no_chat_emails = array();

		extract( $args ); // 'to', 'subject', 'message', 'headers', 'attachments'

		if ( ! apply_filters( 'wptelegram_notify_send_notification', true, $args ) ) {
			return $args;
		}

		if ( ! is_array( $to ) ) {
			$to = explode( ',', $to );
		}

		foreach ( (array) $to as $recipient ) {
			// Break $recipient into name and address parts if in the format "Foo <bar@baz.com>"
			if ( preg_match( '/(.*)<(.+)>/', $recipient, $matches ) && count( $matches ) == 3 ) {
				$email = $matches[2];
			} else {
				$email = $recipient;
			}

			// for comparisons
			$email = strtolower( $email );

			if ( 'on' === $user_notify && $chat_id = $this->get_user_chat_id( $email ) ) {

				$this->chats2emails[ $chat_id ] = $email;

			} elseif ( 'any' == $_watch_emails || in_array( $email, $watch_emails ) ) {

				foreach ( $notify_chat_ids as $chat_id ) {
					
					$this->chats2emails[ $chat_id ] = $email;
				}

			} else {
				$no_chat_emails[] = $recipient; // User not having Chat ID
			}
		}

		if ( ! empty( $this->chats2emails ) ) {

			$this->prepare_default_responses();

			if ( ! empty( $this->responses ) ) {

				$this->send_responses();
			}
		}

		do_action( 'wptelegram_notify_finish', $args, $this->module_options );

		if ( apply_filters( 'wptelegram_notify_abort_email', false, $args ) ) {
			$to = $no_chat_emails;
			return compact( 'to', 'subject', 'message', 'headers', 'attachments' );
		}
		return $args;
	}

	/**
	 * prepare the text to be sent to Telegram
	 *
	 * @since	1.0.0
     * 
     * @access	private
	 */
	private function prepare_default_responses() {

		$this->responses = array();

		$template = $this->get_message_template();

		$text = $this->get_response_text( $template );

		if ( ! empty( $text ) ) {

			$parse_mode = WPTG()->helpers->valid_parse_mode( $this->module_options->get( 'parse_mode', 'HTML' ) );

			$this->responses = array(
				array(
					'sendMessage'	=> compact( 'text', 'parse_mode' ),
				),
			);
		}

		$this->responses = apply_filters( 'wptelegram_notify_default_responses', $this->responses, $this->wp_mail_args, $this->chats2emails, $this->module_options );
	}

	/**
	 * Get the message template
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	private function get_message_template() {

		$template = $this->module_options->get( 'message_template', '' );

		$template = stripslashes( json_decode( $template ) );

		return apply_filters( 'wptelegram_notify_message_template', $template, $this->wp_mail_args, $this->chats2emails, $this->module_options );
	}

	/**
	 * Get the text based response
	 *
	 * @since 1.0.0
	 * 
	 * @param $template string
	 *
	 * @return string
	 */
	private function get_response_text( $template ) {

		extract( $this->wp_mail_args ); // $to, $subject, $message, $headers, $attachments

		// email subject
		$subject = htmlspecialchars( wp_strip_all_tags( $subject, true ) );
		$subject = apply_filters( 'wptelegram_notify_email_subject', $subject, $this->wp_mail_args, $this->chats2emails, $this->module_options );

		// email message (body)
		$message = convert_html_to_text( $message, true );
		$message = $this->convert_links_for_parsing( $message );
		$message = apply_filters( 'wptelegram_notify_email_message', $message, $this->wp_mail_args, $this->chats2emails, $this->module_options );

		$macro_values = array(
			'{email_subject}'	=>	$subject,
			'{email_message}'	=>	$message,
		);
		/**
         * Use this filter to replace your own macros
         * with the corresponding values
         */
		$macro_values = (array) apply_filters( 'wptelegram_notify_macro_values', $macro_values, $this->wp_mail_args, $this->chats2emails, $this->module_options );

		$text = str_replace( array_keys( $macro_values ), array_values( $macro_values ), $template );

		return apply_filters( 'wptelegram_notify_response_text', $text, $template, $this->wp_mail_args, $this->chats2emails, $this->module_options );
	}

	/**
	 * Send the messages
	 *
	 * @since 1.0.0
	 *
	 */
	public function send_responses() {

		$tg_api = new WPTelegram_Bot_API( $this->bot_token );

		do_action( 'wptelegram_notify_before_send_responses', $this->wp_mail_args, $this->chats2emails, $this->module_options, $this->responses );

		// loop through instance destination channels
		foreach ( $this->chats2emails as $chat_id => $email ) {

			// loop through the prepared responses
			foreach ( $this->responses as $response ) {
				
				$params = reset( $response );
				$method = key( $response );

				$params['chat_id'] = $chat_id;

				$params = apply_filters( 'wptelegram_notify_api_method_params', $params, $method, $this->wp_mail_args, $this->chats2emails, $this->module_options );

				$api_res = call_user_func( array( $tg_api, $method ), $params );

				do_action( 'wptelegram_notify_api_response', $api_res, $tg_api, $response, $email, $this->wp_mail_args, $this->chats2emails, $this->module_options );
			}
		}

		do_action( 'wptelegram_notify_after_send_responses', $this->wp_mail_args, $this->chats2emails, $this->module_options, $this->responses, $this );
	}

	/** 
	 * Get Telegram Chat ID from email address
	 *
	 * @since	1.0.0
	 * 
	 * @param	string	$email	Email ID of the user
	 * @return	int
	 */
	private function get_user_chat_id( $email ) {
		$chat_id = 0;

		$user = get_user_by( 'email', $email );

		if ( $user instanceof WP_User ) {

			$chat_id = $user->telegram_chat_id;
		}
		return apply_filters( 'wptelegram_notify_user_chat_id', $chat_id, $email, $this->wp_mail_args );
	}

	/**
	 * [text](url) to <a href="url">text</a>
	 *
	 * @since	1.0.0
	 * 
     * @param	string	$text
	 */
	private function convert_links_for_parsing( $text ) {
		
		$parse_mode = WPTG()->helpers->valid_parse_mode( $this->module_options->get( 'parse_mode', 'HTML' ) );

		if ( 'Markdown' !== $parse_mode ) {
			$text = preg_replace( '/\[([^\]]+?)\]\(([^\)]+?)\)/ui', '<a href="$2">$1</a>', $text );

			if ( 'HTML' !== $parse_mode ) {
				$text = wp_strip_all_tags( $text, false );
			}
		}
		return $text;
	}
}