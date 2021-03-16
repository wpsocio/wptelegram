<?php
/**
 * Do the necessary db upgrade
 *
 * @link       https://t.me/manzoorwanijk
 * @since      2.2.0
 *
 * @package    WPTelegram
 * @subpackage WPTelegram/includes
 */

namespace WPTelegram\Core\includes;

use WPTelegram\Core\modules\p2tg\restApi\RulesController;

/**
 * Do the necessary db upgrade.
 *
 * Do the nececessary the incremental upgrade.
 *
 * @package    WPTelegram
 * @subpackage WPTelegram/includes
 * @author     Manzoor Wani <@manzoorwanijk>
 */
class Upgrade {

	/**
	 * The plugin class instance.
	 *
	 * @since    2.2.0
	 * @access   private
	 * @var      WPTelegram $plugin The plugin class instance.
	 */
	private $plugin;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 2.2.0
	 * @param WPTelegram $plugin The plugin class instance.
	 */
	public function __construct( $plugin ) {

		$this->plugin = $plugin;
	}

	/**
	 * Do the necessary db upgrade, if needed
	 *
	 * @since    2.0.0
	 */
	public function do_upgrade() {

		$current_version = get_option( 'wptelegram_ver', '1.9.4' );

		if ( ! version_compare( $current_version, WPTELEGRAM_VER, '<' ) ) {
			return;
		}

		if ( ! defined( 'WPTELEGRAM_DOING_UPGRADE' ) ) {
			define( 'WPTELEGRAM_DOING_UPGRADE', true );
		}

		do_action( 'wptelegram_before_do_upgrade', $current_version );

		$is_new_install = ! get_option( 'wptelegram_telegram' ) && ! get_option( 'wptelegram' );

		$version_upgrades = array();
		if ( ! $is_new_install ) {
			// the sequential upgrades
			// subsequent upgrade depends upon the previous one.
			$version_upgrades = array(
				'2.0.0', // first upgrade.
				'2.1.9',
				'2.2.0',
				'3.0.0',
			);
		}

		// always.
		if ( ! in_array( WPTELEGRAM_VER, $version_upgrades, true ) ) {
			$version_upgrades[] = WPTELEGRAM_VER;
		}

		foreach ( $version_upgrades as $target_version ) {

			if ( version_compare( $current_version, $target_version, '<' ) ) {

				$this->upgrade_to( $target_version );

				$current_version = $target_version;
			}
		}

		do_action( 'wptelegram_after_do_upgrade', $current_version );
	}

	/**
	 * Upgrade to a specific version.
	 *
	 * @since    2.0.0
	 *
	 * @param string $version The version to upgrade to.
	 */
	private function upgrade_to( $version ) {

		// 2.0.1 becomes 2_0_1
		$_version = str_replace( '.', '_', $version );

		$method = array( $this, "upgrade_to_{$_version}" );

		if ( is_callable( $method ) ) {

			call_user_func( $method );
		}

		update_option( 'wptelegram_ver', $version );
	}

