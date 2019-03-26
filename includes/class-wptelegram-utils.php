<?php

/**
 * WPTelegram Utilities
 *
 * @link	   https://t.me/manzoorwanijk
 * @since	  1.0.0
 *
 * @package	WPTelegram
 * @subpackage WPTelegram/includes
 */
class WPTelegram_Utils {

	/**
	 * The single instance of the class.
	 *
	 * @since 1.0.0
	 */
	protected static $_instance = null;

	/**
	 * Main class Instance.
	 *
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * 
	 * @return Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Check if WP version is at least $version.
	 *
	 * @since  2.0.13
	 * @param  string $version WP version string to compare.
	 * @return bool            Result of comparison check.
	 */
	public static function wp_at_least( $version ) {
		return version_compare( get_bloginfo( 'version' ), $version, '>=' );
	}

	/**
	 * Sanitize the input
	 *
	 * @param  mixed	$input
	 * @param  bool		$typefy Whether to convert strings to the appropriate data type 
	 * @since  1.0.0
	 * 
	 * @return mixed
	 */
	public function sanitize( $input, $typefy = false ) {

		$raw_input = $input;

		if ( is_array( $input ) ) {

			foreach ( $input as $key => $value ) {

				$input[ sanitize_text_field( $key ) ] = $this->sanitize( $value, $typefy );
			}
		} else {
			$input = sanitize_text_field( $input );

			// avoid numeric or boolean values as strings
			if ( $typefy ) {

				$input = $this->typefy( $input );
			}
		}

		return apply_filters( 'wptelegram_utils_sanitize', $input, $raw_input, $typefy );
	}

	/**
	 * Convert the input into the proper data type
	 *
	 * @param  mixed	$input
	 * @since  1.0.0
	 * 
	 * @return mixed
	 */
	public function typefy( $input ) {

		$raw_input = $input;

		if ( is_numeric( $input ) ) {

			$input = intval( $input );

		} elseif ( is_string( $input ) && preg_match( '/^(?:true|false)$/i', $input ) ) {

			$input = ( 'true' == strtolower( $input ) ) ? true : false;
		}

		return apply_filters( 'wptelegram_utils_typefy', $input, $raw_input );
	}

	/**
	 * Converts an error to a response object.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Error $error WP_Error instance.
	 */
	public function error_to_response( $error, $json_encode = false ) {

		$response = array(
			'ok'			=> false,
			'error_code'	=> 500,
			'description'	=> $error->get_error_code() . ' - ' . $error->get_error_message(),
		);

		if ( $json_encode ) {
			return json_encode( $response );
		}

		return apply_filters( 'wptelegram_utils_error_to_response', $response, $error, $json_encode );
	}

	/**
	 * Sends an HTTP status code.
	 *
	 * @since 1.0.0
	 *
	 * @param int $code HTTP status.
	 */
	public function set_status_header( $code ) {

		$protocol = function_exists( 'wp_get_server_protocol' ) ? wp_get_server_protocol() : $this->server_protocol();
		
		$description = get_status_header_desc( $code );

		$status_header = "{$protocol} {$code} {$description}";

		@header( $status_header, true, $code );
	}

	/**
	 * Set HTTP status header.
	 *
	 * @since 1.0.0
	 * 
	 * @return string
	 */
	public function server_protocol() {

		$protocol = $_SERVER['SERVER_PROTOCOL'];
		if ( ! in_array( $protocol, array( 'HTTP/1.1', 'HTTP/2', 'HTTP/2.0' ) ) ) {
			$protocol = 'HTTP/1.0';
		}
		return $protocol;
	}

