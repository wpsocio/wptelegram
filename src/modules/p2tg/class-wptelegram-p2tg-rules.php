<?php
/**
 * Handle the rules for P2TG
 *
 * @link       https://t.me/manzoorwanijk
 * @since      1.0.0
 *
 * @package    WPTelegram
 * @subpackage WPTelegram/modules
 */

/**
 * Class responsible for handling the rules for P2TG
 *
 *
 * @package    WPTelegram
 * @subpackage WPTelegram/modules
 * @author     Manzoor Wani
 */
class WPTelegram_P2TG_Rules {

	/**
	 * The CMB2_Types object
	 *
	 * @var CMB2_Types
	 */
	public static $types;

	/**
	 * Initialize the class
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
	}

	/**
	 * Render the rules
	 *
	 * @since    1.0.0
	 */
	public function render() {

		$rule_groups = $this->get_rule_groups();

		foreach ( $rule_groups as $group_id => $rule_group ) :

			$group_id = 'group_' . $group_id;
			?>
			<div class="p2tg-rules-group" data-id="<?php echo $group_id; ?>">

				<?php
				if ( is_array( $rule_group ) ) :
					?>
					<h4><?php esc_html_e( 'OR', 'wptelegram' ); ?></h4>
					<table class="p2tg-rules widefat">
						<tbody>

							<?php
							foreach ( $rule_group as $rule_id => $rule ) : 
								$rule_id = 'rule_' . $rule_id;
								?>

								<tr data-id="<?php echo $rule_id; ?>">
									<td class="param">
										<?php $this->render_rule_types( $group_id, $rule_id, $rule ); ?>
									</td>
									<td class="operator">
										<?php $this->render_operators( $group_id, $rule_id, $rule ); ?>
									</td>
									<td class="values">
										<?php $this->render_values( $group_id, $rule_id, $rule ); ?>
									</td>
									<td class="add">
										<a href="#" class="p2tg-rules-add button"><?php esc_html_e( 'AND', 'wptelegram' ); ?></a>
									</td>
									<td class="remove">
										<a href="#" class="p2tg-rules-remove button">X</a>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>

		<h4><?php esc_html_e( 'OR', 'wptelegram' ); ?></h4>

		<a class="button p2tg-rules-add-group" href="#"><?php esc_html_e( 'Add', 'wptelegram' ); ?></a>
		<?php
	}

	/**
	 * Get the rule groups
	 *
	 * @since    1.0.0
	 */
	public function get_rule_groups() {

		$rule_groups = WPTG()->options( 'p2tg' )->get( 'rules', array() );

		if ( empty( $rule_groups ) ) {

			$rule_groups = array(
				array( // group_0.
					array( // rule_0.
						'param'    => '',
						'operator' => '==',
						'values'   => '',
					),
				),
			);
		}

		return (array) apply_filters( 'wptelegram_p2tg_rule_groups', $rule_groups );
	}

	/**
	 * Render select html field
	 *
	 * @since    1.0.0
	 */
	public static function render_select( $args ) {

		if ( ! isset( $args['id'] ) ) {

			$args['id'] = str_replace( array( '[]', '][', '[', ']' ), array( '', '_', '-', '' ), $args['name'] );
		}

		// create field.
		$field = new CMB2_Field( array(
			'field_args' => array_merge( array(
					'type' => 'select_plus',
				), $args
			),
		) );

		// pass the field to custom class.
		if ( ! isset( self::$types ) ) {
			self::$types = new Select_Plus_CMB2_Types( $field );
		} else {
			self::$types->field = $field;
		}

		// render the field with new id and name.
		echo self::$types->select_plus( array(
			'name' => $args['name'],
		) );
	}

	/**
	 * Render the types of rules
	 *
	 * @since    1.0.0
	 */
	public function render_rule_types( $group_id, $rule_id, $rule ) {

		$rule_types = $this->get_rule_types();

		$args = array(
			'name'             => 'rules[' . $group_id . '][' . $rule_id . '][param]',
			'default'          => $rule['param'],
			'options'          => $rule_types,
			'show_option_none' => ' ',
		);

		self::render_select( $args );
	}

	/**
	 * get the types of rules
	 *
	 * @since    1.0.0
	 */
	public function get_rule_types() {

		$rule_types = array(
			__( 'Post', 'wptelegram' )            => array(
				'post'        => __( 'Post', 'wptelegram' ),
				'category'    => __( 'Post Category', 'wptelegram' ),
				'post_tag'    => __( 'Post Tag', 'wptelegram' ),
				'post_format' => __( 'Post Format', 'wptelegram' ),
				'post_author' => __( 'Post Author', 'wptelegram' ),
			),
			__( 'Custom Taxonomy', 'wptelegram' ) => self::get_taxonomy_rule_types(),
		);

		// allow custom rule_types.
		return (array) apply_filters( 'wptelegram_p2tg_rule_types', $rule_types );
	}