	/**
	 * Upgrade to a specific version
	 *
	 * @since    2.0.0
	 */
	protected function upgrade_to_2_0_0() {

		$telegram_opts = get_option( 'wptelegram_telegram', array() );

		$wptelegram['bot_token'] = $telegram_opts['bot_token'];

		// if P2TG should be active.
		if ( ! empty( $telegram_opts['chat_ids'] ) ) {

			// activate p2tg.
			$wptelegram['modules'][0]['p2tg'] = 'on';

			// migrate Telegram Destination.
			$wptelegram_p2tg['channels'] = $telegram_opts['chat_ids'];
		}

		// fetch other stuff.
		$wordpress_opts = get_option( 'wptelegram_wordpress', array() );

		/**
		 * Migrate Rules
		 */
		// when.
		if ( ! empty( $wordpress_opts['send_when'] ) ) {

			if ( in_array( 'send_new', $wordpress_opts['send_when'], true ) ) {
				$wptelegram_p2tg['send_when'][] = 'new';
			}
			if ( in_array( 'send_updated', $wordpress_opts['send_when'], true ) ) {
				$wptelegram_p2tg['send_when'][] = 'existing';
			}
		}

		// post_type.
		if ( ! empty( $wordpress_opts['which_post_type'] ) ) {

			$wptelegram_p2tg['post_types'] = (array) $wordpress_opts['which_post_type'];
		}

		$author_rule = array();

		// Authors.
		if ( ! empty( $wordpress_opts['from_authors'][0] ) && 'all' !== $wordpress_opts['from_authors'][0] && ! empty( $wordpress_opts['authors'] ) ) {

			$param    = 'post_author';
			$operator = ( 'selected' === $wordpress_opts['from_authors'][0] ) ? 'in' : 'not_in';
			$values   = $wordpress_opts['authors'];

			$author_rule = compact( 'param', 'operator', 'values' );
		}

		$taxonomy_rules = array();

		// categories.
		if ( ! empty( $wordpress_opts['from_terms'][0] ) && 'all' !== $wordpress_opts['from_terms'][0] && ! empty( $wordpress_opts['terms'] ) ) {

			// taxonomy with their terms.
			$tax_terms = array();

			foreach ( $wordpress_opts['terms'] as $term_tax ) {

				list( $term_id, $taxonomy ) = explode( '@', $term_tax );
				if ( 'category' === $taxonomy ) {
					$tax_terms[ $taxonomy ][] = $term_id;
				} else {
					$tax_terms[ 'tax:' . $taxonomy ][] = $term_id;
				}
			}

			$operator = ( 'selected' === $wordpress_opts['from_terms'][0] ) ? 'in' : 'not_in';

			foreach ( $tax_terms as $taxonomy => $terms ) {

				$param  = $taxonomy;
				$values = $terms;

				$taxonomy_rules[] = compact( 'param', 'operator', 'values' );
			}
		}

		$rule_groups = array();

		foreach ( $taxonomy_rules as $tax_rule ) {
			$rule_group = array();

			$rule_group[] = $tax_rule;

			if ( ! empty( $author_rule ) ) {
				$rule_group[] = $author_rule;
			}

			$rule_groups[] = $rule_group;
		}

		// if we do not have anything.
		if ( empty( $rule_groups ) && ! empty( $author_rule ) ) {

			$rule_group = array();

			$rule_group[] = $author_rule;

			$rule_groups[] = $rule_group;
		}

		// if we have something.
		if ( ! empty( $rule_groups ) ) {

			$wptelegram_p2tg['rules'] = $rule_groups;
		}

		/* Migrate Message settings */
		$message_opts = get_option( 'wptelegram_message', array() );
		$keys         = array(
			'message_template',
			'excerpt_source',
			'excerpt_length',
			'parse_mode',
			'inline_url_button',
			'inline_button_text',
			'send_featured_image',
			'image_position',
			'misc',
		);

		foreach ( $keys as $key ) {

			if ( ! empty( $message_opts[ $key ] ) ) {

				$wptelegram_p2tg[ $key ] = ( 'off' === $message_opts[ $key ] ) ? 0 : $message_opts[ $key ];
			}
		}

		// convert macros in template.
		if ( ! empty( $wptelegram_p2tg['message_template'] ) ) {

			$template = json_decode( $wptelegram_p2tg['message_template'] );

			$macros = array(
				'title',
				'author',
				'excerpt',
				'content',
			);

			foreach ( $macros as $macro ) {

				$template = str_replace( '{' . $macro . '}', '{post_' . $macro . '}', $template );
			}

			// replace taxonomy macros.
			if ( preg_match_all( '/(?<=\{\[)[a-z0-9_]+?(?=\]\})/iu', $template, $matches ) ) {

				foreach ( $matches[0] as $taxonomy ) {

					$template = str_replace( '{[' . $taxonomy . ']}', '{terms:' . $taxonomy . '}', $template );
				}
			}

			// replace custom fields macros.
			if ( preg_match_all( '/(?<=\{\[\[).+?(?=\]\]\})/u', $template, $matches ) ) {

				foreach ( $matches[0] as $custom_field ) {

					$template = str_replace( '{[[' . $custom_field . ']]}', '{cf:' . $custom_field . '}', $template );
				}
			}

			$wptelegram_p2tg['message_template'] = Utils::sanitize_message_template( $template, false );
		}

		// now decide about single_message.
		$wptelegram_p2tg['single_message'] = 0;
		if ( ( ! empty( $message_opts['attach_image'] ) && 'on' === $message_opts['attach_image'] ) || ( ! empty( $message_opts['image_style'] ) && 'with_caption' === $message_opts['image_style'] ) ) {

			$wptelegram_p2tg['single_message'] = 'on';
		}

		$notify_opts = get_option( 'wptelegram_notify', array() );
		// if Notify should be active.
		if ( ! empty( $notify_opts['chat_ids'] ) && ! empty( $notify_opts['watch_emails'] ) ) {

			// activate notify.
			$wptelegram['modules'][0]['notify'] = 'on';

			// migrate NOTIFICATION SETTINGS.
			$wptelegram_notify['chat_ids']           = $notify_opts['chat_ids'];
			$wptelegram_notify['watch_emails']       = $notify_opts['watch_emails'];
			$wptelegram_notify['user_notifications'] = empty( $notify_opts['user_notifications'] ) ? 0 : $notify_opts['user_notifications'];

			$template = '🔔‌<b>{email_subject}</b>🔔' . PHP_EOL . PHP_EOL . '{email_message}';
			if ( ! empty( $notify_opts['hashtag'] ) ) {
				$template .= PHP_EOL . PHP_EOL . $notify_opts['hashtag'];
			}

			$wptelegram_notify['message_template'] = Utils::sanitize_message_template( $template, false );

			$wptelegram_notify['parse_mode'] = 'HTML';
		}

		// if proxy should be active.
		if ( apply_filters( 'wptelegram_bot_api_use_proxy', false ) ) {
			// activate proxy.
			$wptelegram['modules'][0]['proxy'] = 'on';
		}

		$proxy_opts = get_option( 'wptelegram_proxy', array() );

		if ( ! empty( $proxy_opts ) ) {

			$wptelegram_proxy = $proxy_opts;

			if ( ! empty( $proxy_opts['script_url'] ) ) {
				$wptelegram_proxy['proxy_method'] = 'google_script';
			} else {
				$wptelegram_proxy['proxy_method'] = 'php_proxy';
			}
		}

		$sections = array(
			'telegram',
			'wordpress',
			'message',
			'notify',
			'proxy',
		);
		foreach ( $sections as $section ) {
			delete_option( 'wptelegram_' . $section );
		}

		// update the options.
		$options = compact( 'wptelegram', 'wptelegram_p2tg', 'wptelegram_notify', 'wptelegram_proxy' );
		foreach ( $options as $option => $value ) {
			update_option( $option, $value );
		}
	}

