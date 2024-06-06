<?php
/**
 * Image converter.
 *
 * @package WPSocio\TelegramFormatText\Converter
 */

namespace WPSocio\TelegramFormatText\Converter;

use WPSocio\TelegramFormatText\ElementInterface;

/**
 * Class LinkConverter
 */
class ImageConverter extends BaseConverter {

	/**
	 * {@inheritdoc}
	 */
	public function convert( ElementInterface $element ) {
		$imagesInLinks = $this->config->getOption( 'images_in_links', [] );

		$retainTitleOrAlt    = isset( $imagesInLinks['title_or_alt'] ) && 'retain' === $imagesInLinks['title_or_alt'];
		$retainLoneImageLink = isset( $imagesInLinks['lone_image_link'] ) && 'retain' === $imagesInLinks['lone_image_link'];

		list($src, $text ) = $this->getImageInfo( $element );

		// If the image is inside a link, return the image text if present.
		if ( $retainTitleOrAlt && $element->isDescendantOf( [ 'a' ] ) && $text ) {
			return $text;
		}

		// If the image is the only child of the parent link, return the image link.
		if ( $retainLoneImageLink && $this->isOnlyChildOfLink( $element ) ) {
			return $src;
		}

		return '';
	}

	/**
	 * Get image info.
	 *
	 * @param ElementInterface $element The element.
	 *
	 * @return array - The image info.
	 */
	private function getImageInfo( ElementInterface $element ) {
		$src   = trim( $element->getAttribute( 'src' ) );
		$alt   = trim( $element->getAttribute( 'alt' ) );
		$title = trim( $element->getAttribute( 'title' ) );

		$text = $title ? $title : $alt;

		return [ $src, $text ];
	}

	/**
	 * Whether the image is the only child of the parent link.
	 *
	 * @param ElementInterface $element The element.
	 *
	 * @return boolean
	 */
	private function isOnlyChildOfLink( ElementInterface $element ) {
		$parent = $element->getParent();

		if ( $parent && 'a' === $parent->getTagName() ) {
			$children = $parent->getChildren();

			foreach ( $children as $child ) {
				if ( ! $child->isWhitespace() && ! $child->equals( $element ) ) {
					return false;
				}
			}
			return true;
		}
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSupportedTags() {
		return [ 'img' ];
	}
}
