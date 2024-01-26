<?php
/**
 * Comment converter.
 *
 * @package WPSocio\TelegramFormatText\Converter
 */

namespace WPSocio\TelegramFormatText\Converter;

use WPSocio\TelegramFormatText\ElementInterface;

/**
 * Class CommentConverter
 */
class CommentConverter extends BaseConverter {

	/**
	 * {@inheritdoc}
	 */
	public function convert( ElementInterface $element ) {
		return '';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSupportedTags() {
		return [ '#comment' ];
	}
}