	/**
	 * For PHP < 5.4 and WP < 4.1
	 * 
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function json_encode( $value, $option = 128 ) {

		$param_arr = func_get_args();

		if ( version_compare( PHP_VERSION, '5.4.0', '<' ) ) {
			// remove second param
			unset( $param_arr[1] );
		}

		return call_user_func_array( 'json_encode', $param_arr );
	}

	/**
	 * dirty hack for PHP < 5.4
	 *
	 * has $delimiter param
	 * 
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function ucwords( $str, $delimiters = " \t\r\n\f\v" ) {

		$raw_str = $str;

		$delims = preg_split( '//u', $delimiters, -1, PREG_SPLIT_NO_EMPTY );

		foreach ( $delims as $delim ) {
			
			if ( false !== strpos( $str, $delim ) ) {

				$str = implode( $delim, array_map( 'ucfirst', explode( $delim, $str ) ) );
			}
		}

		return apply_filters( 'wptelegram_utils_ucwords', $str, $delimiters, $raw_str );
	}

	/**
	 * Get file type from extension
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function guess_file_type( $id, $file ) {

		$filetype = get_post_mime_type( $id );

		if ( empty( $filetype ) ) {
			$filetype = wp_check_filetype( $file );
			$filetype = $filetype['type'];
		}

		// default type
		$type = 'document';

		if ( ! empty( $filetype ) ) {
			$filetype = explode( '/', $filetype );

			$type = reset( $filetype );

			switch ( $type ) {
				case 'video':
				case 'audio':
					break; 
				case 'image':
					$type = 'photo';
					break;
				default: 
					$type = 'document';
					break;
			}
		}

		return apply_filters( 'wptelegram_utils_file_type', $type, $id, $file );
	}

	/**
	 * Sanitizes hashtag(s)
	 *
	 * Specifically, spaces are removed
	 *
	 * @since 1.0.0
	 *
	 * @param string|array $hashtag The string or array of strings to be sanitized.
	 * 
	 * @return string|array The sanitized string or array of strings
	 */
	public function sanitize_hashtag( $hashtag ) {
			
		$raw_hashtag = $hashtag;

		if ( is_array( $hashtag ) ) {
			foreach ( $hashtag as &$string ) {
				$string = $this->strip_non_word_chars( $string );
			}
		} else {
			$hashtag = $this->strip_non_word_chars( $hashtag );
		}

		return apply_filters( 'wptelegram_utils_sanitize_hashtag', $hashtag, $raw_hashtag );
	}

	/**
	 * Strips non-word characters from the string
	 * or replaces them with underscore
	 *
	 * @since 1.0.0
	 *
	 * @param string $text The target string
	 * 
	 * @return string
	 */
	public function strip_non_word_chars( $text ) {
		$raw_text = $text;
		// remove trailing non-word characters
		$text = preg_replace( '/(^\W+|\W+$)/u', '', $text );
		// replace one or more continuous non-word characters by _
		$text = preg_replace( '/\W+/u', '_', $text );

		return apply_filters( 'wptelegram_utils_strip_non_word_chars', $text, $raw_text );
	}

	/**
	 * Trims text to a certain number of words.
	 *
	 * @since 2.1.0
	 *
	 * @return string|NULL
	 */
	public function trim_words( $text, $num_words = 55, $more = null, $preserve_eol = false ) {

		if ( ! $preserve_eol ) {
			return wp_trim_words( $text, $num_words, $more );
		}

		if ( null === $more ) {
			$more = '&hellip;';
		}

		$original_text = $text;
		$text          = trim( wp_strip_all_tags( $text ) );
		$total_words   = preg_match_all( '/[\n\r\t\s]*[^\n\r\t\s]+/', $text );

		// if total words are greater than num_words.
		if ( $total_words > $num_words ) {
			$pattern = '/((?:[\n\r\t\s]*[^\n\r\t\s]+){1,' . $num_words . '}).*/su';
			$text    = preg_replace( $pattern, '${1}', $text ) . $more;
		}

		// remove multiple newlines.
		$text = preg_replace( '/\n[\n\r\s]*\n[\n\r\s]*\n/u', "\n\n", $text );

		return apply_filters( 'wptelegram_utils_trim_words', $text, $num_words, $more, $original_text );
	}

	/**
	 * Gets the current post type in the WordPress Admin
	 *
	 * @since 1.0.0
	 * 
	 * @return string|NULL
	 */
	public function get_current_post_type() {

		global $post, $typenow, $pagenow, $current_screen;

		//we have a post so we can just get the post type from that
		if ( $post && $post->post_type ) {

			return $post->post_type;

		} elseif ( $typenow ) { //check the global $typenow - set in admin.php
			
			return $typenow;

		} elseif ( $current_screen && $current_screen->post_type ) { //check the global $current_screen object - set in screen.php

			return $current_screen->post_type;

		} elseif ( isset( $_GET['post_type'] ) ) { //check the post_type query string

			return sanitize_key( $_GET['post_type'] );

		} elseif ( isset( $_GET['post'] ) ) { //check if post ID is in query string

			return get_post_type( $_GET['post'] );

		} elseif ( $pagenow == 'edit.php' || $pagenow == 'post-new.php' ) { //lastly check if the page is edit.php or post-new.php

			return 'post';

		}

		//we do not know the post type!
		return NULL;
	}

	/**
	 * Determine if the Post was created using the Gutenberg Editor.
	 *
	 * A dirty hack which assumes that the post content is not empty
	 *
	 * @since   2.0.13
	 *
	 * @param   WP_Post		$post	Post
	 * @return  bool				Post created using Gutenberg Editor
	 */
	public function is_gutenberg_post( $post ) {

		if ( false !== strpos( $post->post_content, '<!-- wp:' ) ) {
			return true;
		}
		return false;
	}
}