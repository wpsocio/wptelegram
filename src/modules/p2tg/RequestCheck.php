<?php
/**
 * Check the current request details.
 *
 * @link       https://wpsocio.com
 * @since      3.0.0
 *
 * @package    WPTelegram
 * @subpackage WPTelegram\Core\modules\p2tg
 */

namespace WPTelegram\Core\modules\p2tg;

use WP_Post;

/**
 * Class checking the current request details.
 *
 * @package    WPTelegram
 * @subpackage WPTelegram\Core\modules\p2tg
 * @author     WP Socio
 */
class RequestCheck {

	const IS_GB_METABOX = 'is_gb_metabox';

	const WP_IMPORTING = 'wp_importing';

	const BULK_EDIT = 'bulk_edit';

	const QUICK_EDIT = 'quick_edit';

	const FROM_WEB = 'from_web';

	const DOING_AUTOSAVE = 'doing_autosave';

	const POST_REVISION = 'post_revision';

	const DOING_CRON = 'doing_cron';

	const WP_CLI = 'wp_cli';

	const REST_REQUEST = 'rest_request';

	const REST_PRE_INSERT = 'rest_pre_insert';

	/**
	 * If the request is a POST request
	 *
	 * @since   3.0.0
	 *
	 * @access  private
	 * @var     boolean  If the request is a POST request.
	 */
	private static $is_post_request = null;

	/**
	 * If the request is a POST request
	 *
	 * @since    3.0.0
	 */
	public static function is_post_request() {

		if ( is_null( self::$is_post_request ) ) {
			self::$is_post_request = isset( $_SERVER['REQUEST_METHOD'] ) && 'post' === strtolower( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) );
		}
		return self::$is_post_request;
	}

	/**
	 * Get the taxonomy for rule types
	 *
	 * @since    3.0.0
	 *
	 * @param string  $type The type of request.
	 * @param WP_Post $post The to check against.
	 */
	public static function if_is( $type, $post = null ) {
		$is_rest_request = defined( 'REST_REQUEST' ) && REST_REQUEST;

		switch ( $type ) {
			case self::IS_GB_METABOX:
				return self::is_post_request() && isset( $_POST[ Main::PREFIX . 'is_gb_metabox' ] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

			case self::WP_IMPORTING:
				return defined( 'WP_IMPORTING' ) && WP_IMPORTING;

			case self::BULK_EDIT:
				return isset( $_GET['bulk_edit'] ); // phpcs:ignore

			case self::QUICK_EDIT:
				return defined( 'DOING_AJAX' ) && DOING_AJAX && isset( $_REQUEST['action'] ) && 'inline-save' === $_REQUEST['action']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			case self::FROM_WEB:
				return self::is_post_request() && isset( $_POST[ Main::PREFIX . 'from_web' ] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

			case self::DOING_AUTOSAVE:
				return defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE;

			case self::POST_REVISION:
				return wp_is_post_revision( $post );

			case self::DOING_CRON:
				return defined( 'DOING_CRON' ) && DOING_CRON;

			case self::WP_CLI:
				return defined( 'WP_CLI' ) && constant( 'WP_CLI' );

			case self::REST_REQUEST:
				return $is_rest_request;

			case self::REST_PRE_INSERT:
				return $is_rest_request
					&& did_action( 'wptelegram_rest_pre_insert_' . $post->post_type )
					// if not doing "rest_after_insert_{$post_type}" action.
					&& current_filter() !== 'rest_after_insert_' . $post->post_type;

			default:
				return false;
		}
	}
}
