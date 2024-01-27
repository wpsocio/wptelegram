<?php
/**
 * Handles the plugin requirements.
 *
 * @link      https://wpsocio.com
 * @since     4.0.4
 *
 * @package WPTelegram
 * @subpackage WPTelegram\Core\includes
 */

namespace WPTelegram\Core\includes;

/**
 * Handles the plugin requirements.
 *
 * @package WPTelegram
 * @subpackage WPTelegram\Core\includes
 * @author   WP Socio
 */
class Requirements extends \WPSocio\WPUtils\Requirements {

	/**
	 * Sets the environment details.
	 *
	 * @return $this
	 */
	public function read_env() {
		$data = parent::read_env();

		$extensions = [];

		// Check for Required PHP extensions.
		foreach ( [ 'dom', 'mbstring' ] as $extension ) {
			$loaded = extension_loaded( $extension );

			$extensions[ $extension ] = $loaded;

			if ( $data['satisfied'] && ! $loaded ) {
				$data['satisfied'] = false;
			}
		}

		$data['PHP']['extensions'] = $extensions;

		return $data;
	}

	/**
	 * Get missing PHP extensions.
	 *
	 * @since 2.1.9
	 *
	 * @return array The missing PHP extensions.
	 */
	public function get_missing_extensions() {
		$env_details = $this->get_env_details();

		$missing = [];

		foreach ( $env_details['PHP']['extensions'] as $extension => $loaded ) {
			if ( ! $loaded ) {
				$missing[] = $extension;
			}
		}

		return $missing;
	}

	/**
	 * Display the requirements.
	 *
	 * @since 4.0.4
	 */
	public function display_requirements() {
		$env_details = $this->get_env_details();
		?>
		<tr class="plugin-update-tr">
			<td colspan="5" class="plugin-update colspanchange">
				<div class="update-message notice inline notice-error notice-alt" style="padding-block-end: 1rem;">
					<p>
						<?php esc_html_e( 'This plugin is not compatible with your website configuration.', 'wptelegram' ); ?>
					</p>
					<span><?php esc_html_e( 'Missing requirements', 'wptelegram' ); ?>&nbsp;👇</span>
					<ul style="list-style-type: disc; margin-inline-start: 2rem;">
						<?php
						foreach ( $env_details['data'] as $name => $requirement ) :
							if ( ! $requirement['satisfied'] ) :
								?>
								<li>
									<?php
									echo esc_html( $name );
									echo '&nbsp;&dash;&nbsp;';
									echo esc_html(
										sprintf(
										/* translators: %s: Version number */
											__( 'Current version: %s', 'wptelegram' ),
											$requirement['version']
										)
									);
									echo '&nbsp;&comma;&nbsp;';
									echo esc_html(
										sprintf(
										/* translators: %s: Version number */
											__( 'Minimum required version: %s', 'wptelegram' ),
											$requirement['min']
										)
									);
									?>
								</li>
								<?php
							endif;
						endforeach;
						$missing_extensions = $this->get_missing_extensions();

						if ( ! empty( $missing_extensions ) ) :
							?>
							<li>
								<?php
								echo esc_html(
									sprintf(
									/* translators: %s: comma separated list of missing extensions */
										__( 'Missing PHP extensions: %s', 'wptelegram' ),
										implode( ', ', $missing_extensions )
									)
								);
								?>
							</li>
							<?php
						endif;
						?>
					</ul>
					<span>
						<?php esc_html_e( 'Please contact your hosting provider to ensure the above requirements are met.', 'wptelegram' ); ?>
					</span>
				</div>
			</td>
		</tr>
		<?php
	}
}
