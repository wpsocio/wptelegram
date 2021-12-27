<?php
/**
 * Handles the options access of the plugin
 *
 * @link https://wpsocio.com
 * @since 1.0.0
 *
 * @package    WPTelegram
 * @subpackage WPTelegram\Core\includes
 */

namespace WPTelegram\Core\includes;

use Iterator;
use ArrayAccess;

/**
 * Allows an easy access to plugin options/settings
 * which are in the form of an array
 *
 * @package    WPTelegram
 * @subpackage WPTelegram\Core\includes
 * @author     WP Socio
 */
class Options implements Iterator, ArrayAccess {

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
	 * Whether the data should be stored as json or as serialized.
	 *
	 * Non UTF-8 (old) databases do not support multibyte characters
	 * (like emojis) when using the default (serialization) method.
	 *
	 * @since 1.0.0
	 * @var string Whether to store data as json.
	 */
	protected $store_as_json;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param string $option_key    The option key name.
	 * @param string $store_as_json Whether to store data as json.
	 */
	public function __construct( $option_key = '', $store_as_json = false ) {

		$this->store_as_json = $store_as_json;

		// Make sure we have an array to avoid adding values to null.
		$this->data = [];

		if ( ! empty( $option_key ) ) {
			$this->set_option_key( $option_key );
		}
	}

	/**
	 * Checks if an option key exists.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key Option key.
	 *
	 * @return bool Whether the option key exists.
	 */
	public function exists( $key ) {
		return array_key_exists( $key, $this->get_data() );
	}

	/**
	 * Retrieves an option by key.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key     Options array key.
	 * @param mixed  $default Optional default value.
	 *
	 * @return mixed Option value
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
	 * Retrieves an option by nested path, with keys separated by dot.
	 *
	 * @since 3.0.0
	 *
	 * @param string $path    Path to the value..
	 * @param mixed  $default Optional default value.
	 *
	 * @return mixed Option value.
	 */
	public function get_path( $path = '', $default = false ) {
		// If it's not a nested path.
		if ( false === strpos( $path, '.' ) ) {
			return $this->get( $path, $default );
		}

		$value = $this->get( 'all' );

		if ( ! is_array( $value ) ) {
			return $default;
		}

		foreach ( explode( '.', $path ) as $key ) {

			if ( ! is_array( $value ) || ! array_key_exists( $key, $value ) ) {
				return $default;
			}
			$value = $value[ $key ];
		}

		return apply_filters( strtolower( __CLASS__ ) . "_{$this->option_key}_get_path_{$path}", $value, $default );
	}

	/**
	 * Sets an option by key.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key   Options array key.
	 * @param mixed  $value Option value.
	 *
	 * @return mixed Option value
	 */
	public function set( $key, $value = '' ) {

		if ( ! empty( $this->option_key ) ) {

			$this->data[ $key ] = apply_filters( strtolower( __CLASS__ ) . "_{$this->option_key}_set_{$key}", $value );

			return $this->update_data();
		}

		$this->data[ $key ] = $value;

		return $this;
	}

	/**
	 * Remove an option by key.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key Options array key.
	 */
	public function remove( $key ) {

		unset( $this->data[ $key ] );

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
	 * Set the option key.
	 *
	 * @since 1.0.0
	 *
	 * @param string $option_key Option name in the database.
	 */
	public function set_option_key( $option_key ) {
		$this->option_key = $option_key;
		$this->set_data();

		return $this;
	}

	/**
	 * Gets all options.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_data() {
		return (array) $this->get();
	}

	/**
	 * Sets the options data.
	 *
	 * @since 1.0.0
	 *
	 * @param array   $data    The options array.
	 * @param boolean $unslash Whether to unslash the data.
	 */
	public function set_data( array $data = [], $unslash = false ) {
		if ( empty( $data ) && ! empty( $this->option_key ) ) {

			$default = $this->store_as_json ? '' : [];

			$data = get_option( $this->option_key, $default );

			if ( $this->store_as_json ) {
				$data = json_decode( $data, true );
			}
		}

		if ( $unslash ) {
			$data = wp_unslash( $data );
		}
		$this->data = (array) $data;

		return $this;
	}

	/**
	 * Updates the options in the database.
	 *
	 * @since 1.0.0
	 * @param boolean $unslash Whether to unslash the data.
	 */
	public function update_data( $unslash = false ) {

		// Make sure we have something to work upon.
		if ( ! empty( $this->option_key ) ) {
			$data = $this->get_data();
			if ( $this->store_as_json ) {
				$data = wp_json_encode( $data );
			}
			if ( $unslash ) {
				$data = wp_unslash( $data );
			}

			return update_option( $this->option_key, $data );
		}
		return false;
	}

	/**
	 * Magic method for accessing options as object props.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key Options array key.
	 *
	 * @return mixed Value of the option
	 */
	public function __get( $key ) {
		return $this->get( $key );
	}

	/**
	 * Magic method for setting options as object props.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key   Options array key.
	 * @param string $value Option value.
	 */
	public function __set( $key, $value ) {
		return $this->set( $key, $value );
	}

	/**
	 * Magic method for un-setting options as object props.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key Options array key.
	 */
	public function __unset( $key ) {
		return $this->remove( $key );
	}

	/**
	 * Magic method to check for existence of a key.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key Options array key.
	 */
	public function __isset( $key ) {
		return $this->exists( $key );
	}

	/**
	 * Allows the object being called as a function
	 * to retrieve an option.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key Options array key.
	 *
	 * @return mixed Option value.
	 */
	public function __invoke( $key ) {
		return $this->get( $key );
	}

	/**
	 * Allows the object being treated as string
	 *
	 * @since 1.0.0
	 *
	 * @return string json encoded.
	 */
	public function __toString() {
		return wp_json_encode( $this->get_data() );
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
		return $this->remove( $offset );
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
