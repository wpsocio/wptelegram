<?php
/**
 * ConverterInterface
 *
 * @package WPSocio\TelegramFormatText\Converter
 */

namespace WPSocio\TelegramFormatText\Converter;

use WPSocio\TelegramFormatText\Configuration;
use WPSocio\TelegramFormatText\ElementInterface;

interface ConverterInterface {

	/**
	 * Set the configuration.
	 *
	 * @param Configuration $config The configuration.
	 *
	 * @return void
	 */
	public function setConfig( Configuration $config );

	/**
	 * Convert the given element.
	 *
	 * @param ElementInterface $element The element to convert.
	 * @return string - The converted string
	 */
	public function convert( ElementInterface $element );

	/**
	 * Get an array of the tags that this converter supports
	 *
	 * @return string[] - An array of the supported tags
	 */
	public function getSupportedTags();
}
