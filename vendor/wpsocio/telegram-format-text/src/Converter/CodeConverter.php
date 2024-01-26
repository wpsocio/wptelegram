<?php
/**
 * Code converter.
 *
 * @package WPSocio\TelegramFormatText\Converter
 */

namespace WPSocio\TelegramFormatText\Converter;

use WPSocio\TelegramFormatText\ElementInterface;

/**
 * Class CodeConverter
 */
class CodeConverter extends BaseConverter {

	/**
	 * {@inheritdoc}
	 */
	public function convertToMarkdown( ElementInterface $element ) {
		$language = '';

		// Checking for language class on the code block.
		$classes = $element->getAttribute( 'class' );

		if ( $classes ) {
			// Since tags can have more than one class, we need to find the one that starts with 'language-'.
			$classes = explode( ' ', $classes );
			foreach ( $classes as $class ) {
				if ( strpos( $class, 'language-' ) !== false ) {
					// Found one, save it as the selected language and stop looping over the classes.
					$language = str_replace( 'language-', '', $class );
					break;
				}
			}
		}

		$markdown = '';

		$code = $this->getCodeContent( $element );

		// Checking if it's a code block or span.
		if ( $this->shouldBeBlock( $element, $code ) ) {
			// Code block detected, newlines will be added in parent.
			$markdown .= '```' . $language . "\n" . $code . "\n" . '```';
		} else {
			// One line of code, wrapping it on one backtick, removing new lines.
			$markdown .= '`' . preg_replace( '/\r\n|\r|\n/', '', $code ) . '`';
		}

		return $markdown;
	}

	/**
	 * Get the code content.
	 *
	 * @param ElementInterface $element   The element that is being processed.
	 * @param boolean          $removeTag Whether to remove the code tags or not.
	 *
	 * @return string The code content.
	 */
	private function getCodeContent( ElementInterface $element, bool $removeTag = true ) {
		$code = html_entity_decode( $element->getChildrenAsString() );

		if ( $removeTag ) {
			// Remove the code tags at the beginning and end of the code.
			$code = preg_replace( '/(?:\A<code[^>]*?>|<\/code>\Z)/i', '', trim( $code ) );
		}

		$code = Utils::processPlaceholders( $code, 'add' );

		return $code;
	}

	/**
	 * If the parent element is a `<pre>` element, or if the code contains a backtick, then the code
	 * should be a block
	 *
	 * @param ElementInterface $element The element that is being processed.
	 * @param string           $code    The code to be highlighted.
	 *
	 * @return boolean A boolean value.
	 */
	private function shouldBeBlock( ElementInterface $element, string $code ) {
		$parent = $element->getParent();
		if ( null !== $parent && $parent->getTagName() === 'pre' ) {
			return true;
		}

		return preg_match( '/[^\s]` `/', $code ) === 1;
	}

	/**
	 * {@inheritdoc}
	 */
	public function convertToHtml( ElementInterface $element ) {
		$code = Utils::decodeHtmlEntities( $this->getCodeContent( $element, false ) );

		$pattern = '/(?P<startTag><code[^>]*?>)(?P<content>.+)(?P<endTag><\/code>)/i';

		if ( preg_match( $pattern, $code, $matches ) ) {
			// The code is already wrapped in a code tag, so we need to encode the HTML entities.
			return $matches['startTag'] . Utils::htmlSpecialChars( $matches['content'] ) . $matches['endTag'];
		}

		return $code;
	}

	/**
	 * {@inheritdoc}
	 */
	public function convertToText( ElementInterface $element ) {
		$code = $this->getCodeContent( $element );

		return Utils::decodeHtmlEntities( $code );
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSupportedTags() {
		return [ 'code' ];
	}
}
