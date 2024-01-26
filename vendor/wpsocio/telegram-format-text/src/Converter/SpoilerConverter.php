<?php
/**
 * Spoiler converter.
 *
 * @package WPSocio\TelegramFormatText\Converter
 */

namespace WPSocio\TelegramFormatText\Converter;

use WPSocio\TelegramFormatText\ElementInterface;

/**
 * Class SpoilerConverter
 */
class SpoilerConverter extends BaseConverter {

	/**
	 * {@inheritdoc}
	 */
	public function convert( ElementInterface $element ) {
		$value = $element->getValue();

		if ( '' === trim( $value ) ) {
			return '';
		}

		$tag = $element->getTagName();

		if ( 'span' === $tag && strpos( $element->getAttribute( 'class' ), 'tg-spoiler' ) === false ) {
			return $element->getValue();
		}

		return parent::convert( $element );
	}

	/**
	 * {@inheritdoc}
	 */
	public function convertToMarkdown( ElementInterface $element ) {
		$value = $element->getValue();

		// If this is a v1 format, don't emit, because v1 doesn't support spoiler.
		if ( 'v1' === $this->formattingToMarkdown() ) {
			return $value;
		}

		return '||' . $value . '||';
	}

	/**
	 * {@inheritdoc}
	 */
	public function convertToHtml( ElementInterface $element ) {

		return sprintf( '<tg-spoiler>%1$s</tg-spoiler>', $element->getValue() );
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSupportedTags() {
		return [ 'tg-spoiler', 'span' ];
	}
}
