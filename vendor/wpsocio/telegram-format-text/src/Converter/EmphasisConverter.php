<?php
/**
 * Emphasis converter.
 *
 * @package WPSocio\TelegramFormatText\Converter
 */

namespace WPSocio\TelegramFormatText\Converter;

use WPSocio\TelegramFormatText\ElementInterface;

/**
 * Class EmphasisConverter
 */
class EmphasisConverter extends BaseConverter {

	/**
	 * Get the normalized tag name.
	 *
	 * @param ElementInterface|null $element The element.
	 *
	 * @return string The normalized tag name.
	 */
	protected function getNormTag( $element ) {
		if ( null !== $element && ! $element->isText() ) {
			$tag = $element->getTagName();
			if ( 'i' === $tag || 'em' === $tag ) {
				return 'em';
			}

			if ( 'b' === $tag || 'strong' === $tag ) {
				return 'b';
			}
		}

		return '';
	}

	/**
	 * {@inheritdoc}
	 */
	public function convertToMarkdown( ElementInterface $element ) {
		$tag   = $this->getNormTag( $element );
		$value = $element->getValue();

		if ( '' === trim( $value ) ) {
			return '';
		}

		// If this node is a descendant of the same tag, don't emit the tag.
		// This prevents <b>foo <b>bar</b></b> from being converted to *foo *bar**
		// which is incorrect. We want *foo bar* instead.
		if ( $element->isDescendantOf( $tag ) ) {
			return $value;
		}

		$isNestedTag  = $element->isDescendantOf( $this->getV1SupportedTags() );
		$isMdV1Format = 'v1' === $this->formattingToMarkdown();

		// Markdown v1 doesn't support nested tags.
		if ( $isMdV1Format && $isNestedTag ) {
			return $value;
		}

		$style = 'em' === $tag ? '_' : '*';

		$prefix = ltrim( $value ) !== $value ? ' ' : '';
		$suffix = rtrim( $value ) !== $value ? ' ' : '';

		/*
		 * If this node is immediately preceded or followed by one of the same type don't emit
		 * the start or end $style, respectively. This prevents <em>foo</em><em>bar</em> from
		 * being converted to *foo**bar* which is incorrect. We want *foobar* instead.
		 */
		$preStyle  = $this->getNormTag( $element->getPreviousSibling() ) === $tag ? '' : $style;
		$postStyle = $this->getNormTag( $element->getNextSibling() ) === $tag ? '' : $style;

		if ( '_' === $postStyle && in_array( $element->getParent()->getTagName(), [ 'u', 'ins' ], true ) ) {
			$postStyle .= preg_match( '/' . preg_quote( $value, '/' ) . '$/', $element->getParent()->getValue() ) ? '{:cr:}' : '';
		}

		return $prefix . $preStyle . trim( $value ) . $postStyle . $suffix;
	}

	/**
	 * {@inheritdoc}
	 */
	public function convertToHtml( ElementInterface $element ) {
		$tag   = $this->getNormTag( $element );
		$value = $element->getValue();

		if ( '' === trim( $value ) ) {
			return $value;
		}

		// If this node is a descendant of the same tag, don't emit the tag.
		// This prevents <em>foo <em>bar</em></em> from being converted to <em>foo <em>bar</em></em>
		// which is incorrect/useless. We want <em>foo bar</em> instead.
		if ( $element->isDescendantOf( $tag ) ) {
			return $value;
		}

		return sprintf( '<%1$s>%2$s</%1$s>', $tag, $value );
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSupportedTags() {
		return [ 'em', 'i', 'strong', 'b' ];
	}
}
