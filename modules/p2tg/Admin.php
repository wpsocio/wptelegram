<?php
/**
 * The admin-specific functionality of the module.
 *
 * @link       https://wpsocio.com
 * @since      1.0.0
 *
 * @package    WPTelegram
 * @subpackage WPTelegram\Core\modules\p2tg
 */

namespace WPTelegram\Core\modules\p2tg;

use WPTelegram\Core\modules\BaseClass;
use WPTelegram\Core\includes\Utils as MainUtils;
use WPTelegram\Core\includes\AssetManager;
use WPTelegram\Core\modules\p2tg\restApi\RulesController;
use WP_Post;
use WPSocio\WPUtils\Options;

/**
 * The admin-specific functionality of the module.
 *
 * @package    WPTelegram
 * @subpackage WPTelegram\Core\modules\p2tg
 * @author     WP Socio
 */
class Admin extends BaseClass {

	const OVERRIDE_METABOX_ID = 'wptelegram_p2tg_override';

	/**
	 * Saved Settings/Options e.g. in meta
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    Options   $saved_options Options
	 */
	private static $saved_options = null;

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    3.0.3
	 */
	public function enqueue_admin_scripts() {

		if ( ! self::show_post_edit_switch() ) {
			return;
		}

		$screens = $this->get_override_meta_box_screens();

		// Load Post to Telegram js for classic editor if CMB2 is loaded.
		if (
			MainUtils::is_post_edit_page( $screens )
			&& did_action( 'cmb2_init' )
			&& ! did_action( 'enqueue_block_editor_assets' )
		) {
			WPTG()->assets()->enqueue( AssetManager::P2TG_CLASSIC_EDITOR_ENTRY );
		}
	}

	/**
	 * Enqueue assets for the Gutenberg block
	 *
	 * @since    3.0.3
	 */
	public function enqueue_block_editor_assets() {

		if ( ! self::show_post_edit_switch() ) {
			return;
		}

		$screens = $this->get_override_meta_box_screens();

		if ( ! MainUtils::is_post_edit_page( $screens ) ) {
			return;
		}

		WPTG()->assets()->enqueue( AssetManager::P2TG_BLOCK_EDITOR_ENTRY );

		AssetManager::instance()->add_inline_script( AssetManager::P2TG_BLOCK_EDITOR_ENTRY );
	}

	/**
	 * Hooks into "rest_pre_insert_{$post->post_type}"
	 * to create a hack for did_action for filters.
	 *
	 * @since 3.0.11
	 */
	public function hook_into_rest_pre_insert() {
		$post_types = $this->module()->options()->get( 'post_types', [] );

		foreach ( $post_types as $post_type ) {
			add_filter( "rest_pre_insert_{$post_type}", [ $this, 'do_rest_pre_insert_action' ], 10, 1 );
		}
	}

	/**
	 * Sets the rest_pre_insert action for post types to use in PostSender.
	 *
	 * @since 3.0.11
	 *
	 * @param \stdClass $post An object representing a single post prepared.
	 */
	public function do_rest_pre_insert_action( $post ) {

		do_action( 'wptelegram_rest_pre_insert_' . $post->post_type );

		return $post;
	}

	/**
	 * Register WP REST API routes.
	 *
	 * @since 3.0.0
	 */
	public function register_rest_routes() {
		$controller = new RulesController();
		$controller->register_routes();
	}

