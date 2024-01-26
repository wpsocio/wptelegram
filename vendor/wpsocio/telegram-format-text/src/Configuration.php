<?php
/**
 * Configuration.
 *
 * @package WPSocio\TelegramFormatText
 */

namespace WPSocio\TelegramFormatText;

/**
 * Class Configuration
 */
class Configuration {

	/**
	 * Configuration.
	 *
	 * @var array<string, mixed>
	 */
	protected $config;

	/**
	 * Configuration constructor.
	 *
	 * @param array<string, mixed> $config Configuration.
	 */
	public function __construct( array $config = [] ) {
		$this->config = $config;
	}

	/**
	 * Merge configuration.
	 *
	 * @param array<string, mixed> $config Configuration.
	 *
	 * @return void
	 */
	public function merge( array $config = [] ) {
		$this->config = array_merge( $this->config, $config );
	}

	/**
	 * Replace configuration.
	 *
	 * @param array<string, mixed> $config Configuration.
	 *
	 * @return void
	 */
	public function replace( array $config = [] ) {
		$this->config = $config;
	}

	/**
	 * Set option.
	 *
	 * @param string     $key   Key.
	 * @param mixed|null $value Value.
	 *
	 * @return void
	 */
	public function setOption( string $key, $value ) {
		$this->config[ $key ] = $value;
	}

	/**
	 * Get option.
	 *
	 * @param string|null $key     Key.
	 * @param mixed|null  $default Default value.
	 *
	 * @return mixed|null
	 */
	public function getOption( $key = null, $default = null ) {
		if ( null === $key ) {
			return $this->config;
		}

		if ( ! isset( $this->config[ $key ] ) ) {
			return $default;
		}

		return $this->config[ $key ];
	}
}
