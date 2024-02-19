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
use WPSocio\TelegramFormatText\HtmlConverter;
use WPSocio\TelegramFormatText\Converter\Utils as FormatTextUtils;
use WPSocio\TelegramFormatText\Exceptions\ConverterException;
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
class Utils extends \WPSocio\WPUtils\Helpers {

	/**
	 * HTML tags allowed in Telegram messages.
	 *
	 * @var string Tags.
	 * @since 4.0.0
	 */
	const SUPPORTED_HTML_TAGS = [
		// Link.
		'a'          => [
			'href' => true,
		],
		// bold.
		'b'          => [],
		'strong'     => [],
		// blockquote.
		'blockquote' => [
			'cite' => true,
		],
		// italic.
		'em'         => [],
		'i'          => [],
		// code.
		'pre'        => [],
		'code'       => [
			'class' => true,
		],
		// underline.
		'u'          => [],
		'ins'        => [],
	];

	/**
	 * Pattern to match the smart excerpt tag.
	 *
	 * @var string Pattern.
	 *
	 * @since 4.0.4
	 */
	const EXCERPT_PATTERN = '/<excerpt>(.*?)<\/excerpt>/ius';

	/**
	 * The maximum size of an image to be sent to Telegram by URL.
	 */
	const IMAGE_BY_URL_SIZE_LIMIT = 1024 * 1024 * 5; // 5MB.

	/**
	 * The maximum size of an image to be sent to Telegram by file.
	 */
	const IMAGE_BY_FILE_SIZE_LIMIT = 1024 * 1024 * 10; // 10MB.

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
		if ( is_object( $value ) || is_array( $value ) ) {
			return '';
		}
		$value = (string) $value;

		$guard = new TemplateGuard();

		$value = $guard->safeguard_macros( $value );

		$filtered = wp_check_invalid_utf8( $value );

		$filtered = trim( wp_kses( $filtered, self::SUPPORTED_HTML_TAGS ) );

