<?php
/**
 * WP Telegram P2TG Utilities
 *
 * @link       https://wpsocio.com
 * @since      3.0.10
 *
 * @package    WPTelegram
 * @subpackage WPTelegram\Core\modules\p2tg
 */

namespace WPTelegram\Core\modules\p2tg;

use WP_Post;

/**
 * WP Telegram P2TG Utilities
 *
 * @link       https://wpsocio.com
 * @since      3.0.10
 *
 * @package    WPTelegram
 * @subpackage WPTelegram\Core\modules\p2tg
 */
class Utils {

	/**
	 * Whether the post is new.
	 *
	 * @since   3.0.10
	 *
	 * @param   int|WP_Post $post The post to check.
	 *
	 * @return  bool
	 */
	public static function is_post_new( $post ) {

		$post = get_post( $post );

		if ( ! $post ) {
			return false;
		}

		$post_publish_time = get_post_time( 'U', true, $post->ID, false );

		// if the post has been published more than one day ago.
		$is_more_than_a_day_old = $post_publish_time ? ( ( time() - $post_publish_time ) / DAY_IN_SECONDS ) > 1 : false;

		// whether the post has already been sent to Telegram.
		$sent2tg = get_post_meta( $post->ID, Main::PREFIX . 'sent2tg', true );

		// if the meta value is empty - it's new.
		$is_new = empty( $sent2tg ) && ! $is_more_than_a_day_old;

		return (bool) apply_filters( 'wptelegram_p2tg_is_post_new', $is_new, $post );
	}
}