	/**
	 * Render the operators
	 *
	 * @since    1.0.0
	 */
	public function render_operators( $group_id, $rule_id, $rule ) {

		$rule_operators = array(
			'in'     => __( 'is in', 'wptelegram' ),
			'not_in' => __( 'is not in', 'wptelegram' ),
		);

		// allow custom rule_operators.
		$rule_operators = apply_filters( 'wptelegram_p2tg_rule_operators', $rule_operators );

		$args = array(
			'name'    => 'rules[' . $group_id . '][' . $rule_id . '][operator]',
			'default' => $rule['operator'],
			'options' => $rule_operators,
		);

		self::render_select( $args );
	}

	/**
	 * Render the values for selection.
	 *
	 * @since    1.0.0
	 */
	public function render_values( $group_id, $rule_id, $rule ) {
		$args = array(
			'group_id' => $group_id,
			'rule_id'  => $rule_id,
			'values'   => $rule['values'],
			'param'    => $rule['param'],
		);

		self::render_ajax_values( $args );
	}

	/**
	 * Get the taxonomy for rule types
	 *
	 * @since    1.0.0
	 */
	public static function get_taxonomy_rule_types() {

		$to_skip = array(
			'product_shipping_class',
		);

		$rule_types = array();

		$args = array(
			'public'   => true,
			'_builtin' => false,
		);

		$taxonomies = get_taxonomies( $args, 'objects' );

		foreach ( $taxonomies as $taxonomy ) {

			if ( in_array( $taxonomy->name, $to_skip ) ) {
				continue;
			}

			// use a prefix for identification.
			$rule_types[ 'tax:' . $taxonomy->name ] = "{$taxonomy->labels->singular_name} ({$taxonomy->name})";
		}

		return apply_filters( 'wptelegram_p2tg_taxonomy_rule_types', $rule_types );
	}

	/**
	 * Render the values for selection in ajax
	 *
	 * @since    1.0.0
	 */
	public static function render_ajax_values( $args = array() ) {

		$is_ajax = defined( 'DOING_AJAX' ) && DOING_AJAX;

		$defaults = array(
			'group_id' => 0,
			'rule_id'  => 0,
			'values'   => array(),
			'param'    => '',
		);

		if ( $is_ajax ) {

			$args = array_merge( $defaults, $_POST );

		} else {

			$args = array_merge( $defaults, $args );
		}

		$options = array();

		switch ( $args['param'] ) {

			case 'post':
				$post_types = get_post_types( array( 'public' => true ), 'objects' );

				unset( $post_types['attachment'] );

				foreach ( $post_types as $post_type ) {

					$posts = get_posts(
						array(
							'numberposts' => -1,
							'post_type'   => $post_type->name,
							'post_status' => 'publish',
						)
					);

					if ( $posts ) {

						foreach ( $posts as $post ) {

							$options[ "{$post_type->labels->singular_name} ({$post_type->name})" ][ $post->ID ] = get_the_title( $post );
						}
					}
				}
				break;

			case 'post_format':
				$options = get_post_format_strings();

				break;

			case 'post_author':
				$options = self::get_author_list();

				break;

			default:
				// if it's a taxonomy.
				if ( preg_match( '/^(?:tax:|category$|post_tag$)/i', $args['param'] ) ) {

					$taxonomy = preg_replace( '/^tax:/i', '', $args['param'] );

					$options = self::get_term_list( $taxonomy );
				}
				break;
		}

		// allow custom rule_operators.
		$options = apply_filters( 'wptelegram_p2tg_rule_values', $options, $args );

		$args = array(
			'name'       => 'rules[' . $args['group_id'] . '][' . $args['rule_id'] . '][values][]',
			'default'    => $args['values'],
			'options'    => $options,
			'class'      => 'select2',
			'attributes' => array(
				'multiple' => 'multiple',
			),
		);

		self::render_select( $args );

		if ( $is_ajax ) {
			die();
		}
	}