	/** Updates the DOM data related to p2tg.
	 *
	 * @param array  $data The existing DOM data.
	 * @param string $for  The domain for which the DOM data is to be rendered.
	 *
	 * @return array
	 */
	public function update_inline_script_data( $data, $for ) {

		if ( AssetManager::ADMIN_SETTINGS_ENTRY === $for ) {
			$data['uiData'] = array_merge(
				$data['uiData'],
				[
					'post_types'          => $this->get_post_type_options(),
					'macros'              => $this->get_macros(),
					'rule_types'          => Rules::get_rule_types(),
					'is_wp_cron_disabled' => defined( 'DISABLE_WP_CRON' ) && constant( 'DISABLE_WP_CRON' ),
				]
			);
		} elseif ( AssetManager::P2TG_BLOCK_EDITOR_ENTRY === $for ) {

			$blocks_fields  = [
				'channels',
				'disable_notification',
				'files',
				'delay',
				'message_template',
				'send_featured_image',
			];
			$saved_settings = [ 'send2tg' => self::send2tg_default() === 'yes' ];

			foreach ( $blocks_fields as $field ) {
				$saved_settings[ $field ] = $this->get_field_default( $field );
			}

			$data['savedSettings'] = $saved_settings;

			$data['uiData'] = [
				// savedSettings may not contain all the channels, so add them here.
				'allChannels' => $this->module->options()->get( 'channels' ),
			];
		}

		return $data;
	}

	/**
	 * Get the registered post types.
	 *
	 * @since  3.0.0
	 * @return array
	 */
	public function get_macros() {
		$to_skip = [
			'product_shipping_class',
		];

		$taxonomies = get_taxonomies(
			[
				'public'   => true,
				'_builtin' => false,
			],
			'names'
		);

		$term_macros = [
			'{tags}',
			'{categories}',
			'{terms:taxonomy}',
		];

		foreach ( $taxonomies as $taxonomy ) {
			if ( in_array( $taxonomy, $to_skip, true ) ) {
				continue;
			}
			$term_macros[] = "{terms:{$taxonomy}}";
		}

		$macro_groups                  = [
			'post'  => [
				'label'  => __( 'Post Data', 'wptelegram' ),
				'macros' => [
					'{ID}',
					'{post_title}',
					'{post_slug}',
					'{post_date}',
					'{post_date_gmt}',
					'{post_author}',
					'{post_excerpt}',
					'{post_content}',
					'{post_type}',
					'{post_type_label}',
					'{short_url}',
					'{full_url}',
				],
			],
			'terms' => [
				'label'  => __( 'Taxonomy Terms', 'wptelegram' ),
				'macros' => $term_macros,
			],
			'cf'    => [
				'label'  => __( 'Custom Fields', 'wptelegram' ),
				'macros' => [
					'{cf:custom_field}',
				],
			],
		];
		$macro_groups['terms']['info'] = sprintf(
			/* translators: 1  taxonomy, 2  {terms:taxonomy} */
			__( 'Replace %1$s in %2$s by the name of the taxonomy to insert its terms attached to the post.', 'wptelegram' ),
			'<code>taxonomy</code>',
			'<code>{terms:taxonomy}</code>'
		) . ' ' . sprintf(
			/* translators: 1  code, 2  code */
			__( 'For example %1$s and %2$s in WooCommerce', 'wptelegram' ),
			'<code>{terms:product_cat}</code>',
			'<code>{terms:product_tag}</code>'
		);

			$macro_groups['cf']['info'] = sprintf(
			/* translators: 1  custom_field, 2  {cf:custom_field} */
				__( 'Replace %1$s in %2$s by the name of the Custom Field.', 'wptelegram' ),
				'<code>custom_field</code>',
				'<code>{cf:custom_field}</code>'
			) . ' ' . sprintf(
				/* translators: 1  code, 2  code */
				__( 'For example %1$s and %2$s in WooCommerce', 'wptelegram' ),
				'<code>{cf:_regular_price}</code>',
				'<code>{cf:_sale_price}</code>'
			);

		/**
		 * If you add your own macro_groups using this filter
		 * You should use "wptelegram_p2tg_macro_values" filter
		 * to replace the macro with the corresponding values
		 */
		return (array) apply_filters( 'wptelegram_p2tg_settings_macro_groups', $macro_groups );
	}

