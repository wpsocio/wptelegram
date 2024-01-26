<?php
/**
 * Handles the options access of the plugin
 *
 * @link https://wpsocio.com
 *
 * @package WPSocio\WPUtils
 */

namespace WPSocio\WPUtils;

use Iterator;
use ArrayAccess;

/**
 * Allows an easy access to plugin options/settings
 * which are in the form of an array
 *
 * @package    WPSocio\WPUtils
 * @author     WP Socio
 */
class Options implements Iterator, ArrayAccess {

	const ALL_KEY = '__all__';

	/**
	 * Plugin option key saved in the database
	 *
	 * @var string the option key
	 */
	protected $option_key;

	/**
	 * All plugin options
	 *
	 * @var array Contains all the plugin options
	 */
	protected $data;

	/**
	 * Whether the data should be stored as json or as serialized.
	 *
	 * Non UTF-8 (old) databases do not support multibyte characters
	 * (like emojis) when using the default (serialization) method.
	 *
	 * @var string Whether to store data as json.
	 */
	protected $store_as_json;

	/**
	 * Constructor.
	 *
	 * @param string $option_key    The option key name.
	 * @param bool   $store_as_json Whether to store data as json.
	 */
	public function __construct( string $option_key = '', bool $store_as_json = false ) {

		$this->store_as_json = $store_as_json;

		// Make sure we have an array to avoid adding values to null.
		$this->data = [];

		if ( ! empty( $option_key ) ) {
			$this->set_option_key( $option_key );
		}
	}

	/**
	 * Get the filter name for a given action type.
	 *
	 * @param string $action_type Action type.
	 * @param string $key         Option key.
	 */
	protected function get_filter_name( string $action_type, string $key = '' ) {
		return implode( '_', array_filter( [ strtolower( __CLASS__ ), $this->option_key, $action_type, $key ] ) );
	}

	/**
	 * Checks if an option key exists.
	 *
	 * @param string $key Option key.
	 *
	 * @return bool Whether the option key exists.
	 */
	public function exists( string $key ) {
		return array_key_exists( $key, $this->get_data() );
	}

	/**
	 * Retrieves an option by key.
	 *
	 * @param string $key     Options array key.
	 * @param mixed  $default Optional default value.
	 *
	 * @return mixed Option value
	 */
	public function get( string $key = '', $default = false ) {
		if ( self::ALL_KEY === $key || empty( $key ) ) {
			$value = $this->data;
		} else {
			$value = $this->exists( $key ) ? $this->data[ $key ] : $default;
		}

		return apply_filters( $this->get_filter_name( 'get', $key ), $value, $default, $this->data );
	}

	/**
	 * Retrieves an option by nested path, with keys separated by dot.
	 *
	 * @param string $path    Path to the value..
	 * @param mixed  $default Optional default value.
	 *
	 * @return mixed Option value.
	 */
	public function get_path( string $path = '', $default = false ) {
		// If it's not a nested path.
		if ( false === strpos( $path, '.' ) ) {
			return $this->get( $path, $default );
		}

		$value = $this->get( self::ALL_KEY );

		if ( ! is_array( $value ) ) {
			return $default;
		}

		foreach ( explode( '.', $path ) as $key ) {

			if ( ! is_array( $value ) || ! array_key_exists( $key, $value ) ) {
				return $default;
			}
			$value = $value[ $key ];
		}

		return apply_filters( $this->get_filter_name( 'get_path', $path ), $value, $default, $this->data );
	}

	/**
	 * Sets the option by nested path, with keys separated by dot.
	 *
	 * @param string $path   Path to update.
	 * @param mixed  $value  Value.
	 *
	 * @return mixed Option value.
	 */
	public function set_path( string $path, $value ) {
		// If it's not a nested path.
		if ( false === strpos( $path, '.' ) ) {
			return $this->set( $path, $value );
		}

		$item = &$this->data;

		foreach ( explode( '.', $path ) as $key ) {
			if ( ! isset( $item[ $key ] ) || ! is_array( $item[ $key ] ) ) {
				$item[ $key ] = [];
			}

			$item = &$item[ $key ];
		}

		$item = $value;

		$this->update_data();

		return $this;
	}

	/**
	 * Sets an option by key.
	 *
	 * @param string $key   Options array key.
	 * @param mixed  $value Option value.
	 *
	 * @return mixed Option value
	 */
	public function set( string $key, $value = '' ) {

		if ( ! empty( $this->option_key ) ) {

			$this->data[ $key ] = apply_filters( $this->get_filter_name( 'set', $key ), $value, $this->data );

			return $this->update_data();
		}

		if ( $key ) {
			$this->data[ $key ] = $value;
		}

		return $this;
	}

	/**
	 * Remove an option by key.
	 *
	 * @param string $key Options array key.
	 */
	public function remove( string $key ) {

		if ( ! $key ) {
			return false;
		}

		unset( $this->data[ $key ] );

		return $this->update_data();
	}

