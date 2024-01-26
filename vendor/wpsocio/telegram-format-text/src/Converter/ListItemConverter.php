<?php
/**
 * List item converter.
 *
 * @package WPSocio\TelegramFormatText\Converter
 */

namespace WPSocio\TelegramFormatText\Converter;

use WPSocio\TelegramFormatText\ElementInterface;

/**
 * Class ListItemConverter
 */
class ListItemConverter extends BaseConverter {

	/**
	 * {@inheritdoc}
	 */
	public function convert( ElementInterface $element ) {

		$parent = $element->getParent();

		// If parent is an ol, use numbers, otherwise, use dashes.
		$listType = $parent ? $parent->getTagName() : 'ul';

		// Add spaces to start for nested list items.
		$level = $element->getListItemLevel();

		$siblingPosition = $element->getSiblingPosition();

		$value = $this->getCleanValue( $element );

		// If list item is the first in a nested list, add a newline before it.
		$prefix = '';
		if ( $level > 0 && 1 === $siblingPosition ) {
			$prefix = "\n";
		}

		if ( 'ul' === $listType ) {
			$listItemStyle    = strval( $this->config->getOption( 'list_item_style', '-' ) );
			$subListItemStyle = strval( $this->config->getOption( 'sub_list_item_style', '◦' ) );

			// Use a different style for odd and even levels.
			$itemStyle = $level % 2 ? $subListItemStyle : $listItemStyle;

			return $prefix . $this->escapeMarkdownChars( $itemStyle ) . ' ' . $value . "\n";
		}

		$start = $parent ? intval( $parent->getAttribute( 'start' ) ) : 0;

		if ( 'ol' === $listType && $start ) {
			$number = $start + $siblingPosition - 1;
		} else {
			$number = $siblingPosition;
		}

		return $prefix . $number . $this->escapeMarkdownChars( '.' ) . ' ' . $value . "\n";
	}

	/**
	 * Get clean value.
	 *
	 * @param ElementInterface $element The element.
	 *
	 * @return string The clean value.
	 */
	private function getCleanValue( ElementInterface $element ) {
		// Remove leading and trailing spaces.
		$value = trim( $element->getValue() );

		// Break into lines.
		$value = explode( "\n", $value );

		// Remove leading and trailing spaces from each line.
		$value = array_map( 'trim', $value );

		// Remove empty lines.
		$value = array_filter( $value );

		// Add spaces to start for nested list items.
		$glue = "\n" . str_repeat( '{:space:}', 4 );

		// Implode the lines.
		$value = trim( implode( $glue, $value ) );

		return $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSupportedTags() {
		return [ 'li' ];
	}
}