	/**
	 * Whether to show th epost edit switch or not.
	 *
	 * @since  3.0.10
	 * @return boolean
	 */
	public static function show_post_edit_switch() {

		$bot_token = WPTG()->options()->get( 'bot_token' );

		$show_post_edit_switch = ! empty( $bot_token );

		if ( $show_post_edit_switch ) {
			$show_post_edit_switch = WPTG()->options()->get_path( 'p2tg.post_edit_switch', true );
		}

		return (bool) apply_filters( 'wptelegram_p2tg_show_post_edit_switch', $show_post_edit_switch );
	}

	/**
	 * Get the registered post types.
	 *
	 * @since  3.0.0
	 * @return array
	 */
	public function get_post_type_options() {

		$options = [];

		$post_types = get_post_types( [ 'public' => true ], 'objects' );

		foreach ( $post_types  as $post_type ) {
			if ( 'attachment' !== $post_type->name ) {
				$options[] = [
					'value' => $post_type->name,
					'label' => "{$post_type->labels->singular_name} ({$post_type->name})",
				];
			}
		}

		return apply_filters( 'wptelegram_p2tg_post_type_options', $options, $post_types );
	}

	/**
	 * Create a hidden field on the post edit page
	 * to use it for checking the requests from web
	 * in save_post callback
	 *
	 * @since    1.0.0
	 */
	public function post_edit_form_hidden_input() {
		// phpcs:ignore WordPress.Security.EscapeOutput
		echo '<input type="hidden" id="' . esc_attr( Main::PREFIX . 'from_web' ) . '" name="' . esc_attr( Main::PREFIX . 'from_web' ) . '" value="yes" />';
	}

	/**
	 * Create a hidden field into block editor metabox section.
	 *
	 * @since 3.0.0
	 */
	public function block_editor_hidden_fields() {
		// phpcs:ignore WordPress.Security.EscapeOutput
		echo '<input type="hidden" id="' . Main::PREFIX . 'is_gb_metabox" name="' . Main::PREFIX . 'is_gb_metabox" value="yes" />';
	}

	/**
	 * Add send to Telegram switch to post edit page
	 * when using classic editor.
	 *
	 * @since 1.0.0
	 */
	public function add_post_edit_switch() {

		if ( ! self::show_post_edit_switch() ) {
			return;
		}

		$screens = $this->get_override_meta_box_screens();
		if ( ! MainUtils::is_post_edit_page( $screens ) ) {
			return;
		}

		$is_cmb2_active = function_exists( 'cmb2_get_metabox' );
		?>
			<div class="misc-pub-section">
					<?php $this->render_post_edit_switch( $is_cmb2_active ); ?>
			</div>
		<?php
	}

	/**
	 * Renders the HTML for post edit switch.
	 *
	 * @since  1.0.0
	 *
	 * @param boolean $display_gear Whether to display link to override metabox.
	 * @return void
	 */
	public function render_post_edit_switch( $display_gear = false ) {
		?>
			<div class="wptg-p2tg-post-edit-switch">
				<input type="hidden" name="<?php echo esc_attr( Main::PREFIX . 'send2tg' ); ?>" value="no" />
				<input type="checkbox" id="<?php echo esc_attr( Main::PREFIX . 'send2tg' ); ?>" name="<?php echo esc_attr( Main::PREFIX . 'send2tg' ); ?>" value="yes" <?php checked( self::send2tg_default(), 'yes' ); ?> />
				<label for="<?php echo esc_attr( Main::PREFIX . 'send2tg' ); ?>">
					<span style="padding-left:4px;font-weight:600;"><?php esc_html_e( 'Send to Telegram', 'wptelegram' ); ?></span>
				</label>
					<?php if ( $display_gear ) : ?>
				&nbsp;<a style="text-decoration: none;" href="#<?php echo esc_attr( self::OVERRIDE_METABOX_ID ); ?>"><span class="dashicons dashicons-admin-generic"></span></a>
					<?php endif; ?>
			</div>
		<?php
			MainUtils::nonce_field();
	}

