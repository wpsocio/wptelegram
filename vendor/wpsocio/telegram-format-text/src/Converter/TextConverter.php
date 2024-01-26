<?php
/**
 * Text converter.
 *
 * @package WPSocio\TelegramFormatText\Converter
 */

namespace WPSocio\TelegramFormatText\Converter;

use WPSocio\TelegramFormatText\ElementInterface;


/**
 * Class TextConverter
 */
class TextConverter extends BaseConverter {

	/**
	 * The tags that don't have text nodes as children.
	 */
	const TAGS_WITH_NO_TEXT = [
		'col',
		'colgroup',
		'ol',
		'table',
		'tbody',
		'tfoot',
		'thead',
		'tr',
		'ul',
	];

	/**
	 * {@inheritdoc}
	 */
	public function convertToMarkdown( ElementInterface $element ) {

		$markdown = $this->getCleanValue( $element );

		$parent = $element->getParent();

		$parentTag = $parent->getTagName();

		$isLinkOrCode     = $element->isDescendantOf( [ 'code', 'a', 'pre' ] );
		$willParentEscape = $parent->isDescendantOf( $this->getV1SupportedTags() );
		$isMdV1Format     = 'v1' === $this->formattingToMarkdown();

		$escape       = true;
		$escapeEntity = '';

		// If the text is inside another element supported by Telegram Markdown v1, don't escape markdown chars.
		if ( $isMdV1Format ) {
			if ( $isLinkOrCode || $willParentEscape ) {
				$escape = false;
			} else {
				$escapeEntity = array_key_exists( $parentTag, self::HTML_TO_MARKDOWN_V1_MAP ) ? self::HTML_TO_MARKDOWN_V1_MAP[ $parentTag ] : '';
			}
		}

		if ( $escape ) {
			$markdown = $this->escapeMarkdownChars( $markdown, $escapeEntity );
		}

		return Utils::decodeHtmlEntities( $markdown );
	}

	/**
	 * {@inheritdoc}
	 */
	public function convertToHtml( ElementInterface $element ) {

		$value = $this->getCleanValue( $element );

		return Utils::htmlSpecialChars( Utils::decodeHtmlEntities( $value ) );
	}

	/**
	 * {@inheritdoc}
	 */
	public function convertToText( ElementInterface $element ) {

		$value = Utils::decodeHtmlEntities( $this->getCleanValue( $element ) );

		// Don't strip tags inside pre and code.
		if ( ! $element->isDescendantOf( [ 'pre', 'code' ] ) ) {
			$value = strip_tags( $value );
		}

		return $value;
	}

	/**
	 * Get clean value.
	 *
	 * @param ElementInterface $element The element.
	 *
	 * @return string - The clean value.
	 */
	private function getCleanValue( ElementInterface $element ) {
		$value = $element->getValue();

		$parent = $element->getParent();

		if ( $parent && in_array( $parent->getTagName(), self::TAGS_WITH_NO_TEXT, true ) ) {
			return '';
		}

		return $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSupportedTags() {
		return [ '#text' ];
	}
}
