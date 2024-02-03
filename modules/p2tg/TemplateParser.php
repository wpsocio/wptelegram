<?php
/**
 * Post to Telegram message template parser.
 *
 * @link        https://t.me/WPTelegram
 * @since       4.1.0
 *
 * @package     WPTelegram\Core
 * @subpackage  WPTelegram\Core\modules\p2tg
 */

namespace WPTelegram\Core\modules\p2tg;

use WPSocio\WPUtils\Options;
use WPTelegram\Core\includes\Utils as MainUtils;
use WP_Post;

/**
 * Post to Telegram message template parser.
 *
 * @package     WPTelegram\Core
 * @subpackage  WPTelegram\Core\modules\p2tg
 * @author      WP Socio
 */
class TemplateParser {

	/**
	 * The post to be handled
	 *
	 * @var WP_Post $post   Post object.
	 */
	protected $post;

	/**
	 * The post data
	 *
	 * @since   4.1.0
	 * @access  protected
	 * @var     PostData $post_data The post data.
	 */
	protected $post_data;

	/**
	 * The options for parsing the template.
	 *
	 * @var Options $options The options object.
	 */
	public $options;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since   4.1.0
	 *
	 * @param int|WP_Post $post    Post object or ID.
	 * @param Options     $options The options for parsing the template.
	 */
	public function __construct( $post, $options = null ) {

		$this->set_post( $post );
		$this->set_options( $options );
	}

	/**
	 * Set the post
	 *
	 * @since    4.1.0
	 * @param   int|WP_Post $post   Post object or ID.
	 */
	public function set_post( $post ) {
		$this->post = get_post( $post );

		$this->reset_data();

		return $this;
	}

	/**
	 * Set the post
	 *
	 * @since 4.1.0
	 *
	 * @param Options $options The options for parsing the template.
	 */
	public function set_options( $options ) {
		if ( ! $options ) {
			// set to an empty object by default.
			$options = new Options();
		}
		$this->options = $options;

		return $this;
	}

	/**
	 * Reset the existing post data.
	 */
	public function reset_data() {
		$this->post_data = new PostData( $this->post );

		return $this;
	}

	/**
	 * Returns the valid parse mode.
	 */
	public function get_parse_mode() {
		return MainUtils::valid_parse_mode( $this->options->get( 'parse_mode' ) );
	}

	/**
	 * Parses the given template and converts it to the text.
	 *
	 * @since 4.1.0
	 *
	 * @param  string $template The template to parse.
	 *
	 * @return string The parsed value.
	 */
	public function parse( $template ) {

		$template = $this->normalize_template( $template );

		$macro_values = $this->parse_macros( $template );

		// lets replace the conditional macros.
		$template = $this->process_template_logic( $template, $macro_values );

		$text = str_replace( array_keys( $macro_values ), array_values( $macro_values ), $template );

		$text = $this->encode_values( $text );

		return apply_filters( 'wptelegram_p2tg_parsed_template', $text, $template, $this->post, $this->options );
	}

	/**
	 * Parses the given template to encode the values if needed.
	 *
	 * @since 4.1.0
	 *
	 * @param  string $template The template to parse.
	 *
	 * @return string The parsed value.
	 */
	public function encode_values( $template ) {
		$pattern = '#\{encode:([^\}]+?)\}#iu';

		$encoded = preg_replace_callback(
			$pattern,
			function ( $match ) {
				return rawurlencode( $match[1] );
			},
			$template
		);

		return apply_filters( 'wptelegram_p2tg_template_encoded_values', $encoded, $template );
	}

	/**
	 * Parses the given template to set the correct values to be parsed.
	 *
	 * @since 4.1.0
	 *
	 * @param  string $template The template to parse.
	 *
	 * @return string The normalized value.
	 */
	public function normalize_template( $template ) {

		$raw_template = $template;

		// replace {tags} and {categories} with taxonomy names.
		$replace = [ '{terms:post_tag}', '{terms:category}' ];

		// Use {tags} and {categories} for WooCommerce products.
		if ( class_exists( 'woocommerce' ) && 'product' === $this->post->post_type ) {

			$replace = [ '{terms:product_tag}', '{terms:product_cat}' ];
		}

		// Modify the template.
		$template = str_replace( [ '{tags}', '{categories}' ], $replace, $template );

		return apply_filters( 'wptelegram_p2tg_normalized_template', $template, $raw_template, $this->post, $this->options );
	}

