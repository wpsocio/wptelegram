<?php
/**
 * Converter exception.
 *
 * @package WPSocio\TelegramFormatText\Exceptions
 */

namespace WPSocio\TelegramFormatText\Exceptions;

use Exception;

/**
 * Class ConverterException
 */
class ConverterException extends Exception {

	/**
	 * The error code.
	 *
	 * @var string
	 */
	protected $errorCode;

	/**
	 * The HTML that caused the error.
	 *
	 * @var string
	 */
	protected $html = '';

	/**
	 * ConverterException constructor.
	 *
	 * @param string $message   The error message.
	 * @param string $errorCode The error code.
	 * @param string $html      The HTML that caused the error.
	 */
	public function __construct( string $message, string $errorCode, string $html = '' ) {

		$this->errorCode = $errorCode;
		$this->html      = $html;

		parent::__construct( $message, 0 );
	}

	/**
	 * String representation of the object.
	 *
	 * @link  http://php.net/manual/en/exception.tostring.php
	 *
	 * @return string the string representation of the exception.
	 */
	public function __toString() {
		return 'ConverterException' . ": [{$this->errorCode}]: {$this->message}\n";
	}

	/**
	 * Get the error code.
	 *
	 * @return string
	 */
	public function getErrorCode() {
		return $this->errorCode;
	}

	/**
	 * Get the HTML that caused the error.
	 *
	 * @return string
	 */
	public function getHtml() {
		return $this->html;
	}
}
