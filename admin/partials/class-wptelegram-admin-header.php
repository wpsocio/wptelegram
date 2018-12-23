<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://t.me/manzoorwanijk
 * @since      2.0.13
 *
 * @package    WPTelegram
 * @subpackage WPTelegram/admin
 */

/**
 * The header of the settings pages
 *
 * Renders the header on the plugin settings pages
 *
 * @package    WPTelegram
 * @subpackage WPTelegram/admin
 * @author     Manzoor Wani <@manzoorwanijk>
 */
class WPTelegram_Admin_Header {

    /**
     * The plugin instance
     *
     * Used to fetch title version etc.
     *
     * @since    2.0.13
     * @access   protected
     * @var      mixed    $plugin    The main instance of the plugin
     */
    protected $plugin;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since	2.0.13
	 * @param 	mixed	$plugin	The plugin instance
	 */
	public function __construct( $plugin ) {

        $this->plugin = $plugin;
	}

	/**
	 * Render the header
	 *
	 * @since    2.0.13
	 */
	public function render() {

		$tabs = array(
			'logo',
			'title',
			'social',
		);

		?>
		<div class="wptelegram-header-wrapper wptelegram-box">
			<table>
				<tr>
					<?php foreach ( $tabs as $tab ) : ?>
						<td <?php if ( 'title' === $tab ) echo 'colspan="2"'; ?>><?php call_user_func( array( $this, "render_{$tab}" ) ); ?></td>
					<?php endforeach; ?>
				</tr>
			</table>
		</div>
		<?php
	}

	/**
	 * Render the header
	 *
	 * @since    2.0.13
	 */
	public function render_logo() {
		?>
		<div class="wptelegram-logo">
			<img src="<?php echo esc_url( $this->plugin->get_url() . '/admin/icons/icon-100x100.svg' ); ?>" alt="<?php echo $this->plugin->get_plugin_title(); ?>" />
		</div>
		<?php
	}

	/**
	 * Render the title
	 *
	 * @since    2.0.13
	 */
	public function render_title() {
		?>
		<div class="wptelegram-title"><h1><?php echo esc_html( sprintf( '%1$s %2$s', $this->plugin->get_plugin_title(), $this->plugin->get_version() ) ); ?></h1></div>
		<?php
	}

	/**
	 * Render the social
	 *
	 * @since    2.0.13
	 */
	public function render_social() {
		?>
		<div class="wptelegram-socials">
			<p><?php esc_html_e( 'Get connected', $this->plugin->get_text_domain() ) ?></p>
			<div class="wptelegram-social-bttns">
				<ul style="list-style-type: none">
					<li>
					   <div class="fb-like" data-href="https://www.facebook.com/WPTelegram" data-layout="button_count" data-action="like" data-size="small" data-show-faces="false" data-share="false"></div>
					</li>
					<li>
						<a href="https://twitter.com/WPTelegram" class="twitter-follow-button" data-show-count="false"><?php printf( __( 'Follow %s', $this->plugin->get_text_domain() ), '@WPTelegram' ); ?></a>
					</li>
					<li>
						<a href="https://t.me/WPTelegram" class="telegram-follow-button btn" target="_blank">
                        <img src="<?php echo esc_url( $this->plugin->get_url() . '/admin/icons/tg-icon.svg' ); ?>" alt="<?php echo $this->plugin->get_plugin_title(); ?>" />&nbsp;<?php printf( __( 'Join %s', $this->plugin->get_text_domain() ), '@WPTelegram' ); ?></a>
					</li>
				</ul>
				<div id="fb-root"></div>
				<script>(function(d, s, id) {
				  var js, fjs = d.getElementsByTagName(s)[0];
				  if (d.getElementById(id)) return;
				  js = d.createElement(s); js.id = id;
				  js.src = "//connect.facebook.net/en_GB/sdk.js#xfbml=1&version=v2.9";
				  fjs.parentNode.insertBefore(js, fjs);
				}(document, 'script', 'facebook-jssdk'));</script>
				<script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
			</div>
		</div>
		<?php
	}
}
