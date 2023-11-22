<?php
/**
 * P2Tg rules endpoint for WordPress REST API.
 *
 * @link       https://wpsocio.com
 * @since      3.0.0
 *
 * @package    WPTelegram
 * @subpackage WPTelegram\Core\modules\p2tg\restApi
 */

namespace WPTelegram\Core\modules\p2tg\restApi;

use WPTelegram\Core\includes\restApi\RESTController;
use WPTelegram\Core\includes\Utils;
use WP_REST_Server;
use WP_REST_Request;

/**
 * Class to handle the rules endpoint.
 *
 * @since 3.0.0
 *
 * @package    WPTelegram
 * @subpackage WPTelegram\Core\modules\p2tg\restApi
 * @author     WP Socio
 */
class RulesController extends RESTController {

	/**
	 * The base of this controller's route.
	 *
	 * @var string
	 */
	const REST_BASE = '/p2tg-rules';

	/**
	 * Register the routes for the objects of the controller.
	 *
	 * @since 3.0.0
	 */
	public function register_routes() {

		register_rest_route(
			self::REST_NAMESPACE,
			self::REST_BASE,
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'rest_get_rule_values' ],
					'permission_callback' => [ $this, 'rules_permissions' ],
					'args'                => self::get_rule_value_params(),
				],
			]
		);
	}

	/**
	 * Check request permissions.
	 *
	 * @since 3.0.0
	 *
	 * @return bool
	 */
	public function rules_permissions() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Handle REST request for p2tg rule values.
	 *
	 * @since 3.0.0
	 *
	 * @param WP_REST_Request $request WP REST API request.
	 */
	public function rest_get_rule_values( WP_REST_Request $request ) {

		$options = [];

		$param  = $request->get_param( 'param' );
		$search = $request->get_param( 'search' );

		$options = self::get_rule_values( $param, $search );

		return rest_ensure_response( $options );
	}

	/**
	 * Get the P2Tg rule values.
	 *
	 * @since 3.0.0
	 *
	 * @param string $param   The param to get the rules for.
	 * @param string $search  Search keywords.
	 * @param array  $include Limit the result to given IDs.
	 *
	 * @return array
	 */
	public static function get_rule_values( $param, $search = '', $include = [] ) {

		$options = [];

		switch ( $param ) {

			case 'post':
				$post_types = get_post_types( [ 'public' => true ], 'objects' );
				unset( $post_types['attachment'] );

				foreach ( $post_types as $post_type ) {

					$posts = get_posts(
						[
							'numberposts' => 100,
							'post_type'   => $post_type->name,
							'post_status' => 'publish',
							's'           => $search,
							'include'     => $include,
						]
					);

					if ( $posts ) {

						$post_options = [];

						foreach ( $posts as $post ) {

							$post_options[] = [
								'value' => "{$post->ID}",
								'label' => Utils::decode_html( get_the_title( $post ) ),
							];
						}

						$options[] = [
							'label'   => Utils::decode_html( "{$post_type->labels->singular_name} ({$post_type->name})" ),
							'options' => $post_options,
						];
					}
				}
				break;

			case 'post_format':
				$options = Utils::array_to_select_options( get_post_format_strings() );

				if ( ! empty( $include ) ) {
					$options = array_filter(
						$options,
						function ( $option ) use ( $include ) {
							return in_array( $option['value'], $include, true );
						}
					);

					// Reset array keys.
					$options = array_values( $options );
				}

				break;

			case 'post_author':
				$options = self::get_author_list( $search, $include );

				break;

			default:
				// if it's a taxonomy.
				if ( preg_match( '/^(?:tax:|category$|post_tag$)/i', $param ) ) {

					$taxonomy = preg_replace( '/^tax:/i', '', $param );

					$options = self::get_term_list( $taxonomy, $search, $include );
				}
				break;
		}

		// Allow custom rule_operators.
		return apply_filters( 'wptelegram_p2tg_rule_values', $options, $param, $search, $include );
	}

	/**
	 * Get all post authors.
	 *
	 * @since  3.0.0
	 * @param string $search The search query.
	 * @param array  $include Limit the result to given IDs.
	 * @return array
	 */
	public static function get_author_list( $search, $include = [] ) {

		$author_list = [];

		$args = [
			'orderby' => 'name',
			'who'     => 'authors',
			'search'  => $search,
			'include' => $include,
		];

		$authors = get_users( $args );

		foreach ( $authors as $author ) {

			$author_list[] = [
				'value' => "{$author->ID}",
				'label' => Utils::decode_html( get_the_author_meta( 'display_name', $author->ID ) ),
			];
		}

		return apply_filters( 'wptelegram_p2tg_rules_author_list', $author_list );
	}

	/**
	 * Get all terms of a taxonomy.
	 *
	 * @since  3.0.0
	 *
	 * @param string $taxonomy Taxonomy name.
	 * @param string $search search query.
	 * @param array  $include Limit the result to given IDs.
	 * @return array
	 */
	public static function get_term_list( $taxonomy, $search, $include = [] ) {

		$term_list = [];

		$terms = get_terms(
			[
				'taxonomy'   => $taxonomy,
				'hide_empty' => 0,
				'orderby'    => 'term_group',
				'search'     => $search,
				'include'    => $include,
			]
		);

		if ( is_wp_error( $terms ) ) {
			return $term_list;
		}

		$terms_count = count( $terms );

		foreach ( $terms as $term ) {

			$term_name = $term->name;

			if ( is_taxonomy_hierarchical( $taxonomy ) && $term->parent ) {
				$parent_id  = $term->parent;
				$has_parent = true;

				// Avoid infinite loop with "ghost" categories.
				$found = false;
				$i     = 0;

				while ( $has_parent && ( $i < $terms_count || $found ) ) {

					// Reset each time.
					$found = false;
					$i     = 0;

					foreach ( $terms as $parent_term ) {

						++$i;

						if ( $parent_term->term_id === $parent_id ) {
							$term_name = $parent_term->name . ( is_rtl() ? ' ← ' : ' → ' ) . $term_name;
							$found     = true;

							if ( $parent_term->parent ) {
								$parent_id = $parent_term->parent;

							} else {
								$has_parent = false;
							}
							break;
						}
					}
				}
			}
			$term_list[] = [
				'value' => "{$term->term_id}",
				'label' => Utils::decode_html( $term_name ),
			];
		}

		return apply_filters( 'wptelegram_p2tg_rules_term_list', $term_list, $taxonomy );
	}

	/**
	 * Retrieves the query params for the endpoint.
	 *
	 * @since 3.0.0
	 *
	 * @return array Query parameters for the endpoint.
	 */
	public static function get_rule_value_params() {
		return [
			'param'  => [
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
			],
			'search' => [
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
			],
		];
	}
}
