<?php
/**
 * WPTelegram Utilities
 *
 * @link       https://wpsocio.com
 * @since     1.0.0
 *
 * @package WPTelegram
 * @subpackage WPTelegram\Core\includes
 */

namespace WPTelegram\Core\includes;

use WPTelegram\Core\includes\restApi\RESTController;
use WP_REST_Request;
use WP_Error;

/**
 * WPTelegram Utilities
 *
 * @link       https://wpsocio.com
 * @since     1.0.0
 *
 * @package WPTelegram
 * @subpackage WPTelegram\Core\includes
 */
class Utils {

	/**
	 * Sanitize the input.
	 *
	 * @param  mixed $input  The input.
	 * @param  bool  $typefy Whether to convert strings to the appropriate data type.
	 * @since  1.0.0
	 *
	 * @return mixed
	 */
	public static function sanitize( $input, $typefy = false ) {

		if ( is_array( $input ) ) {

			foreach ( $input as $key => $value ) {

				$input[ sanitize_text_field( $key ) ] = self::sanitize( $value, $typefy );
			}
			return $input;
		}

		// These are safe types.
		if ( is_bool( $input ) || is_int( $input ) || is_float( $input ) ) {
			return $input;
		}

		// Now we will treat it as string.
		$input = sanitize_text_field( $input );

		// avoid numeric or boolean values as strings.
		if ( $typefy ) {
			return self::typefy( $input );
		}

		return $input;
	}

	/**
	 * Convert the input into the proper data type
	 *
	 * @param  mixed $input The input.
	 * @since  1.0.0
	 *
	 * @return mixed
	 */
	public static function typefy( $input ) {

		if ( is_numeric( $input ) ) {

			return floatval( $input );

		} elseif ( is_string( $input ) && preg_match( '/^(?:true|false)$/i', $input ) ) {

			return 'true' === strtolower( $input );
		}

		return $input;
	}

