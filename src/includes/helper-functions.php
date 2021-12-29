<?php
/**
 * Helper functions
 *
 * @link       https://wpsocio.com
 * @since     1.0.0
 *
 * @package WPTelegram
 * @subpackage WPTelegram\Core\includes
 */

if ( ! function_exists( 'wptelegram_p2tg_send_post' ) ) {
	/**
	 * Function to send the post to Telegram.
	 *
	 * @since  1.0.0
	 *
	 * @param   WP_Post $post       The post to be handled.
	 * @param   string  $trigger    The name of the source trigger hook e.g. "save_post".
	 * @param   bool    $force      Whether to bypass the custom rules.
	 */
	function wptelegram_p2tg_send_post( WP_Post $post, $trigger = 'non_wp', $force = false ) {

		do_action( 'wptelegram_p2tg_send_post', $post, $trigger, $force );
	}
}