	/**
	 * Upgrade to a specific version
	 *
	 * @since    2.1.9
	 */
	protected function upgrade_to_2_1_9() {

		$types = array( 'p2tg', 'bot-api' );

		foreach ( $types as $type ) {
			$filename = WP_CONTENT_DIR . "/wptelegram-{$type}.log";
			$filename = apply_filters( "wptelegram_logger_{$type}_log_filename", $filename );
			if ( file_exists( $filename ) ) {
				unlink( $filename );
			}
		}
	}

	/**
	 * Upgrade to a specific version
	 * Changes telegram user id meta key to share between other plugins.
	 *
	 * @since    2.2.0
	 */
	protected function upgrade_to_2_2_0() {
		$old_meta_key = 'telegram_chat_id';

		$args  = array(
			'fields'       => 'ID',
			'meta_key'     => $old_meta_key, // phpcs:ignore
			'meta_compare' => 'EXISTS',
			'number'       => -1,
		);
		$users = get_users( $args );

		foreach ( $users as $id ) {
			// get the existing value.
			$meta_value = get_user_meta( $id, $old_meta_key, true );
			// use the new meta key to retain existing value.
			update_user_meta( $id, WPTELEGRAM_USER_ID_META_KEY, $meta_value );
			// housekeeping.
			delete_user_meta( $id, $old_meta_key );
		}
	}

	/**
	 * Upgrade to 3.0.0
	 *
	 * @since    x.y.z
	 */
	protected function upgrade_to_3_0_0() {
		/**
		 * Since this upgrade needs taxonomies registered,
		 * we will run it on init.
		 */
		add_action( 'init', array( $this, 'upgrade_to_3_0_0_on_init' ), 50 );
	}

