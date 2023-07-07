<?php
/**
 * Adds the third party integrations.
 *
 * @package    WPTelegram
 * @subpackage WPTelegram\Core\includes
 */

namespace WPTelegram\Core\includes;

/**
 * Integrations class.
 */
class Integrations {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 4.0.12
	 */
	public function __construct() {

		add_filter( 'duplicate_post_excludelist_filter', [ self::class, 'yoast_exclude_meta' ], 10, 1 );
	}

	/**
	 * Exclude WP Telegram post meta from duplicate post plugin.
	 *
	 * @param array $meta_excludelist The meta exclude list.
	 * @return array
	 */
	public static function yoast_exclude_meta( $meta_excludelist ) {
		$meta_excludelist[] = '_wptg_*';

		return $meta_excludelist;
	}
}
