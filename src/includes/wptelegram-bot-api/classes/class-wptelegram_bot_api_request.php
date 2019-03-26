<?php

/**
 * Class WPTelegram_Bot_API_Request.
 *
 * 
 */
if ( ! class_exists( 'WPTelegram_Bot_API_Request' ) ) :
class WPTelegram_Bot_API_Request {
    /**
     * @since  1.0.0
     *
     * @var string|null The bot access token to use for this request.
     */
    protected $bot_token;

    /**
     * @since  1.0.5
     *
     * @var string The API api_method for this request.
     */
    protected $api_method;

    /**
     * @since  1.0.0
     *
     * @var array The parameters to send with this request.
     */
    protected $params = array();

    /**
     * Creates a new WPTelegram_Bot_API_Request
     *
     * @param string|null $bot_token
     * @param string|null $api_method
     * @param array|null  $params
     */
    public function __construct( $bot_token = null, $api_method = null, array $params = array() ) {
        $this->set_bot_token( $bot_token );
        $this->set_api_method( $api_method );
        $this->set_params( $params );
    }

    /**
     * Set the bot token for this request.
     *
     * @since  1.0.0
     *
     * @param string
     *
     * @return WPTelegram_Bot_API_Request
     */
    public function set_bot_token( $bot_token ) {
        $this->bot_token = $bot_token;

        return $this;
    }

    /**
     * Return the bot token for this request.
     *
     * @since  1.0.0
     *
     * @return string|null
     */
    public function get_bot_token() {
        return $this->bot_token;
    }

    /**
     * Set the api_method for this request.
     *
     * @since  1.0.5
     *
     * @param string $api_method
     *
     * @return WPTelegram_Bot_API_Request
     */
    public function set_api_method( $api_method ) {
        $this->api_method = $api_method;
        return $this;
    }

    /**
     * Return the API Endpoint for this request.
     *
     * @since  1.0.5
     *
     * @return string
     */
    public function get_api_method() {
        return $this->api_method;
    }

    /**
     * Set the params for this request.
     *
     * @since  1.0.0
     *
     * @param array $params
     *
     * @return WPTelegram_Bot_API_Request
     */
    public function set_params( array $params = array() ) {
        $this->params = array_merge( $this->params, $params );

        return $this;
    }

    /**
     * Return the params for this request.
     *
     * @since  1.0.0
     *
     * @return array
     */
    public function get_params() {
        return $this->params;
    }
}
endif;