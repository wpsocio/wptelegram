<?php
/**
 * A helper class to convert HTML
 *
 * @package HtmlConverter
 */

namespace WPSocio\TelegramFormatText;

use DOMDocument;
use WPSocio\TelegramFormatText\Converter\Utils;
use WPSocio\TelegramFormatText\Exceptions\ConverterException;

/**
 * A helper class to convert HTML
 */
class HtmlConverter implements HtmlConverterInterface {

	/**
	 * The maximum length of the text of a Telegram message.
	 */
	const TG_TEXT_MAX_LENGTH = 4096;

	/**
	 * The maximum length of the caption of a Telegram message/media.
	 */
	const TG_CAPTION_MAX_LENGTH = 1024;

	/**
	 * The environment
	 *
	 * @var Environment
	 */
	protected $environment;

	/**
	 * Constructor
	 *
	 * @param Environment|array<string, mixed> $options Environment object or configuration options.
	 */
	public function __construct( $options = [] ) {
		if ( $options instanceof Environment ) {
			$this->environment = $options;
		} elseif ( is_array( $options ) ) {
			$defaults = [
				// Set the default character set.
				'char_set'            => 'auto',
				// An array of elements to remove.
				'elements_to_remove'  => [ 'form' ],
				// Ellipsis character for truncated text.
				'elipsis'             => '…',
				// Set to 'HTML', 'Markdown' or 'MarkdownV2'.
				'format_to'           => 'text',
				// Images inside links are processed as per these options.
				'images_in_links'     => [
					// Set to 'retain' to retain the image title or alt text.
					'title_or_alt'    => 'skip',
					// Set to 'retain' to retain the image link when the image is the only child of the link.
					'lone_image_link' => 'skip',
				],
				// Set the default character for each <li> in a <ul>. Can be '-', '*', or '+'.
				'list_item_style'     => '-',
				// Set to 'preserve' to preserve relative links.
				'relative_links'      => 'clean',
				// Set to false to keep display:none elements.
				'remove_display_none' => true,
				// A callable to determine if a node should be converted.
				'should_convert_cb'   => null,
				// `list_item_style` for nested <ul> and <ol>.
				'sub_list_item_style' => '◦',
				// Set to false to show warnings when loading malformed HTML.
				'suppress_errors'     => true,
				// Set the default separator for each <td> and <th>.
				'table_cell_sep'      => ' | ',
				// Set the default separator for each <tr>.
				'table_row_sep'       => "\n" . str_repeat( '-', 20 ) . "\n",
				// Set to 'strip' to remove hyperlinks being added in parentheses when formatting to 'text'.
				'text_hyperlinks'     => 'retain',
				// Whether to throw an exception when document parsing fails.
				'throw_on_doc_error'  => false,
			];

			$this->environment = Environment::createDefaultEnvironment( $defaults );

			$this->environment->getConfig()->merge( $options );
		}
	}

	/**
	 * It returns the environment.
	 *
	 * @return Environment The environment.
	 */
	public function getEnvironment() {
		return $this->environment;
	}

	/**
	 * It returns the configuration.
	 *
	 * @return Configuration The configuration.
	 */
	public function getConfig() {
		return $this->environment->getConfig();
	}

	/**
	 * Convert
	 *
	 * @param string $html The html to convert.
	 *
	 * @see HtmlConverter::convert
	 *
	 * @return string The Markdown version of the html
	 */
	public function __invoke( string $html ) {
		return $this->convert( $html );
	}

	/**
	 * {@inheritdoc}
	 */
	public function convert( string $html ) {
		// DOMDocument doesn't support empty value and throws an error.
		if ( trim( $html ) === '' ) {
			return '';
		}

		$document = $this->createDOMDocument( self::prepareHtml( $html ) );

		$root = $document->documentElement;

		$rootElement = new Element( $root );

		$result = $this->convertChildren( $rootElement );

		return self::cleanUp( $result );
	}