	/**
	 * Filter WP REST API errors.
	 *
	 * @param mixed           $response Result to send to the client. Usually a WP_REST_Response or WP_Error.
	 * @param array           $handler  Route handler used for the request.
	 * @param WP_REST_Request $request  Request used to generate the response.
	 *
	 * @since    3.0.3
	 */
	public static function filter_rest_errors( $response, $handler, $request ) {

		$matches_route    = 0 === strpos( ltrim( $request->get_route(), '/' ), RESTController::REST_NAMESPACE );
		$is_invalid_param = is_wp_error( $response ) && 'rest_invalid_param' === $response->get_error_code();

		if ( ! $is_invalid_param || ! $matches_route ) {
			return $response;
		}

		$data = $response->get_error_data();

		$invalid_params = [];
		if ( ! empty( $data['params'] ) ) {
			foreach ( $data['params'] as $error ) {
				preg_match( '/\A\S+/', $error, $match );
				$invalid_params[ $match[0] ] = $error;
			}
		}

		$data['params'] = $invalid_params;

		return new WP_Error(
			$response->get_error_code(),
			$response->get_error_message(),
			$data
		);
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
	public static function sanitize_hashtag( $hashtag ) {

		$raw_hashtag = $hashtag;

		if ( is_array( $hashtag ) ) {
			foreach ( $hashtag as &$string ) {
				$string = self::strip_non_word_chars( $string );
			}
		} else {
			$hashtag = self::strip_non_word_chars( $hashtag );
		}

		return apply_filters( 'wptelegram_utils_sanitize_hashtag', $hashtag, $raw_hashtag );
	}

	/**
	 * Get file type from extension.
	 *
	 * @since 1.0.0
	 *
	 * @param string $id   The file ID.
	 * @param string $file The file path.
	 *
	 * @return string
	 */
	public static function guess_file_type( $id, $file ) {

		$filetype = get_post_mime_type( $id );

		if ( empty( $filetype ) ) {
			$filetype = wp_check_filetype( $file );
			$filetype = $filetype['type'];
		}

		// default type.
		$type = 'document';

		if ( ! empty( $filetype ) ) {
			$filetype = explode( '/', $filetype );

			$type = reset( $filetype );

			switch ( $type ) {
				case 'video':
				case 'audio':
					break;
				case 'image':
					$type = next( $filetype ) === 'gif' ? 'animation' : 'photo';
					break;
				default:
					$type = 'document';
					break;
			}
		}

		return apply_filters( 'wptelegram_utils_file_type', $type, $id, $file );
	}

	/**
	 * Strips non-word characters from the string
	 * or replaces them with underscore
	 *
	 * @since 1.0.0
	 *
	 * @param string $text The target string.
	 *
	 * @return string
	 */
	public static function strip_non_word_chars( $text ) {
		$raw_text = $text;
		// decode all HTML entities.
		$text = self::decode_html( $text );

		// remove trailing non-word characters.
		$text = preg_replace( '/(?:^\W+|\W+$)/u', '', $text );
		// replace one or more continuous non-word characters by _.
		$text = preg_replace( '/\W+/u', '_', $text );

		return apply_filters( 'wptelegram_utils_strip_non_word_chars', $text, $raw_text );
	}

	/**
	 * Trims text to a certain number of words.
	 *
	 * @since 2.1.0
	 *
	 * @param string  $text         The text to trim.
	 * @param integer $num_words    Number of words.
	 * @param string  $more         The end charactor to append.
	 * @param boolean $preserve_eol Whether to preserve newlines.
	 *
	 * @return string|NULL
	 */
	public static function trim_words( $text, $num_words = 55, $more = null, $preserve_eol = false ) {

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

		return apply_filters( 'wptelegram_utils_trim_words', $text, $num_words, $more, $original_text );
	}

	/**
	 * Handles sanitization for message template
	 *
	 * @since 1.0.0
	 *
	 * @param  mixed   $value       The unsanitized value from the form.
	 * @param  boolean $addslashes  Whether to addslashes for database.
	 * @param  boolean $json_encode Whether to json_encode.
	 *
	 * @return mixed Sanitized value.
	 */
	public static function sanitize_message_template( $value, $addslashes = false, $json_encode = false ) {

		$filtered = wp_check_invalid_utf8( $value );

		if ( strpos( $filtered, '<' ) !== false ) {
			$filtered = wp_pre_kses_less_than( $filtered );

			$filtered = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', $filtered );
			// This will strip extra whitespace for us.
			$filtered = strip_tags( $filtered, '<b><strong><i><em><a><code><pre>' );
		}
		$filtered = trim( $filtered );

		$found = false;
		while ( preg_match( '/%[a-f0-9]{2}/i', $filtered, $match ) ) {
			$filtered = str_replace( $match[0], '', $filtered );
			$found    = true;
		}

		if ( $found ) {
			// Strip out the whitespace that may now exist after removing the octets.
			$filtered = trim( preg_replace( '/\s+/', ' ', $filtered ) );
		}

		if ( $json_encode ) {
			// json_encode to avoid errors when saving multi-byte emojis into database with no multi-byte support.
			$filtered = wp_json_encode( $filtered );
		}

		if ( $addslashes ) {
			// add slashes to avoid stripping of backslashes.
			$filtered = addslashes( $filtered );
		}

		return apply_filters( 'wptelegram_sanitize_message_template', $filtered, func_get_args() );
	}

	/**
	 * Escape the Markdown symbols
	 *
	 * @since 1.0.0
	 *
	 * @param  string $string The string to be escaped.
	 * @return string
	 */
	public static function esc_markdown( $string ) {

		$markdown_search  = [ '_', '*', '[' ];
		$markdown_replace = [ '\_', '\*', '\[' ];

		$esc_string = str_replace( $markdown_search, $markdown_replace, $string );

		return apply_filters( 'wptelegram_esc_markdown', $esc_string, $string );
	}

	/**
	 * Get a valid parse mode
	 *
	 * @since 1.0.0
	 *
	 * @param string $parse_mode Parse mode.
	 *
	 * @return string
	 */
	public static function valid_parse_mode( $parse_mode ) {

		switch ( $parse_mode ) {
			case 'Markdown':
			case 'MarkdownV2':
			case 'HTML':
				break;
			default:
				$parse_mode = '';
				break;
		}
		return $parse_mode;
	}

	/**
	 * Filter Text to make it ready for parsing.
	 *
	 * @since 1.0.0
	 *
	 * @param string $text       Message text.
	 * @param string $parse_mode Parse mode.
	 *
	 * @return string
	 */
	public static function filter_text_for_parse_mode( $text, $parse_mode ) {

		$unfiltered_text = $text;

		if ( 'HTML' === $parse_mode ) {

			$allowable_tags = [ 'em', 'strong', 'b', 'i', 'a', 'pre', 'code' ];

			// remove unnecessary tags.
			$text = strip_tags( $text, '<' . implode( '><', $allowable_tags ) . '>' );

			foreach ( $allowable_tags as $tag ) {

				// remove $tag if <a> is nested in it.
				$pattern = '#(<' . $tag . '>)((.+)?<a\s+(?:[^>]*?\s+)?href=["\']?([^\'"]*)["\']?.*?>(.*?)<\/a>(.+)?)(<\/' . $tag . '>)#iu';

				$text = preg_replace( $pattern, '$2', $text );
			}

			$pattern = '#(?:<\/?)(?:(?:a(?:[^<>]+?)?>)|(?:b>)|(?:strong>)|(?:i>)|(?:em>)|(?:pre>)|(?:code>))(*SKIP)(*FAIL)|[<>&]+#iu';

			$text = preg_replace_callback(
				$pattern,
				function ( $match ) {

					return htmlentities( $match[0], ENT_NOQUOTES, 'UTF-8', false );
				},
				$text
			);

		} else {

			$text = wp_strip_all_tags( $text );

			if ( 'Markdown' === $parse_mode ) {

				$text = preg_replace_callback(
					'/\*(.+?)\*/su',
					function ( $match ) {
						return str_replace( [ '\\[', '\\_' ], [ '[', '_' ], $match[0] );
					},
					$text
				);
			}
		}
		return apply_filters( 'wptelegram_filter_text_for_parse_mode', $text, $unfiltered_text, $parse_mode );
	}

	/**
	 * Returns Jed-formatted localization data.
	 *
	 * @source gutenberg_get_jed_locale_data()
	 *
	 * @since 3.0.0
	 *
	 * @param  string $domain Translation domain.
	 *
	 * @return array
	 */
	public static function get_jed_locale_data( $domain ) {
		$translations = get_translations_for_domain( $domain );

		$locale = [
			'' => [
				'domain' => $domain,
				'lang'   => is_admin() ? get_user_locale() : get_locale(),
			],
		];

		if ( ! empty( $translations->headers['Plural-Forms'] ) ) {
			$locale['']['plural_forms'] = $translations->headers['Plural-Forms'];
		}

		foreach ( $translations->entries as $msgid => $entry ) {
			$locale[ $msgid ] = $entry->translations;
		}

		return $locale;
	}

	/**
	 * Create a regex from the given pattern.
	 *
	 * @since    3.0.0
	 *
	 * @param string  $pattern     The pattern to match.
	 * @param boolean $allow_empty Whether to allow an ampty string.
	 * @param boolean $match_full  Whether to match the complete word.
	 * @param string  $delim       The delimiter to use.
	 *
	 * @return string
	 */
	public static function enhance_regex( $pattern, $allow_empty = false, $match_full = true, $delim = '' ) {
		if ( $allow_empty ) {
			$pattern = '(?:' . $pattern . ')?';
		}
		if ( $match_full ) {
			$pattern = '\A' . $pattern . '\Z';
		}
		if ( $delim ) {
			$pattern = $delim . $pattern . $delim;
		}
		return $pattern;
	}

	/**
	 * Convert a key value array to value label options.
	 *
	 * @since    3.0.0
	 *
	 * @param array $data The values to be converted.
	 */
	public static function array_to_select_options( $data ) {

		$options = [];

		foreach ( $data as $value => $label ) {

			$options[] = compact( 'value', 'label' );
		}
		return $options;
	}

	/**
	 * Generate a unique nonce field.
	 *
	 * @since  3.0.0
	 *
	 * @param boolean $echo Whether to echo the HTML.
	 * @return string
	 */
	public static function nonce_field( $echo = true ) {
		return wp_nonce_field( self::nonce(), self::nonce(), false, $echo );
	}

	/**
	 * Generate a nonce.
	 *
	 * @since  3.0.0
	 * @param string $name The name suffix.
	 *
	 * @return string unique nonce string.
	 */
	public static function nonce( $name = '_wptelegram' ) {

		return 'nonce_' . $name;
	}

	/**
	 * Whether the current screen is a post edit page.
	 *
	 * @since 3.0.3
	 *
	 * @param string|string[] $post_type The post type to check.
	 *
	 * @return bool
	 */
	public static function is_post_edit_page( $post_type = null ) {

		global $pagenow, $typenow;

		$is_edit_page = 'post-new.php' === $pagenow || 'post.php' === $pagenow;

		if ( $is_edit_page ) {
			if ( $post_type ) {
				return in_array( $typenow, (array) $post_type, true );
			}
			return true;
		}
		return false;
	}

	/**
	 * Decode HTML entities.
	 *
	 * @since 3.0.9
	 *
	 * @param string $text The text to decode HTML entities in.
	 *
	 * @return string The text with HTML entities decoded.
	 */
	public static function decode_html( $text ) {
		return html_entity_decode( $text, ENT_QUOTES, 'UTF-8' );
	}
}
