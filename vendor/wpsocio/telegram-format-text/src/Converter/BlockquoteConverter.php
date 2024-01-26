<?php
/**
 * Blockquote converter.
 *
 * @package WPSocio\TelegramFormatText\Converter
 */

namespace WPSocio\TelegramFormatText\Converter;

use WPSocio\TelegramFormatText\ElementInterface;

/**
 * Class BlockquoteConverter
 */
class BlockquoteConverter extends BaseConverter {

	/**
	 * {@inheritdoc}
	 */
	public function getSupportedTags() {
		return [ 'blockquote' ];
	}

	/**
	 * {@inheritdoc}
	 */
	public function convertToMarkdown( ElementInterface $element ) {
		$value = trim( $element->getValue() );

		// If this is a v1 format, don't emit, because v1 doesn't support blockquote.
		if ( 'v1' === $this->formattingToMarkdown() ) {
			return $value;
		}

		return '>' . $value;
	}
}
