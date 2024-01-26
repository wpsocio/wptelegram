<?php
/**
 * Base converter.
 *
 * @package WPSocio\TelegramFormatText\Converter
 */

namespace WPSocio\TelegramFormatText\Converter;

use WPSocio\TelegramFormatText\Configuration;
use WPSocio\TelegramFormatText\ElementInterface;

/**
 * Class BaseConverter
 */
abstract class BaseConverter implements ConverterInterface {

	const HTML_TO_MARKDOWN_V1_MAP = [
		// bold.
		'b'      => '*',
		'strong' => '*',
		// italic.
		'i'      => '_',
		'em'     => '_',
		// code.
		'code'   => '`',
		'pre'    => '```',
	];

	const HTML_TO_MARKDOWN_V2_MAP = [
		// underline.
		'u'          => '__',
		'ins'        => '__',
		// strikethrough.
		'del'        => '~',
		's'          => '~',
		'strike'     => '~',
		// blockquote.
		'blockquote' => '>',
	] + self::HTML_TO_MARKDOWN_V1_MAP;

	/**
	 * Special characters in Telegram Markdown.
	 *
	 * @var string[] Markdown special characters.
	 */
	const MARKDOWN_V1_SPECIAL_CHARS = [ '_', '*', '`', '[' ];

	/**
	 * Special characters in Telegram MarkdownV2.
	 *
	 * @var string[] Markdown special characters.
	 */
	const MARKDOWN_V2_SPECIAL_CHARS = [ '_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!', '\\' ];

	/**
	 * The configuration.
	 *
	 * @var Configuration
	 */
	protected $config;

	/**
	 * {@inheritdoc}
	 */
	public function setConfig( Configuration $config ) {
		$this->config = $config;
	}

	/**
	 * {@inheritdoc}
	 */
	public function convert( ElementInterface $element ) {
		if ( $this->formattingToHtml() ) {
			return $this->convertToHtml( $element );
		}

		if ( $this->formattingToMarkdown() ) {
			return $this->convertToMarkdown( $element );
		}

		return $this->convertToText( $element );
	}

	/**
	 * Get the supported rquivalent tags for MarkdownV1.
	 *
	 * @return string[] An array of the supported tags
	 */
	protected function getV1SupportedTags() {
		return array_merge( array_keys( self::HTML_TO_MARKDOWN_V1_MAP ), [ 'a' ] );
	}

	/**
	 * Whether we are formatting to text.
	 *
	 * @return boolean Whether we are formatting to text.
	 */
	protected function formattingToText() {

		return ! $this->formattingToMarkdown() && ! $this->formattingToHtml();
	}

	/**
	 * Whether we are formatting to Markdown.
	 *
	 * @return string|false - The version of Markdown or false.
	 */
	protected function formattingToMarkdown() {
		$formatTo = $this->config->getOption( 'format_to' );

		if ( 'Markdown' === $formatTo ) {
			return 'v1';
		}

		if ( 'MarkdownV2' === $formatTo ) {
			return 'v2';
		}

		return false;
	}

	/**
	 * Whether we are formatting to HTML.
	 *
	 * @return boolean Whether we are formatting to HTML.
	 */
	protected function formattingToHtml() {
		$formatTo = $this->config->getOption( 'format_to' );

		return 'HTML' === $formatTo;
	}

	/**
	 * Convert the given element.
	 *
	 * @param ElementInterface $element The element to convert.
	 *
	 * @return string The converted element.
	 */
	private function convertElement( ElementInterface $element ) {

		$value = $element->getValue();

		if ( '' === trim( $value ) ) {
			return $value;
		}

		if ( $element->isDescendantOf( [ 'head' ] ) ) {
			return '';
		}

		if ( $element->isBlock() ) {
			$value = "\n" . trim( $value ) . "\n";
		}

		if ( $this->formattingToText() ) {
			return $value;
		}

		$tag = $element->getTagName();

		$formattingToMd = $this->formattingToMarkdown();

		$markdownMap = 'v1' === $formattingToMd ? self::HTML_TO_MARKDOWN_V1_MAP : self::HTML_TO_MARKDOWN_V2_MAP;

		$isTelegramTag = array_key_exists( $tag, $markdownMap );

		if ( ! $isTelegramTag ) {
			return $value;
		}

		if ( $formattingToMd ) {

			$markdownChar = $markdownMap[ $tag ];

			return $markdownChar . $value . $markdownChar;
		}

		// We are formatting to HTML.
		return sprintf( '<%1$s>%2$s</%1$s>', $tag, $value );
	}

	/**
	 * {@inheritdoc}
	 */
	protected function convertToMarkdown( ElementInterface $element ) {
		return $this->convertElement( $element );
	}

	/**
	 * {@inheritdoc}
	 */
	protected function convertToHtml( ElementInterface $element ) {
		return $this->convertElement( $element );
	}

	/**
	 * {@inheritdoc}
	 */
	protected function convertToText( ElementInterface $element ) {
		return $this->convertElement( $element );
	}

	/**
	 * Escape special characters in Telegram Markdown.
	 *
	 * @param string $text         The text to escape.
	 * @param string $parentEntity The parent entity (Markdown v1 character) for which we are escaping.
	 * @param array  $characters   The characters to escape.
	 *
	 * @return string The escaped text.
	 */
	protected function escapeMarkdownChars( string $text, string $parentEntity = '', array $characters = [] ) {

		$formattingToMarkdown = $this->formattingToMarkdown();

		// if we are not converting to Markdown, no need to escape.
		if ( ! $formattingToMarkdown ) {
			return $text;
		}

		if ( 'v1' === $formattingToMarkdown && $parentEntity ) {
			// Close the parent entity first, then escape the entity character, then open the entity again.
			return str_replace( $parentEntity, $parentEntity . '\\' . $parentEntity . $parentEntity, $text );
		}

		$special_chars = $characters;

		if ( count( $special_chars ) === 0 ) {
			$special_chars = 'v1' === $formattingToMarkdown ? self::MARKDOWN_V1_SPECIAL_CHARS : self::MARKDOWN_V2_SPECIAL_CHARS;
		}

		// Escape the special characters in Telegram Markdown.
		$markdown_search = array_map(
			function ( $value ) {
				return preg_quote( $value, '/' );
			},
			$special_chars
		);
		$markdown_search = '/(' . implode( '|', $markdown_search ) . ')/';

		return preg_replace( $markdown_search, '\\\\${1}', $text );
	}

	/**
	 * {@inheritdoc}
	 */
	abstract public function getSupportedTags();
}