	/**
	 * Override metabox.
	 *
	 * @since    3.0.0
	 */
	public function may_be_remove_override_metabox() {
		if ( did_action( 'enqueue_block_editor_assets' ) ) {
			// Lets remove the override metabox for block editor.
			global $post, $wp_meta_boxes;
			// Remove the metabox.
			unset( $wp_meta_boxes[ $post->post_type ]['normal']['high'][ self::OVERRIDE_METABOX_ID ] );
		}
	}

	/**
	 * Override metabox
	 *
	 * @since    1.0.0
	 */
	public function create_cmb2_override_metabox() {

		if ( ! self::show_post_edit_switch() ) {
			return;
		}

		$screens = $this->get_override_meta_box_screens();

		/**
		 * Initiate the metabox
		 */
		$box = [
			'id'           => self::OVERRIDE_METABOX_ID,
			'title'        => sprintf( '%s (%s)', __( 'Post to Telegram', 'wptelegram' ), WPTG()->title() ),
			'object_types' => $screens,
			'context'      => 'normal',
			'priority'     => 'high',
			'save_fields'  => false,
			'classes'      => 'wptelegram-box',
		];

		$cmb2 = new_cmb2_box( $box );

		$cmb2->add_field(
			[
				'name' => __( 'Override settings', 'wptelegram' ),
				'id'   => Main::PREFIX . 'override_switch',
				'type' => 'checkbox',
			]
		);

		$channels = $this->module->options()->get( 'channels', [] );

		$cmb2->add_field(
			[
				'name'              => __( 'Send to', 'wptelegram' ),
				'id'                => Main::PREFIX . 'channels',
				'type'              => 'multicheck',
				'select_all_button' => false,
				'default_cb'        => [ $this, 'override_opt_default_cb' ],
				'options'           => array_combine( $channels, $channels ),
				'classes'           => 'hidden depends-upon-override_switch',
			]
		);

		$cmb2->add_field(
			[
				'name'       => __( 'Disable Notifications', 'wptelegram' ),
				'id'         => Main::PREFIX . 'disable_notification',
				'type'       => 'checkbox',
				'default_cb' => [ $this, 'override_opt_default_cb' ],
				'classes'    => 'hidden depends-upon-override_switch',
			]
		);

		$cmb2->add_field(
			[
				'name'       => __( 'Files', 'wptelegram' ),
				'desc'       => __( 'Files to be sent after the message.', 'wptelegram' ),
				'id'         => Main::PREFIX . 'files',
				'type'       => 'file_list',
				'default_cb' => [ $this, 'override_opt_default_cb' ],
				'classes'    => 'hidden depends-upon-override_switch',
			]
		);

		$cmb2->add_field(
			[
				'name'       => __( 'Delay in Posting', 'wptelegram' ),
				'desc'       => __( 'Minute(s)', 'wptelegram' ),
				'default_cb' => [ $this, 'override_opt_default_cb' ],
				'id'         => Main::PREFIX . 'delay',
				'type'       => 'text_small',
				'classes'    => 'hidden depends-upon-override_switch',
				'attributes' => [
					'min'         => 0,
					'placeholder' => '0.0',
					'step'        => 'any',
					'type'        => 'number',
				],
			]
		);

		$cmb2->add_field(
			[
				'name'       => __( 'Featured Image', 'wptelegram' ),
				'desc'       => __( 'Send Featured Image (if exists).', 'wptelegram' ),
				'id'         => Main::PREFIX . 'send_featured_image',
				'type'       => 'checkbox',
				'default_cb' => [ $this, 'override_opt_default_cb' ],
				'classes'    => 'hidden depends-upon-override_switch',
				'before'     => '<input type="hidden" name="' . Main::PREFIX . 'send_featured_image" value="off" />',
			]
		);

		$cmb2->add_field(
			[
				'name'       => __( 'Message Template', 'wptelegram' ),
				'desc'       => __( 'Structure of the message to be sent.', 'wptelegram' ),
				'id'         => Main::PREFIX . 'message_template',
				'type'       => 'textarea',
				'default_cb' => [ $this, 'override_opt_default_cb' ],
				'escape_cb'  => [ __CLASS__, 'escape_message_template' ],
				'classes'    => 'hidden depends-upon-override_switch',
			]
		);
	}

