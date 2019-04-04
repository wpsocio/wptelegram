<?php

/**
 * Proxy Handling functionality of the plugin.
 *
 * @link       https://t.me/WPTelegram
 * @since      1.0.0
 *
 * @package    WPTelegram
 * @subpackage WPTelegram/includes
 */

/**
 * The Proxy Handling functionality of the plugin.
 *
 * @package    WPTelegram
 * @subpackage WPTelegram/includes
 * @author     Manzoor Wani <@manzoorwanijk>
 */
class WPTelegram_Proxy_Handler extends WPTelegram_Module_Base {

    /**
     * @var array The proxy configuration
     * array(
     *  'host'      => '',
     *  'port'      => '',
     *  'username'  => '',
     *  'password'  => '',
     * )
     */
    private static $proxy;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $module_name, $module_title ) {

		parent::__construct( $module_name, $module_title );
	}

    /**
     * configure the proxy
     *
     * @since  1.7.8
     */
    public function configure_proxy() {
        self::setup_proxy();
    }

    /**
     * remove the proxy
     *
     * @since  2.1.2
     */
    public function remove_proxy() {
        remove_action( 'http_api_curl', array( __CLASS__, 'modify_http_api_curl' ), 10, 3 );
    }

    /**
     * Setup the proxy
     *
     * @since  2.0.8
     *
     */
    public static function setup_proxy() {

    	$proxy_method = WPTG()->options( 'proxy' )->get( 'proxy_method' );
		$script_url = WPTG()->options( 'proxy' )->get( 'script_url' );

		if ( ! $proxy_method ) {
			return;
		}

		if ( 'php_proxy' === $proxy_method ) {

			self::setup_php_proxy();

		} elseif ( $script_url ) {
			// setup Google Script args
			add_filter( 'wptelegram_bot_api_remote_post_args', array( __CLASS__, 'google_script_request_args' ), 20, 2 );
			// set URL
			add_filter( 'wptelegram_bot_api_request_url', array( __CLASS__, 'google_script_request_url' ), 20, 1 );
		}
    }

    /**
     * Setup PHP proxy
     *
     * @since  2.0.8
     *
     */
    private static function setup_php_proxy() {
        $defaults = array(
			'host'		=> '',
			'port'		=> '',
			'type'		=> '',
			'username'	=> '',
			'password'	=> '',
		);

		// get the values from settings/defaults
		foreach ( $defaults as $key => $value ) {
			self::$proxy[ $key ] = WPTG()->options( 'proxy' )->get( 'proxy_' . $key, '' );
		}

        // modify curl
        add_action( 'http_api_curl', array( __CLASS__, 'modify_http_api_curl' ), 10, 3 );
    }

    /**
     * Returns The proxy options
     *
     * @return array
     */
    private static function get_proxy() {
        return (array) apply_filters( 'wptelegram_bot_api_curl_proxy', self::$proxy );
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
    public static function modify_http_api_curl( &$handle, $r, $url ) {

        $bot_api_url = 'https://api.telegram.org/bot';
        $user_link = 'https://t.me/';

        $pattern = '/^(?:' . preg_quote( $bot_api_url, '/' ) . '|' . preg_quote( $user_link, '/' ) . ')/i';

        $to_telegram = preg_match( $pattern, $url );

        $by_wptelegram = ( isset( $r['headers']['wptelegram_bot'] ) && $r['headers']['wptelegram_bot'] );
        
        // if the request is sent to Telegram by WP Telegram
        if ( $to_telegram && $by_wptelegram ) {

            foreach ( self::get_proxy() as $option => $value ) {
                ${'proxy_' . $option} = apply_filters( "wptelegram_bot_api_curl_proxy_{$option}", $value );
            }

            if ( ! empty( $proxy_host ) && ! empty( $proxy_port ) ) {
                
                curl_setopt( $handle, CURLOPT_PROXYTYPE, constant( $proxy_type ) );
                curl_setopt( $handle, CURLOPT_PROXY, $proxy_host );
                curl_setopt( $handle, CURLOPT_PROXYPORT, $proxy_port );

                if ( ! empty( $proxy_username ) && ! empty( $proxy_password ) ) {
                    $authentication = $proxy_username . ':' . $proxy_password;
                    curl_setopt( $handle, CURLOPT_PROXYAUTH, CURLAUTH_ANY );

                    curl_setopt( $handle, CURLOPT_PROXYUSERPWD, $authentication );
                }
            }
        }
    }

	/**
	 * Set Google Script args
	 *
	 * @since  1.7.8
	 */
	public static function google_script_request_args( $args, $request ) {

		$args['body'] = array(
			'bot_token'	=> $request->get_bot_token(),
			'method'	=> $request->get_api_method(),
			'args'		=> json_encode( $args['body'] ),
		);
		$args['method'] = 'GET';

		return $args;
	}

	/**
	 * Set Google Script URL
	 *
	 * @since  1.7.8
	 */
	public static function google_script_request_url( $url ) {

		return WPTG()->options( 'proxy' )->get( 'script_url' );
	}
}