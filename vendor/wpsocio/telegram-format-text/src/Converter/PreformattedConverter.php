<?php
/**
 * Preformatted converter.
 *
 * @package WPSocio\TelegramFormatText\Converter
 */

namespace WPSocio\TelegramFormatText\Converter;

use WPSocio\TelegramFormatText\ElementInterface;

/**
 * Class PreformattedConverter
 */
class PreformattedConverter extends BaseConverter {

	/**
	 * {@inheritdoc}
	 */
	public function convertToMarkdown( ElementInterface $element ) {
		$preContent = html_entity_decode( $element->getChildrenAsString() );
		$preContent = preg_replace( '/<\/?pre[^>]*?>/i', '', $preContent );

		/*
		 * Checking for the code tag.
		 * Usually pre tags are used along with code tags. This conditional will check for already converted code tags,
		 * which use backticks, and if those backticks are at the beginning and at the end of the string it means
		 * there's no more information to convert.
		 */

		$firstBacktick = strpos( trim( $preContent ), '`' );
		$lastBacktick  = strrpos( trim( $preContent ), '`' );
		if ( 0 === $firstBacktick && ( strlen( trim( $preContent ) ) - 1 ) === $lastBacktick ) {
			return $preContent . "\n\n";
		}

		// If the execution reaches this point it means it's just a pre tag, with no code tag nested.

		// Empty lines are a special case.
		if ( '' === $preContent ) {
			return "```\n```\n\n";
		}

		// Normalizing new lines.
		$preContent = preg_replace( '/\r\n|\r|\n/', "\n", $preContent );

		// Ensure there's a newline at the end.
		if ( strrpos( $preContent, "\n" ) !== strlen( $preContent ) - strlen( "\n" ) ) {
			$preContent .= "\n";
		}

		// Use three backticks.
		return "```\n" . $preContent . "```\n\n";
	}

	/**
	 * {@inheritdoc}
	 */
	public function convertToText( ElementInterface $element ) {
		$value = $element->getValue();

		return Utils::decodeHtmlEntities( $value );
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSupportedTags() {
		return [ 'pre' ];
	}
}
