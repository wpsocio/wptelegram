<?php
/**
 * Post Handling functionality of the plugin.
 *
 * @link        https://t.me/WPTelegram
 * @since       2.0.0
 *
 * @package     WPTelegram
 * @subpackage  WPTelegram\Core\modules\p2tg
 */

namespace WPTelegram\Core\modules\p2tg;

use WPTelegram\Core\includes\Utils;

/**
 * The Post Handling functionality of the plugin.
 *
 * @package    WPTelegram
 * @subpackage WPTelegram\Core\modules\p2tg
 * @author     WP Socio
 */
class PostData {

	/**
	 * The post to be handled.
	 *
	 * @var WP_Post $post Post object.
	 */
	protected $post;

	/**
	 * The post data
	 *
	 * @since  2.0.0
	 * @access protected
	 * @var    array     $data The array containing the post data.
	 */
	protected $data;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 2.0.0
	 *
	 * @param string $post The current post.
	 */
	public function __construct( $post ) {

		$this->data = [];

		$this->set_post( $post );
	}

	/**
	 * Set the post.
	 *
	 * @since    2.0.0
	 *
	 * @param string $post The current post.
	 */
	public function set_post( $post ) {

		$this->post = get_post( $post );
	}

	/**
	 * Retrieves a field from post data
	 * And updates the data if not found.
	 *
	 * @since 2.0.0
	 *
	 * @param  string $field  The field to be retrieved.
	 * @param  string $params Optional params to be used for some fields.
	 *
	 * @return mixed Field value.
	 */
	public function get_field( $field, $params = [] ) {

		// if the data already exists for the field.
		if ( ! array_key_exists( $field, $this->data ) ) {

			$this->data[ $field ] = $this->get_field_value( $field, $params );
		}

		$value = apply_filters( 'wptelegram_p2tg_post_data_field', $this->data[ $field ], $field, $this->post );

		return apply_filters( "wptelegram_p2tg_post_data_{$field}", $value, $this->post );
	}

