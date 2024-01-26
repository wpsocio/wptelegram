<?php
/**
 * The file that defines the module
 *
 * A class definition that includes attributes and functions used across the module
 *
 * @link       https://wpsocio.com
 * @since      1.0.0
 *
 * @package    WPTelegram
 * @subpackage WPTelegram\Core\modules\p2tg
 */

namespace WPTelegram\Core\modules\p2tg;

use WPTelegram\Core\modules\BaseModule;

/**
 * The main module class.
 *
 * @since      1.0.0
 * @package    WPTelegram
 * @subpackage WPTelegram\Core\modules\p2tg
 * @author     WP Socio
 */
class Main extends BaseModule {

	const PREFIX = '_wptg_p2tg_';

	/**
	 * Register all of the hooks.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	protected function define_necessary_hooks() {
		$admin = Admin::instance();

		add_filter( 'wptelegram_inline_script_data', [ $admin, 'update_inline_script_data' ], 10, 2 );

		add_action( 'rest_api_init', [ $admin, 'register_rest_routes' ] );
	}

	/**
	 * Register all of the hooks.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	protected function define_on_active_hooks() {
		$admin = Admin::instance();

		add_action( 'admin_enqueue_scripts', [ $admin, 'enqueue_admin_scripts' ] );

		add_action( 'enqueue_block_editor_assets', [ $admin, 'enqueue_block_editor_assets' ] );

		add_action( 'post_submitbox_misc_actions', [ $admin, 'add_post_edit_switch' ] );

		add_action( 'edit_form_top', [ $admin, 'post_edit_form_hidden_input' ] );
		add_action( 'block_editor_meta_box_hidden_fields', [ $admin, 'block_editor_hidden_fields' ] );
		add_action( 'cmb2_admin_init', [ $admin, 'create_cmb2_override_metabox' ] );
		add_action( 'add_meta_boxes', [ $admin, 'may_be_remove_override_metabox' ], 100 );

		add_action( 'rest_api_init', [ $admin, 'hook_into_rest_pre_insert' ] );

		$post_sender = PostSender::instance();

		add_action( 'wp_insert_post', [ $post_sender, 'wp_insert_post' ], 20, 2 );

		// delay event handler.
		add_action( 'wptelegram_p2tg_delayed_post', [ $post_sender, 'delayed_post' ], 10, 1 );

		// trigger handler.
		add_action( 'wptelegram_p2tg_send_post', [ $post_sender, 'send_post' ], 10, 3 );
	}
}
