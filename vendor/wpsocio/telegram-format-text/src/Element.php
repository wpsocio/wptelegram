<?php
/**
 * Element
 *
 * @package WPSocio\TelegramFormatText
 */

namespace WPSocio\TelegramFormatText;

use DOMElement;
use DOMNode;
use DOMText;

/**
 * Class Element
 */
class Element implements ElementInterface {

	const BLOCK_ELEMENTS = [
		'article',
		'aside',
		'body',
		'div',
		'footer',
		'h1',
		'h2',
		'h3',
		'h4',
		'h5',
		'h6',
		'header',
		'hr',
		'html',
		'li',
		'main',
		'nav',
		'ol',
		'p',
		'section',
		'table',
		'ul',
	];

	/**
	 * The supported void elements.
	 *
	 * @var array
	 */
	const VOID_ELEMENTS = [
		'hr',
		'img',
	];

	/**
	 * The node.
	 *
	 * @var DOMNode
	 */
	protected $node;

	/**
	 * The next node.
	 *
	 * @var ElementInterface|null
	 * */
	private $nextCached;

	/**
	 * The cached next element.
	 *
	 * @var ElementInterface|null
	 * */
	private $nextElementCached;

	/**
	 * The previous element.
	 *
	 * @var DOMNode|null
	 */
	private $previousSiblingCached;

	/**
	 * {@inheritdoc}
	 */
	public function __construct( DOMNode $node ) {
		$this->node = $node;

		$this->previousSiblingCached = $this->node->previousSibling;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getNode() {
		return $this->node;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isBlock() {
		return in_array( $this->getTagName(), self::BLOCK_ELEMENTS, true );
	}

	/**
	 * {@inheritdoc}
	 */
	public function isText() {
		return $this->getTagName() === '#text';
	}

	/**
	 * {@inheritdoc}
	 */
	public function isWhitespace() {
		return $this->getTagName() === '#text' && trim( $this->getValue() ) === '';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getTagName() {
		return $this->node->nodeName;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getValue() {
		return isset( $this->node->nodeValue ) ? $this->node->nodeValue : '';
	}

	/**
	 * {@inheritdoc}
	 */
	public function hasParent() {
		return null !== $this->node->parentNode;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getParent() {
		return $this->node->parentNode ? new self( $this->node->parentNode ) : null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getNextSibling() {
		return null !== $this->node->nextSibling ? new self( $this->node->nextSibling ) : null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getPreviousSibling() {
		return null !== $this->previousSiblingCached ? new self( $this->previousSiblingCached ) : null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function hasChildren() {
		return $this->node->hasChildNodes();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getChildren() {
		$children = [];
		foreach ( $this->node->childNodes as $node ) {
			if ( $node instanceof DOMNode ) {
				$children[] = new self( $node );
			}
		}

		return $children;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getNext() {
		if ( null === $this->nextCached ) {
			$nextNode = $this->getNextNode( $this->node );
			if ( null !== $nextNode ) {
				$this->nextCached = new self( $nextNode );
			}
		}

		return $this->nextCached;
	}

	/**
	 * Get the next node in the DOM tree.
	 *
	 * @param DOMNode $node          The node to start from.
	 * @param boolean $checkChildren Whether to check the children of the node.
	 *
	 * @return DOMNode|null The next node.
	 */
	private function getNextNode( DOMNode $node, bool $checkChildren = true ) {
		if ( $checkChildren && $node->firstChild ) {
			return $node->firstChild;
		}

		if ( $node->nextSibling ) {
			return $node->nextSibling;
		}

		if ( $node->parentNode ) {
			return $this->getNextNode( $node->parentNode, false );
		}

		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isDescendantOf( $tagNames ) {
		if ( ! is_array( $tagNames ) ) {
			$tagNames = [ $tagNames ];
		}

		for ( $p = $this->node->parentNode; false !== $p; $p = $p->parentNode ) {
			if ( null === $p ) {
				return false;
			}

			if ( in_array( $p->nodeName, $tagNames, true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getChildrenAsString() {
		return $this->node->C14N();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSiblingPosition() {
		$position = 0;

		$parent = $this->getParent();
		if ( null === $parent ) {
			return $position;
		}

		// Loop through all nodes and find the given $node.
		foreach ( $parent->getChildren() as $currentNode ) {
			if ( ! $currentNode->isWhitespace() ) {
				++$position;
			}

			// Perhaps we can somehow ensure that we always have the exact same object and use === instead?
			if ( $this->equals( $currentNode ) ) {
				break;
			}
		}

		return $position;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getListItemLevel() {
		$level  = 0;
		$parent = $this->getParent();

		while ( null !== $parent && $parent->hasParent() ) {
			if ( $parent->getTagName() === 'li' ) {
				++$level;
			}

			$parent = $parent->getParent();
		}

		return $level;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getAttribute( string $name ) {
		if ( $this->node instanceof DOMElement ) {
			return $this->node->getAttribute( $name );
		}

		return '';
	}

	/**
	 * {@inheritdoc}
	 */
	public function equals( ElementInterface $element ) {
		if ( $element instanceof self ) {
			return $element->node === $this->node;
		}

		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setFinalOutput( string $content ) {
		if ( ! $this->node->ownerDocument || ! $this->node->parentNode ) {
			return;
		}

		$node = $this->node->ownerDocument->createTextNode( $content );
		$this->node->parentNode->replaceChild( $node, $this->node );
	}

	/**
	 * Get the next element node.
	 *
	 * @param DOMNode $node The node to start from.
	 *
	 * @return DOMNode|null The next element node.
	 */
	private function getCachedNextElement( DOMNode $node ) {
		$next = $node->nextSibling;
		if ( ! $next ) {
			return null;
		}
		// Skip over text nodes.
		if ( $next instanceof DOMText ) {
			return $this->getCachedNextElement( $next );
		}
		return $next;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getNextElement() {
		if ( null === $this->nextElementCached ) {
			$nextSibling = $this->getCachedNextElement( $this->node );

			$this->nextElementCached = $nextSibling ? new self( $nextSibling ) : null;
		}

		return $this->nextElementCached;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isVoid() {
		return in_array( $this->getTagName(), self::VOID_ELEMENTS, true );
	}
}
