<?php
/**
 * Handles the dependencies for the built assets.
 *
 * @link       https://wpsocio.com
 *
 * @package WPSocio\WPUtils
 */

namespace WPSocio\WPUtils;

use Exception;

/**
 * Easily access the dependencies for the built assets.
 *
 * @package    WPSocio\WPUtils
 * @author     WP Socio
 */
class JsDependencies {

	/**
	 * The path to the directory containing the dependencies file.
	 *
	 * @var string
	 */
	protected $deps_dir;

	/**
	 * The name of the dependencies file.
	 *
	 * @var string
	 */
	protected $file_name;

	/**
	 * The decoded contents of the dependencies file.
	 *
	 * @var array
	 */
	protected $dependencies = null;

	/**
	 * The path to dependencies file.
	 *
	 * @var string
	 */
	protected $path;

	/**
	 * Constructor.
	 *
	 * @param string $deps_dir  The path to the directory containing the dependencies file.
	 * @param string $file_name The name of the dependencies file.
	 */
	public function __construct( $deps_dir, $file_name = 'dependencies.json' ) {
		$this->deps_dir  = untrailingslashit( $deps_dir );
		$this->file_name = $file_name;
		$this->initialize();
	}

	/**
	 * Initializes the assets.
	 *
	 * @return void
	 */
	public function initialize() {
		if ( ! $this->dependencies ) {
			$this->set_filepath();
			$this->load();
		}
	}

	/**
	 * Sets the path to dependencies file.
	 *
	 * @param string $path Path to dependencies JSON file.
	 *
	 * @throws Exception Dependencies file check.
	 *
	 * @return void
	 */
	public function set_filepath( $path = '' ) {
		$this->path = $path ? $path : $this->deps_dir . '/' . $this->file_name;
	}

	/**
	 * Loads the dependencies file.
	 *
	 * @return void
	 */
	private function load() {
		if ( null !== $this->dependencies ) {
			return;
		}

		if ( ! $this->path || ! is_readable( $this->path ) ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error
			trigger_error( 'Dependencies file not found or is not readable: ' . $this->path, E_USER_WARNING );

			$this->dependencies = [];

			return;
		}

		$dependencies = json_decode( file_get_contents( $this->path ), true );

		$this->dependencies = $dependencies ? $dependencies : [];
	}

	/**
	 * Get the dependencies of an entry.
	 *
	 * @param string $entry The entry point to get the asset dependencies for.
	 *
	 * @return array
	 */
	public function get( $entry ) {
		$dependencies = $this->dependencies[ $entry ] ?? [];

		return $dependencies;
	}
}