	/**
	 * Safely trim the given html to the given number of words.
	 *
	 * @param string  $html    The html to trim.
	 * @param string  $limitBy The type of limit to apply. Can be 'words' or 'chars'.
	 * @param integer $limit   The number of words or chars to limit to.
	 *
	 * @return string The trimmed html.
	 */
	public function safeTrim( string $html, string $limitBy = 'chars', int $limit = self::TG_TEXT_MAX_LENGTH ) {
		// DOMDocument doesn't support empty value and throws an error.
		if ( trim( $html ) === '' ) {
			return '';
		}

		$document = $this->createDOMDocument( self::prepareHtml( $html ) );

		$root = $document->documentElement;

		$rootElement = new Element( $root );

		$content = $rootElement->getNode()->textContent;

		$count = 'words' === $limitBy ? Utils::strWordCount( $content ) : mb_strlen( $content );

		if ( $count <= $limit ) {
			return $html;
		}

		Utils::limitContentBy( $document, $limitBy, $limit );

		$elipsis = $this->getConfig()->getOption( 'elipsis', '…' );

		$result = trim( $this->convertChildren( $rootElement ) ) . $elipsis;

		return $result;
	}

	/**
	 *  Prepare HTML
	 *
	 * @param string $html The html to prepare.
	 * @return string The prepared html.
	 */
	public static function prepareHtml( string $html ) {

		// replace &nbsp; with spaces.
		$html = str_replace( '&nbsp;', ' ', $html );
		$html = str_replace( "\xc2\xa0", ' ', $html );
		// replace \r\n to \n.
		$html = str_replace( "\r\n", "\n", $html );
		// remove \r.
		$html = str_replace( "\r", "\n", $html );
		// remove <head>, <script> and <style> tags.
		$html = preg_replace( '@<(head|script|style)[^>]*?>.*?</\\1>@si', '', $html );
		// Convert <br> to \n.
		$html = preg_replace( '@[\n\t\s]*<br[^>]*?/?>[\n\t\s]*@si', "\n", $html );

		return trim( $html );
	}

	/**
	 * Create a DOMDocument from the given $html
	 *
	 * @param string $html The html to convert.
	 *
	 * @return DOMDocument The DOMDocument version of the html
	 *
	 * @throws ConverterException If unable to load the html.
	 */
	private function createDOMDocument( string $html ) {
		$document = new DOMDocument();

		// use mb_convert_encoding for legacy versions of php.
		if ( ! Utils::phpAtLeast( '8.1' ) && mb_detect_encoding( $html, 'UTF-8', true ) ) {
			$html = mb_convert_encoding( $html, 'HTML-ENTITIES', 'UTF-8' );
		}

		$suppress_errors = $this->getConfig()->getOption( 'suppress_errors' );

		// If the HTML does not start with a tag, add <body> tag.
		if ( 0 !== strpos( trim( $html ), '<' ) ) {
			$html = '<body>' . $html . '</body>';
		}

		$header = '';
		// use char sets for modern versions of php.
		if ( Utils::phpAtLeast( '8.1' ) ) {
			// use specified char_set, or auto detect if not set.
			$char_set = $this->getConfig()->getOption( 'char_set', 'auto' );
			if ( 'auto' === $char_set ) {
				$char_set = mb_detect_encoding( $html );
			} elseif ( strpos( $char_set, ',' ) ) {
				mb_detect_order( $char_set );
				$char_set = mb_detect_encoding( $html );
			}
			// turn off error detection for Windows-1252 legacy html.
			if ( strpos( $char_set, '1252' ) ) {
				$suppress_errors = true;
			}
			$header = '<?xml version="1.0" encoding="' . $char_set . '">';
		}

		if ( $suppress_errors ) {
			// Suppress conversion errors.
			$document->strictErrorChecking = false;
			$document->recover             = true;
			$document->xmlStandalone       = true;
			libxml_use_internal_errors( true );
		}

		$result = $document->loadHTML( $header . $html, LIBXML_NOWARNING | LIBXML_NOERROR | LIBXML_NONET | LIBXML_PARSEHUGE );

		if ( $suppress_errors ) {
			libxml_clear_errors();
		}

		$throwOnDocError = $this->getConfig()->getOption( 'throw_on_doc_error' );

		if ( ! $result && $throwOnDocError ) {
			throw new ConverterException( 'Unable to load HTML.', 'load_html_failed', $html );
		}

		if ( ! isset( $document->documentElement ) && $throwOnDocError ) {
			throw new ConverterException( 'Unable to find document root element.', 'document_element_error', $html );
		}

		return $document;
	}

