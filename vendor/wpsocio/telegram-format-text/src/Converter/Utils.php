<?php
/**
 * Utilities for converters.
 *
 * @package WPSocio\TelegramFormatText\Converter
 */

namespace WPSocio\TelegramFormatText\Converter;

use DOMDocument;
use DOMXPath;

/**
 * Class Utils
 */
class Utils {

	const PLACEHOLDERS = [
		'{:space:}' => ' ',
		'{:tab:}'   => "\t",
		'{:cr:}'    => "\r",
	];

	/**
	 * It processes the pre-defined placeholders in the given text.
	 *
	 * @param string $text   The text to process.
	 * @param string $action The action to take - 'add' or 'replace'.
	 *
	 * @return string The processed text.
	 */
	public static function processPlaceholders( string $text, string $action = 'replace' ) {

		$placeholders = array_keys( self::PLACEHOLDERS );
		$values       = array_values( self::PLACEHOLDERS );

		if ( 'add' === $action ) {
			$output = str_replace( $values, $placeholders, $text );
		} else {
			$output = str_replace( $placeholders, $values, $text );
		}

		return $output;
	}

	/**
	 * Decode HTML entities.
	 *
	 * @param string $value The value to decode.
	 *
	 * @return string The decoded value.
	 */
	public static function decodeHtmlEntities( string $value ) {

		return html_entity_decode( $value, ENT_QUOTES, 'UTF-8' );
	}

	/**
	 * Convert special characters to HTML entities.
	 *
	 * @param string $value The string to be converted.
	 *
	 * @return string The converted string.
	 */
	public static function htmlSpecialChars( string $value ) {

		return htmlspecialchars( $value, ENT_NOQUOTES, 'UTF-8' );
	}

	/**
	 * Check if PHP version is at least $version.
	 *
	 * @param  string $version PHP version string to compare.
	 *
	 * @return boolean Result of comparison check.
	 */
	public static function phpAtLeast( string $version ) {
		return version_compare( PHP_VERSION, $version, '>=' );
	}

	/**
	 * Parse style attribute
	 *
	 * @param string $style The style attribute.
	 *
	 * @return array The style attribute as an array.
	 */
	public static function parseStyle( string $style ) {
		$style_array = [];

		if ( empty( $style ) ) {
			return $style_array;
		}

		$parts = explode( ';', $style );

		foreach ( $parts as $part ) {
			$part = trim( $part );

			if ( empty( $part ) ) {
				continue;
			}

			list( $key, $value ) = array_map( 'trim', array_pad( explode( ':', $part, 2 ), 2, '' ) );

			if ( ! empty( $key ) ) {
				$style_array[ $key ] = $value;
			}
		}

		return $style_array;
	}

	/**
	 * Limit the text content of the given DOMDocument to the given number of words or characters.
	 *
	 * @param DOMDocument $document The DOMDocument to limit. It is modified in place.
	 * @param string      $limitBy  The type of limit to apply. Can be 'words' or 'chars'.
	 * @param integer     $limit    The number of words or chars to limit to.
	 *
	 * @return void
	 */
	public static function limitContentBy( DOMDocument $document, string $limitBy, int $limit ) {
		// Create a new DOMXPath object from the DOMDocument.
		$xpath = new DOMXPath( $document );
		// Get all text nodes in the DOMDocument.
		$textNodes = $xpath->query( '//text()' );
		// Initialize the count.
		$count = 0;

		$limitReached = false;

		// Iterate over each text node.
		for ( $i = 0; $i < $textNodes->length; $i++ ) {
			$textNode = $textNodes->item( $i );
			if ( $limitReached ) {
				$textNode->parentNode->removeChild( $textNode );
				continue;
			}
			// Get the length of the text node's value.
			$nodeLength = 'words' === $limitBy ? self::strWordCount( $textNode->nodeValue ) : mb_strlen( $textNode->nodeValue );

			// If the new count becomes greater than the limit.
			if ( ( $count + $nodeLength ) >= $limit ) {
				// Calculate the number of items to retain.
				$numberToRetain = $limit - $count - 1; // -1 for the ellipsis

				if ( $numberToRetain > 0 ) {

					// Set the value of the text node to the truncated text.
					$textNode->nodeValue = self::limitTextBy( $textNode->nodeValue, $limitBy, $numberToRetain );
				} else {
					$textNode->parentNode->removeChild( $textNode );
				}

				// Set the limit reached flag to true.
				$limitReached = true;
			}

			// Add the length of the text node's value to the count.
			$count += $nodeLength;
		}

		self::removeEmptyNodes( $document );
	}

	/**
	 * Count the number of words in the given string.
	 *
	 * Works with any locale unlike str_word_count().
	 *
	 * @param string $str The string to count the words in.
	 *
	 * @return integer The number of words in the string.
	 */
	public static function strWordCount( string $str ) {
		return count( preg_split( '/[\s\n\r]+/u', $str ) );
	}

	/**
	 * Limit the text string to the given number of words or characters.
	 * It preserves the words and doesn't cut them off.
	 *
	 * @param string  $text    The text to limit.
	 * @param string  $limitBy The type of limit to apply. Can be 'words' or 'chars'.
	 * @param integer $limit   The number of words or chars to limit to.
	 *
	 * @return string The limited text.
	 */
	public static function limitTextBy( string $text, string $limitBy, int $limit ) {

		// Get the length of the text.
		$textLength = 'words' === $limitBy ? self::strWordCount( $text ) : mb_strlen( $text );

		// If the text is shorter than the limit, return the text.
		if ( $textLength <= $limit || $limit < 1 ) {
			return $text;
		}

		// Truncate the text after the last space before the limit.
		$pattern = 'words' === $limitBy ? '/((?:[\n\r\t\s]*[^\n\r\t\s]+){1,' . $limit . '}).*/su' : '/(.{1,' . $limit . '}(?:\s|$)).*/su';

		return preg_replace( $pattern, '${1}', $text );
	}

	/**
	 * Remove empty nodes from the given DOMDocument.
	 *
	 * @param DOMDocument $document The DOMDocument to remove empty nodes from.
	 *
	 * @return void
	 */
	public static function removeEmptyNodes( DOMDocument $document ) {

		// Create a new DOMXPath object from the DOMDocument.
		$xpath = new DOMXPath( $document );
		// XPath expression to select nodes with empty text content.
		$expression = '/child::*//*[not(*) and not(text()[normalize-space()])]';
		// Get a list of nodes that match the XPath expression.
		$nodeList = $xpath->query( $expression );

		// Iterate over the list of nodes and remove them from the DOM.
		while ( $nodeList && $nodeList->length ) {
			foreach ( $nodeList as $node ) {
				$node->parentNode->removeChild( $node );
			}
			$nodeList = $xpath->query( $expression );
		}
	}

	/**
	 * Prepare a string for use as a regular expression replacement.
	 * It escapes the "$" and "\" characters to avoid it being interpreted as a backreference.
	 *
	 * @param string $text The text to prepare.
	 *
	 * @return string
	 */
	public static function preparePregReplacement( string $text ) {
		return addcslashes( $text, '\\$' );
	}
}