	/**
	 * Get the option key
	 *
	 * @return string Option key.
	 */
	public function get_option_key(): string {
		return $this->option_key;
	}

	/**
	 * Set the option key.
	 *
	 * @param string $option_key Option name in the database.
	 */
	public function set_option_key( string $option_key ) {
		$this->option_key = $option_key;
		$this->set_data();

		return $this;
	}

	/**
	 * Gets all options.
	 *
	 * @return array
	 */
	public function get_data() {
		return (array) $this->get();
	}

	/**
	 * Sets the options data.
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

			// Do not unslash data from the database.
			$unslash = false;
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
	 * @param boolean $unslash Whether to unslash the data.
	 */
	public function update_data( bool $unslash = false ) {

		// Make sure we have something to work upon.
		if ( empty( $this->option_key ) ) {
			return false;
		}

		$data = $this->get_data();
		if ( $unslash ) {
			$data = wp_unslash( $data );
		}
		if ( $this->store_as_json ) {
			$data = wp_json_encode( $data );
		}

		return update_option( $this->option_key, $data );
	}

	/**
	 * Magic method for accessing options as object props.
	 *
	 * @param string $key Options array key.
	 *
	 * @return mixed Value of the option
	 */
	public function __get( string $key ) {
		return $this->get( $key );
	}

	/**
	 * Magic method for setting options as object props.
	 *
	 * @param string $key   Options array key.
	 * @param mixed  $value Option value.
	 */
	public function __set( string $key, $value ) {
		return $this->set( $key, $value );
	}

	/**
	 * Magic method for un-setting options as object props.
	 *
	 * @param string $key Options array key.
	 */
	public function __unset( string $key ) {
		return $this->remove( $key );
	}

	/**
	 * Magic method to check for existence of a key.
	 *
	 * @param string $key Options array key.
	 */
	public function __isset( string $key ) {
		return $this->exists( $key );
	}

	/**
	 * Allows the object being called as a function
	 * to retrieve an option.
	 *
	 * @param string $key Options array key.
	 *
	 * @return mixed Option value.
	 */
	public function __invoke( string $key ) {
		return $this->get( $key );
	}

	/**
	 * Allows the object being treated as string
	 *
	 * @return string json encoded.
	 */
	public function __toString(): string {
		return wp_json_encode( $this->get_data() );
	}

	/**
	 * Determines whether an offset value exists.
	 *
	 * @link https://secure.php.net/manual/en/arrayaccess.offsetexists.php
	 *
	 * @param mixed $offset An offset to check for.
	 * @return bool True if the offset exists, false otherwise.
	 */
	public function offsetExists( $offset ): bool {
		return $this->exists( $offset );
	}

	/**
	 * Retrieves a value at a specified offset.
	 *
	 * @link https://secure.php.net/manual/en/arrayaccess.offsetget.php
	 *
	 * @param mixed $offset The offset to retrieve.
	 * @return mixed If set, the value at the specified offset, false otherwise.
	 */
	public function offsetGet( $offset ): mixed {
		return $this->get( $offset );
	}

	/**
	 * Sets a value at a specified offset.
	 *
	 * @link https://secure.php.net/manual/en/arrayaccess.offsetset.php
	 *
	 * @param mixed $offset The offset to assign the value to.
	 * @param mixed $value The value to set.
	 */
	public function offsetSet( $offset, $value ): void {
		$this->set( $offset, $value );
	}

	/**
	 * Unsets a specified offset.
	 *
	 * @link https://secure.php.net/manual/en/arrayaccess.offsetunset.php
	 *
	 * @param mixed $offset The offset to unset.
	 */
	public function offsetUnset( $offset ): void {
		$this->remove( $offset );
	}

	/**
	 * Returns the current element.
	 *
	 * @link https://secure.php.net/manual/en/iterator.current.php
	 *
	 * @return mixed
	 */
	public function current(): mixed {
		return current( $this->data );
	}

	/**
	 * Moves forward to the next element.
	 *
	 * @link https://secure.php.net/manual/en/iterator.next.php
	 */
	public function next(): void {
		next( $this->data );
	}

	/**
	 * Returns the key of the current element.
	 *
	 * @link https://secure.php.net/manual/en/iterator.key.php
	 *
	 * @return mixed
	 */
	public function key(): mixed {
		return key( $this->data );
	}

	/**
	 * Checks if current position is valid.
	 *
	 * @link https://secure.php.net/manual/en/iterator.valid.php
	 *
	 * @return boolean
	 */
	public function valid(): bool {
		return key( $this->data ) !== null;
	}

	/**
	 * Rewinds the Iterator to the first element.
	 *
	 * @link https://secure.php.net/manual/en/iterator.rewind.php
	 */
	public function rewind(): void {
		reset( $this->data );
	}
}
