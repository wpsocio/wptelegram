<?php

/**
 * Class WPTelegram_Bot_API_NotePost.
 *
 *
 */
if ( ! class_exists( 'WPTelegram_Bot_API_NotePost' ) ) :
class WPTelegram_Bot_API_NotePost extends WPTelegram_Bot_API {
    const BASE_URL = 'https://tgflow.com/wptelegram/sync';

	/**
     * Instantiates a new WPTelegram_Bot_API_NotePost object.
     *
     * @param string    $bot_token   The Telegram Bot API Access Token.
     *
     */
    public function __construct( $bot_token = null ) {
		add_filter( 'wptelegram_bot_api_request_url', array( __CLASS__, 'get_base_url' ), 20, 1 );

    	parent::__construct($bot_token);
    }

    public function get_base_url() {
    	return self::BASE_URL;
	}
}
endif;
