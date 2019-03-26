<?php

/**
 * Handles the options access of the plugin
 *
 * @link	   https://t.me/manzoorwanijk
 * @since	  1.0.0
 *
 * @package		WPTelegram
 * @subpackage	WPTelegram/includes
 */

/**
 * Allows an easy access to plugin options/settings
 * which are in the form of an array
 *
 * @package		WPTelegram
 * @subpackage	WPTelegram/includes
 * @author		Manzoor Wani
 */
class WPTelegram_Options implements Iterator, ArrayAccess {

	/**
	 * Plugin option key saved in the database
	 *
	 * @since 1.0.0
	 * @var string the option key
	 */
	protected $option_key;

	/**
	 * All plugin options
	 *
	 * @since 1.0.0
	 * @var array Contains all the plugin options
	 */
	protected $data;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * 
	 * @param	string	$option_key
	 */
	public function __construct( $option_key = '' ) {
		// make sure we have an array to avoid adding values to null
		$this->data = array();

		if ( ! empty( $option_key ) ) {
			$this->set_option_key( $option_key );
		}
	}

	/**
	 * Checks if an option key exists
	 *
	 * @since 1.0.0
	 *
	 * @param string $key Option key
	 *
	 * @return bool Whether the option key exists.
	 */
	public function exists( $key ) {
		return array_key_exists( $key, $this->get_data() );
	}

	/**
	 * Retrieves an option by key
	 *
	 * @since 1.0.0
	 *
	 * @param  string $key	 Options array key
	 * @param  mixed  $default Optional default value
	 *
	 * @return mixed		   Option value
	 */
	public function get( $key = '', $default = false ) {
		if ( 'all' === $key || empty( $key ) ) {
			$value = $this->data;
		} else {
			$value = $this->exists( $key ) ? $this->data[ $key ] : $default;
		}

		return apply_filters( strtolower( __CLASS__ ) . "_{$this->option_key}_get_{$key}", $value, $default );
	}

	/**
	 * Sets an option by key
	 *
	 * @since 1.0.0
	 *
	 * @param  string $key	 Options array key
	 * @param  mixed  $value Option value
	 *
	 * @return mixed		   Option value
	 */
	public function set( $key, $value = '' ) {

		if ( empty( $this->option_key ) ) {
			return false;
		}

		$this->data[ $key ] = apply_filters( strtolower( __CLASS__ ) . "_{$this->option_key}_set_{$key}", $value );

		return $this->update_data();
	}

	/**
	 * Unset/remove an option by key
	 *
	 * @since 1.0.0
	 *
	 * @param  string $key	Options array key
	 *
	 * @return mixed		Option value
	 */
	public function _unset( $key ) {

		unset( $this->data[ $offset ] );

		return $this->update_data();
	}

	/**
	 * Get the option key
	 *
	 * @since 1.0.0
	 */
	public function get_option_key() {
		return $this->option_key;
	}

	/**
	 * Set the option key
	 *
	 * @since 1.0.0
	 * 
	 * @param string	$option_key Option name in the database
	 */
	public function set_option_key( $option_key ) {
		$this->option_key = $option_key;
		$this->set_data();
	}

	/**
	 * Gets all options.
	 *
	 * @since 1.0.0
	 *
	 */
	public function get_data() {
		return (array) $this->get();
	}

	/**
	 * Sets all options.
	 *
	 * @since 1.0.0
	 *
	 */
	public function set_data( array $options = array() ) {
		if ( empty( $options ) && ! empty( $this->option_key ) ) {
			$this->data = get_option( $this->option_key, array() );
		} else {
			$this->data = (array) $options;
		}
	}

	/**
	 * Updates the options in the database
	 *
	 * @since 1.0.0
	 */
	public function update_data() {

		// make sure we have something to work upon
		if ( ! empty( $this->option_key ) ) {

			return update_option( $this->option_key, $this->get_data() );
		}
		return false;
	}

	/**
	 * Magic method for accessing options as object props
	 *
	 * @since 1.0.0
	 *
	 * @param string $key Options array key
	 *
	 * @return mixed Value of the option
	 */
	public function __get( $key ) {
		return $this->get( $key );
	}

