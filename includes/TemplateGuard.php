<?php
/**
 * Template Guard
 *
 * @link       https://wpsocio.com
 * @since      4.0.9
 *
 * @package    WPTelegram\Core
 * @subpackage WPTelegram\Core\includes
 */

namespace WPTelegram\Core\includes;

/**
 * This class is used to guard the template from being broken
 * during sanitization.
 *
 * @link       https://wpsocio.com
 * @since      4.0.9
 *
 * @package    WPTelegram\Core
 * @subpackage WPTelegram\Core\includes
 */
class TemplateGuard {

	/**
	 * The map of macros to their temporary placeholders.
	 *
	 * @var array $macro_map The map of macros to their temporary placeholders.
	 */
	protected $macro_map = [];

	/**
	 * Safeguard the template macros from being broken by wp_kses().
	 *
	 * For example wp_kses() can result in malformed template
	 * For example,
	 * <a href="{cf:_field_name}">Click here</a>
	 * gets converted to
	 * <a href="_field_name}">Click here</a>
	 * due to ":" in the href being treated as a part of some protocol.
	 *
	 * @since 4.0.9
	 *
	 * @param string $template The template to safeguard.
	 *
	 * @return string The safeguarded template.
	 */
	public function safeguard_macros( $template ) {

		$this->macro_map = [];

		// Match all macros in the template.
		if ( preg_match_all( '/\{[^\}]+?\}/iu', $template, $matches ) ) {

			$total = count( $matches[0] );
			// Replace the macros with temporary placeholders.
			for ( $i = 0; $i < $total; $i++ ) {
				$this->macro_map[ "##MACRO{$i}##" ] = $matches[0][ $i ];
			}
		}

		// Replace the macros with temporary placeholders.
		$safe_template = str_replace( array_values( $this->macro_map ), array_keys( $this->macro_map ), $template );

		return $safe_template;
	}

	/**
	 * Restore the template macros.
	 *
	 * @since 4.0.9
	 *
	 * @param string $template The template to restore.
	 *
	 * @return string The restored template.
	 */
	public function restore_macros( $template ) {

		// Restore the macros with the original values.
		$restored_template = str_replace( array_keys( $this->macro_map ), array_values( $this->macro_map ), $template );

		return $restored_template;
	}
}
