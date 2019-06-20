<?php

/**
 * Post Handling functionality of the plugin.
 *
 * @link		https://t.me/WPTelegram
 * @since		2.0.0
 *
 * @package		WPTelegram
 * @subpackage	WPTelegram/includes
 */

/**
 * The Post Handling functionality of the plugin.
 *
 * @package		WPTelegram
 * @subpackage	WPTelegram/includes
 * @author		Manzoor Wani <@manzoorwanijk>
 */
class WPTelegram_P2TG_Post_Data {

	/**
	 * The post to be handled
	 *
	 * @var	WP_Post	$post	Post object.
	 */
	protected $post;

	/**
	 * The post data
	 *
	 * @since	2.0.0
	 * @access	protected
	 * @var		array 		$data 	The array containing the post data
	 */
	protected $data;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since   2.0.0
	 * @param   string    $module_name  The name of the module.
	 */
	public function __construct( $post ) {

		$this->data = array();

		$this->set_post( $post );
	}

	/**
	 * Set the post
	 *
	 * @since    2.0.0
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
	 * @param  string $field	The field to be retrieved
	 * @param  string $params	Optional params to be used for some fields
	 *
	 * @return mixed			Field value
	 */
	public function get_field( $field, $params = array() ) {

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
	 * @return mixed			Field value
	 */
	public function get_field_value( $field, $params = array() ) {

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

			/* Post Excerpt */
			case 'excerpt':
			case 'post_excerpt':
				$excerpt_source = isset( $params['excerpt_source'] ) ? $params['excerpt_source'] : 'post_content';
				$excerpt_length = isset( $params['excerpt_length'] ) ? $params['excerpt_length'] : 55;
				$preserve_eol = ( isset( $params['excerpt_preserve_eol'] ) && 'on' === $params['excerpt_preserve_eol'] );

				if ( 'before_more' === $excerpt_source ) {

					$parts   = get_extended( apply_filters( 'the_content', get_post_field( 'post_content', $this->post ) ) );
					$excerpt = $parts['main'];

				} else {

					$excerpt = get_post_field( $excerpt_source, $this->post );

					$filter = str_replace( 'post', 'the', $excerpt_source );

					// apply the_content or the_excerpt.
					$excerpt = apply_filters( $filter, $excerpt );
				}

				// remove shortcodes and convert <br> to EOL.
				$excerpt = str_replace( '<br>', PHP_EOL, strip_shortcodes( $excerpt ) );

				$value = WPTG()->utils->trim_words( $excerpt, $excerpt_length, '…', $preserve_eol );
				break;

			/* Post Content */
			case 'content':
			case 'post_content':
				$content = get_post_field( 'post_content', $this->post );
				$content = str_replace( '<br>', PHP_EOL, $content );
				$content = apply_filters( 'the_content', $content );
				$content = trim( strip_tags( html_entity_decode( $content ), '<b><strong><em><i><a><pre><code>' ) );
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

							$cats_as_tags = ( isset( $params['cats_as_tags'] ) && 'on' === $params['cats_as_tags'] );

							$cats_as_tags = apply_filters( "wptelegram_p2tg_post_data_send_{$taxonomy}_as_tags", $cats_as_tags, $this->post, $params );

							if ( taxonomy_exists( $taxonomy ) ) {

								$terms = get_the_terms( $this->post->ID, $taxonomy );

								$names = ( is_wp_error( $terms ) || empty( $terms ) ) ? array() : wp_list_pluck( $terms, 'name' );

								if ( ! empty( $names ) ) {

									if ( ! $cats_as_tags && is_taxonomy_hierarchical( $taxonomy ) ) {

										$value = implode( ' | ', $names );

									} else {

										$names = WPTG()->utils->sanitize_hashtag( $names );
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

		return (string) apply_filters( "wptelegram_p2tg_post_data_{$field}_value", $value, $this->post, $params );
	}
}