	/**
	 * Handles escaping for message template
	 *
	 * @param  mixed       $value      The unescaped value from the database.
	 * @param  array       $field_args Array of field arguments.
	 * @param  \CMB2_Field $field      The field object.
	 *
	 * @return mixed                  Escaped value to be displayed.
	 */
	public static function escape_message_template( $value, $field_args, $field ) {

		$value = $field->val_or_default( $value );

		return esc_textarea( $value );
	}

	/**
	 * Set default value for override options
	 *
	 * @param  object $field_args Current field args.
	 * @param  object $field      Current field object.
	 *
	 * @since  2.0.9
	 */
	public function override_opt_default_cb( $field_args, $field ) {

		// remove prefix.
		$id = str_replace( Main::PREFIX, '', $field->args( 'id' ) );

		return $this->get_field_default( $id );
	}

	/**
	 * Get default value for a field
	 *
	 * @param string $field The field name.
	 *
	 * @since  3.0.0
	 */
	public function get_field_default( $field ) {

		$saved_options = self::get_saved_options();

		switch ( $field ) {
			case 'channels':
			case 'files':
				return $saved_options->get( $field, [] );
			case 'disable_notification':
			case 'delay':
			case 'message_template':
			case 'send_featured_image':
				return $saved_options->get( $field );
		}
	}

	/**
	 * Get the latest saved options.
	 *
	 * @since  3.0.0
	 *
	 * @return Options
	 */
	public static function get_saved_options() {
		// if not previously set.
		if ( is_null( self::$saved_options ) ) {
			self::$saved_options = new Options();
			self::$saved_options->set_data( WPTG()->options()->get( 'p2tg' ) );

			$post_id = filter_input( INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT );

			if ( ! empty( $post_id ) ) {
				// try to get the options from meta.
				$options = get_post_meta( $post_id, Main::PREFIX . 'options', true );

				// If the meta was saved before upgrade.
				if ( is_array( $options ) ) {
					$options = wp_json_encode( $options );
				}

				if ( ! empty( $options ) ) {

					self::$saved_options->set_data( json_decode( $options, true ) );
				}
			}
		}
		return self::$saved_options;
	}

	/**
	 * Set default value for send2tg
	 *
	 * @since  1.0.0
	 */
	public static function send2tg_default() {

		$send_when     = WPTG()->options()->get_path( 'p2tg.send_when', [] );
		$send_new      = in_array( 'new', $send_when, true );
		$send_existing = in_array( 'existing', $send_when, true );

		$default = 'yes';

		$post_id = filter_input( INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT );

		// if we are on edit page.
		if ( ! empty( $post_id ) ) {

			// if saved in meta e.g. for future or draft.
			$send2tg = get_post_meta( $post_id, Main::PREFIX . 'send2tg', true );
			$post    = get_post( $post_id );

			if ( $send2tg && in_array( $send2tg, [ 'yes', 'no' ], true ) ) {

				$default = $send2tg;
			} elseif ( $post instanceof WP_Post ) {

				$is_new = Utils::is_post_new( $post );

				$is_new_and_dont_send_new           = $is_new && ! $send_new;
				$is_existing_and_dont_send_existing = ! $is_new && ! $send_existing;

				if ( $is_new_and_dont_send_new || $is_existing_and_dont_send_existing ) {
					$default = 'no';
				}
			}
		} elseif ( ! $send_new ) {
			$default = 'no';
		}

		return (string) apply_filters( 'wptelegram_p2tg_send2tg_default', $default, $send_when, $post_id );
	}

	/**
	 * Get registered post type names.
	 *
	 * @since  1.0.0
	 * @return array
	 */
	public function get_override_meta_box_screens() {

		$screens = $this->module->options()->get( 'post_types', [] );

		return (array) apply_filters( 'wptelegram_p2tg_override_meta_box_screens', $screens );
	}
}
