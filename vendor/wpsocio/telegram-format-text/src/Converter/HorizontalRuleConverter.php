<?php
/**
 * Horizontal rule converter.
 *
 * @package WPSocio\TelegramFormatText\Converter
 */

namespace WPSocio\TelegramFormatText\Converter;

use WPSocio\TelegramFormatText\ElementInterface;

/**
 * Class HorizontalRuleConverter
 */
class HorizontalRuleConverter extends BaseConverter {

	/**
	 * {@inheritdoc}
	 */
	public function convert( ElementInterface $element ) {

		$output = "\n-------------\n\n";

		if ( $this->formattingToMarkdown() ) {
			$output = $this->escapeMarkdownChars( $output );
		}

		return $output;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSupportedTags() {
		return [ 'hr' ];
	}
}