		// Restore the macros with the original values.
		$filtered = $guard->restore_macros( $filtered );

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
	 * Get a valid parse mode
	 *
	 * @since 1.0.0
	 *
	 * @param string $parse_mode Parse mode.
	 *
	 * @return string
	 */
	public static function valid_parse_mode( $parse_mode ) {

		return 'HTML' === $parse_mode ? 'HTML' : '';
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
	 * The HTMLConverter instance
	 *
	 * @param string $options The options for HtmlConverter.
	 * @param string $id      The identifier of the converter options.
	 *
	 * @return HtmlConverter The HTMLConverter instance.
	 */
	public static function get_html_converter( $options = [], $id = 'default' ) {

		$defaults = [
			'format_to'          => 'text',
			'table_row_sep'      => "\n" . str_repeat( '-', 30 ) . "\n",
			'throw_on_doc_error' => true,
		];

		$options = wp_parse_args( $options, $defaults );
		$options = apply_filters( 'wptelegram_html_converter_options', $options, $id );
		$options = apply_filters( "wptelegram_{$id}_html_converter_options", $options );

		$converter = new HtmlConverter( $options );
		// Use this filter to add your own HTML converters.
		$converter = apply_filters( 'wptelegram_html_converter', $converter, $options, $id );
		$converter = apply_filters( "wptelegram_{$id}_html_converter", $converter, $options );

		return $converter;
	}

	/**
	 * Prepare the content for sending to Telegram.
	 *
	 * @param string $content The content to prepare.
	 * @param array  $options The options {
	 *     Optional. The options to prepare the content.
	 *
	 *     @type string $elipsis      The elipsis to use. Default '…'.
	 *     @type string $format_to    The format to convert to. Default 'text'.
	 *     @type string $id           The identifier of the converter options. Default 'default'.
	 *     @type int    $limit        The limit of the content. Default 55.
	 *     @type string $limit_by     The limit type. Default 'words'.
	 *     @type bool   $preserve_eol Whether to preserve new lines. Default true.
	 * }
	 *
	 * @return string The prepared content.
	 */
	public static function prepare_content( $content, $options = [] ) {

		$defaults = [
			'elipsis'         => '…',
			'format_to'       => 'text',
			'id'              => 'default',
			'limit'           => 55,
			'limit_by'        => 'words',
			'text_hyperlinks' => 'strip',
			'preserve_eol'    => true,
		];

		$options = wp_parse_args( $options, $defaults );

		$converter = self::get_html_converter( $options, $options['id'] );

		$result = trim( strip_shortcodes( $content ) );

		try {
			$result = $converter->convert( $result );
		} catch ( ConverterException $exception ) {

			// Since there was an error, we supposedly cannot format to HTML.
			$result = wp_strip_all_tags( HtmlConverter::prepareHtml( $result ) );

			$result = FormatTextUtils::decodeHtmlEntities( HtmlConverter::cleanUp( $result ) );

			// If we were supposed to format to HTML,
			// we need to ensure that special characters are escaped.
			if ( 'HTML' === $options['format_to'] ) {
				$result = FormatTextUtils::htmlSpecialChars( $result );
			}

			do_action( 'wptelegram_prepare_content_error', $exception, $content, $options );

			// override formatting.
			$options['format_to'] = 'text';
		}

		// Remove new lines if not preserving them.
		if ( ! $options['preserve_eol'] ) {
			$result = preg_replace( '/[\n\r]+/u', ' ', $result );
		}

		// if there is limit set.
		if ( $options['limit'] && $options['limit'] > 0 ) {
			if ( 'HTML' === $options['format_to'] ) {
				// We are formatting to HTML, so we will use the safe trim to avoid breaking the HTML.
				$result = $converter->safeTrim( $result, $options['limit_by'], $options['limit'] );
			} else {
				$result_before_limit = $result;
				// We are formatting to text.
				$result = FormatTextUtils::limitTextBy( $result, $options['limit_by'], $options['limit'] );

				// Add the elipsis if the text is trimmed.
				if ( $result !== $result_before_limit ) {
					$result = trim( $result ) . $options['elipsis'];
				}
			}
		}

		return apply_filters( 'wptelegram_prepare_content', $result, $content, $options );
	}

	/**
	 * Smartly trim the content inside <excerpt></excerpt> until the whole content is less than the limit.
	 *
	 * @param string $content The content to trim.
	 * @param array  $options The options. See prepare_content() for more info.
	 *
	 * @return string The prepared content.
	 */
	public static function smart_trim_excerpt( $content, $options = [] ) {

		if ( ! preg_match( self::EXCERPT_PATTERN, $content, $match ) ) {
			return self::prepare_content( $content, $options );
		}

		$placeholder = '{:excerpt:}';
		$delimiter   = '{::excerpt::}';

		$excerpt = $match[1];

		/**
		 * Add a placeholder for the excerpt.
		 *
		 * $content: `This is the starting content. <excerpt>This is the excerpt.</excerpt> This is the rest of the content.`.
		 *
		 * $result: `This is the starting content. {:excerpt:} This is the rest of the content.`
		 */
		$result = preg_replace( self::EXCERPT_PATTERN, $placeholder, $content );

		/**
		 * Place the excerpt at the end of the content separated by delimiter.
		 *
		 * $result: `This is the starting content. {:excerpt:} This is the rest of the content.{::excerpt::}This is the excerpt`
		 */
		$result = $result . $delimiter . $excerpt;

		/**
		 * Since the excerpt is at the end, it will be trimmed first.
		 *
		 * $result: `This is the starting content. {:excerpt:} This is the rest of the content.{::excerpt::}This is…`
		 */
		$result = self::prepare_content( $result, $options );

		/**
		 * If the delimiter is found, we will split the content to get the excerpt.
		 *
		 * $result: `This is the starting content. {:excerpt:} This is the rest of the content.{::excerpt::}This is…`
		 * OR
		 * $result: `This is the starting content. {:excerpt:} This is the rest of the…`
		 */
		if ( false !== strpos( $result, $delimiter ) ) {
			/**
			 * The first part is the trimmed content and the second part is the trimmed excerpt.
			 *
			 * $result: `This is the starting content. {:excerpt:} This is the rest of the content.`
			 *
			 * $trimmed_excerpt: `This is…`
			 */
			list( $result, $trimmed_excerpt) = array_pad( explode( $delimiter, $result ), 2, '' );

		} else {
			/**
			 * Sorry, it was all nuked.
			 *
			 * $result: `This is the starting content. {:excerpt:} This is the rest of the…`
			 */
			$trimmed_excerpt = '';
		}
		// Escape the placeholder.
		$placeholder = preg_quote( $placeholder, '/' );
		// Remove the new line if the excerpt is empty.
		$placeholder = '/' . ( $trimmed_excerpt ? $placeholder : $placeholder . '[\n\r]?' ) . '/ius';

		/**
		 * Replace the placeholder with the trimmed excerpt.
		 *
		 * $result: `This is the starting content. This is… This is the rest of the content.`
		 * OR
		 * $result: `This is the starting content. This is the rest of the…`
		 */
		$result = preg_replace( $placeholder, FormatTextUtils::preparePregReplacement( $trimmed_excerpt ), $result );

		return apply_filters( 'wptelegram_smart_trim_excerpt', $result, $content, $options );
	}

	/**
	 * Split content into multiple chunks in order to send to Telegram as multiple messages.
	 *
	 * @since 4.0.4
	 *
	 * @param string $content The content to split.
	 * @param string $parse_mode The parse mode.
	 *
	 * @return string[] The split content.
	 */
	public static function split_content( $content, $parse_mode = '' ) {
		$limit = self::get_max_text_length( 'text' );

		// Remove <excerpt>...</excerpt> tags.
		$content = preg_replace( self::EXCERPT_PATTERN, '${1}', $content );

		// break text after every nth character given by the limit and preserve words.
		preg_match_all( '/.{1,' . $limit . '}(?:\s|$)/su', $content, $matches );

		$parts = $matches[0];

		// If the parse mode is HTML, we need to do some extra work
		// to make sure the HTML tags are not broken.
		if ( 'HTML' === $parse_mode ) {
			$parts = array_map( 'force_balance_tags', $parts );
		}

		return $parts;
	}

	/**
	 * Get the maximum length of the text to send to Telegram.
	 *
	 * @since 4.0.0
	 *
	 * @param string $for     The type of the text. Can be 'text' or 'caption' Default 'text'.
	 * @param int    $padding Safety padding to add to the limit. Default 20 characters.
	 *
	 * @return int The maximum length of the text to send to Telegram.
	 */
	public static function get_max_text_length( $for = 'text', $padding = 20 ) {

		$length = 'caption' === $for ? HtmlConverter::TG_CAPTION_MAX_LENGTH : HtmlConverter::TG_TEXT_MAX_LENGTH;

		// Add the safety padding.
		$length -= abs( (int) $padding );

		return (int) apply_filters( 'wptelegram_max_text_length', $length, $for, $padding );
	}

	/**
	 * Get the attachment limited by the maximum size.
	 *
	 * @param int    $id The attachment ID.
	 * @param int    $filesize The maximum file size.
	 * @param string $return The return type. Can be 'path' or 'url'. Default null.
	 *
	 * @return string|false The attachment path or URL.
	 */
	public static function get_attachment_by_filesize( $id, $filesize, $return = null ) {
		if ( null === $return ) {
			$return = self::send_files_by_url() ? 'url' : 'path';
		}

		return parent::get_attachment_by_filesize( $id, $filesize, $return );
	}

	/**
	 * Get the limit of the image size to send to Telegram.
	 *
	 * @return int
	 */
	public static function get_image_size_limit() {

		return self::send_files_by_url() ? self::IMAGE_BY_URL_SIZE_LIMIT : self::IMAGE_BY_FILE_SIZE_LIMIT;
	}

	/**
	 * Whether to send files by URL.
	 *
	 * @since 4.0.14
	 */
	public static function send_files_by_url() {
		$send_files_by_url = WPTG()->options()->get_path( 'advanced.send_files_by_url', true );

		return (bool) apply_filters( 'wptelegram_send_files_by_url', $send_files_by_url );
	}
}