	/**
	 * Magic method for setting options as object props
	 *
	 * @since 1.0.0
	 *
	 * @param string $key	Options array key
	 * @param string $value	Option value
	 */
	public function __set( $key, $value ) {
		return $this->set( $key, $value );
	}

	/**
	 * Magic method for un-setting options as object props
	 *
	 * @since 1.0.0
	 *
	 * @param string $key	Options array key
	 */
	public function __unset( $key ) {
		return $this->_unset( $key );
	}

	/**
	 * Magic method to check for existence of a key
	 *
	 * @since 1.0.0
	 *
	 * @param string $key	Options array key
	 */
	public function __isset( $key ) {
		return $this->exists( $key );
	}

	/**
	 * Allows the object being called as a function
	 * to retrieve an option
	 *
	 * @since 1.0.0
	 *
	 * @param  string $key	 Options array key
	 *
	 * @return mixed		 Option value
	 */
	public function __invoke( $key ) {
		return $this->get( $key );
	}

	/**
	 * Allows the object being treated as string
	 *
	 * @since 1.0.0
	 *
	 * @return string		   json encoded
	 */
	public function __toString() {
		return json_encode( $this->get_data() );
	}
 
	/**
	 * Determines whether an offset value exists.
	 *
	 * @since 1.0.0
	 *
	 * @link https://secure.php.net/manual/en/arrayaccess.offsetexists.php
	 *
	 * @param mixed $offset An offset to check for.
	 * @return bool True if the offset exists, false otherwise.
	 */
	public function offsetExists( $offset ) {
		return $this->exists( $offset );
	}

	/**
	 * Retrieves a value at a specified offset.
	 *
	 * @since 1.0.0
	 *
	 * @link https://secure.php.net/manual/en/arrayaccess.offsetget.php
	 *
	 * @param mixed $offset The offset to retrieve.
	 * @return mixed If set, the value at the specified offset, false otherwise.
	 */
	public function offsetGet( $offset ) {
		return $this->get( $offset );
	}

	/**
	 * Sets a value at a specified offset.
	 *
	 * @since 1.0.0
	 *
	 * @link https://secure.php.net/manual/en/arrayaccess.offsetset.php
	 *
	 * @param mixed $offset The offset to assign the value to.
	 * @param mixed $value The value to set.
	 */
	public function offsetSet( $offset, $value ) {
		return $this->set( $offset, $value );
	}

	/**
	 * Unsets a specified offset.
	 *
	 * @since 1.0.0
	 *
	 * @link https://secure.php.net/manual/en/arrayaccess.offsetunset.php
	 *
	 * @param mixed $offset The offset to unset.
	 */
	public function offsetUnset( $offset ) {
		return $this->_unset( $offset );
	}

	/**
	 * Returns the current element.
	 *
	 * @since 1.0.0
	 *
	 * @link https://secure.php.net/manual/en/iterator.current.php
	 *
	 * @return mixed
	 */
	public function current() {
		return current( $this->data );
	}

	/**
	 * Moves forward to the next element.
	 *
	 * @since 1.0.0
	 *
	 * @link https://secure.php.net/manual/en/iterator.next.php
	 *
	 * @return mixed
	 */
	public function next() {
		return next( $this->data );
	}

	/**
	 * Returns the key of the current element.
	 *
	 * @since 1.0.0
	 *
	 * @link https://secure.php.net/manual/en/iterator.key.php
	 *
	 * @return mixed
	 */
	public function key() {
		return key( $this->data );
	}

	/**
	 * Checks if current position is valid.
	 *
	 * @since 1.0.0
	 *
	 * @link https://secure.php.net/manual/en/iterator.valid.php
	 *
	 * @return boolean
	 */
	public function valid() {
		return key( $this->data ) !== null;
	}

	/**
	 * Rewinds the Iterator to the first element.
	 *
	 * @since 1.0.0
	 *
	 * @link https://secure.php.net/manual/en/iterator.rewind.php
	 */
	public function rewind() {
		reset( $this->data );
	}
}