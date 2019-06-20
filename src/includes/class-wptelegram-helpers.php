<?php

/**
 * WPTelegram Helper functions
 *
 * @link	   https://t.me/manzoorwanijk
 * @since	  1.0.0
 *
 * @package	WPTelegram
 * @subpackage WPTelegram/includes
 */
class WPTelegram_Helpers {

	/**
	 * The single instance of the class.
	 *
	 * @since 1.0.0
	 */
	protected static $_instance = null;

	/**
	 * Main class Instance.
	 *
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * 
	 * @return Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Retrieve the active modules array
	 *
	 * @since	1.0.0
	 */
	public function get_active_modules() {

		$_modules = WPTG()->options()->get( 'modules', array() );
		
		if ( ! empty( $_modules ) ) {
			$_modules = reset( $_modules );
			unset( $_modules['fake'] );
		}

		$active_modules = array_keys( $_modules );

		return apply_filters( 'wptelegram_active_modules', $active_modules );
	}

	/**
	 * Whether the current screen belongs to WP Telegram
	 *
	 * @since 1.0.0
	 * 
	 * @return bool
	 */
	public function is_wptelegram_screen( $hook_suffix = '' ) {

		$cur_screen = get_current_screen();

		// check for plugin page
		$is_plugin_page = ( ! empty( $cur_screen ) && FALSE !== strpos( $cur_screen->id, 'wptelegram' ) );

		if ( $is_plugin_page ) {
			return true;
		}
		return false;
	}

