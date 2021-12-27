<?php
/**
 * Handle the rules for P2TG
 *
 * @link       https://wpsocio.com
 * @since      1.0.0
 *
 * @package    WPTelegram
 * @subpackage WPTelegram\Core\modules\p2tg
 */

namespace WPTelegram\Core\modules\p2tg;

use WP_Post;

/**
 * Class responsible for handling the rules for P2TG
 *
 * @package    WPTelegram
 * @subpackage WPTelegram\Core\modules\p2tg
 * @author     WP Socio
 */
class Rules {

	/**
	 * Get the types of rules.
	 *
	 * @since    1.0.0
	 */
	public static function get_rule_types() {

		$rule_types = [
			[
				'label'   => __( 'Post', 'wptelegram' ),
				'options' => [
					[
						'value' => 'post',
						'label' => __( 'Post', 'wptelegram' ),
					],
					[
						'value' => 'category',
						'label' => __( 'Post Category', 'wptelegram' ),
					],
					[
						'value' => 'post_tag',
						'label' => __( 'Post Tag', 'wptelegram' ),
					],
					[
						'value' => 'post_format',
						'label' => __( 'Post Format', 'wptelegram' ),
					],
					[
						'value' => 'post_author',
						'label' => __( 'Post Author', 'wptelegram' ),
					],
				],
			],
			[
				'label'   => __( 'Custom Taxonomy', 'wptelegram' ),
				'options' => self::get_taxonomy_rule_types(),
			],
		];

		// allow custom rule_types.
		return (array) apply_filters( 'wptelegram_p2tg_rule_types', $rule_types );
	}

	/**
	 * Get the taxonomy for rule types
	 *
	 * @since    1.0.0
	 */
	public static function get_taxonomy_rule_types() {

		$to_skip = [
			'product_shipping_class',
		];

		$rule_types = [];

		$args = [
			'public'   => true,
			'_builtin' => false,
		];

		$taxonomies = get_taxonomies( $args, 'objects' );

		foreach ( $taxonomies as $taxonomy ) {

			if ( in_array( $taxonomy->name, $to_skip, true ) ) {
				continue;
			}

			$rule_types[] = [
				// Use a prefix for identification.
				'value' => 'tax:' . $taxonomy->name,
				'label' => "{$taxonomy->labels->singular_name} ({$taxonomy->name})",
			];
		}

		return apply_filters( 'wptelegram_p2tg_taxonomy_rule_types', $rule_types );
	}

	/**
	 * Check if the rules apply to the post
	 *
	 * @since 1.0.0
	 *
	 * @param array   $rules The saved rules.
	 * @param WP_Post $post  The post being processed.
	 *
	 * @return  bool
	 */
	public function rules_apply( $rules, $post ) {

		// if no rules are set.
		if ( empty( $rules ) ) {
			return true;
		}

		// store the data to avoid multiple calls.
		$post_data = $this->get_post_data( $post );

		// default false.
		// until we find a condition that makes it true.
		$rules_apply = false;

		foreach ( (array) $rules as $rule_group ) {

			$group_matches = true;

			foreach ( (array) $rule_group as $rule ) {

				if ( ! $this->rule_matches( $rule, $post_data ) ) {

					$group_matches = false;

					// no need to check other rules.
					// in the same group.
					break;
				}
			}

			if ( $group_matches ) {

				$rules_apply = true;
			}
		}

		return (bool) apply_filters( 'wptelegram_p2tg_rules_apply', $rules_apply, $rules, $post );
	}

	/**
	 * Check if a particular rule applies to the post
	 *
	 * @since   1.0.0
	 *
	 * @param WP_Post $post  The post being processed.
	 * @return  bool
	 */
	public function get_post_data( $post ) {

		$post_data = [];

		// convert to one dimensional array.
		$rule_types = $this->get_rule_type_keys( $post );

		foreach ( $rule_types as $param ) {

			$post_data[ $param ] = $this->get_post_data_for_param( $param, $post );
		}

		return apply_filters( 'wptelegram_p2tg_rules_post_data', $post_data, $post );
	}

	/**
	 * Get the types of rules.
	 *
	 * @since    3.0.0
	 *
	 * @param WP_Post $post  The post being processed.
	 *
	 * @return array
	 */
	public function get_rule_type_keys( $post ) {

		$rule_type_keys = array_map(
			function ( $rule_type ) {
				return array_column( $rule_type['options'], 'value' );
			},
			$this->get_rule_types()
		);

		// convert to one dimensional array.
		$rule_type_keys = call_user_func_array( 'array_merge', $rule_type_keys );

		return (array) apply_filters( 'wptelegram_p2tg_rule_type_keys', $rule_type_keys, $post );
	}

	/**
	 * Check if a particular rule applies to the post.
	 *
	 * @since   1.0.0
	 *
	 * @param array   $rule      Single rule.
	 * @param WP_Post $post_data The current post data.
	 *
	 * @return  bool
	 */
	public function rule_matches( $rule, $post_data ) {
		/**
		 * Extract values from array of ['value'=> '', 'label'=>''].
		 */
		$values = ! empty( $rule['values'] ) ? wp_list_pluck( $rule['values'], 'value' ) : [];
		$param  = $rule['param'];

		// if the param doesn't exist in $post_data.
		if ( ! isset( $post_data[ $param ] ) ) {

			$post_data[ $param ] = [];
		}

		// if any of the post data values exists in saved values.
		$in_array = (bool) array_intersect( (array) $post_data[ $param ], $values );

		// the default false.
		$rule_matches = false;

		if ( ( 'in' === $rule['operator'] && $in_array ) || ( 'not_in' === $rule['operator'] && ! $in_array ) ) {

			$rule_matches = true;
		}

		return (bool) apply_filters( 'wptelegram_p2tg_rule_matches', $rule_matches, $rule, $post_data );
	}

	/**
	 * Get the data for post params
	 *
	 * @since   1.0.0
	 * @access   private
	 *
	 * @param string  $param The post field.
	 * @param WP_Post $post  The post.
	 *
	 * @return  bool
	 */
	private function get_post_data_for_param( $param, $post ) {

		$data = [];

		switch ( $param ) {

			case 'post':
				$data = [ $post->ID ];

				break;

			case 'post_format':
				$post_format = get_post_format( $post->ID );

				if ( ! $post_format ) {
					$post_format = 'standard';
				}

				$data = [ $post_format ];

				break;

			case 'post_author':
				$data = [ $post->post_author ];

				break;

			default:
				// if it's a taxonomy.
				if ( preg_match( '/^(?:tax:|category$|post_tag$)/i', $param ) ) {

					$taxonomy = preg_replace( '/^tax:/i', '', $param );

					$terms = get_the_terms( $post->ID, $taxonomy );

					// make sure that it's not a non-existent taxonomy.
					if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {

						$data = wp_list_pluck( array_filter( $terms ), 'term_id' );

						$include_child = (bool) apply_filters( 'wptelegram_p2tg_rules_include_child_terms', true, $param, $data, $post );

						if ( ! empty( $data ) && $include_child && is_taxonomy_hierarchical( $taxonomy ) ) {

							// create a copy for loop.
							$_data = $data;
							foreach ( $_data as $term_id ) {

								$children = get_term_children( (int) $term_id, $taxonomy );
								// unite children and their parents.
								$data = array_merge( $data, $children );
							}
							$data = array_unique( $data );
						}
					}
				}

				break;
		}

		$data = (array) apply_filters( 'wptelegram_p2tg_rules_post_data_for_param', $data, $param, $post );

		return array_map( 'strval', $data );
	}
}
