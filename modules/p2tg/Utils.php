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

		return (bool) apply_filters( 'wptelegram_p2tg_is_post_new', $is_new, $post, $is_more_than_a_day_old );
	}

	/**
	 * The post statuses that are valid/allowed.
	 *
	 * @since 4.2.6
	 *
	 * @param WP_Post $post The post object.
	 *
	 * @return array[]
	 */
	public static function get_valid_post_statuses( $post ) {
		$valid_statuses = [
			'live'     => [ // The ones that are live/visible.
				'publish',
				'private',
			],
			'non_live' => [ // The that are not yet live for the audience.
				'future',
				'draft',
				'pending',
			],
		];
		return (array) apply_filters( 'wptelegram_p2tg_valid_post_statuses', $valid_statuses, $post );
	}

	/**
	 * If it's a valid status that the should be handled.
	 *
	 * @since 4.2.6
	 *
	 * @param WP_Post $post The post object.
	 *
	 * @return bool
	 */
	public static function is_valid_status( $post ) {

		$valid_statuses = call_user_func_array( 'array_merge', array_values( self::get_valid_post_statuses( $post ) ) );

		return in_array( $post->post_status, $valid_statuses, true );
	}

	/**
	 * If it's a live/non_live status.
	 *
	 * @since 4.2.6
	 *
	 * @param WP_Post $post The post object.
	 * @param string  $status_type The type of status.
	 *
	 * @return bool
	 */
	public static function is_status_of_type( $post, $status_type ) {

		$valid_statuses = self::get_valid_post_statuses( $post );

		return in_array( $post->post_status, $valid_statuses[ $status_type ], true );
	}
}
