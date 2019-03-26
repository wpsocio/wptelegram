<?php

/**
 * Class WPTelegram_Bot_API_Client.
 *
 * 
 */
if ( ! class_exists( 'WPTelegram_Bot_API_Client' ) ) :
class WPTelegram_Bot_API_Client {
    /**
     * @const string Telegram Bot API URL.
     *
     * @since  1.0.0
     */
    const BASE_URL = 'https://api.telegram.org/bot';

    /**
     * Returns the base URL of the Bot API.
     *
     * @since  1.0.0
     *
     * @return string
     */
    public function get_base_url() {
        return self::BASE_URL;
    }

    /**
     * Prepares the API request for sending to the client
     *
     * @since  1.0.0
     *
     * @param WPTelegram_Bot_API_Request $request
     *
     * @return array
     */
    public function prepare_request( $request ) {
        $url = $this->get_base_url() . $request->get_bot_token() . '/' . $request->get_api_method();

        return array(
            $url,
            $request->get_params(),
        );
    }

    /**
     * Send an API request and process the result.
     *
     * @since  1.0.0
     *
     * @param WPTelegram_Bot_API_Request $request
     *
     * @return WP_Error|WPTelegram_Bot_API_Response
     */
    public function sendRequest( $request ) {
        list( $url, $params ) = $this->prepare_request( $request );

        $args = array(
            'timeout'   => 20, //seconds
            'blocking'  => true,
            'headers'   => array( 'wptelegram_bot' => true ),
            'body'      => $params,
            'sslverify' => true,
        );

        foreach ( $args as $argument => $value ) {
            $args[ $argument ] = apply_filters( "wptelegram_bot_api_request_arg_{$argument}", $value, $request );
        }

        $url = apply_filters( 'wptelegram_bot_api_request_url', $url );

        $args = apply_filters( 'wptelegram_bot_api_remote_post_args', $args, $request );

        // send the request
        $raw_response = wp_remote_post( $url, $args );

        if ( ! is_wp_error( $raw_response ) ) {
            return $this->get_response( $request, $raw_response );
        }

        return $raw_response;
    }

    /**
     * Creates response object.
     *
     * @since  1.0.0
     *
     * @param WPTelegram_Bot_API_Request   $request
     * @param array                         $raw_response
     *
     * @return WPTelegram_Bot_API_Response
     */
    protected function get_response( $request, $raw_response ) {
        return new WPTelegram_Bot_API_Response( $request, $raw_response );
    }
}
endif;