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

namespace WPTelegram\Core\modules\p2tg;

use WPTelegram\Core\modules\BaseClass;
use WPTelegram\Core\includes\Utils;
use WPTelegram\Core\includes\Options;
use WPTelegram\Core\modules\p2tg\restApi\RulesController;
use WP_Post;

/**
 * The admin-specific functionality of the module.
 *
 * @package    WPTelegram
 * @subpackage WPTelegram/module
 * @author     Manzoor Wani <@manzoorwanijk>
 */
class Admin extends BaseClass {

	const OVERRIDE_METABOX_ID = 'wptelegram_p2tg_override';

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
	public function update_dom_data( $data, $for ) {

		if ( 'SETTINGS_PAGE' === $for ) {
			$data['uiData'] = array_merge(
				$data['uiData'],
				array(
					'post_types'          => $this->get_post_type_options(),
					'macros'              => $this->get_macros(),
					'rule_types'          => Rules::get_rule_types(),
					'is_wp_cron_disabled' => defined( 'DISABLE_WP_CRON' ) && constant( 'DISABLE_WP_CRON' ),
				)
			);
		} elseif ( 'BLOCKS' === $for ) {
			$blocks_fields  = array(
				'channels',
				'disable_notification',
				'files',
				'delay',
				'message_template',
			);
			$saved_settings = array( 'send2tg' => self::send2tg_default() === 'yes' );

			foreach ( $blocks_fields as $field ) {
				$saved_settings[ $field ] = $this->get_field_default( $field );
			}

			$data['savedSettings'] = $saved_settings;

			$data['uiData'] = array(
				'allChannels' => $this->module->options()->get( 'channels' ),
			);
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
		$to_skip = array(
			'product_shipping_class',
		);

		$taxonomies = get_taxonomies(
			array(
				'public'   => true,
				'_builtin' => false,
			),
			'names'
		);

		$term_macros = array(
			'{tags}',
			'{categories}',
			'{terms:taxonomy}',
		);

		foreach ( $taxonomies as $taxonomy ) {
			if ( in_array( $taxonomy, $to_skip, true ) ) {
				continue;
			}
			$term_macros[] = "{terms:{$taxonomy}}";
		}

		$macro_groups = array(
			'post'  => array(
				'label'  => __( 'Post Data', 'wptelegram' ),
				'macros' => array(
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
				'label'  => __( 'Taxonomy Terms', 'wptelegram' ),
				'macros' => $term_macros,
			),
			'cf'    => array(
				'label'  => __( 'Custom Fields', 'wptelegram' ),
				'macros' => array(
					'{cf:custom_field}',
				),
			),
		);
		/* translators: 1  taxonomy, 2  {terms:taxonomy} */
		$macro_groups['terms']['info'] = sprintf( __( 'Replace %1$s in %2$s by the name of the taxonomy to insert its terms attached to the post.', 'wptelegram' ), '<code>taxonomy</code>', '<code>{terms:taxonomy}</code>' ) . ' ' . sprintf( __( 'For example %1$s and %2$s in WooCommerce', 'wptelegram' ), '<code>{terms:product_cat}</code>', '<code>{terms:product_tag}</code>' );

		/* translators: 1  custom_field, 2  {cf:custom_field} */
		$macro_groups['cf']['info'] = sprintf( __( 'Replace %1$s in %2$s by the name of the Custom Field.', 'wptelegram' ), '<code>custom_field</code>', '<code>{cf:custom_field}</code>' ) . ' ' . sprintf( __( 'For example %1$s and %2$s in WooCommerce', 'wptelegram' ), '<code>{cf:_regular_price}</code>', '<code>{cf:_sale_price}</code>' );

		/**
		 * If you add your own macro_groups using this filter
		 * You should use "wptelegram_p2tg_macro_values" filter
		 * to replace the macro with the corresponding values
		 */
		return (array) apply_filters( 'wptelegram_p2tg_settings_macro_groups', $macro_groups );
	}

	/**
	 * Get the registered post types.
	 *
	 * @since  3.0.0
	 * @return array
	 */
	public function get_post_type_options() {

		$options = array();

		$post_types = get_post_types( array( 'public' => true ), 'objects' );

		foreach ( $post_types  as $post_type ) {
			if ( 'attachment' !== $post_type->name ) {
				$options[] = array(
					'value' => $post_type->name,
					'label' => "{$post_type->labels->singular_name} ({$post_type->name})",
				);
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
		echo '<input type="hidden" id="' . self::$prefix . 'from_web" name="' . self::$prefix . 'from_web" value="yes" />';
	}

	/**
	 * Create a hidden field into block editor metabox section.
	 *
	 * @since 3.0.0
	 */
	public function block_editor_hidden_fields() {
		// phpcs:ignore WordPress.Security.EscapeOutput
		echo '<input type="hidden" id="' . self::$prefix . 'is_gb_metabox" name="' . self::$prefix . 'is_gb_metabox" value="yes" />';
	}

	/**
	 * Add send to Telegram swicth to post edit page
	 * when using classic editor.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post $post Current post.
	 */
	public function add_post_edit_switch( $post ) {

		$bot_token = WPTG()->options()->get( 'bot_token' );

		if ( ! $bot_token ) {
			return;
		}

		$post_edit_switch = $this->module->options()->get( 'post_edit_switch', true );

		if ( ! $post_edit_switch ) {
			return;
		}

		$screens = $this->get_override_meta_box_screens();
		if ( ! in_array( $post->post_type, $screens, true ) ) {
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
	 * @param boolean $display_gear Whehter to display link to override metabox.
	 * @return void
	 */
	public function render_post_edit_switch( $display_gear = false ) {
		?>
			<div class="wptg-p2tg-post-edit-switch">
				<input type="hidden" name="<?php echo esc_attr( self::$prefix . 'send2tg' ); ?>" value="no" />
				<input type="checkbox" id="<?php echo esc_attr( self::$prefix . 'send2tg' ); ?>" name="<?php echo esc_attr( self::$prefix . 'send2tg' ); ?>" value="yes" <?php checked( self::send2tg_default(), 'yes' ); ?> />
				<label for="<?php echo esc_attr( self::$prefix . 'send2tg' ); ?>">
					<span style="padding-left:4px;font-weight:600;"><?php esc_html_e( 'Send to Telegram', 'wptelegram' ); ?></span>
				</label>
					<?php if ( $display_gear ) : ?>
				&nbsp;<a style="text-decoration: none;" href="#wptelegram_p2tg_override"><span class="dashicons dashicons-admin-generic"></span></a>
					<?php endif; ?>
			</div>
		<?php
			Utils::nonce_field();
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

		$bot_token = WPTG()->options()->get( 'bot_token' );

		if ( ! $bot_token ) {
			return;
		}

		$post_edit_switch = $this->module->options()->get( 'post_edit_switch', true );

		if ( ! $post_edit_switch ) {
			return;
		}

		$screens = $this->get_override_meta_box_screens();

		/**
		 * Initiate the metabox
		 */
		$box = array(
			'id'           => self::OVERRIDE_METABOX_ID,
			'title'        => sprintf( '%s (%s)', __( 'Post to Telegram', 'wptelegram' ), WPTG()->title() ),
			'object_types' => $screens,
			'context'      => 'normal',
			'priority'     => 'high',
			'save_fields'  => false,
			'classes'      => 'wptelegram-box',
		);

		$cmb2 = new_cmb2_box( $box );

		$cmb2->add_field(
			array(
				'name' => __( 'Override settings', 'wptelegram' ),
				'id'   => self::$prefix . 'override_switch',
				'type' => 'checkbox',
			)
		);

		$channels = $this->module->options()->get( 'channels', array() );

		$cmb2->add_field(
			array(
				'name'              => __( 'Send to', 'wptelegram' ),
				'id'                => self::$prefix . 'channels',
				'type'              => 'multicheck',
				'select_all_button' => false,
				'default_cb'        => array( $this, 'override_opt_default_cb' ),
				'options'           => array_combine( $channels, $channels ),
				'classes'           => 'hidden depends-upon-override_switch',
			)
		);

		$cmb2->add_field(
			array(
				'name'       => __( 'Disable Notifications', 'wptelegram' ),
				'id'         => self::$prefix . 'disable_notification',
				'type'       => 'checkbox',
				'default_cb' => array( $this, 'override_opt_default_cb' ),
				'classes'    => 'hidden depends-upon-override_switch',
			)
		);

		$cmb2->add_field(
			array(
				'name'       => __( 'Files', 'wptelegram' ),
				'desc'       => __( 'Files to be sent after the message.', 'wptelegram' ),
				'id'         => self::$prefix . 'files',
				'type'       => 'file_list',
				'default_cb' => array( $this, 'override_opt_default_cb' ),
				'classes'    => 'hidden depends-upon-override_switch',
			)
		);

		$cmb2->add_field(
			array(
				'name'       => __( 'Delay in Posting', 'wptelegram' ),
				'desc'       => __( 'Minute(s)', 'wptelegram' ),
				'default_cb' => array( $this, 'override_opt_default_cb' ),
				'id'         => self::$prefix . 'delay',
				'type'       => 'text_small',
				'classes'    => 'hidden depends-upon-override_switch',
				'attributes' => array(
					'type'        => 'number',
					'min'         => '0',
					'placeholder' => '0.0',
					'step'        => '0.5',
				),
			)
		);

		$cmb2->add_field(
			array(
				'name'       => __( 'Message Template', 'wptelegram' ),
				'desc'       => __( 'Structure of the message to be sent.', 'wptelegram' ),
				'id'         => self::$prefix . 'message_template',
				'type'       => 'textarea',
				'default_cb' => array( $this, 'override_opt_default_cb' ),
				'escape_cb'  => array( __CLASS__, 'escape_message_template' ),
				'classes'    => 'hidden depends-upon-override_switch',
				'attributes' => array(
					'data-emoji-container' => 'p2tg-template-container',
				),
			)
		);
	}

	/**
	 * Handles escaping for message template
	 *
	 * @param  mixed $value      The unescaped value from the database.
	 * @param  array $field_args Array of field arguments.
	 * @param  mixed $field      The field object.
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
		$id = str_replace( self::$prefix, '', $field->args( 'id' ) );

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
				return $saved_options->get( $field, array() );
			case 'disable_notification':
				return in_array( $field, $saved_options->get( 'misc', array() ), true );
			case 'delay':
			case 'message_template':
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

			// phpcs:ignore WordPress.Security.NonceVerification
			if ( isset( $_GET['post'] ) ) {
				// try to get the options from meta.
				// phpcs:ignore
				$_options = (string) get_post_meta( (int) $_GET['post'], self::$prefix . 'options', true );

				if ( ! empty( $_options ) ) {

					self::$saved_options->set_data( json_decode( $_options, true ) );
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

		$send_when = WPTG()->options()->get_path( 'p2tg.send_when', array() );

		$default = 'yes';

		// if we are on edit page.
		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_GET['post'] ) ) {

			// if saved in meta e.g. for future or draft.
			// phpcs:ignore
			if ( $send2tg = get_post_meta( $_GET['post'], self::$prefix . 'send2tg', true ) ) {

				$default = $send2tg;

				// if it's a published post.
				// phpcs:ignore
			} elseif ( ( $post = get_post( $_GET['post'] ) ) && $post instanceof WP_Post && 'publish' === $post->post_status ) {

				// whether already sent to Telegram.
				// phpcs:ignore
				$sent = get_post_meta( $_GET['post'], self::$prefix . 'sent2tg', true );

				if ( ! in_array( 'existing', $send_when, true ) || ! empty( $sent ) ) {
					$default = 'no';
				}
			}
		} elseif ( ! in_array( 'new', $send_when, true ) ) {
			$default = 'no';
		}

		return (string) apply_filters( 'wptelegram_p2tg_send2tg_default', $default, $send_when );
	}

	/**
	 * Get registered post type names.
	 *
	 * @since  1.0.0
	 * @return array
	 */
	public function get_override_meta_box_screens() {

		$screens = $this->module->options()->get( 'post_types', array() );

		return (array) apply_filters( 'wptelegram_p2tg_override_meta_box_screens', $screens );
	}
}
