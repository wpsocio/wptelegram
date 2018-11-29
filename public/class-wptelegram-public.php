<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://t.me/manzoorwanijk
 * @since      1.0.0
 *
 * @package    WPTelegram
 * @subpackage WPTelegram/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    WPTelegram
 * @subpackage WPTelegram/public
 * @author     Manzoor Wani <@manzoorwanijk>
 */
class WPTelegram_Public extends WPTelegram_Core_Base {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since	1.0.0
	 * @param 	string    $plugin_title	Title of the plugin
	 * @param	string    $plugin_name	The name of the plugin.
	 * @param	string    $version		The version of this plugin.
	 */
	public function __construct( $plugin_title, $plugin_name, $version ) {

		parent::__construct( $plugin_title, $plugin_name, $version );

        $this->sub_dir = 'public';
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		parent::enqueue_style( $this->plugin_name, $this->sub_dir );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		parent::enqueue_script( $this->plugin_name, $this->sub_dir, 'js', array( 'jquery' ) );

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

		// the sequential upgrades
		// subsequent upgrade depends upon the previous on
		$version_upgrades = array(
			'2.0.0', // first upgrade
		);

		// always
		if ( ! in_array( WPTELEGRAM_VER, $version_upgrades ) ) {
			$version_upgrades[] = WPTELEGRAM_VER;
		}
		
		foreach ( $version_upgrades as $target_version ) {
			
			if ( version_compare( $current_version, $target_version, '<' ) ) {

				$this->upgrade_to( $target_version );

				$current_version = $target_version;
			}
		}
	}