	/**
	 * Parses the given template for all possible macros and returns the macro data.
	 *
	 * @since 4.1.0
	 *
	 * @param  string $template The template to parse.
	 *
	 * @return array The parsed macro values.
	 */
	public function parse_macros( $template ) {

		// Remove wpautop() from the `the_content` filter
		// to preserve newlines.
		self::bypass_wpautop_for( 'the_content' );

		$excerpt_source       = $this->options->get( 'excerpt_source' );
		$excerpt_length       = (int) $this->options->get( 'excerpt_length' );
		$excerpt_preserve_eol = $this->options->get( 'excerpt_preserve_eol' );
		$cats_as_tags         = $this->options->get( 'cats_as_tags' );
		$parse_mode           = MainUtils::valid_parse_mode( $this->options->get( 'parse_mode' ) );

		$template = $this->normalize_template( $template );

		$macro_keys = [
			'ID',
			'featured_image_url',
			'full_url',
			'post_author',
			'post_content',
			'post_date',
			'post_date_gmt',
			'post_excerpt',
			'post_slug',
			'post_title',
			'post_type',
			'post_type_label',
			'short_url',
		];

		// for post excerpt.
		$params = compact(
			'excerpt_source',
			'excerpt_length',
			'excerpt_preserve_eol',
			'cats_as_tags',
			'parse_mode'
		);

		$macro_values = [];

		foreach ( $macro_keys as $macro_key ) {
			$key = '{' . $macro_key . '}';

			// get the value only if it's in the template.
			if ( false !== strpos( $template, $key ) ) {

				$macro_values[ $key ] = $this->post_data->get_field( $macro_key, $params );
			}
		}

		// if it's something unusual :) .
		if ( preg_match_all( '/(?<=\{)(terms|a?cf):([^\}]+?)(?=\})/iu', $template, $matches ) ) {

			foreach ( $matches[0] as $field ) {
				$key = '{' . $field . '}';

				$macro_values[ $key ] = $this->post_data->get_field( $field, $params );
			}
		}

		/**
		 * Use this filter to replace your own macros
		 * with the corresponding values
		 */
		$macro_values = (array) apply_filters( 'wptelegram_p2tg_template_macro_values', $macro_values, $template, $this->post, $this->options );

		$macro_values = (array) apply_filters_deprecated( 'wptelegram_p2tg_macro_values', [ $macro_values, $this->post, $this->options ], '4.1.0', 'wptelegram_p2tg_template_macro_values' );

		// Prepare macro values for further processing.
		$macro_values = array_map( [ $this, 'prepare_macro_value' ], $macro_values );

		return $macro_values;
	}

	/**
	 * Prepare macro value for further processing.
	 *
	 * @since 4.1.0
	 *
	 * @param string $macro_value The value for a macro.
	 *
	 * @return string
	 */
	public function prepare_macro_value( $macro_value ) {
		// Remove unwanted slashes.
		return stripslashes( $macro_value );
	}

	/**
	 * Resolve the conditional macros in the template.
	 *
	 * @since 4.1.0
	 *
	 * @param string $template     The message template being processed.
	 * @param array  $macro_values The values for all macros.
	 *
	 * @return string
	 */
	private function process_template_logic( $template, $macro_values ) {

		$raw_template = $template;

		$pattern = '/\[if\s*?	# Conditional block starts
			(\{[^\}]+?\})		# Conditional expression, a macro
		\]						# Conditional block ends
		\[						# Consequence block starts
			([^\]]+?)			# Consequence expression
		\]						# Consequence block ends
		(?:						# non-capturing alternative block
			\[					# Alternative block starts
				([^\]]*?)		# Alternative expression
			\]					# Alternative block ends
		)?						# Make alternative block optional
		/ix';

		preg_match_all( $pattern, $template, $matches );

		// loop through the conditional expressions.
		foreach ( $matches[1] as $key => $macro ) {

			// if expression is false, take from alternative.
			$index = empty( $macro_values[ $macro ] ) ? 3 : 2;

			$replace = str_replace( array_keys( $macro_values ), array_values( $macro_values ), $matches[ $index ][ $key ] );

			$template = str_replace( $matches[0][ $key ], $replace, $template );
		}

		// remove the ugly empty lines.
		$template = preg_replace( '/(?:\A|[\n\r]).*?\{remove_line\}.*/u', '', $template );

		return apply_filters( 'wptelegram_p2tg_process_template_logic', $template, $macro_values, $raw_template, $this->post, $this->options );
	}

	/**
	 * Bypass wpautop() from the given filter
	 * to preserve newlines.
	 *
	 * @since 4.1.0
	 *
	 * @param string $tag The name of the filter hook like "the_content".
	 */
	public static function bypass_wpautop_for( $tag ) {
		$priority = has_filter( $tag, 'wpautop' );
		if ( false !== $priority ) {
			remove_filter( $tag, 'wpautop', $priority );
			add_filter( $tag, [ __CLASS__, 'restore_wpautop_hook' ], $priority + 1 );
		}
	}

	/**
	 * Re-add wp_autop() to the given filter.
	 *
	 * @access public
	 *
	 * @since 4.1.0
	 *
	 * @param string $content The post content running through this filter.
	 * @return string The unmodified content.
	 */
	public static function restore_wpautop_hook( $content ) {
		$tag = current_filter();

		$current_priority = has_filter( $tag, [ __CLASS__, 'restore_wpautop_hook' ] );

		add_filter( $tag, 'wpautop', $current_priority - 1 );
		remove_filter( $tag, [ __CLASS__, 'restore_wpautop_hook' ], $current_priority );

		return $content;
	}
}