	/**
	 * Upgrade to 3.0.0
	 *
	 * @since    x.y.z
	 */
	public function upgrade_to_3_0_0_on_init() {
		$main_options = get_option( 'wptelegram', array() );

		$modules = reset( $main_options['modules'] );
		unset( $modules['fake'] );

		$active_modules = array_keys( $modules );

		$p2tg_options     = get_option( 'wptelegram_p2tg', array() );
		$notify_options   = get_option( 'wptelegram_notify', array() );
		$proxy_options    = get_option( 'wptelegram_proxy', array() );
		$advanced_options = array();

		$upgraded_options = array();

		foreach ( array( 'bot_token', 'bot_username' ) as $field ) {
			if ( ! empty( $main_options[ $field ] ) ) {
				$upgraded_options[ $field ] = $main_options[ $field ];
			}
		}

		/************************ POST TO TELEGRAM */
		$p2tg_options['active'] = in_array( 'p2tg', $active_modules, true );
		// Break channels string into array.
		if ( ! empty( $p2tg_options['channels'] ) ) {
			$p2tg_options['channels'] = array_map( 'trim', explode( ',', $p2tg_options['channels'] ) );
		} else {
			$p2tg_options['channels'] = array();
		}
		// message template needs json_decode.
		if ( ! empty( $p2tg_options['message_template'] ) ) {
			$p2tg_options['message_template'] = stripslashes( json_decode( $p2tg_options['message_template'] ) );
		} else {
			$p2tg_options['message_template'] = '';
		}
		// convert boolean fields.
		$p2tg_bool_fields = array(
			'excerpt_preserve_eol',
			'send_featured_image',
			'single_message',
			'cats_as_tags',
			'inline_url_button',
			'post_edit_switch',
			'plugin_posts',
		);
		foreach ( $p2tg_bool_fields as $field ) {
			$p2tg_options[ $field ] = ! empty( $p2tg_options[ $field ] );
		}
		if ( ! empty( $p2tg_options['inline_button_text'] ) ) {
			$p2tg_options['inline_button_text'] = '🔗 ' . $p2tg_options['inline_button_text'];
		}
		$p2tg_options['inline_button_url'] = '{full_url}';

		// convert numeric fields.
		$p2tg_numeric_fields = array(
			'excerpt_length',
			'delay',
		);
		foreach ( $p2tg_numeric_fields as $field ) {
			$p2tg_options[ $field ] = (float) $p2tg_options[ $field ];
		}

		$misc = ! empty( $p2tg_options['misc'] ) ? $p2tg_options['misc'] : array();

		$p2tg_options['disable_web_page_preview'] = in_array( 'disable_web_page_preview', $misc, true );
		$p2tg_options['disable_notification']     = in_array( 'disable_notification', $misc, true );

		if ( ! empty( $p2tg_options['rules'] ) ) {
			$rules = $p2tg_options['rules'];

			$upgraded_rules = array();

			foreach ( $rules as $rule_group ) {
				$upgraded_rule_group = array();

				foreach ( $rule_group as $rule ) {
					$upgraded_rule = array();

					if ( ! empty( $rule['values'] ) ) {
						$param  = $rule['param'];
						$values = $rule['values'];

						$new_values = RulesController::get_rule_values( $param, '', $values );
						if ( ! empty( $new_values ) ) {
							$rule['values'] = $new_values;

							$upgraded_rule = $rule;
						}
					}

					// Add the rule to the group.
					if ( ! empty( $upgraded_rule ) ) {
						$upgraded_rule_group[] = $upgraded_rule;
					}
				}

				// Add the rule group to the rules.
				if ( ! empty( $upgraded_rule_group ) ) {
					$upgraded_rules[] = $upgraded_rule_group;
				}
			}

			$p2tg_options['rules'] = $upgraded_rules;
		}
		/************************ POST TO TELEGRAM */

		/************************* NOTIFICATIONS */
		$notify_options['active'] = in_array( 'notify', $active_modules, true );
		// Break chat ids string into array.
		if ( ! empty( $notify_options['chat_ids'] ) ) {
			$notify_options['chat_ids'] = array_map( 'trim', explode( ',', $notify_options['chat_ids'] ) );
		} else {
			$notify_options['chat_ids'] = array();
		}
		// message template needs json_decode.
		if ( ! empty( $notify_options['message_template'] ) ) {
			$notify_options['message_template'] = stripslashes( json_decode( $notify_options['message_template'] ) );
		} else {
			$notify_options['message_template'] = '';
		}
		$notify_options['user_notifications'] = ! empty( $notify_options['user_notifications'] );
		/************************* NOTIFICATIONS */

		/************************* PROXY */
		$proxy_options['active'] = in_array( 'proxy', $active_modules, true );
		// `script_url` is now `google_script_url`.
		if ( ! empty( $proxy_options['script_url'] ) ) {
			$proxy_options['google_script_url'] = $proxy_options['script_url'];
			unset( $proxy_options['script_url'] );
		}
		/************************* PROXY */

		/************************* ADVANCED */
		$advanced_options['send_files_by_url'] = ! empty( $main_options['send_files_by_url'] );
		$advanced_options['clean_uninstall']   = ! empty( $main_options['clean_uninstall'] );
		$advanced_options['enable_logs']       = $main_options['enable_logs'];
		/************************* ADVANCED */

		$upgraded_options['p2tg']     = $p2tg_options;
		$upgraded_options['notify']   = $notify_options;
		$upgraded_options['proxy']    = $proxy_options;
		$upgraded_options['advanced'] = $advanced_options;

		update_option( 'wptelegram', wp_json_encode( $upgraded_options ) );

		foreach ( array( 'p2tg', 'notify', 'proxy' ) as $module ) {
			delete_option( 'wptelegram_' . $module );
		}
	}
}
