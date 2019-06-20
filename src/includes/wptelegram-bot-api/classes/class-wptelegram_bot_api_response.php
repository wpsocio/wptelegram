<?php

/**
 * Class WPTelegram_Bot_API_Response.
 *
 * 
 */
if ( ! class_exists( 'WPTelegram_Bot_API_Response' ) ) :
class WPTelegram_Bot_API_Response {
    /**
     * @since  1.0.0
     *
     * @var bool If the response is valid JSON
     */
    protected $valid_json;

    /**
     * @since  1.0.0
     *
     * @var null|int The HTTP response code from API.
     */
    protected $response_code;
    /**
     * @since  1.0.0
     *
     * @var null|int The HTTP response message from API.
     */
    protected $response_message;

    /**
     * @since  1.0.0
     *
     * @var array The headers returned from API request.
     */
    protected $headers = null;

    /**
     * @since  1.0.0
     *
     * @var string The raw body of the response from API request.
     */
    protected $body = null;

    /**
     * @since  1.0.0
     *
     * @var array The decoded body of the API response.
     */
    protected $decoded_body = array();

    /**
     * @since  1.0.0
     *
     * @var string API Endpoint used to make the request.
     */
    protected $api_method;

    /**
     * @since  1.0.0
     *
     * @var WPTelegram_Bot_API_Request The original request that returned this response.
     */
    protected $request;

    /**
     * @since   1.0.0
     *
     * @var array   The original response from wp_remote_post
     */
    protected $raw_response;

    /**
     * Gets the relevant data from the client.
     * @since   1.0.0
     *
     * @param WPTelegram_Bot_API_Request   $request
     * @param array                         $raw_response
     */
    public function __construct( $request, $raw_response ) {
        
        $this->set_properties( $raw_response );

        $this->valid_json = $this->decode_body();

        $this->request = $request;
        $this->raw_response = $raw_response;
        $this->api_method = (string) $request->get_api_method();
    }

    /**
     * Sets the class properties
     * @since   1.0.0
     *
     */
    public function set_properties( $raw_response ) {
        $properties = array(
            'response_code',
            'response_message',
            'body',
            'headers',
        );
        foreach ( $properties as $property ) {
            $this->$property = call_user_func( 'wp_remote_retrieve_' . $property, $raw_response );
        }
    }

    /**
     * Return the original request that returned this response.
     * @since   1.0.0
     *
     * @return WPTelegram_Bot_API_Request
     */
    public function get_request() {
        return $this->request;
    }

    /**
     * Gets the original HTTP response.
     * @since   1.0.0
     *
     * @return array
     */
    public function get_raw_response() {
        return $this->raw_response;
    }

    /**
     * Gets the HTTP response code.
     * @since   1.0.0
     *
     * @return null|int
     */
    public function get_response_code() {
        return $this->response_code;
    }

    /**
     * Returns the value of valid_json
     * @since   1.0.0
     *
     * @return bool
     */
    public function is_valid_json() {
        return $this->valid_json;
    }

    /**
     * Gets the HTTP response message.
     * @since   1.0.0
     *
     * @return null|string
     */
    public function get_response_message() {
        return $this->response_message;
    }

    /**
     * Gets the Request Endpoint used to get the response.
     * @since   1.0.5
     *
     * @return string
     */
    public function get_api_method() {
        return $this->api_method;
    }

    /**
     * Return the bot access token that was used for this request.
     * @since   1.0.0
     *
     * @return string|null
     */
    public function get_bot_token() {
        return $this->request->get_bot_token();
    }

    /**
     * Return the HTTP headers for this response.
     * @since   1.0.0
     *
     * @return array
     */
    public function get_headers() {
        return $this->headers;
    }

    /**
     * Return the raw body response.
     * @since   1.0.0
     *
     * @return string
     */
    public function get_body() {
        return $this->body;
    }

    /**
     * Return the decoded body response.
     * @since   1.0.0
     *
     * @return array
     */
    public function get_decoded_body() {
        return $this->decoded_body;
    }

    /**
     * Helper function to return the payload of a successful response.
     * @since   1.0.0
     *
     * @return mixed
     */
    public function get_result() {
        return $this->decoded_body['result'];
    }

    /**
     * Converts raw API response to proper decoded response.
     * @since   1.0.0
     */
    public function decode_body() {
        $this->decoded_body = json_decode( $this->body, true );
        // check for PHP < 5.3
        if ( function_exists( 'json_last_error' ) && defined( 'JSON_ERROR_NONE' ) ) {
            return ( json_last_error() == JSON_ERROR_NONE );
        }
        return true;
    }
}
endif;