	/**
	 * Whether the current screen is a settings page
	 *
	 * @since 1.0.0
	 * 
	 * @return bool
	 */
	public function is_settings_page( $module_slug = '' ) {

		$slug = 'wptelegram';

		if ( $module_slug ) {
			$slug .= "_{$module_slug}";
		}

		if ( isset( $_GET['page'] ) && FALSE !== strpos( $_GET['page'], $slug ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Display options-page output
	 *
	 * @since  2.0.13
	 */
	public function render_cmb2_options_page( $hookup ) {

		$tabs = $hookup->get_tab_group_tabs();
		?>
		<div class="wrap wrap-wptelegram cmb2-options-page option-<?php echo $hookup->option_key; ?>">
			<?php if ( $hookup->cmb->prop( 'title' ) ) : ?>
				<h2><?php echo wp_kses_post( $hookup->cmb->prop( 'title' ) ); ?></h2>
			<?php endif; ?>
			<?php if ( ! empty( $tabs ) ) : ?>
				<h2 class="nav-tab-wrapper">
					<?php foreach ( $tabs as $option_key => $tab_title ) :
					?>
						<a class="nav-tab<?php if ( call_user_func( get_class( $hookup ) . '::is_page', $option_key ) ) : ?> nav-tab-active<?php endif; ?>" href="<?php menu_page_url( $option_key ); ?>"><?php echo wp_kses_post( $tab_title ); ?></a>
					<?php endforeach; ?>
				</h2>
			<?php endif; ?>
			<?php do_action( 'wptelegram_before_cmb2_form',  $hookup ); ?>
			<form class="cmb-form wptelegram-form wptelegram-column-1" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="POST" id="<?php echo $hookup->cmb->cmb_id; ?>" enctype="multipart/form-data" encoding="multipart/form-data">
				<input type="hidden" name="action" value="<?php echo esc_attr( $hookup->option_key ); ?>">
				<?php $hookup->options_page_metabox(); ?>
				<?php submit_button( esc_attr( $hookup->cmb->prop( 'save_button' ) ), 'primary', 'submit-cmb' ); ?>
			</form>
			<?php do_action( 'wptelegram_after_cmb2_form', $hookup ); ?>
		</div>
		<?php
	}

	/**
	 * Get the HTML for displaying the error message
	 *
	 * @since 1.0.0
	 * 
	 * @return string
	 */
	public function get_empty_bot_token_html() {
		return sprintf( '<p class="wptelegram-error"><b>%s</b> <a href="%s" target="_blank">%s</a></p>', __( 'You must set the Bot Token first.', 'wptelegram' ), esc_attr( admin_url( 'admin.php?page=wptelegram' ) ), __( 'Click here', 'wptelegram' ) );
	}

	/**
	 * Handles sanitization for the fields
	 *
	 * @param  mixed	  $value	  The unsanitized value from the form.
	 * @param  array	  $field_args Array of field arguments.
	 * @param  CMB2_Field $field	  The field object
	 *
	 * @return mixed				  Sanitized value to be stored.
	 */
	public function sanitize_channels( $value, $field_args, $field ) {

		$channels = WPTG()->utils->sanitize( explode( ',', $value ) );
		
		return implode( ',', array_filter( $channels ) );
	}

	/**
	 * Handles sanitization for message template
	 *
	 * @since 1.0.0
	 *
	 * @param  mixed	$value		 The unsanitized value from the form.
	 * @param  boolean  $addslashes	Whether to addslashes for database
	 * @param  boolean  $json_encode   Whether to json_encode
	 *
	 * @return mixed				  Sanitized value
	 */
	public function sanitize_message_template( $value, $addslashes = true, $json_encode = true ) {

		$filtered = wp_check_invalid_utf8( $value );
		
		if ( strpos( $filtered, '<' ) !== false ) {
			$filtered = wp_pre_kses_less_than( $filtered );

			$filtered = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', $filtered );
			// This will strip extra whitespace for us.
			$filtered = strip_tags( $filtered, "<b><strong><i><em><a><code><pre>");
		}
		$filtered = trim( $filtered );

		$found = false;
		while ( preg_match( '/%[a-f0-9]{2}/i', $filtered, $match ) ) {
			$filtered = str_replace( $match[0], '', $filtered );
			$found = true;
		}

		if ( $found ) {
			// Strip out the whitespace that may now exist after removing the octets.
			$filtered = trim( preg_replace( '/\s+/', ' ', $filtered ) );
		}

		if ( $json_encode ) {
			// json_encode to avoid errors when saving multi-byte emojis into database with no multi-byte support
			$filtered = json_encode( $filtered );
		}

		if ( $addslashes ) {
			// add slashes to avoid stripping of backslashes
			$filtered = addslashes( $filtered );
		}

		// save to a variable for PHP < 5.3
		$args = func_get_args();

		return apply_filters( 'wptelegram_sanitize_message_template', $filtered, $args );
	}

	/**
	 * Handles escaping for message template
	 *
	 * @param  mixed	  $value	  The unescaped value from the database.
	 * @param  array	  $field_args Array of field arguments.
	 * @param  CMB2_Field $field	  The field object
	 *
	 * @return mixed				  Escaped value to be displayed.
	 */
	public function escape_message_template( $value, $field_args, $field ) {

		$value = $field->val_or_default( $value );

		return esc_textarea( stripslashes( json_decode( $value ) ) );
	}

	/**
	 * Escape the Markdown symbols
	 *
	 * @since 1.0.0
	 * 
	 * @param  string $string The string to be escaped
	 * @return string
	 */
	public function esc_markdown( $string ) {

		$markdown_search = array( '_', '*', '[' );
		$markdown_replace = array( '\_', '\*', '\[' );

		$esc_string = str_replace( $markdown_search, $markdown_replace, $string );

		return apply_filters( 'wptelegram_esc_markdown', $esc_string, $string );
	}

	/**
	 * Get a valid parse mode
	 *
	 * @since 1.0.0
	 *
	 * @param $parse_mode string
	 *
	 * @return string|NULL
	 */
	public function valid_parse_mode( $parse_mode ) {

		switch ( $parse_mode ) {
			case 'Markdown':
			case 'HTML':
				break;
			default:
				$parse_mode = NULL;
				break;
		}
		return $parse_mode;
	}

	/**
	 * Filter Text to make it ready for parsing
	 *
	 * @since 1.0.0
	 *
	 * @param $text	   string
	 * @param $parse_mode string
	 *
	 * @return string
	 */
	public function filter_text_for_parse_mode( $text, $parse_mode ) {

		$unfiltered_text = $text; 

		if ( 'HTML' === $parse_mode ) {

			$allowable_tags = array( 'em', 'strong', 'b', 'i', 'a', 'pre', 'code' );

			// remove unnecessary tags
			$text = strip_tags( $text, '<' . implode( '><', $allowable_tags ) . '>' );

			foreach ( $allowable_tags as $tag ) {

			   // remove $tag if <a> is nested in it
				$pattern = '#(<' . $tag . '>)((.+)?<a\s+(?:[^>]*?\s+)?href=["\']?([^\'"]*)["\']?.*?>(.*?)<\/a>(.+)?)(<\/' . $tag . '>)#iu';

				$text = preg_replace( $pattern, '$2', $text );
			}

			$pattern = '#(?:<\/?)(?:(?:a(?:[^<>]+?)?>)|(?:b>)|(?:strong>)|(?:i>)|(?:em>)|(?:pre>)|(?:code>))(*SKIP)(*FAIL)|[<>&]+#iu';
		
			$text = preg_replace_callback( $pattern, array( $this, 'encode_spl_entities' ), $text );

		} else {

			$text = wp_strip_all_tags( $text );

			if ( 'Markdown' == $parse_mode ) {

				$text = preg_replace_callback( '/\*(.+?)\*/su', array( $this, 'replace_nested_markdown' ), $text );
			}
		}
		return apply_filters( 'wptelegram_filter_text_for_parse_mode', $text, $unfiltered_text );
	}

	/**
	 * Convert the characters into html codes
	 *
	 * @since 2.0.10
	 *
	 * @param $match array
	 *
	 * @return string
	 */
	public function encode_spl_entities( $match ) {

		return htmlentities( $match[0], ENT_NOQUOTES, 'UTF-8', false );
	}

	/**
	 * Replace nested "[" and "_" between two "*"
	 *
	 * @since 1.0.0
	 *
	 * @param $match array
	 *
	 * @return string
	 */
	public function replace_nested_markdown( $match ) {
		return str_replace( array( '\\[', '\\_' ), array( '[', '_' ), $match[0] );
	}

	/**
	 * Get Markup for a test button
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_test_button_html( $text,  $id = '', $class = '' ) {

		$target = empty( $id ) ? $class : $id;
		$id = empty( $id ) ? '' : 'id="test-' . $id . '"';

		return sprintf( '<button type="button" %s class="button-secondary test-%s" data-target="%s" data-text="%s">%s</button>', $id, $target, $target, $text, $text );
	}

	/**
	 * Start the grid row and
	 * add start the first column
	 * @param  int $col_width Width of the column
	 */
	public function open_grid_row_with_col( $col_width = 6 ) {
		return sprintf( '<div class="cmb-row row cmb2GridViewRow"><div class="col-md-%d">', $col_width );
	}

	/**
	 * Close the grid column
	 */
	public function close_grid_col() {
		return '</div>';
	}

	/**
	 * add a column to the row
	 * @param  int $col_width Width of the column
	 */
	public function add_grid_col_to_row( $col_width = 6 ) {
		return sprintf( '<div class="col-md-%d">', $col_width );
	}

	/**
	 * Close the grid column
	 */
	public function close_grid_col_and_row() {
		return '</div></div>';
	}
}