	/**
	 * Retrieves a field value from post without modifying $this->data.
	 *
	 * @since 2.0.0
	 *
	 * @param string $field  The field to be retrieved.
	 * @param string $params Optional params to be used for some fields.
	 *
	 * @return mixed Field value.
	 */
	public function get_field_value( $field, $params = [] ) {

		$value = '';

		switch ( $field ) {

			case 'id':
			case 'ID':
				$value = $this->post->ID;
				break;

			/* Post Title */
			case 'title':
			case 'post_title':
				$value = get_the_title( $this->post );
				break;

			/* Post slug */
			case 'slug':
			case 'post_slug':
			case 'post_name':
				$value = $this->post->post_name;
				break;

			/* The post's local publication time */
			case 'post_date':
				$value = get_the_date( '', $this->post->ID );
				break;

			/* The post's GMT publication time */
			case 'post_date_gmt':
				$value = date_i18n( get_option( 'date_format' ), strtotime( $this->post->post_date_gmt ) );
				break;

			/* Post Author */
			case 'author':
			case 'post_author':
				$value = get_the_author_meta( 'display_name', $this->post->post_author );
				break;

			/* The post type label (singular), like Post or Page. */
			case 'post_type_label':
				$post_type = get_post_type_object( $this->post->post_type );
				// Return singular name or the slug.
				$value = ! empty( $post_type->labels->singular_name ) ? $post_type->labels->singular_name : $this->post->post_type;
				break;

				/* The post type slug, like 'post' or 'page'. */
			case 'post_type':
				$value = $this->post->post_type;
				break;

			/* Post Excerpt */
			case 'excerpt':
			case 'post_excerpt':
				$excerpt_source = isset( $params['excerpt_source'] ) ? $params['excerpt_source'] : 'post_content';
				$excerpt_length = isset( $params['excerpt_length'] ) ? $params['excerpt_length'] : 55;
				$preserve_eol   = ( isset( $params['excerpt_preserve_eol'] ) && $params['excerpt_preserve_eol'] );

				if ( 'before_more' === $excerpt_source ) {

					$parts   = get_extended( apply_filters( 'the_content', get_post_field( 'post_content', $this->post ) ) );
					$excerpt = $parts['main'];

				} else {

					$excerpt = get_post_field( $excerpt_source, $this->post );

					$filter = str_replace( 'post', 'the', $excerpt_source );

					self::remove_autoembed_filter();

					// apply the_content or the_excerpt.
					$excerpt = apply_filters( $filter, $excerpt );

					self::restore_autoembed_filter();
				}

				// remove shortcodes and convert <br> to EOL.
				$excerpt = str_replace( '<br>', PHP_EOL, strip_shortcodes( $excerpt ) );

				$value = Utils::trim_words( $excerpt, $excerpt_length, '…', $preserve_eol );
				break;

			/* Post Content */
			case 'content':
			case 'post_content':
				$content = get_post_field( 'post_content', $this->post );
				$content = str_replace( '<br>', PHP_EOL, $content );

				self::remove_autoembed_filter();
				$content = apply_filters( 'the_content', $content );
				self::restore_autoembed_filter();

				$content = trim( strip_tags( Utils::decode_html( $content ), '<b><strong><em><i><a><pre><code>' ) );
				$value   = trim( strip_shortcodes( $content ) );
				break;

			case 'short_url':
				$value = wp_get_shortlink( $this->post->ID );
				break;

			case 'full_url':
				$value = urldecode( get_permalink( $this->post->ID ) );
				break;

			case 'featured_image_url':
				// post thumbnail ID.
				$thumbnail_id = get_post_thumbnail_id( $this->post->ID );

				$value = wp_get_attachment_url( $thumbnail_id );
				break;

			case 'featured_image_path':
				// post thumbnail ID.
				$thumbnail_id = get_post_thumbnail_id( $this->post->ID );

				$value = get_attached_file( $thumbnail_id );
				break;

			default:
				// if it's something special.
				if ( preg_match( '/^(terms|cf):/i', $field, $match ) ) {

					$_field = preg_replace( '/^' . $match[1] . ':/i', '', $field );

					switch ( $match[1] ) {

						case 'terms': // if taxonomy.
							$taxonomy = $_field;

							$cats_as_tags = ( isset( $params['cats_as_tags'] ) && $params['cats_as_tags'] );

							$cats_as_tags = apply_filters( "wptelegram_p2tg_post_data_send_{$taxonomy}_as_tags", $cats_as_tags, $this->post, $params );

							if ( taxonomy_exists( $taxonomy ) ) {

								$terms = get_the_terms( $this->post->ID, $taxonomy );

								$names = ( is_wp_error( $terms ) || empty( $terms ) ) ? [] : wp_list_pluck( $terms, 'name' );

								if ( ! empty( $names ) ) {

									if ( ! $cats_as_tags && is_taxonomy_hierarchical( $taxonomy ) ) {

										$value = implode( ' | ', $names );

									} else {

										$names = Utils::sanitize_hashtag( $names );
										$value = '#' . implode( ' #', $names );
									}
								}
							}
							break;

						case 'cf': // if custom field.
							$value = get_post_meta( $this->post->ID, $_field, true );
							break;
					}
				}
				break;
		}

		$value = apply_filters( 'wptelegram_p2tg_post_data_field_value', $value, $field, $this->post, $params );

		$remove_multi_eol = apply_filters( 'wptelegram_p2tg_post_data_remove_multi_eol', true, $this->post );

		if ( $remove_multi_eol ) {
			// remove multiple newlines.
			$value = preg_replace( '/\n[\n\r\s]*\n[\n\r\s]*\n/u', "\n\n", $value );
		}

		return (string) apply_filters( "wptelegram_p2tg_post_data_{$field}_value", $value, $this->post, $params );
	}

	/**
	 * Removes the autoembed filter from the_content
	 *
	 * @since 3.0.0
	 */
	public static function remove_autoembed_filter() {
		remove_filter( 'the_content', [ $GLOBALS['wp_embed'], 'autoembed' ], 8 );
	}

	/**
	 * Restores the autoembed filter to the_content
	 *
	 * @since 3.0.0
	 */
	public static function restore_autoembed_filter() {
		add_filter( 'the_content', [ $GLOBALS['wp_embed'], 'autoembed' ], 8 );
	}
}
