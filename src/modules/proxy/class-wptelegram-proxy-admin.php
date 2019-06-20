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
class WPTelegram_Proxy_Admin extends WPTelegram_Module_Base {

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
			'desc'			=> __( 'The module will help you bypass the ban on Telegram by making use of proxy.', 'wptelegram' ),
		);
		$cmb2 = new_cmb2_box( $box );

		$cmb2->add_field( array(
			'name' 		=> __( 'DISCLAIMER!','wptelegram' ),
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
			'name'  => __( 'Proxy Method', 'wptelegram' ),
			'type'  => 'title',
			'id'    => 'proxy_method_title',
		) );

		$cmb2->add_field( array(
			'show_names'	=> false,
			'id'			=> 'proxy_method',
			'type'			=> 'radio_inline',
			'default'		=> 'google_script',
			'desc'			=> __( 'Google Script is preferred', 'wptelegram' ),
			'options'		=> array(
				'google_script'	=> __( 'Google Script', 'wptelegram' ),
				'php_proxy'		=> __( 'PHP Proxy', 'wptelegram' ),
			),
		) );

		$cmb2->add_field( array(
			'name'  => __( 'Google Script', 'wptelegram' ),
			'type'  => 'title',
			'id'    => 'google_script_title',
			'classes'       => 'google-script',
		) );

		$cmb2->add_field( array(
			'name'          => __( 'Google Script URL', 'wptelegram' ),
			'id'            => 'script_url',
			'desc'          => __( 'The requests to Telegram will be sent via your Google Script.', 'wptelegram' ) . ' <a href="https://gist.github.com/manzoorwanijk/ee9ed032caedf2bb0c83dea73bc9a28e#how-to-deploy" target="_blank">' . __( 'See this tutorial', 'wptelegram' ) . '</a>',
			'type'          => 'text',
			'classes'       => 'google-script',
		) );

		$cmb2->add_field( array(
			'name'  => __( 'PHP Proxy', 'wptelegram' ),
			'type'  => 'title',
			'id'    => 'php_proxy_title',
			'classes'       => 'php-proxy',
		) );

		$cmb2->add_field( array(
			'name'	=> __( 'Proxy Host', 'wptelegram' ),
			'id'	=> 'proxy_host',
			'type'	=> 'text_medium',
			'desc'	=> sprintf( __( 'Host IP or domain name like %s', 'wptelegram' ), '<b>192.168.84.101</b>' ),
			'classes'       => 'php-proxy',
		) );

		$cmb2->add_field( array(
			'name'	=> __( 'Proxy Port', 'wptelegram' ),
			'id'	=> 'proxy_port',
			'type'	=> 'text_medium',
			'desc'	=> sprintf( __( 'Target Port like %s', 'wptelegram' ), '<b>8080</b>' ),
			'classes'       => 'php-proxy',
		) );

		$cmb2->add_field( array(
			'name'      => __( 'Proxy Type', 'wptelegram' ),
			'id'        => 'proxy_type',
			'type'      => 'radio',
			'default'   => 'CURLPROXY_HTTP',
			'options'   => array(
				'CURLPROXY_HTTP'            => 'HTTP',
				'CURLPROXY_SOCKS4'          => 'SOCKS4',
				'CURLPROXY_SOCKS4A'         => 'SOCKS4A',
				'CURLPROXY_SOCKS5'          => 'SOCKS5',
				'CURLPROXY_SOCKS5_HOSTNAME' => 'SOCKS5_HOSTNAME',
			),
			'classes'       => 'php-proxy',
		) );

		$cmb2->add_field( array(
			'name'	=> __( 'Username', 'wptelegram' ),
			'id'	=> 'proxy_username',
			'type'	=> 'text_medium',
			'desc'	=> __( 'Leave empty if not required', 'wptelegram' ),
			'classes'       => 'php-proxy',
		) );

		$cmb2->add_field( array(
			'name'	=> __( 'Password', 'wptelegram' ),
			'id'	=> 'proxy_password',
			'type'	=> 'text_medium',
			'desc'	=> __( 'Leave empty if not required', 'wptelegram' ),
			'attributes'	=> array(
				'type'	=> 'password',
			),
			'classes'       => 'php-proxy',
		) );
	}

	/**
	 * Output the Telegram Instructions
	 * @param  object $field_args Current field args
	 * @param  object $field      Current field object
	 */
	public static function render_telegram_guide( $field_args, $field ) { ?>
		<div class="cmb-row cmb-type-text cmb2-id-telegram_guide">
			<span><?php _e( 'Use the proxy at your own risk!', 'wptelegram' ); ?></span>
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
	public function sanitize_catch_emails( $value, $field_args, $field ) {

		$value = WPTG()->utils->sanitize( $value );

		$clean_value = array();

		foreach ( $value as $group ) {

			if ( empty( $group['emails'] ) || empty( $group['chat_ids'] ) ) {
				continue;
			}

			// break into pieces
			$group['emails'] = array_map( 'trim', explode( ',', $group['emails'] ) );

			foreach ( $group['emails'] as $key => $email ) {
				if ( ! is_email( $email ) ) {
					unset( $group['emails'][ $key ] );
				}
			}

			// join them again
			$group['emails'] = implode( ',', $group['emails'] );

			if ( ! empty( $group['emails'] ) ) {
				
				$group['chat_ids'] = implode( ',', array_map( 'trim', explode( ',', $group['chat_ids'] ) ) );

				$clean_value[] = $group;
			} 
		}
		
		return $clean_value;
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
			'{hashtag}',
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
	 * Add admin sidebar content 
	 *
	 * @since  2.0.13
	 */
	public function add_sidebar_row( $object_id, $hookup ) {
		if ( 'wptelegram_proxy' === $object_id ) : ?>
			<div class="cell">
				<iframe src="https://www.youtube.com/embed/B4pCZNW8qrw" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
			</div>
		<?php endif;
	}
}
