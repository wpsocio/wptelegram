<?php
/**
 * Interface for an HTML converter.
 *
 * @package HtmlConverter
 */

namespace WPSocio\TelegramFormatText;

use InvalidArgumentException;

/**
 * Interface for an HTML converter.
 */
interface HtmlConverterInterface {

	/**
	 * Convert the given $html
	 *
	 * @param string $html The html to convert.
	 *
	 * @return string The desired version of the html
	 *
	 * @throws InvalidArgumentException If the html is invalid.
	 */
	public function convert( string $html );
}
