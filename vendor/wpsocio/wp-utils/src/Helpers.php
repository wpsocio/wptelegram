<?php
/**
 * Helper functions for WordPress.
 *
 * @link       https://wpsocio.com
 *
 * @package WPSocio\WPUtils
 */

namespace WPSocio\WPUtils;

/**
 * Class Helpers
 *
 * @package    WPSocio\WPUtils
 * @author     WP Socio
 */
class Helpers {

	/**
	 * Sanitize the input.
	 *
	 * @param  mixed $input  The input.
	 * @param  bool  $typefy Whether to convert strings to the appropriate data type.
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
	 * Strips non-word characters from the string
	 * or replaces them with underscore
	 *
	 * @param string $text The target string.
	 *
	 * @return string
	 */
	public static function strip_non_word_chars( $text ) {
		// decode all HTML entities.
		$text = html_entity_decode( $text, ENT_QUOTES, 'UTF-8' );

		// remove trailing non-word characters.
		$text = preg_replace( '/(^\W+|\W+$)/u', '', $text );
		// replace one or more continuous non-word characters by _.
		$text = preg_replace( '/\W+/u', '_', $text );

		return $text;
	}

	/**
	 * Sanitizes hashtag(s)
	 *
	 * Specifically, spaces are removed
	 *
	 * @param string|array $hashtag The string or array of strings to be sanitized.
	 *
	 * @return string|array The sanitized string or array of strings
	 */
	public static function sanitize_hashtag( $hashtag ) {

		if ( is_array( $hashtag ) ) {
			foreach ( $hashtag as &$string ) {
				$string = self::strip_non_word_chars( $string );
			}
		} else {
			$hashtag = self::strip_non_word_chars( $hashtag );
		}

		return $hashtag;
	}

	/**
	 * Convert a key value array to value label options.
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
	 * Decode HTML entities.
	 *
	 * @param string $text The text to decode HTML entities in.
	 *
	 * @return string The text with HTML entities decoded.
	 */
	public static function decode_html( $text ) {
		return html_entity_decode( $text, ENT_QUOTES, 'UTF-8' );
	}

	/**
	 * Get file type from extension.
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

		return $type;
	}

	/**
	 * Returns Jed-formatted localization data.
	 *
	 * @source gutenberg_get_jed_locale_data()
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
			$locale[ is_int( $msgid ) ? $entry->singular : $msgid ] = $entry->translations;
		}

		return $locale;
	}

	/**
	 * Whether the current screen is a post edit page.
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
	 * Whether the current screen is a posts list page.
	 *
	 * @param string|string[] $post_type The post type to check.
	 *
	 * @return bool
	 */
	public static function is_post_list_page( $post_type = null ) {

		global $pagenow, $typenow;

		$is_list_page = 'edit.php' === $pagenow;

		if ( $is_list_page ) {
			if ( $post_type ) {
				return in_array( $typenow, (array) $post_type, true );
			}
			return true;
		}
		return false;
	}

	/**
	 * Convert underscored  or dashed strings to camelCase (medial capitals).
	 *
	 * @param string $string The string to convert.
	 * @param bool   $lcfirst Whether to convert first character to lowercase.
	 * @param bool   $separators The optional separators contains the word separator characters.
	 *
	 * @return string
	 */
	public static function snake_to_camel( $string, $lcfirst = false, $separators = ' -_' ) {
		$string = str_replace( str_split( $separators ), '', ucwords( $string, $separators ) );

		return $lcfirst ? lcfirst( $string ) : $string;
	}