	/**
	 * get all terms of a taxonomy.
	 *
	 * @since  1.0.0
	 * @return array
	 */
	public static function get_term_list( $taxonomy ) {

		$term_list = array();

		$terms = get_terms(
			$taxonomy,
			array(
				'hide_empty' => 0,
				'orderby'    => 'term_group',
			)
		);

		$terms_count = count( $terms );

		foreach ( $terms as $term ) {

			$term_name = $term->name;

			if ( is_taxonomy_hierarchical( $taxonomy ) && $term->parent ) {
				$parent_id  = $term->parent;
				$has_parent = true;

				// avoid infinite loop with "ghost" categories.
				$found = false;
				$i     = 0;

				while ( $has_parent && ( $i < $terms_count || $found ) ) {

					// Reset each time.
					$found = false;
					$i     = 0;

					foreach ( $terms as $parent_term ) {

						$i++;

						if ( $parent_term->term_id == $parent_id ) {
							$term_name = $parent_term->name . ' &rarr; ' . $term_name;
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
			$term_list[ $term->term_id ] = $term_name;
		}
		return apply_filters( 'wptelegram_p2tg_rules_term_list', $term_list, $taxonomy );
	}

	/**
	 * get all post authors.
	 *
	 * @since  1.0.0
	 * @return array
	 */
	public static function get_author_list() {

		$author_list = array();

		$args = array(
			'orderby' => 'name',
			'who'     => 'authors',
		);

		$authors = get_users( $args );

		foreach ( $authors as $author ) {

			$author_list[ $author->ID ] = get_the_author_meta( 'display_name', $author->ID );
		}
		return apply_filters( 'wptelegram_p2tg_rules_author_list', $author_list );
	}

	/**
	 * Check if the rules apply to the post
	 *
	 * @since   1.0.0
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
	 * @return  bool
	 */
	public function get_post_data( $post ) {

		$post_data = array();

		// convert to one dimensional array.
		$rule_types = array_keys( call_user_func_array( 'array_merge', $this->get_rule_types() ) );

		foreach ( $rule_types as $param ) {

			$post_data[ $param ] = $this->get_post_data_for_param( $param, $post );
		}

		return apply_filters( 'wptelegram_p2tg_rules_post_data', $post_data, $post );
	}

	/**
	 * Check if a particular rule applies to the post.
	 *
	 * @since   1.0.0
	 *
	 * @return  bool
	 */
	public function rule_matches( $rule, $post_data ) {

		// if the param is a taxonomy, add children of each taxonomy to values.
		if ( preg_match( '/^(?:tax:|category$|post_tag$)/i', $rule['param'] ) && ! empty( $rule['values'] ) ) {

			$taxonomy = preg_replace( '/^tax:/i', '', $rule['param'] );

			$include_child = (bool) apply_filters( 'wptelegram_p2tg_rules_include_child_terms', true, $rule['param'], $post_data );

			if ( $include_child && is_taxonomy_hierarchical( $taxonomy ) ) {

				// create a copy for loop.
				$values = $rule['values'];

				foreach ( $values as $term_id ) {

					$children = get_term_children( (int) $term_id, $taxonomy );
					// unite children and their parents.
					$rule['values'] = array_unique( array_merge( $rule['values'], $children ) );
				}
			}
		}

		// if the param doesn't exist in $post_data.
		if ( ! isset( $post_data[ $rule['param'] ] ) ) {

			$post_data[ $rule['param'] ] = array();
		}

		$in_array = false;

		foreach ( (array) $post_data[ $rule['param'] ] as $value ) {

			// if any of the post data values exists in saved values.
			if ( $in_array = in_array( $value, $rule['values'] ) ) {

				break;
			}
		}

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
	 * @param string  $param The post field.
	 * @param WP_Post $post  The post.
	 *
	 * @return  bool
	 */
	public function get_post_data_for_param( $param, $post ) {

		$data = array();

		switch ( $param ) {

			case 'post':
				$data = $post->ID;

				break;

			case 'post_format':
				$post_format = get_post_format( $post->ID );

				if ( ! $post_format ) {
					$post_format = 'standard';
				}

				$data = $post_format;

				break;

			case 'post_author':
				$data = $post->post_author;

				break;

			default:
				// if it's a taxonomy.
				if ( preg_match( '/^(?:tax:|category$|post_tag$)/i', $param ) ) {

					$taxonomy = preg_replace( '/^tax:/i', '', $param );

					$terms = get_the_terms( $post->ID, $taxonomy );

					// make sure that it's not a non-existent taxonomy.
					if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {

						$data = wp_list_pluck( array_filter( $terms ), 'term_id' );
					}
				}

				break;
		}

		return apply_filters( 'wptelegram_p2tg_rules_post_data_for_param', $data, $param, $post );
	}
}
