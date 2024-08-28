<?php
/**
 * Do the necessary db upgrade
 *
 * @link       https://wpsocio.com
 * @since      2.2.0
 *
 * @package    WPTelegram
 * @subpackage WPTelegram\Core\includes
 */

namespace WPTelegram\Core\includes;

use WPTelegram\Core\modules\p2tg\restApi\RulesController;

/**
 * Do the necessary db upgrade.
 *
 * Do the nececessary the incremental upgrade.
 *
 * @package    WPTelegram
 * @subpackage WPTelegram\Core\includes
 * @author     WP Socio
 */
class Upgrade extends BaseClass {

	/**
	 * Do the necessary db upgrade, if needed
	 *
	 * @since    2.0.0
	 */
	public function do_upgrade() {

		$current_version = get_option( 'wptelegram_ver', '1.9.4' );

		if ( ! version_compare( $current_version, $this->plugin()->version(), '<' ) ) {
			return;
		}

		if ( ! defined( 'WPTELEGRAM_DOING_UPGRADE' ) ) {
			define( 'WPTELEGRAM_DOING_UPGRADE', true );
		}

		do_action( 'wptelegram_before_do_upgrade', $current_version );

		$is_new_install = ! get_option( 'wptelegram_telegram' ) && ! get_option( 'wptelegram' );

		$version_upgrades = [];
		if ( ! $is_new_install ) {
			// the sequential upgrades
			// subsequent upgrade depends upon the previous one.
			$version_upgrades = [
				'2.0.0', // first upgrade.
				'2.1.9',
				'2.2.0',
				'3.0.0',
				'3.0.8',
				'4.0.0',
				'4.1.0',
			];
		}

		// always.
		if ( ! in_array( $this->plugin()->version(), $version_upgrades, true ) ) {
			$version_upgrades[] = $this->plugin()->version();
		}

		foreach ( $version_upgrades as $target_version ) {

			if ( version_compare( $current_version, $target_version, '<' ) ) {

				$this->upgrade_to( $target_version, $is_new_install );

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
	 * @param string  $version        The plugin version to upgrade to.
	 * @param boolean $is_new_install Whether it's a fresh install of the plugin.
	 */
	private function upgrade_to( $version, $is_new_install ) {

		// 2.0.1 becomes 2_0_1
		$_version = str_replace( '.', '_', $version );

		$method = [ $this, "upgrade_to_{$_version}" ];

		// No upgrades for fresh installations.
		if ( ! $is_new_install && is_callable( $method ) ) {

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

		$telegram_opts = get_option( 'wptelegram_telegram', [] );

		$wptelegram['bot_token'] = $telegram_opts['bot_token'];

		// if P2TG should be active.
		if ( ! empty( $telegram_opts['chat_ids'] ) ) {

			// activate p2tg.
			$wptelegram['modules'][0]['p2tg'] = 'on';

			// migrate Telegram Destination.
			$wptelegram_p2tg['channels'] = $telegram_opts['chat_ids'];
		}

		// fetch other stuff.
		$wordpress_opts = get_option( 'wptelegram_wordpress', [] );

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

		$author_rule = [];

		// Authors.
		if ( ! empty( $wordpress_opts['from_authors'][0] ) && 'all' !== $wordpress_opts['from_authors'][0] && ! empty( $wordpress_opts['authors'] ) ) {

			$param    = 'post_author';
			$operator = ( 'selected' === $wordpress_opts['from_authors'][0] ) ? 'in' : 'not_in';
			$values   = $wordpress_opts['authors'];

			$author_rule = compact( 'param', 'operator', 'values' );
		}

		$taxonomy_rules = [];

		// categories.
		if ( ! empty( $wordpress_opts['from_terms'][0] ) && 'all' !== $wordpress_opts['from_terms'][0] && ! empty( $wordpress_opts['terms'] ) ) {

			// taxonomy with their terms.
			$tax_terms = [];

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

		$rule_groups = [];

		foreach ( $taxonomy_rules as $tax_rule ) {
			$rule_group = [];

			$rule_group[] = $tax_rule;

			if ( ! empty( $author_rule ) ) {
				$rule_group[] = $author_rule;
			}

			$rule_groups[] = $rule_group;
		}

		// if we do not have anything.
		if ( empty( $rule_groups ) && ! empty( $author_rule ) ) {

			$rule_group = [];

			$rule_group[] = $author_rule;

			$rule_groups[] = $rule_group;
		}

		// if we have something.
		if ( ! empty( $rule_groups ) ) {

			$wptelegram_p2tg['rules'] = $rule_groups;
		}

		/* Migrate Message settings */
		$message_opts = get_option( 'wptelegram_message', [] );
		$keys         = [
			'message_template',
			'excerpt_source',
			'excerpt_length',
			'parse_mode',
			'inline_url_button',
			'inline_button_text',
			'send_featured_image',
			'image_position',
			'misc',
		];

		foreach ( $keys as $key ) {

			if ( ! empty( $message_opts[ $key ] ) ) {

				$wptelegram_p2tg[ $key ] = ( 'off' === $message_opts[ $key ] ) ? 0 : $message_opts[ $key ];
			}
		}

		// convert macros in template.
		if ( ! empty( $wptelegram_p2tg['message_template'] ) ) {

			$template = json_decode( $wptelegram_p2tg['message_template'] );

			$macros = [
				'title',
				'author',
				'excerpt',
				'content',
			];

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

			$wptelegram_p2tg['message_template'] = Utils::sanitize_message_template( $template, false, true );
		}

		// now decide about single_message.
		$wptelegram_p2tg['single_message'] = 0;
		if ( ( ! empty( $message_opts['attach_image'] ) && 'on' === $message_opts['attach_image'] ) || ( ! empty( $message_opts['image_style'] ) && 'with_caption' === $message_opts['image_style'] ) ) {

			$wptelegram_p2tg['single_message'] = 'on';
		}

		$notify_opts = get_option( 'wptelegram_notify', [] );
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

			$wptelegram_notify['message_template'] = Utils::sanitize_message_template( $template, false, true );

			$wptelegram_notify['parse_mode'] = 'HTML';
		}

		// if proxy should be active.
		if ( apply_filters( 'wptelegram_bot_api_use_proxy', false ) ) {
			// activate proxy.
			$wptelegram['modules'][0]['proxy'] = 'on';
		}

		$proxy_opts = get_option( 'wptelegram_proxy', [] );

		if ( ! empty( $proxy_opts ) ) {

			$wptelegram_proxy = $proxy_opts;

			if ( ! empty( $proxy_opts['script_url'] ) ) {
				$wptelegram_proxy['proxy_method'] = 'google_script';
			} else {
				$wptelegram_proxy['proxy_method'] = 'php_proxy';
			}
		}

		$sections = [
			'telegram',
			'wordpress',
			'message',
			'notify',
			'proxy',
		];
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

		$types = [ 'p2tg', 'bot-api' ];

		foreach ( $types as $type ) {
			$filename = WP_CONTENT_DIR . "/wptelegram-{$type}.log";
			$filename = apply_filters( "wptelegram_logger_{$type}_log_filename", $filename );
			if ( file_exists( $filename ) ) {
				wp_delete_file( $filename );
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

		$args  = [
			'fields'       => 'ID',
			'meta_key'     => $old_meta_key, // phpcs:ignore
			'meta_compare' => 'EXISTS',
			'number'       => -1,
		];
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
	 * @since    3.0.0
	 */
	protected function upgrade_to_3_0_0() {
		$main_options = get_option( 'wptelegram', [] );

		$modules = ! empty( $main_options['modules'] ) && is_array( $main_options['modules'] ) ? reset( $main_options['modules'] ) : [];
		unset( $modules['fake'] );

		$active_modules = array_keys( $modules );

		$p2tg_options     = get_option( 'wptelegram_p2tg', [] );
		$notify_options   = get_option( 'wptelegram_notify', [] );
		$proxy_options    = get_option( 'wptelegram_proxy', [] );
		$advanced_options = [];

		$upgraded_options = [];

		foreach ( [ 'bot_token', 'bot_username' ] as $field ) {
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
			$p2tg_options['channels'] = [];
		}
		// message template needs json_decode.
		if ( ! empty( $p2tg_options['message_template'] ) ) {
			$p2tg_options['message_template'] = stripslashes( json_decode( $p2tg_options['message_template'] ) );
		} else {
			$p2tg_options['message_template'] = '';
		}
		// convert boolean fields.
		$p2tg_bool_fields = [
			'excerpt_preserve_eol',
			'send_featured_image',
			'single_message',
			'cats_as_tags',
			'inline_url_button',
			'post_edit_switch',
			'plugin_posts',
			'protect_content',
		];
		foreach ( $p2tg_bool_fields as $field ) {
			$p2tg_options[ $field ] = ! empty( $p2tg_options[ $field ] );
		}
		if ( ! empty( $p2tg_options['inline_button_text'] ) ) {
			$p2tg_options['inline_button_text'] = '🔗 ' . $p2tg_options['inline_button_text'];
		}
		$p2tg_options['inline_button_url'] = '{full_url}';

		$is_wp_cron_disabled = defined( 'DISABLE_WP_CRON' ) && constant( 'DISABLE_WP_CRON' );

		// convert numeric fields.
		$p2tg_numeric_fields = [
			'excerpt_length' => 55,
			'delay'          => $is_wp_cron_disabled ? 0 : 0.5,
		];
		foreach ( $p2tg_numeric_fields as $field => $default ) {
			$p2tg_options[ $field ] = ! empty( $p2tg_options[ $field ] ) ? (float) $p2tg_options[ $field ] : $default;
		}

		$misc = ! empty( $p2tg_options['misc'] ) ? $p2tg_options['misc'] : [];

		$p2tg_options['disable_web_page_preview'] = in_array( 'disable_web_page_preview', $misc, true );
		$p2tg_options['disable_notification']     = in_array( 'disable_notification', $misc, true );
		unset( $p2tg_options['misc'] );

		/**
		 * Since rules upgrade needs taxonomies registered,
		 * we will run it on init.
		 */
		add_action(
			'init',
			function () {
				$p2tg = WPTG()->options()->get( 'p2tg' );
				if ( ! empty( $p2tg['rules'] ) ) {
					$rules = $p2tg['rules'];

					$upgraded_rules = [];

					foreach ( $rules as $rule_group ) {
						$upgraded_rule_group = [];

						foreach ( $rule_group as $rule ) {
							$upgraded_rule = [];

							if ( ! empty( $rule['values'] ) ) {
								$param  = $rule['param'];
								$values = $rule['values'];

								$new_values = RulesController::get_rule_values( $param, '', $values );
								if ( ! empty( $new_values ) ) {
									// if it's a Post based rule, it can have option groups.
									if ( 'post' === $param ) {
										$new_values = wp_list_pluck( $new_values, 'options' );
										if ( ! empty( $new_values ) ) {
											$new_values = call_user_func_array( 'array_merge', $new_values );
										}
									}
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

					$p2tg['rules'] = $upgraded_rules;

					WPTG()->options()->set( 'p2tg', $p2tg );
				}
			},
			50
		);

		/************************ POST TO TELEGRAM */

		/************************* NOTIFICATIONS */
		$notify_options['active'] = in_array( 'notify', $active_modules, true );
		// Break chat ids string into array.
		if ( ! empty( $notify_options['chat_ids'] ) ) {
			$notify_options['chat_ids'] = array_map( 'trim', explode( ',', $notify_options['chat_ids'] ) );
		} else {
			$notify_options['chat_ids'] = [];
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
		$advanced_options['enable_logs']       = [];
		/************************* ADVANCED */

		$upgraded_options['p2tg']     = $p2tg_options;
		$upgraded_options['notify']   = $notify_options;
		$upgraded_options['proxy']    = $proxy_options;
		$upgraded_options['advanced'] = $advanced_options;

		update_option( 'wptelegram', wp_json_encode( $upgraded_options ) );

		foreach ( [ 'p2tg', 'notify', 'proxy' ] as $module ) {
			delete_option( 'wptelegram_' . $module );
		}
	}

	/**
	 * Upgrade to 3.0.8
	 *
	 * In the past upgrades, enable_logs was erroneously set to null.
	 *
	 * @since    3.0.8
	 */
	protected function upgrade_to_3_0_8() {
		$advanced = WPTG()->options()->get( 'advanced' );

		if ( empty( $advanced['enable_logs'] ) ) {
			$advanced['enable_logs'] = [];

			WPTG()->options()->set( 'advanced', $advanced );
		}
	}

	/**
	 * Upgrade to 4.0.0
	 *
	 * - Change parse_mode from Markdown to HTML.
	 *
	 * @since    4.0.0
	 */
	protected function upgrade_to_4_0_0() {
		$sections = [ 'p2tg', 'notify' ];

		$markdown_v1_to_html_map = [
			'*'   => 'b',
			'_'   => 'i',
			'```' => 'pre',
			'`'   => 'code',
		];

		foreach ( $sections as $section ) {
			$options = WPTG()->options()->get( $section );

			if ( isset( $options['parse_mode'] ) && 'Markdown' === $options['parse_mode'] ) {

				$template = $options['message_template'];

				// Escape the HTML chars.
				$template = htmlspecialchars( $template, ENT_NOQUOTES, 'UTF-8' );

				$macro_map = [];

				if ( preg_match_all( '/\{[^\}]+?\}/iu', $template, $matches ) ) {

					$total = count( $matches[0] );
					// Replace the macros with temporary placeholders.
					// This is to prevent the markdown chars in macros from being replaced.
					// For example, if the macro is {post_title}, "_" will get replaced with "<i>". This is not desired.
					for ( $i = 0; $i < $total; $i++ ) {
						$macro_map[ "{:MACRO{$i}:}" ] = $matches[0][ $i ];
					}
				}

				// Replace the macros with temporary placeholders.
				$template = str_replace( array_values( $macro_map ), array_keys( $macro_map ), $template );

				// Convert links to html.
				$template = preg_replace( '/\[([^\]]+?)\]\(([^\)]+?)\)/ui', '<a href="${2}">${1}</a>', $template );

				foreach ( $markdown_v1_to_html_map as $char => $tag ) {
					if ( false === strpos( $template, $char ) ) {
						continue;
					}
					$placeholder = '{:' . $tag . ':}';
					// Replace the escaped chars  with temporary placeholders.
					$template = str_replace( '\\' . $char, $placeholder, $template );

					$regex_char = preg_quote( $char, '/' );

					// Create a regex pattern to match the chars.
					// The pattern is like: /_([^_]+?)_/ius and replaces it with <i>${1}</i>.
					$pattern = sprintf( '/%1$s([^%1$s]+?)%1$s/ius', $regex_char );
					// Replace the markdown v1 chars with html.
					$replace = sprintf( '<%1$s>${1}</%1$s>', $tag );

					$template = preg_replace( $pattern, $replace, $template );
					// Replace the temporary placeholders with the chars.
					$template = str_replace( $placeholder, $char, $template );
				}

				// Replace the macros with the original values.
				$template = str_replace( array_keys( $macro_map ), array_values( $macro_map ), $template );

				$template = stripslashes( $template );

				// Update the message template.
				$options['message_template'] = $template;
				// Set the parse mode to HTML.
				$options['parse_mode'] = 'HTML';

				// Update the options.
				WPTG()->options()->set( $section, $options );
			}
		}
	}

	/**
	 * Upgrade to 4.1.0
	 *
	 * - Upgrade to link_preview_options
	 *
	 * @since    4.1.0
	 */
	protected function upgrade_to_4_1_0() {
		$p2tg_settings = WPTG()->options()->get( 'p2tg' );

		$p2tg_settings['link_preview_disabled'] = isset( $p2tg_settings['disable_web_page_preview'] ) ? boolval( $p2tg_settings['disable_web_page_preview'] ) : false;

		unset( $p2tg_settings['disable_web_page_preview'] );

		// Update the options.
		WPTG()->options()->set( 'p2tg', $p2tg_settings );
	}
}
