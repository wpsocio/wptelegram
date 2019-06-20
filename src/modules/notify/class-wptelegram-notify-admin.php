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
class WPTelegram_Notify_Admin extends WPTelegram_Module_Base {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since	1.0.0
	 * @param	string    $module_name	The name of the module.
	 */
	public function __construct( $module_name, $module_title ) {

		parent::__construct( $module_name, $module_title );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts( $hook_suffix ) {

		parent::enqueue_script( $this->module_name, $this->slug, 'js', array( 'jquery', $this->plugin_name ) );
	}

	/**
	 * Build Options page
	 *
	 * @since    1.0.0
	 */
	public function create_options_page() {

		$bot_token = WPTG()->options()->get( 'bot_token' );

		$box = array(
			'id'            => $this->plugin_name . '_' . $this->module_name,
			'parent_slug'   => $this->plugin_name,
			'title'         => $this->plugin_title . ' &rsaquo; ' . $this->module_title,
			'menu_title'    => $this->module_title,
			'object_types'	=> array( 'options-page' ),
			'option_key'	=> $this->plugin_name . '_' . $this->module_name,
			'icon_url'		=> WPTELEGRAM_URL . '/admin/icons/icon-16x16-white.svg',
			'capability'	=> 'manage_options',
			'classes'		=> 'wptelegram-box',
			'display_cb'	=> array( WPTG()->helpers, 'render_cmb2_options_page' ),
			'desc'			=> __( 'The module will watch the Email Notifications sent from this site and deliver them on your Telegram', 'wptelegram' ),
		);
		$cmb2 = new_cmb2_box( $box );

		$cmb2->add_field( array(
			'name' 		=> __( 'INSTRUCTIONS!','wptelegram' ),
			'type' 		=> 'title',
			'id'   		=> 'instructions_title',
			'classes'	=> 'highlight',
		) );

		// Instructions
		$cmb2->add_field( array(
			'name'			=> '',
			'type'			=> 'text', // fake
			'show_names'	=> false,
			'save_field'	=> false,
			'id'			=> 'telegram_guide',
			'render_row_cb'	=> array( __CLASS__, 'render_telegram_guide' ),
		) );

		$cmb2->add_field( array(
			'name'  => __( 'Notification Settings', 'wptelegram' ),
			'type'  => 'title',
			'id'    => 'notification_settings_title',
		) );

		$cmb2->add_field( array(
			'name'          => __( 'If Email goes to', 'wptelegram' ),
			'id'            => 'watch_emails',
			'desc'          => sprintf( __( 'If you want to receive notification for every email, then write %s', 'wptelegram' ), '<code>any</code>' ),
			'type'          => 'text',
			'default'       => get_option( 'admin_email' ),
			'before_row'    => WPTG()->helpers->open_grid_row_with_col(),
			'after_row'     => WPTG()->helpers->close_grid_col(),
		) );

		$cmb2->add_field( array(
			'name'              => __( 'Send it to', 'wptelegram' ),
			'desc'              => WPTG()->helpers->get_test_button_html( __( 'Send Test', 'wptelegram' ), '', 'chat_ids' ) . '<br>' . __( 'Telegram User or Group Chat ID.', 'wptelegram' ),
			'id'                => 'chat_ids',
			'type'              => 'text_medium',
			'sanitization_cb'   => array( WPTG()->helpers, 'sanitize_channels' ),
			'after'             => array( $this, 'after_chat_cb' ),
			'before_row'        => WPTG()->helpers->add_grid_col_to_row(),
			'after_row'         => WPTG()->helpers->close_grid_col_and_row(),
			'classes'			=> 'no-bottom-border chat_ids',
			'attributes'        => array(
				'placeholder'   => '-1234567,98765432',
			),
		) );

		$cmb2->add_field( array(
			'name'	=> __( 'Notifications to Users', 'wptelegram' ),
			'desc'	=> __( 'Allow users to receive their email notifications on Telegram', 'wptelegram' ),
			'after'	=> sprintf( __( 'Use %s to let them connect their Telegram account.', 'wptelegram' ), '<a href="' . esc_attr( 'https://wordpress.org/plugins/wptelegram-login' ) . '" target="_blank">WP Telegram Login & Register</a>' ) . '<br>' . sprintf( __( 'They can also enter their Telegram Chat ID manually on %s page', 'wptelegram' ), sprintf( '<a href="' . esc_url( get_edit_profile_url( get_current_user_id() ) . '#into-title' ) . '" target="_blank">%s</a>', __( 'profile', 'wptelegram' ) ) ),
			'id'	=> 'user_notifications',
			'type'	=> 'switch',
			'sanitization_cb'	=> array( $this, 'sanitize_checkbox' ),
		) );

		$cmb2->add_field( array(
			'name'  => __( 'Message Settings', 'wptelegram' ),
			'type'  => 'title',
			'id'    => 'message_settings_title',
		) );

		$cmb2->add_field( array(
			'name'              => __( 'Message Template', 'wptelegram' ),
			'desc'              => __( 'Structure of the message to be sent', 'wptelegram' ),
			'id'                => 'message_template',
			'type'              => 'textarea',
			'default'           => json_encode( '🔔‌<b>{email_subject}</b>🔔' . PHP_EOL . PHP_EOL . '{email_message}' ),
			'sanitization_cb'   => array( $this, 'sanitize_message_template' ),
			'escape_cb'         => array( WPTG()->helpers, 'escape_message_template' ),
			'after'             => array( $this, 'message_template_after_cb' ),
			'classes'           => 'emojionearea-enabled',
			'attributes'        => array(
				'data-emoji-container'   => 'notify-template-container',
			),
		) );

		$cmb2->add_field( array(
			'name'      => __( 'Parse Mode', 'wptelegram' ),
			'id'        => 'parse_mode',
			'type'      => 'radio',
			'default'   => 'HTML',
			'after'     => '<a href="'. esc_url( 'https://core.telegram.org/bots/api/#formatting-options' ) . '" target="_blank">' . __( 'Learn more', 'wptelegram' ) . '</a>',
			'options'   => array(
				'none'      => __( 'None', 'wptelegram' ),
				'Markdown'  => __( 'Markdown style', 'wptelegram' ),
				'HTML'      => __( 'HTML style', 'wptelegram' ),
			),
		) );
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
	 * Output the Telegram Instructions
	 * @param  object $field_args Current field args
	 * @param  object $field      Current field object
	 */
	public static function render_telegram_guide( $field_args, $field ) { ?>
		<div class="cmb-row cmb-type-text cmb2-id-telegram_guide">
			<ol style="list-style-type: disc">
				<li><?php _e( 'To receive notifications privately:', 'wptelegram' ); ?>
					<ol>
						<li><?php printf( __( 'Get your Chat ID from %s and enter it below.', 'wptelegram' ), '<a href="https://t.me/MyChatInfoBot" target="_blank">@MyChatInfoBot</a>' ); ?></li>
						<li><span style="color:#f10e0e;"><?php esc_html_e( 'Send YOUR OWN BOT a message to start the conversation.', 'wptelegram' );?></span></li>
					</ol>
				</li>
				<li><?php _e( 'To receive notifications into a group:', 'wptelegram' ); ?>
					<ol>
						<li><?php printf( __( 'Add %s to the group to get its Chat ID.', 'wptelegram' ), '<b>@MyChatInfoBot</b>' ); ?></li>
						<li><?php printf( __( 'Enter the Chat ID in %s field below.', 'wptelegram' ), '<b>"' . __( 'Send it to', 'wptelegram' ) . '"</b>' ); ?></li>
						<li><?php esc_html_e( 'Add YOUR OWN BOT to the group.', 'wptelegram' );?></span></li>
					</ol>
				</li>
			</ol>
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
	public function after_chat_cb( $field_args, $field ) {
		?>
		<table class="notify-chat-table hidden">
			<tbody>
				<tr>
					<th><?php esc_html_e( 'Chat ID', 'wptelegram' ); ?></th>
					<th><?php esc_html_e( 'Name/Title', 'wptelegram' ); ?></th>
					<th><?php esc_html_e( 'Chat Type', 'wptelegram' ); ?></th>
					<th><?php esc_html_e( 'Test Status', 'wptelegram' ); ?></th>
				</tr>
			</tbody>
		</table>
		<?php
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
	 * get message template HTML
	 *
	 * @since  1.0.0
	 */
	public function message_template_after_cb() {
		?>
		<p class="wptelegram-macro" data-target="notify"><?php esc_html_e( 'You can use any text, emojis or these macros in any order:', 'wptelegram' ); ?> <b><i>(<?php esc_html_e( 'Click to insert', 'wptelegram' ); ?>)</i></b><?php echo $this->get_macros(); ?></p>
		<?php
	}

	/**
	 * get macros
	 *
	 * @since  1.0.0
	 */
	private function get_macros() {
		$macros = array(
			'{email_subject}',
			'{email_message}',
		);

		/**
		 * If you add your own macros using this filter
		 * You should use "wptelegram_macro_values" filter
		 * to replace the macro with the corresponding values
		 *
		 */
		$macros = (array) apply_filters( 'wptelegram_notify_settings_macros', $macros );

		$html = '';
		foreach ( $macros as $macro ) {
			$html .= '<a class="btn" href="#"><code>' . esc_html( $macro ) . '</code></a>';
		}
		return $html;
	}

	/**
	 * Add the profile fields
	 *
	 * @since    1.0.0
	 */
	public function add_user_profile_fields() {

		$bot_token = WPTG()->options()->get( 'bot_token' );

		if ( ! $bot_token ) {
			return;
		}

		$bot_username = WPTG()->options()->get( 'bot_username' );

		// if somehow the bot_username is not set
		if ( ! $bot_username ) {
			// get the bot username from the API
			$tg_api = new WPTelegram_Bot_API( $bot_token );
			$res = $tg_api->getMe();

			if ( $tg_api->is_success( $res ) ) {

				$bot_info = $res->get_result();
				$bot_username = $bot_info['username'];
				// update options
				WPTG()->options()->set( 'bot_username', $bot_username );
			}
		}

		$cmb2 = new_cmb2_box( array(
			'id'			=> $this->plugin_name . '_' . $this->module_name . '_user',
			'object_types'	=> array( 'user' ),
			'message_cb'	=> array( $this, 'custom_settings_messages' ),
		) );

		$cmb2->add_field( array(
			'name' => __( 'Telegram Info', 'wptelegram' ),
			'desc'	=> sprintf( __( 'You can receive your email notifications on Telegram from %s', 'wptelegram' ), '@' . $bot_username ),
			'type' => 'title',
			'id'   => 'into_title',
		) );

		$cmb2->add_field( array(
			'name'	=> __( 'Telegram Chat ID', 'wptelegram' ),
			'desc'	=> '<br>' . __( 'Please enter your Telegram Chat ID', 'wptelegram' ),
			'after'	=> array( $this, 'after_telegram_chat_id_cb' ),
			'id'                => 'telegram_chat_id',
			'type'              => 'text_medium',
			'display_cb'		=> array( $this, 'chat_id_column_display_cb' ),
			'sanitization_cb'	=> array( $this, 'sanitize_chat_id' ),
			'column'            => array(
				'position'  => 6,
			),
		) );
	}

	/**
	 * Output a message if WordPress cron is disabled
	 * @param  object $field_args Current field args
	 * @param  object $field      Current field object
	 */
	public function after_telegram_chat_id_cb( $field_args, $field ) {

		$bot_username = WPTG()->options()->get( 'bot_username' );
		?>
		<p style="color:#f10e0e;"><b><?php echo __( 'INSTRUCTIONS!','wptelegram' ); ?></b></p>
		<ul style="list-style-type: disc;">
			<li><?php printf( __( 'Get your Chat ID from %s and enter it above.', 'wptelegram' ), '<a href="https://t.me/MyChatInfoBot" target="_blank">@MyChatInfoBot</a>' ); ?></li>
			<li><?php printf( __( 'Start a conversation with %s to receive notifications.', 'wptelegram' ), '<a href="https://t.me/' . $bot_username . '"  target="_blank">@' . $bot_username . '</a>' ); ?></li>
		</ul>
		<?php
	}

	/**
	 * Manually render a field column display.
	 *
	 * @param  array      $field_args Array of field arguments.
	 * @param  CMB2_Field $field      The field object
	 */
	public function chat_id_column_display_cb( $field_args, $field ) {

		$value = $field->escaped_value();

		// if empty, get the value from Login plugin
		if ( empty( $value ) && defined( 'WPTELEGRAM_LOGIN_LOADED' ) && WPTELEGRAM_LOGIN_LOADED ) {

			$user_id = $field->object_id;

			$user = get_user_by( 'id', $user_id );

			if ( $user && $user instanceof WP_User ) {
				
				$value = $user->wptelegram_login_user_id;

				$value .= ' (' . sprintf( __( 'via %s', 'wptelegram' ), 'WP Telegram Login' ) . ')';
			}
		}
		?>
		<div class="chat-id-column-display <?php echo $field->row_classes(); ?>">
			<?php echo $value; ?>
		</div>
		<?php
	}

	/**
	 * Handles sanitization for the fields
	 *
	 * @param  mixed      $value      The unsanitized value from the form.
	 * @param  array      $field_args Array of field arguments.
	 * @param  CMB2_Field $field      The field object
	 *
	 * @return mixed                  Sanitized value to be stored.
	 */
	public function sanitize_chat_id( $value, $field_args, $field ) {
		
		$value = WPTG()->utils->sanitize( $value, true );

		if ( $value && preg_match( '/^\-?[^0\D]\d{6,51}$/', $value ) ) {

			return $value;
		}
	}

	/**
	 * Render the profile fields
	 *
	 * @since	1.6.0
	 * @param	WP_Error	$errors	WP_Error object (passed by reference)
	 * @param	bool		$update	Whether this is a user update.
	 * @param	stdClass	$user	User object (passed by reference).
	 */
	public function validate_user_profile_fields( &$errors, $update = null, &$user = null ) {

		if ( isset( $_POST['telegram_chat_id'] ) ) {

			$chat_id = WPTG()->utils->sanitize( $_POST['telegram_chat_id'], true );

			if ( $chat_id && ! preg_match( '/^\-?[^0\D]\d{6,51}$/', $chat_id ) ) {

				$errors->add( 'invalid_chat_id', __( 'Error:', 'wptelegram' ) . ' ' . __( 'Please Enter a valid Chat ID', 'wptelegram' ) );
			}
		}
	}
}
