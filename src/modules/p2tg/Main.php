<?php
/**
 * The file that defines the module
 *
 * A class definition that includes attributes and functions used across the module
 *
 * @link       https://t.me/manzoorwanijk
 * @since      1.0.0
 *
 * @package    WPTelegram
 * @subpackage WPTelegram/modules
 */

namespace WPTelegram\Core\modules\p2tg;

use WPTelegram\Core\modules\BaseModule;

/**
 * The module core class.
 *
 * @since      1.0.0
 * @package    WPTelegram
 * @subpackage WPTelegram/modules
 * @author     Manzoor Wani <@manzoorwanijk>
 */
class Main extends BaseModule {

	/**
	 * The single instance of the class.
	 *
	 * @since x.y.z
	 * @var   Main $instance The instance.
	 */
	protected static $instance = null;

	/**
	 * Register all of the hooks.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	protected function define_necessary_hooks() {
		$admin = new Admin( $this );

		add_filter( 'wptelegram_assets_dom_data', array( $admin, 'update_dom_data' ), 10, 2 );

		add_action( 'rest_api_init', array( $admin, 'register_rest_routes' ) );
	}

	/**
	 * Register all of the hooks.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	protected function define_on_active_hooks() {
		$admin = new Admin( $this );

		add_action( 'post_submitbox_misc_actions', array( $admin, 'add_post_edit_switch' ), 10, 1 );

		add_action( 'edit_form_top', array( $admin, 'post_edit_form_hidden_input' ) );
		add_action( 'block_editor_meta_box_hidden_fields', array( $admin, 'block_editor_hidden_fields' ) );

		add_action( 'cmb2_admin_init', array( $admin, 'create_cmb2_override_metabox' ) );

		add_action( 'add_meta_boxes', array( $admin, 'may_be_remove_override_metabox' ), 100 );

		$post_sender = new PostSender( $this );

		add_action( 'wp_insert_post', array( $post_sender, 'wp_insert_post' ), 20, 2 );

		// scheduled post handler.
		add_action( 'future_to_publish', array( $post_sender, 'future_to_publish' ), 20, 1 );

		// delay event handler.
		add_action( 'wptelegram_p2tg_delayed_post', array( $post_sender, 'delayed_post' ), 10, 1 );

		// trigger handler.
		add_action( 'wptelegram_p2tg_send_post', array( $post_sender, 'send_post' ), 10, 3 );
	}
}
