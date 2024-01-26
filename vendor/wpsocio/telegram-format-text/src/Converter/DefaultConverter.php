<?php
/**
 * Default converter.
 *
 * @package WPSocio\TelegramFormatText\Converter
 */

namespace WPSocio\TelegramFormatText\Converter;

/**
 * Class DefaultConverter
 */
class DefaultConverter extends BaseConverter {

	const DEFAULT_CONVERTER = '_default';

	/**
	 * {@inheritdoc}
	 */
	public function getSupportedTags() {
		return [ self::DEFAULT_CONVERTER ];
	}
}
