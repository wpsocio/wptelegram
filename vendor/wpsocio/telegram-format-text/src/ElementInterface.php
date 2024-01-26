<?php
/**
 * ElementInterface
 *
 * @package WPSocio\TelegramFormatText
 */

namespace WPSocio\TelegramFormatText;

use DOMNode;

interface ElementInterface {

	/**
	 * Get the DOM Node.
	 *
	 * @return DOMNode - The DOM Node.
	 */
	public function getNode();

	/**
	 * Check if the element is a block element
	 *
	 * @return boolean - Whether the element is a block element
	 */
	public function isBlock();

	/**
	 * Check if the element is a text element
	 *
	 * @return boolean - Whether the element is a text element
	 */
	public function isText();

	/**
	 * Check if the element is whitespace
	 *
	 * @return boolean - Whether the element is whitespace
	 */
	public function isWhitespace();

	/**
	 * Get the tag name of the element
	 *
	 * @return string - The tag name of the element
	 */
	public function getTagName();

	/**
	 * Get the value of the element
	 *
	 * @return string - The value of the element
	 */
	public function getValue();

	/**
	 * Check if the element has a parent
	 *
	 * @return boolean - Whether the element has a parent
	 */
	public function hasParent();

	/**
	 * Get the parent element
	 *
	 * @return ElementInterface|null - The parent element, or null if it does not have one
	 */
	public function getParent();

	/**
	 * Get the next sibling element
	 *
	 * @return ElementInterface|null - The next sibling element, or null if it does not have one
	 */
	public function getNextSibling();

	/**
	 * Get the previous sibling element
	 *
	 * @return ElementInterface|null - The previous sibling element, or null if it does not have one
	 */
	public function getPreviousSibling();

	/**
	 * Check if the element is a descendant of an element with the given tag name(s)
	 *
	 * @param string|string[] $tagNames The tag name(s) to check for.
	 * @return boolean - Whether the element is a descendant of an element with the given tag name(s)
	 */
	public function isDescendantOf( $tagNames );

	/**
	 * Check if the element has children
	 *
	 * @return boolean - Whether the element has children
	 */
	public function hasChildren();

	/**
	 * Get an array of the element's children
	 *
	 * @return ElementInterface[] - An array of the element's children
	 */
	public function getChildren();

	/**
	 * Get the next element in the document
	 *
	 * @return ElementInterface|null - The next element in the document, or null if it does not have one
	 */
	public function getNext();

	/**
	 * Get the element's position among its siblings
	 *
	 * @return integer - The element's position among its siblings
	 */
	public function getSiblingPosition();

	/**
	 * Get a string representation of the element's children
	 *
	 * @return string - A string representation of the element's children
	 */
	public function getChildrenAsString();

	/**
	 * Used to determine the level of the list item.
	 *
	 * @return integer - The level of the list item.
	 */
	public function getListItemLevel();

	/**
	 * Get the attribute by name
	 *
	 * @param string $name The name of the attribute.
	 * @return string The value of the attribute
	 */
	public function getAttribute( string $name );

	/**
	 * Set the final output for this node.
	 *
	 * @param string $content The final output.
	 *
	 * @return void
	 */
	public function setFinalOutput( string $content );

	/**
	 * Get the next element node.
	 *
	 * @return ElementInterface|null
	 */
	public function getNextElement();

	/**
	 * Whether the element is a void element, i.e. it cannot have children.
	 *
	 * @see https://developer.mozilla.org/en-US/docs/Glossary/Void_element
	 *
	 * @return boolean
	 */
	public function isVoid();

	/**
	 * Whether the element is the same as the given element.
	 *
	 * @param ElementInterface $element The element to compare.
	 *
	 * @return boolean
	 */
	public function equals( ElementInterface $element );
}