	/**
	 * Get the attachment limited by the maximum size.
	 *
	 * @param int    $id The attachment ID.
	 * @param int    $filesize The maximum file size.
	 * @param string $return The return type. Can be 'path' or 'url'.
	 *
	 * @return string|false The attachment path or URL.
	 */
	public static function get_attachment_by_filesize( $id, $filesize, $return = 'url' ) {

		if ( ! get_post( $id ) ) {
			return false;
		}

		$file_path = get_attached_file( $id );

		$path = 'url' === $return ? wp_get_attachment_url( $id ) : $file_path;

		// For now, we only deal with images.
		if ( ! wp_attachment_is_image( $id ) ) {
			return $path;
		}

		$original_image_path = function_exists( 'wp_get_original_image_path' ) ? wp_get_original_image_path( $id ) : null;

		// If the original image is less than the limit.
		if ( $original_image_path && is_readable( $original_image_path ) && filesize( $original_image_path ) <= $filesize ) {
			return 'url' === $return ? wp_get_original_image_url( $id ) : $original_image_path;
		}

		$meta = wp_get_attachment_metadata( $id );

		// For WP < 6.0.
		if ( empty( $meta['filesize'] ) ) {
			if ( ! file_exists( $file_path ) || ! is_readable( $file_path ) ) {
				return $path;
			}

			$meta['filesize'] = filesize( $file_path );
		}

		// The file size is already less than the limit.
		if ( $meta['filesize'] <= $filesize ) {
			return $path;
		}

		if ( ! empty( $meta['sizes'] ) ) {
			$size_data = [];

			$directory = dirname( $file_path );

			foreach ( $meta['sizes'] as $data ) {
				if ( empty( $data['file'] ) ) {
					continue;
				}

				$size_file_path = $directory . DIRECTORY_SEPARATOR . $data['file'];

				if ( ! file_exists( $size_file_path ) || ! is_readable( $size_file_path ) ) {
					continue;
				}

				$size = ! empty( $data['filesize'] ) ? $data['filesize'] : filesize( $size_file_path );

				$size_data[ $data['file'] ] = $size;
			}

			// Sort the sizes by file size.
			arsort( $size_data );

			// Get the first size that is less than the limit.
			foreach ( $size_data as $file => $size ) {
				if ( $size <= $filesize ) {

					$separator = 'url' === $return ? '/' : DIRECTORY_SEPARATOR;
					return dirname( $path ) . $separator . $file;
				}
			}
		}

		return $path;
	}

	/**
	 * Check whether the template path is valid.
	 *
	 * @param string $template The template path.
	 *
	 * @return bool
	 */
	public static function is_valid_theme_template( $template ) {
		/**
		 * Only allow templates that are in the active theme directory,
		 * parent theme directory, or the /wp-includes/theme-compat/ directory
		 * (prevent directory traversal attacks)
		 */
		$valid_paths = array_map(
			'realpath',
			[
				get_stylesheet_directory(),
				get_template_directory(),
				ABSPATH . WPINC . '/theme-compat/',
			]
		);

		$path = realpath( $template );

		foreach ( $valid_paths as $valid_path ) {
			if ( preg_match( '#\A' . preg_quote( $valid_path, '#' ) . '#', $path ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Create a regex from the given pattern.
	 *
	 * @param string  $pattern     The pattern to match.
	 * @param boolean $allow_empty Whether to allow an empty string.
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
	 * Get the current URL
	 *
	 * A fix for WordPress installed in subdirectory
	 *
	 * @source https://roots.io/routing-wp-requests/
	 *
	 * @return string        The current URL
	 */
	public static function wp_get_current_url() {

		$current_uri = trim( esc_url_raw( add_query_arg( [] ) ), '/' );

		$home_path = trim( wp_parse_url( home_url(), PHP_URL_PATH ), '/' );

		if ( $home_path && strpos( $current_uri, $home_path ) === 0 ) {

			$current_uri = trim( substr( $current_uri, strlen( $home_path ) ), '/' );
		}

		return home_url( $current_uri );
	}

	/**
	 * Gets the current post type in the WordPress Admin
	 *
	 * @return string|NULL
	 */
	public static function get_current_post_type() {

		global $post, $typenow, $pagenow, $current_screen;

		// we have a post so we can just get the post type from that.
		if ( $post && $post->post_type ) {
			return $post->post_type;
		}

		// check the global $typenow - set in admin.php.
		if ( $typenow ) {
			return $typenow;
		}

		// check the global $current_screen object - set in screen.php.
		if ( $current_screen && $current_screen->post_type ) {
			return $current_screen->post_type;
		}

		// check the post_type query string.
		$post_type = filter_input( INPUT_GET, 'post_type', FILTER_UNSAFE_RAW );

		if ( $post_type ) {
			return sanitize_key( $post_type );
		}

		// check if post ID is in query string.
		$post_id = filter_input( INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT );
		if ( $post_id ) {
			return get_post_type( (int) $post_id );
		}

		// lastly check if the page is edit.php or post-new.php.
		if ( 'edit.php' === $pagenow || 'post-new.php' === $pagenow ) {
			return 'post';
		}

		// we do not know the post type!
		return null;
	}
}