	/**
	 * Convert Children
	 *
	 * Recursive function to drill into the DOM and convert each node into Markdown from the inside out.
	 *
	 * Finds children of each node and convert those to #text nodes containing their Markdown equivalent,
	 * starting with the innermost element and working up to the outermost element.
	 *
	 * @param ElementInterface $element The element to convert.
	 *
	 * @return string The children converted.
	 */
	private function convertChildren( ElementInterface $element ) {

		// Give converter a chance to inspect/modify the DOM before children are converted.
		$converter = $this->environment->getConverterByTag( $element->getTagName() );
		if ( is_callable( [ $converter, 'preConvert' ] ) ) {
			call_user_func( [ $converter, 'preConvert' ], $element );
		}

		// If the node has children, convert those first.
		if ( $element->hasChildren() ) {
			foreach ( $element->getChildren() as $child ) {
				$this->convertChildren( $child );
			}
		}

		// Now that child nodes have been converted, convert the original node.
		$output = $this->shouldConvert( $element ) ? $this->convertElement( $element ) : '';

		$element->setFinalOutput( $output );

		return $output;
	}

	/**
	 * Whether the element should be converted
	 *
	 * @param ElementInterface $element The element to check.
	 *
	 * @return boolean Whether the element should be converted.
	 */
	public function shouldConvert( ElementInterface $element ) {
		$shouldConvert = '' !== $element->getValue() || $element->isVoid();

		$elementsToRemove = $this->getConfig()->getOption( 'elements_to_remove', [] );

		// If the element is in the list of elements to remove, don't convert it.
		if ( $shouldConvert && in_array( $element->getTagName(), $elementsToRemove, true ) ) {
			$shouldConvert = false;
		}

		if ( $shouldConvert && $this->getConfig()->getOption( 'remove_display_none', true ) ) {
			$style = Utils::parseStyle( $element->getAttribute( 'style' ) );

			if ( isset( $style['display'] ) && 'none' === $style['display'] ) {
				$shouldConvert = false;
			}
		}

		$shouldConvertCb = $this->getConfig()->getOption( 'should_convert_cb', null );

		// Have shouldConvert callback the final say.
		if ( is_callable( $shouldConvertCb ) ) {
			$shouldConvertVal = call_user_func( $shouldConvertCb, $element, $shouldConvert );

			$shouldConvert = is_bool( $shouldConvertVal ) ? $shouldConvertVal : $shouldConvert;
		}

		return $shouldConvert;
	}

	/**
	 * Convert element
	 *
	 * Converts an individual node into a #text node containing a string of its Markdown equivalent.
	 *
	 * Example: An <h3> node with text content of 'Title' becomes a text node with content of '### Title'
	 *
	 * @param ElementInterface $element The element to convert.
	 *
	 * @return string The converted HTML as Markdown
	 */
	protected function convertElement( ElementInterface $element ) {
		$tag = $element->getTagName();

		$converter = $this->environment->getConverterByTag( $tag );

		return $converter->convert( $element );
	}

	/**
	 * Clean up the result
	 *
	 * @param string $input The input to clean up.
	 *
	 * @return string The clean text.
	 */
	public static function cleanUp( string $input ) {
		$output = $input;

		// remove leading and trailing spaces on each line.
		$output = preg_replace( "/[ \t]*\n[ \t]*/im", "\n", $output );
		$output = preg_replace( "/ *\t */im", "\t", $output );

		// unarmor pre blocks.
		$output = str_replace( "\r", "\n", $output );

		$output = Utils::processPlaceholders( $output, 'replace' );

		// remove unnecessary empty lines.
		$output = preg_replace( "/\n\n\n*/im", "\n\n", $output );

		return trim( $output );
	}

	/**
	 * Pass a series of key-value pairs in an array; these will be passed
	 * through the config and set.
	 *
	 * @param array<string, mixed> $options Options to set.
	 *
	 * @return $this
	 */
	public function setOptions( array $options ) {
		$config = $this->getConfig();

		foreach ( $options as $key => $option ) {
			$config->setOption( $key, $option );
		}

		return $this;
	}
}
