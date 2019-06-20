<?php
/**
 * The admin-specific functionality of the module.
 *
 * @link       https://t.me/manzoorwanijk
 * @since      1.0.0
 *
 * @package    WPTelegram
 * @subpackage WPTelegram/module
 */

/**
 * The admin-specific functionality of the module.
 *
 * @package    WPTelegram
 * @subpackage WPTelegram/module
 * @author     Manzoor Wani <@manzoorwanijk>
 */
class WPTelegram_P2TG_Admin extends WPTelegram_Module_Base {

	/**
	 * The prefix for meta data
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string  The prefix for meta data
	 */
	private static $prefix = '_wptg_p2tg_';

	/**
	 * Saved Settings/Options e.g. in meta
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    array   $saved_options Options
	 */
	private static $saved_options = null;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 *
	 * @param string $module_name  The name of the module.
	 * @param string $module_title The title of the module.
	 */
	public function __construct( $module_name, $module_title ) {

		parent::__construct( $module_name, $module_title );
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		if ( WPTG()->helpers->is_settings_page( $this->module_name ) ) {
			parent::enqueue_style( $this->module_name, $this->slug );
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		if ( WPTG()->helpers->is_settings_page( $this->module_name ) ) {
			parent::enqueue_script( $this->module_name, $this->slug, 'js', array( 'jquery', $this->plugin_name ) );
		}
		parent::enqueue_script( $this->module_name . '-global', "{$this->slug}-global", 'js', array( 'jquery', $this->plugin_name ) );
	}

	/**
	 * Build Options page
	 *
	 * @since    1.0.0
	 */
	public function create_options_page() {

		$bot_token = WPTG()->options()->get( 'bot_token' );
		$box       = array(
			'id'           => $this->plugin_name . '_' . $this->module_name,
			'parent_slug'  => $this->plugin_name,
			'title'        => $this->plugin_title . ' &rsaquo; ' . $this->module_title,
			'menu_title'   => $this->module_title,
			'object_types' => array( 'options-page' ),
			'option_key'   => $this->plugin_name . '_' . $this->module_name,
			'icon_url'     => WPTELEGRAM_URL . '/admin/icons/icon-16x16-white.svg',
			'capability'   => 'manage_options',
			'classes'      => 'wptelegram-box',
			'display_cb'   => array( WPTG()->helpers, 'render_cmb2_options_page' ),
			'desc'         => __( 'With this module, you can configure how the posts are sent to Telegram', 'wptelegram' ),
		);
		$cmb2      = new_cmb2_box( $box );

		$cmb2->add_field( array(
				'name'    => __( 'INSTRUCTIONS!', 'wptelegram' ),
				'type'    => 'title',
				'id'      => 'instructions_title',
				'classes' => 'highlight',
		) );

		// Instructions.
		$cmb2->add_field( array(
				'name'          => '',
				'type'          => 'text', // fake
				'show_names'    => false,
				'save_field'    => false,
				'id'            => 'telegram_guide',
				'render_row_cb' => array( __CLASS__, 'render_telegram_guide' ),
		) );

		$cmb2->add_field( array(
			'name' => __( 'Telegram Destination', 'wptelegram' ),
			'type' => 'title',
			'id'   => 'destination_title',
		) );

		$args = array(
			'name'            => __( 'Channel Username', 'wptelegram' ),
			'desc'            => WPTG()->helpers->get_test_button_html( __( 'Send Test', 'wptelegram' ), 'channels' ) . '<br>' . sprintf( __( 'Telegram Channel Username or Chat ID. Username must start with %s.', 'wptelegram' ), '<code>@</code>' ) . '<br>' . __( 'If more than one, separate them by comma', 'wptelegram' ),
			'id'              => 'channels',
			'type'            => 'text_medium',
			'sanitization_cb' => array( WPTG()->helpers, 'sanitize_channels' ),
			'after_row'       => array( $this, 'after_channels_cb' ),
			'classes'         => 'large-font',
			'attributes'      => array(
				'placeholder' => '@WPTelegram,-12345678998765',
				'required'    => 'required',
			),
		);

		if ( empty( $bot_token ) ) {
			unset( $args['desc'] );
			$args['attributes']['readonly'] = 'readonly';
			$args['attributes']['disabled'] = 'disabled';
			$args['after'] = WPTG()->helpers->get_empty_bot_token_html();
		}

		$cmb2->add_field( $args );

		$fields = array(
			array(
				'name'	=> __( 'Rules', 'wptelegram' ),
				'type'	=> 'title',
				'id'	=> 'rules_title',
			),
			array(
				'name'				=> __( 'Send when', 'wptelegram' ),
				'desc'				=> __( 'When the post should be sent to Telegram', 'wptelegram' ),
				'id'				=> 'send_when',
				'type'				=> 'multicheck',
				'before_row'		=> WPTG()->helpers->open_grid_row_with_col(),
				'after_row'			=> WPTG()->helpers->close_grid_col(),
				'default'			=> $this->options_checkbox_default( 'new' ),
				'select_all_button'	=> false,
				'options'			=> array(
					'new'		=> __( 'A new post is published', 'wptelegram' ),
					'existing'	=> __( 'An existing post is updated', 'wptelegram' ),
				),
			),
			array(
				'name'				=> __( 'Post type', 'wptelegram' ),
				'id'				=> 'post_types',
				'type'				=> 'multicheck',
				'desc'				=> __( 'Which post types should be sent.', 'wptelegram' ),
				'options_cb'		=> array( $this, 'get_post_type_options' ),
				'before_row'		=> WPTG()->helpers->add_grid_col_to_row(),
				'after_row'			=> WPTG()->helpers->close_grid_col_and_row(),
				'default'			=> $this->options_checkbox_default( 'post' ),
				'select_all_button'	=> false,
			),
			array(
				'name'				=> __( 'Rules', 'wptelegram' ),
				'id'				=> 'rules',
				'type'				=> 'text',
				'render_row_cb'		=> array( $this, 'render_rules' ),
				'sanitization_cb'	=> array( $this, 'sanitize_rules' ),
			),
		);

		foreach ( $fields as $field ) {
			$cmb2->add_field( $field );
		}

		// Message settings
		$fields = array(
			array(
				'name'	=> __( 'Message Settings', 'wptelegram' ),
				'type'	=> 'title',
				'id'	=> 'message_settings_title',
			),
			array(
				'name'				=> __( 'Message Template', 'wptelegram' ),
				'desc'				=> __( 'Structure of the message to be sent', 'wptelegram' ),
				'id'				=> 'message_template',
				'type'				=> 'textarea',
				'default'			=> json_encode( '{post_title}' . PHP_EOL . PHP_EOL . '{post_excerpt}' . PHP_EOL . PHP_EOL . '{full_url}' ),
				'sanitization_cb'	=> array( $this, 'sanitize_message_template' ),
				'escape_cb'			=> array( WPTG()->helpers, 'escape_message_template' ),
				'after'				=> array( $this, 'message_template_after_cb' ),
				'classes'			=> 'emojionearea-enabled',
				'attributes'		=> array(
					'data-emoji-container'   => 'p2tg-template-container',
				),
			),
			array(
				'name'			=> __( 'Excerpt Source', 'wptelegram' ),
				'id'			=> 'excerpt_source',
				'type'			=> 'radio',
				'default'		=> 'post_content',
				'before_row'	=> WPTG()->helpers->open_grid_row_with_col( 4 ),
				'after_row'		=> WPTG()->helpers->close_grid_col(),
				'options'		=> array(
					'post_content'	=> __( 'Post Content', 'wptelegram' ),
					'before_more'	=> __( 'Post Content before Read More tag', 'wptelegram' ),
					'post_excerpt'	=> __( 'Post Excerpt', 'wptelegram' ),
				),
			),
			array(
				'name'			=> __( 'Excerpt Length', 'wptelegram' ),
				'desc'			=> '<br>' . __( 'Number of words for the excerpt. Won\'t be used when Caption Source is "Post Content before Read More tag"', 'wptelegram' ),
				'default'		=> 55,
				'id'			=> 'excerpt_length',
				'type'			=> 'text_small',
				'before_row'	=> WPTG()->helpers->add_grid_col_to_row( 4 ),
				'after_row'		=> WPTG()->helpers->close_grid_col(),
				'attributes'	=> array(
					'type'	=> 'number',
					'min'	=> 1,
					'max'	=> 300,
				),
			),
			array(
				'name'            => __( 'Excerpt Newlines', 'wptelegram' ),
				'desc'            => __( 'Preserve newlines in Post Excerpt.', 'wptelegram' ),
				'id'              => 'excerpt_preserve_eol',
				'type'            => 'switch',
				'default'         => 'off',
				'before_row'      => WPTG()->helpers->add_grid_col_to_row( 4 ),
				'after_row'       => WPTG()->helpers->close_grid_col_and_row(),
				'sanitization_cb' => array( $this, 'sanitize_checkbox' ),
			),
			array(
				'name'            => __( 'Featured Image', 'wptelegram' ),
				'desc'            => __( 'Send Featured Image (if exists)', 'wptelegram' ),
				'id'              => 'send_featured_image',
				'type'            => 'switch',
				'default'         => 'on',
				'before_row'      => WPTG()->helpers->open_grid_row_with_col( 4 ),
				'after_row'       => WPTG()->helpers->close_grid_col(),
				'sanitization_cb' => array( $this, 'sanitize_checkbox' ),
			),
			array(
				'name'			=> __( 'Image Position', 'wptelegram' ),
				'id'			=> 'image_position',
				'type'			=> 'radio',
				'default'		=> 'before',
				'before_row'	=> WPTG()->helpers->add_grid_col_to_row( 4 ),
				'after_row'		=> WPTG()->helpers->close_grid_col(),
				'options'		=> array(
					'before'	=> __( 'Before the Text', 'wptelegram' ),
					'after'		=> __( 'After the Text', 'wptelegram' ),
				),
			),
			array(
				'name'				=> __( 'Single message', 'wptelegram' ),
				'desc'				=> __( 'Send both text and image in single message', 'wptelegram' ),
				'id'				=> 'single_message',
				'type'				=> 'switch',
				'before_row'	    => WPTG()->helpers->add_grid_col_to_row( 4 ),
				'after_row'		    => WPTG()->helpers->close_grid_col_and_row(),
				'after'				=> array( $this, 'after_single_message' ),
				'sanitization_cb'	=> array( $this, 'sanitize_checkbox' ),
			),
			array(
				'name'			  => __( 'Categories as hashtags', 'wptelegram' ),
				'desc'			  => __( 'Send categories as hashtags.', 'wptelegram' ),
				'id'			  => 'cats_as_tags',
				'type'			  => 'switch',
				'before_row'	  => WPTG()->helpers->open_grid_row_with_col( 6 ),
				'after_row'		  => WPTG()->helpers->close_grid_col(),
				'sanitization_cb' => array( $this, 'sanitize_checkbox' ),
			),
			array(
				'name'			=> __( 'Parse Mode', 'wptelegram' ),
				'id'			=> 'parse_mode',
				'type'			=> 'radio',
				'default'		=> 'none',
				'after'			=> '<a href="'. esc_url( 'https://core.telegram.org/bots/api/#formatting-options' ) . '" target="_blank">' . __( 'Learn more', 'wptelegram' ) . '</a>',
				'before_row'	=> WPTG()->helpers->add_grid_col_to_row( 6 ),
				'after_row'		=> WPTG()->helpers->close_grid_col_and_row(),
				'options'		=> array(
					'none'		=> __( 'None', 'wptelegram' ),
					'Markdown'	=> __( 'Markdown style', 'wptelegram' ),
					'HTML'		=> __( 'HTML style', 'wptelegram' ),
				),
			),
		);

		foreach ( $fields as $field ) {
			$cmb2->add_field( $field );
		}

		// Inline Keyboard settings
		$fields = array(
			array(
				'name'	=> __( 'Inline Keyboard', 'wptelegram' ),
				'type'	=> 'title',
				'id'	=> 'inline_keyboard_title',
			),
			array(
				'name'				=> __( 'Inline Button', 'wptelegram' ),
				'desc'				=> __( 'Add Inline URL Button', 'wptelegram' ),
				'after'				=> '<p class="description">' . __( 'Add an inline clickable button for the post URL just below the message.', 'wptelegram' ) . '</p>',
				'id'				=> 'inline_url_button',
				'type'				=> 'switch',
				'before_row'		=> WPTG()->helpers->open_grid_row_with_col(),
				'after_row'			=> WPTG()->helpers->close_grid_col(),
				'sanitization_cb'	=> array( $this, 'sanitize_checkbox' ),
			),
			array(
				'name'			=> __( 'Inline button text', 'wptelegram' ),
				'default'		=> __( 'View Post', 'wptelegram' ),
				'id'			=> 'inline_button_text',
				'type'			=> 'text_medium',
				'before_row'	=> WPTG()->helpers->add_grid_col_to_row(),
				'after_row'		=> WPTG()->helpers->close_grid_col_and_row(),
			),
		);

		foreach ( $fields as $field ) {
			$cmb2->add_field( $field );
		}

		// Other settings
		$fields = array(
			array(
				'name'	=> __( 'Miscellaneous', 'wptelegram' ),
				'type'	=> 'title',
				'id'	=> 'miscellaneous_title',
			),
			array(
				'name'				=> __( 'When editing a post', 'wptelegram' ),
				'desc'				=> __( 'Show an ON/OFF switch on the post edit screen', 'wptelegram' ),
				'after'				=> '<p class="description">' . __( 'You can use this switch to override the above settings for a particular post', 'wptelegram' ) . '</p>',
				'id'				=> 'post_edit_switch',
				'type'				=> 'switch',
				'default'			=> 'on',
				'sanitization_cb'	=> array( $this, 'sanitize_checkbox' ),
			),
			array(
				'name'       => __( 'Delay in Posting', 'wptelegram' ),
				'desc'       => __( 'Minute(s)', 'wptelegram' ),
				'default'    => 0,
				'after'      => array( $this, 'after_delay_cb' ),
				'id'         => 'delay',
				'type'       => 'text_small',
				'attributes' => array(
					'type'        => 'number',
					'min'         => '0',
					'placeholder' => '0.0',
					'step'        => '0.5',
				),
			),
			array(
				'name'				=> __( 'Other settings', 'wptelegram' ),
				'id'				=> 'misc',
				'type'				=> 'multicheck',
				'select_all_button'	=> false,
				'options'			=> array(
					'disable_web_page_preview'  => __( 'Disable Web Page Preview', 'wptelegram' ),
					'disable_notification'      => __( 'Disable Notifications', 'wptelegram' ),
				),
			),
		);

		foreach ( $fields as $field ) {
			$cmb2->add_field( $field );
		}
	}

	/**
	 * Output a message if WordPress cron is disabled
	 * @param  object $field_args Current field args
	 * @param  object $field      Current field object
	 */
	public function after_delay_cb( $field_args, $field ) {

		echo '<br>';

		if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {

			echo '<span class="wptelegram-error">';
			esc_html_e( 'WordPress cron should not be disabled!', 'wptelegram' );
			echo '<span>';
		} else {
			esc_html_e( 'The delay starts after the post gets published.', 'wptelegram' );
		}
	}

	/**
	 * render after message template HTML
	 *
	 * @since  1.0.0
	 */
	public function message_template_after_cb() {
		?>
		<p><?php esc_html_e( 'You can use any text, emojis or these macros in any order:', 'wptelegram' ); ?> <b><i>(<?php esc_html_e( 'Click to insert', 'wptelegram' ); ?>)</i></b></p>

		<p><b><?php esc_html_e( 'You can also use conditional logic in the template', 'wptelegram' ); ?></b> 😉 <a href="https://www.youtube.com/watch?v=rAFCY4haTiM" target="_blank"><?php esc_html_e( 'Learn more', 'wptelegram' ); ?></a></p>

		<?php $this->render_macros(); ?>
		<?php
	}

	/**
	 * render macros
	 *
	 * @since  1.0.0
	 */
	private function render_macros() {
		$macro_groups = array(
			'post'  => array(
				'label'     => __( 'Post Data', 'wptelegram' ),
				'macros'    => array(
					'{ID}',
					'{post_title}',
					'{post_date}',
					'{post_date_gmt}',
					'{post_author}',
					'{post_excerpt}',
					'{post_content}',
					'{short_url}',
					'{full_url}',
				),
			),
			'terms' => array(
				'label'     => __( 'Taxonomy Terms', 'wptelegram' ),
				'macros'    => array(
					'{tags}',
					'{categories}',
					'{terms:taxonomy}',
				),
			),
			'cf'    => array(
				'label'     => __( 'Custom Fields', 'wptelegram' ),
				'macros'    => array(
					'{cf:custom_field}',
				),
			),
		);

		$macro_groups['terms']['html'] = sprintf( __( 'Replace %1$s in %2$s by the name of the taxonomy to insert its terms attached to the post.', 'wptelegram' ), '<code>taxonomy</code>', '<code>{terms:taxonomy}</code>' ) . '&nbsp;' . sprintf( __( 'For example %1$s and %2$s in WooCommerce', 'wptelegram' ), '<code>{terms:product_cat}</code>', '<code>{terms:product_tag}</code>' );

		$macro_groups['cf']['html'] = sprintf( __( 'Replace %1$s in %2$s by the name of the Custom Field.', 'wptelegram' ), '<code>custom_field</code>', '<code>{cf:custom_field}</code>' ) . '&nbsp;' . sprintf( __( 'For example %1$s and %2$s in WooCommerce', 'wptelegram' ), '<code>{cf:_regular_price}</code>', '<code>{cf:_sale_price}</code>' );

		/**
		 * If you add your own macro_groups using this filter
		 * You should use "wptelegram_p2tg_macro_values" filter
		 * to replace the macro with the corresponding values
		 *
		 */
		$macro_groups = (array) apply_filters( 'wptelegram_p2tg_settings_macro_groups', $macro_groups );

		echo '<table class="wptelegram-macro" data-target="p2tg"><tbody>';

		foreach ( $macro_groups as $group ) {

			printf( '<tr><th>%s</th>', $group['label'] );

			echo '<td>';
			foreach ( $group['macros'] as $macro ) {

				printf( '<a class="btn" href="#"><code>%s</code></a> ', esc_html( $macro ) );
			}
			echo '</td></tr>';

			if ( ! empty( $group['html'] ) ) {
				printf( '<tr><td colspan="2"><span class="description">%s</span></td></tr>', $group['html'] );
			}
		}
		echo '</tbody></table>';
	}

	/**
	 * Handles sanitization for message template
	 *
	 * @param  mixed      $value      The unsanitized value from the form.
	 * @param  array      $field_args Array of field arguments.
	 * @param  CMB2_Field $field      The field object
	 *
	 * @return mixed                  Sanitized value to be stored.
	 */
	public function sanitize_message_template( $value, $field_args, $field ) {

		return WPTG()->helpers->sanitize_message_template( $value );
	}

	/**
	 * after channels HTML
	 *
	 * @since  1.0.0
	 * 
	 * @param  object $field_args Current field args
	 * @param  object $field      Current field object
	 * 
	 */
	public function after_channels_cb( $field_args, $field ) {
		?>
		<div class="cmb-row cmb-type-text cmb2-id-after-channels" data-fieldtype="text">
			<p><span id="p2tg-mem-count" class="hidden"><?php esc_html_e( "Members Count:", "wptelegram" ); ?></span></p>
			<ol style="list-style-type: disc;" id="p2tg-chat-list">
			</ol>
			<table id="p2tg-chat-table" class="hidden">
				<tbody>
					<tr>
						<th><?php esc_html_e( 'Chat ID', 'wptelegram' ); ?></th>
						<th><?php esc_html_e( 'Name/Title', 'wptelegram' ); ?></th>
						<th><?php esc_html_e( 'Chat Type', 'wptelegram' ); ?></th>
						<th><?php esc_html_e( 'Test Status', 'wptelegram' ); ?></th>
					</tr>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * after channels HTML
	 *
	 * @since  1.0.0
	 * 
	 * @param  object $field_args Current field args
	 * @param  object $field      Current field object
	 * 
	 */
	public function after_single_message( $field_args, $field ) {
		?>
		<ul>
			<li><span class="should-be" data-id="parse_mode"><?php printf( __( '%1$s should not be %2$s', 'wptelegram' ), '<b>' . __( 'Parse Mode', 'wptelegram' ) . '</b>', '<b>' . esc_html__( 'None', 'wptelegram' ) . '</b>' ); ?></span></li>
			<li><span class="should-be" data-id="misc1"><?php printf( __( '%s should not be checked', 'wptelegram' ), '<b>' . esc_html__( 'Disable Web Page Preview', 'wptelegram' ) . '</b>' ); ?></span></li>
		</ul>
		<?php
	}

	/**
	 * Manually render a field.
	 *
	 * @param  array      $field_args Array of field arguments.
	 * @param  CMB2_Field $field      The field object
	 */
	public function render_rules( $field_args, $field ) {
		$id          = $field->args( 'id' );
		$label       = __( 'Send when', 'wptelegram' );
		?>
		<div class="cmb-row cmb-type-text cmb2-id-wptg_p2tg-and no-padding" data-fieldtype="text">
			<!-- <div class="cmb-th">
			</div> -->
			<div class="cmb-td">
				<h4 style="text-align:center;margin:0px;"><?php _e( 'AND', 'wptelegram' ); ?></h4>
			</div>
		</div>

		<div class="cmb-row cmb-type-text cmb2-id-wptg_p2tg-rules" data-fieldtype="text">
			<div class="cmb-th">
			<label for="<?php echo $id; ?>"><?php echo $label; ?></label>
			</div>
			<div class="cmb-td">
			<?php
				$rules = new WPTelegram_P2TG_Rules();
				$rules->render();
			?>
			</div>

		</div>

		<?php
	}

	/**
	 * Handle ajax request for rule values
	 *
	 * @since    1.0.0
	 */
	public function ajax_render_rule_values() {

		check_ajax_referer( 'wptelegram', 'nonce' );

		WPTelegram_P2TG_Rules::render_ajax_values();
	}

	/**
	 * Handles sanitization for rules
	 *
	 * @param  mixed		$value      The unsanitized value from the form.
	 * @param  array		$field_args Array of field arguments.
	 * @param  CMB2_Field	$field      The field object
	 *
	 * @return mixed                  Sanitized value to be stored.
	 */
	public function sanitize_rules( $rule_groups, $field_args, $field ) {

		$rule_groups = WPTG()->utils->sanitize( $rule_groups, true );

		$rules = array();

		foreach ( (array) $rule_groups as $group_id => $rule_group ) {

			$group = array();

			if ( is_array( $rule_group ) ) {

				foreach( $rule_group as $rule_id => $rule ) {

					// remove empty values
					$rule = array_filter( (array) $rule );

					if ( empty( $rule['param'] ) || empty( $rule['operator'] ) || empty( $rule['values'] ) ) {
						continue;
					}

					$group[] = $rule;
				}
			}

			if ( ! empty( $group ) ) {
				$rules[] = $group;
			}
		}
		return $rules;
	}

	/**
	 * Only return default value if we don't have the settings saved
	 *
	 * @param  bool     $default The default value
	 * @return mixed    Returns true or '', the blank default
	 */
	public function options_checkbox_default( $default ) {

		$options = WPTG()->options( $this->module_name )->get();

		// set default if no options found
		if ( empty( $options ) ) {
			return $default;
		}

		return '';
	}

	/**
	 * get registered post types
	 *
	 * @since  1.0.0
	 * @return array
	 */
	public function get_post_type_options() {

		$options = array();

		$post_types = get_post_types( array( 'public' => true ), 'objects' );

		foreach ( $post_types  as $post_type ) {

			if ( 'attachment' != $post_type->name ) {

				$options[ $post_type->name ] =  "{$post_type->labels->singular_name} ({$post_type->name})";
			}
		}

		return $options;
	}

	/**
	 * Create a hidden field on the post edit page
	 * to use it for checking the requests from web
	 * in save_post callback
	 *
	 * @since    1.0.0
	 */
	public function post_edit_form_hidden_input() {

		echo '<input type="hidden" id="' . self::$prefix . 'from_web" name="' . self::$prefix . 'from_web" value="ok" />';

	}

	/**
	 * Add send to Telegram swicth to post edit page
	 * when using classic editor.
	 * 
	 * @since 1.0.0
	 */
	public function add_switch_to_submitbox() {

		$bot_token = WPTG()->options()->get( 'bot_token' );

		if ( ! $bot_token ) {
			return;
		}

		$post_edit_switch = $this->module_options->get( 'post_edit_switch', 'on' );

		if ( 'on' !== $post_edit_switch ) {
			return;
		}

		// try to remove the option from the metabox.
		$meta_box = cmb2_get_metabox( 'wptelegram_p2tg_override' );
		if ( $meta_box instanceof CMB2 ) {
			$meta_box->remove_field( self::$prefix . 'send2tg' );
		}

		global $typenow;
		$post_types = $this->get_override_meta_box_screens();

		if ( in_array( $typenow, $post_types, true ) ) :
			$default = $this->send2tg_default();
			?>
			<div class="misc-pub-section misc-pub-wptg-p2tg">
				<input type="hidden" name="<?php echo self::$prefix; ?>send2tg" value="no" />
				<label><input type="checkbox" name="<?php echo self::$prefix; ?>send2tg" value="yes" <?php checked( $this->send2tg_default(), 'yes' ); ?> /><span style="padding-left:4px;font-weight:600;"><?php esc_html_e( 'Send to Telegram', 'wptelegram' ); ?></span></label>
				&nbsp;<a style="text-decoration: none;" href="#wptelegram_p2tg_override"><span class="dashicons dashicons-admin-generic"></span></a>
			</div>

		<?php endif;

	}

	/**
	 * Override metabox
	 *
	 * @since    1.0.0
	 */
	public function create_override_metabox() {

		$bot_token = WPTG()->options()->get( 'bot_token' );

		if ( ! $bot_token ) {
			return;
		}

		$post_edit_switch = $this->module_options->get( 'post_edit_switch', 'on' );

		if ( 'on' !== $post_edit_switch ) {
			return;
		}

		$screens = $this->get_override_meta_box_screens();

		/**
		 * Initiate the metabox
		 */
		$box = array(
			'id'            => 'wptelegram_p2tg_override',
			'title'         => sprintf( '%s (%s)', $this->module_title, $this->plugin_title ),
			'object_types'  => $screens,
			'context'       => 'normal',
			'priority'      => 'high',
			'save_fields'   => false,
			'classes'       => 'wptelegram-box',
		);
		$cmb2 = new_cmb2_box( $box );

		$cmb2->add_field( array(
			'name'      	=> __( 'Send to Telegram', 'wptelegram' ),
			'id'        	=> self::$prefix . 'send2tg',
			'type'      	=> 'radio_inline',
			'default'   	=> $this->send2tg_default(),
			'options'   	=> array(
				'yes'  => __( 'Yes', 'wptelegram' ),
				'no'   => __( 'No', 'wptelegram' ),
			),
		) );

		$cmb2->add_field( array(
			'name' 			=> __( 'Override settings', 'wptelegram' ),
			'id'   			=> self::$prefix . 'override_switch',
			'type' 			=> 'switch',
		) );

		$channels = $this->module_options->get( 'channels', '' );
		$channels = explode( ',', $channels );

		$cmb2->add_field( array(
			'name'				=> __( 'Send to', 'wptelegram' ),
			'id'				=> self::$prefix . 'channels',
			'type'				=> 'multicheck',
			'select_all_button' => false,
			'default_cb'		=> array( $this, 'override_opt_default_cb' ),
			'options'			=> array_combine( $channels, $channels ),
			'classes'			=> 'hidden depends-upon-override_switch',
		) );

		$cmb2->add_field( array(
			'name' 			=> __( 'Disable Notifications', 'wptelegram' ),
			'id'   			=> self::$prefix . 'disable_notification',
			'type' 			=> 'switch',
			'default_cb'	=> array( $this, 'override_opt_default_cb' ),
			'classes'		=> 'hidden depends-upon-override_switch',
		) );

		$cmb2->add_field( array(
			'name'			=> __( 'Files', 'wptelegram' ),
			'desc'			=> __( 'Files to be sent after the message', 'wptelegram' ),
			'id'			=> self::$prefix . 'files',
			'type'			=> 'file_list',
			'default_cb'	=> array( $this, 'override_opt_default_cb' ),
			'classes'		=> 'hidden depends-upon-override_switch',
		) );

		$cmb2->add_field( array(
			'name'          => __( 'Delay in Posting', 'wptelegram' ),
			'desc'          => __( 'Minute(s)', 'wptelegram' ),
			'default_cb'	=> array( $this, 'override_opt_default_cb' ),
			'id'            => self::$prefix . 'delay',
			'after'			=> array( $this, 'after_delay_cb' ),
			'type'          => 'text_small',
			'classes'		=> 'hidden depends-upon-override_switch',
			'attributes'    => array(
				'type'        => 'number',
				'min'         => '0',
				'placeholder' => '0.0',
				'step'        => '0.5',
			),
		) );

		$cmb2->add_field( array(
			'name'			=> __( 'Message Template', 'wptelegram' ),
			'desc'			=> __( 'Structure of the message to be sent', 'wptelegram' ),
			'id'			=> self::$prefix . 'message_template',
			'type'			=> 'textarea',
			'default_cb'	=> array( $this, 'override_opt_default_cb' ),
			'after'			=> array( $this, 'message_template_after_cb' ),
			'escape_cb'		=> array( WPTG()->helpers, 'escape_message_template' ),
			'classes'		=> 'hidden emojionearea-enabled depends-upon-override_switch',
			'attributes'	=> array(
				'data-emoji-container'	=> 'p2tg-template-container',
			),
		) );
	}

	/**
	 * Set default value for override options
	 * 
	 * @param  object $field_args Current field args
	 * @param  object $field      Current field object
	 *
	 * @since  2.0.9
	 */
	public function override_opt_default_cb( $field_args, $field ) {

		// if not previously set
		if ( is_null( self::$saved_options ) ) {
			// so as no to modify original options
			self::$saved_options = $this->module_options;

			if ( isset( $_GET['post'] ) ) {
				// try to get the options from meta
				$_options = get_post_meta( $_GET['post'], self::$prefix . 'options', true );

				if ( ! empty( $_options ) ) {

					self::$saved_options->set_data( $_options );
				}
			}
		}

		// remove prefix
		$id = str_replace( self::$prefix, '', $field->args( 'id' ) );

		switch ( $id ) {
			case 'channels':
				return explode( ',' , self::$saved_options->get( $id ) );
				break; // not really needed
			case 'disable_notification':
				return ( in_array( $id, self::$saved_options->get( 'misc', array() ) ) ) ? 'on' : 'off';
				break;
			case 'files':
			case 'delay':
			case 'message_template':
				return self::$saved_options->get( $id );
				break;
		}
	}

	/**
	 * Set default value for send2tg
	 *
	 * @since  1.0.0
	 */
	public function send2tg_default() {

		$send_when = $this->module_options->get( 'send_when', array() );

		$default = 'yes';

		// if we are on edit page
		if ( isset( $_GET['post'] ) ) {

			// if saved in meta e.g. for future or draft
			if ( $send2tg = get_post_meta( $_GET['post'], self::$prefix . 'send2tg', true ) ) {

				$default = $send2tg;

			// if it's a published post
			} elseif ( ( $post = get_post( $_GET['post'] ) ) && $post instanceof WP_Post && 'publish' == $post->post_status ) {

				// whether already sent to Telegram
				$sent = get_post_meta( $_GET['post'], self::$prefix . 'sent2tg', true );

				if ( ! in_array( 'existing', $send_when ) || ! empty( $sent ) ) {
					$default = 'no';
				}
			}

		} elseif ( ! in_array( 'new', $send_when ) ) {
			$default = 'no';
		}

		return (string) apply_filters( 'wptelegram_p2tg_send2tg_default', $default, $send_when );
	}

	/**
	 * get registered post type names
	 *
	 * @since  1.0.0
	 * @return array
	 */
	public function get_override_meta_box_screens() {

		$screens = $this->module_options->get( 'post_types', array() );

		return (array) apply_filters( 'wptelegram_p2tg_override_meta_box_screens', $screens );
	}

	/**
	 * Output the Telegram Instructions
	 * @param  object $field_args Current field args
	 * @param  object $field      Current field object
	 */
	public static function render_telegram_guide( $field_args, $field ) { ?>
		<div class="cmb-row cmb-type-text cmb2-id-telegram_guide">
			<ol>
				<li><?php esc_html_e( 'Create a Channel/group/supergroup', 'wptelegram' ); ?></li>
				<li><?php esc_html_e( 'Add the Bot as Administrator to your Channel/Group', 'wptelegram' ); ?></li>
				<li><?php esc_html_e( 'Enter the Channel Username in the field below', 'wptelegram' ); ?></li>
					<ol style="list-style-type: disc;">
						<li><?php echo sprintf( __( 'Username must start with %s', 'wptelegram' ), '<code>@</code>' ); ?></li>
						<li><?php esc_html_e( 'You can also use the Chat ID of a group or private chat.', 'wptelegram' ); ?>&nbsp;<?php printf( __( 'Get it from %s', 'wptelegram' ), '<a href="https://t.me/MyChatInfoBot" target="_blank">@MyChatInfoBot</a>' ); ?></li>
					</ol>
				<li><?php printf( __( 'Hit %s below', 'wptelegram' ), '<b>' . __( 'Save Changes', 'wptelegram' ) . '</b>' ); ?></li>
				<li><?php esc_html_e( 'That\'s it. You are ready to rock :)', 'wptelegram' ); ?></li>
			</ol>
		</div>
		<?php
	}

	/**
	 * Handles checkbox sanitization
	 *
	 * @param  mixed      $value      The unsanitized value from the form.
	 * @param  array      $field_args Array of field arguments.
	 * @param  CMB2_Field $field      The field object
	 *
	 * @return mixed                  Sanitized value to be stored.
	 */
	public function sanitize_checkbox( $value, $field_args, $field ) {

		return is_null( $value ) ? 0 : $value;
	}

	/**
	 * Show admin notices on failure
	 *
	 * @since  1.0.0
	 */
	public function admin_notices() {

		$transient = 'wptelegram_p2tg_errors';

		if ( isset( $_GET[ self::$prefix . 'error'] ) && $p2tg_errors = array_filter( (array) get_transient( $transient ) ) ) {

			$html = sprintf( '<b>%s (%s):</b> %s', $this->plugin_title, $this->module_title, __( 'There was some error!', 'wptelegram' ) );

			$html .= '<table>';

			$html .= sprintf(
				'<tr><th>%s</th><th>%s</th><th>%s</th></tr>',
				__( 'Channel', 'wptelegram' ),
				__( 'Error code', 'wptelegram' ),
				__( 'Error message', 'wptelegram' )
			);

			foreach ( $p2tg_errors as $channel => $errors ) {

				foreach ( $errors as $code => $message ) {

					$html .= sprintf(
						'<tr><td><b>%s</b></td><td>%s</td><td><b>%s</b></td></tr>',
						$channel,
						$code,
						$message );
				}
			}

			$html .= '</table>';

			?>
			<div class="notice notice-error is-dismissible">
			  <p><?php echo $html; ?></p>
			</div>
			<?php
		}
		delete_transient( $transient );
	}

	/**
	 * Add admin sidebar content 
	 *
	 * @since  2.0.13
	 */
	public function add_sidebar_row( $object_id, $hookup ) {
		if ( 'wptelegram_p2tg' === $object_id ) : ?>
			<div class="cell">
				<iframe src="https://www.youtube.com/embed/MFTQo3ObWmc" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
			</div>
		<?php endif;
	}
}