	/**
	 * Upgrade to a specific version
	 *
	 * @since    2.0.0
	 */
	private function upgrade_to( $version ) {

		// 2.0.1 becomes 201
		$_version = str_replace( '.', '', $version );

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
	private function upgrade_to_200() {

		$telegram_opts = get_option( 'wptelegram_telegram', array() );

		// possibly a new install
		if ( empty( $telegram_opts['bot_token'] ) ) {
			return;
		}

		$wptelegram['bot_token'] = $telegram_opts['bot_token'];

		// if P2TG should be active
		if ( ! empty( $telegram_opts['chat_ids'] ) ) {

			// activate p2tg
			$wptelegram['modules'][0]['p2tg'] = 'on';

			// migrate Telegram Destination
			$wptelegram_p2tg['channels'] = $telegram_opts['chat_ids'];
		}

		// fetch other stuff
		$wordpress_opts = get_option( 'wptelegram_wordpress', array() );

		/* Migrate Rules */
		// when
		if ( ! empty( $wordpress_opts['send_when'] ) ) {

			if ( in_array( 'send_new', $wordpress_opts['send_when'] ) ) {
				$wptelegram_p2tg['send_when'][] = 'new';
			}
			if ( in_array( 'send_updated', $wordpress_opts['send_when'] ) ) {
				$wptelegram_p2tg['send_when'][] = 'existing';
			}
		}

		// post_type
		if ( ! empty( $wordpress_opts['which_post_type'] ) ) {

			$wptelegram_p2tg['post_types'] = (array) $wordpress_opts['which_post_type'];
		}

		$author_rule = array();

		// Authors
		if ( ! empty( $wordpress_opts['from_authors'][0] ) && 'all' !== $wordpress_opts['from_authors'][0] && ! empty( $wordpress_opts['authors'] ) ) {

			$param = 'post_author';
			$operator = ( 'selected' == $wordpress_opts['from_authors'][0] ) ? 'in' : 'not_in';
			$values = $wordpress_opts['authors'];

			$author_rule = compact( 'param', 'operator', 'values' );
		}

		$taxonomy_rules = array();

		// categories
		if ( ! empty( $wordpress_opts['from_terms'][0] ) && 'all' !== $wordpress_opts['from_terms'][0] && ! empty( $wordpress_opts['terms'] ) ) {

			// taxonomy with their terms
			$tax_terms = array();

			foreach ( $wordpress_opts['terms'] as $term_tax ) {
				
				list( $term_id, $taxonomy ) = explode( '@', $term_tax );
				if ( 'category' === $taxonomy ) {
					$tax_terms[ $taxonomy ][] = $term_id;
				} else {
					$tax_terms[ 'tax:' . $taxonomy ][] = $term_id;
				}
			}

			$operator = ( 'selected' == $wordpress_opts['from_terms'][0] ) ? 'in' : 'not_in';

			foreach ( $tax_terms as $taxonomy => $terms ) {

				$param = $taxonomy;
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

		// if we do not have anything
		if ( empty( $rule_groups ) && ! empty( $author_rule ) ) {

			$rule_group = array();

			$rule_group[] = $author_rule;

			$rule_groups[] = $rule_group;
		}

		// if we have something
		if ( ! empty( $rule_groups ) ) {

			$wptelegram_p2tg['rules'] = $rule_groups;
		}

		/* Migrate Message settings */
		$message_opts = get_option( 'wptelegram_message', array() );
		$keys = array(
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

		// convert macros in template
		if ( ! empty( $wptelegram_p2tg[ 'message_template' ] ) ) {

			$template = json_decode( $wptelegram_p2tg[ 'message_template' ] );

			$macros = array(
				'title',
				'author',
				'excerpt',
				'content',
			);

			foreach ( $macros as $macro ) {
				
				$template = str_replace( '{' . $macro . '}', '{post_' . $macro . '}', $template );
			}

			// replace taxonomy macros
			if ( preg_match_all( '/(?<=\{\[)[a-z0-9_]+?(?=\]\})/iu', $template, $matches ) ) {

				foreach ( $matches[0] as $taxonomy ) {

					$template = str_replace( '{[' . $taxonomy . ']}', '{terms:' . $taxonomy . '}', $template );
				}
			}

			// replace custom fields macros
			if ( preg_match_all( '/(?<=\{\[\[).+?(?=\]\]\})/u', $template, $matches ) ) {

				foreach ( $matches[0] as $custom_field ) {

					$template = str_replace( '{[[' . $custom_field . ']]}', '{cf:' . $custom_field . '}', $template );
				}
			}

			$wptelegram_p2tg['message_template'] = WPTG()->helpers->sanitize_message_template( $template, false );
		}

		// now decide about single_message
		$wptelegram_p2tg[ 'single_message' ] = 0;
		if ( ( ! empty( $message_opts[ 'attach_image' ] ) && 'on' == $message_opts[ 'attach_image' ] ) || ( ! empty( $message_opts[ 'image_style' ] ) && 'with_caption' == $message_opts[ 'image_style' ] ) ) {

			$wptelegram_p2tg[ 'single_message' ] = 'on';
		}

		$notify_opts = get_option( 'wptelegram_notify', array() );
		// if Notify should be active
		if ( ! empty( $notify_opts['chat_ids'] ) && ! empty( $notify_opts['watch_emails'] ) ) {

			// activate notify
			$wptelegram['modules'][0]['notify'] = 'on';

			// migrate NOTIFICATION SETTINGS
			$wptelegram_notify['chat_ids'] = $notify_opts['chat_ids'];
			$wptelegram_notify['watch_emails'] = $notify_opts['watch_emails'];
			$wptelegram_notify['user_notifications'] = empty( $notify_opts['user_notifications'] ) ? 0 : $notify_opts['user_notifications'];

			$template = '🔔‌<b>{email_subject}</b>🔔' . PHP_EOL . PHP_EOL . '{email_message}';
			if ( ! empty( $notify_opts['hashtag'] ) ) {
				$template .= PHP_EOL . PHP_EOL . $notify_opts['hashtag'];
			}

			$wptelegram_notify['message_template'] = WPTG()->helpers->sanitize_message_template( $template, false );

			$wptelegram_notify['parse_mode'] = 'HTML';
		}

		// if proxy should be active
		if ( apply_filters( 'wptelegram_bot_api_use_proxy', false ) ) {
			// activate proxy
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

		// update the options
		$options = compact( 'wptelegram', 'wptelegram_p2tg', 'wptelegram_notify', 'wptelegram_proxy' );
		foreach ( $options as $option => $value ) {
			update_option( $option, $value );
		}
	}
}
