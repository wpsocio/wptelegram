<?php
/**
 * Handles the options access of the plugin
 *
 * Parts of this code are copied from https://github.com/eventespresso/event-espresso-core
 *
 * @link       https://wpsocio.com
 * @since      3.0.0
 *
 * @package    WPTelegram\Core
 * @subpackage WPTelegram\Core\includes
 */

namespace WPTelegram\Core\includes;

/**
 * Allows an easy access to plugin options/settings
 * which are in the form of an array
 *
 * @package    WPTelegram\Core
 * @subpackage WPTelegram\Core\includes
 * @author     WP Socio
 */
class Assets {


	const ASSET_EXT_CSS = '.css';

	const ASSET_EXT_JS = '.js';

	const ASSET_EXT_PHP = '.php';

	const FILE_NAME = 'asset-manifest.json';

	const KEY_DEPENDENCIES = 'dependencies';

	const KEY_ENTRY_POINTS = 'entrypoints';

	const KEY_FILES = 'files';

	const KEY_VERSION = 'version';


	/**
	 * The asset files.
	 *
	 * @var array $asset_files The asset files.
	 */
	private $asset_files;

	/**
	 * The path to assets directory.
	 *
	 * @var string $assets_path The path to assets directory.
	 */
	private $assets_path;

	/**
	 * The URL to assets directory.
	 *
	 * @var string $assets_url The URL to assets directory.
	 */
	private $assets_url;

	/**
	 * The list of entry points.
	 *
	 * @var array $entry_points The list of entry points.
	 */
	private $entry_points;

	/**
	 * The decoded contents of the manifest file.
	 *
	 * @var array $manifest The decoded contents of the manifest file.
	 */
	private $manifest;

	/**
	 * The path to manifest file.
	 *
	 * @var string $assets_path The path to manifest file.
	 */
	private $manifest_path;

	/**
	 * Assets constructor.
	 *
	 * @param string $assets_path The path to assets directory.
	 * @param string $assets_url  The URL to assets directory.
	 */
	public function __construct( $assets_path, $assets_url ) {
		$this->assets_path = untrailingslashit( $assets_path );
		$this->assets_url  = untrailingslashit( $assets_url );
		$this->initialize();
	}

	/**
	 * Initializes the assets.
	 *
	 * @return void
	 */
	public function initialize() {
		if ( ! $this->manifest ) {
			$this->set_manifest_filepath();
			$this->load_manifest();
			$this->get_asset_files();
			$this->get_entry_points();
		}
	}

	/**
	 * Sets the path to manifest file.
	 *
	 * @param string $manifest_path Patht to manifest JSON file.
	 *
	 * @throws \Exception Manifest file check.
	 *
	 * @return void
	 */
	public function set_manifest_filepath( $manifest_path = '' ) {
		$manifest_path = $manifest_path ? $manifest_path : trailingslashit( $this->assets_path ) . self::FILE_NAME;
		if ( ! is_readable( $manifest_path ) ) {
			throw new \Exception( 'Manifest file not found or is not readable: ' . $manifest_path );
		}
		$this->manifest_path = $manifest_path;
	}

	/**
	 * Loads the manifest file.
	 *
	 * @return void
	 */
	private function load_manifest() {
		if ( ! $this->manifest ) {
			// phpcs:ignore
			$manifest_json  = file_get_contents( $this->manifest_path );
			$this->manifest = json_decode( $manifest_json, true );
		}
	}

	/**
	 * Get the "files" prop from the manifest file.
	 *
	 * @return array
	 */
	public function get_asset_files() {
		if ( ! $this->asset_files ) {
			if ( empty( $this->manifest[ self::KEY_FILES ] ) ) {
				return [];
			}
			$this->asset_files = $this->manifest[ self::KEY_FILES ];
		}
		return $this->asset_files;
	}

	/**
	 * Get the "entrypoints" prop from the manifest file.
	 *
	 * @return array
	 */
	public function get_entry_points() {
		if ( ! $this->entry_points ) {
			if ( empty( $this->manifest[ self::KEY_ENTRY_POINTS ] ) ) {
				return [];
			}
			$this->entry_points = array_keys( $this->manifest[ self::KEY_ENTRY_POINTS ] );
		}
		return $this->entry_points;
	}

	/**
	 * Get the relative path of an asset.
	 *
	 * @param string $entry_point The entry point to get the asset for.
	 * @param string $type        The type of asset - JS, PHP, CSS.
	 *
	 * @return string
	 */
	private function get_asset( $entry_point, $type = self::ASSET_EXT_JS ) {
		return $this->has_asset( $entry_point, $type ) ? $this->asset_files[ $entry_point . $type ] : '';
	}

	/**
	 * Get the dependencies of an asset.
	 *
	 * @param string $entry_point The entry point to get the asset dependencies for.
	 * @param string $type        The type of asset - JS, PHP, CSS.
	 *
	 * @return array
	 */
	public function get_asset_dependencies( $entry_point, $type = self::ASSET_EXT_JS ) {
		$asset = $this->get_asset_details( $entry_point );
		if ( ! isset( $asset[ self::KEY_DEPENDENCIES ] ) ) {
			return [];
		}

		$dependencies = $asset[ self::KEY_DEPENDENCIES ];

		return $dependencies;
	}

	/**
	 * Get the asset details from the asset.php file for the entrypoint.
	 *
	 * @param string $entry_point The entry point to get the asset details for.
	 *
	 * @throws \Exception Assets file check.
	 *
	 * @return array
	 */
	public function get_asset_details( $entry_point ) {
		$file_name = $this->get_asset( $entry_point, self::ASSET_EXT_PHP );
		if ( ! $file_name ) {
			return [];
		}
		$full_path = $this->assets_path . $file_name;
		if ( ! is_readable( $full_path ) ) {
			throw new \Exception( 'Asset file not found or is not readable: ' . $full_path );
		}
		return require $full_path;
	}

	/**
	 * Get the path to assets directory.
	 *
	 * @param string $path Path to append.
	 * @return string
	 */
	public function path( $path = '' ) {
		return $this->assets_path . $path;
	}

	/**
	 * Get the URL to assets directory.
	 *
	 * @param string $path Path to append.
	 * @return string
	 */
	public function url( $path = '' ) {
		return $this->assets_url . $path;
	}

	/**
	 * Get the absolute path of an asset.
	 *
	 * @param string $entry_point The entry point to get the asset for.
	 * @param string $type        The type of asset - JS, PHP, CSS.
	 *
	 * @return string
	 */
	public function get_asset_path( $entry_point, $type = self::ASSET_EXT_JS ) {
		$path = $this->get_asset( $entry_point, $type );
		return $this->path( $path );
	}

	/**
	 * Get the URL for an asset.
	 *
	 * @param string $entry_point The entry point to get the asset dependencies for.
	 * @param string $type        The type of asset - JS, PHP, CSS.
	 *
	 * @return string
	 */
	public function get_asset_url( $entry_point, $type = self::ASSET_EXT_JS ) {
		$path = $this->get_asset( $entry_point, $type );
		return $this->url( $path );
	}

	/**
	 * Get the version for an asset.
	 *
	 * @param string $entry_point The entry point to get the asset dependencies for.
	 * @param string $type        The type of asset - JS, PHP, CSS.
	 *
	 * @return string|int|false
	 */
	public function get_asset_version( $entry_point, $type = self::ASSET_EXT_JS ) {
		$asset = $this->get_asset_details( $entry_point );
		return self::ASSET_EXT_JS === $type && isset( $asset[ self::KEY_VERSION ] )
			? $asset[ self::KEY_VERSION ]
			: filemtime( $this->get_asset_path( $entry_point, $type ) );
	}

	/**
	 * Check whether the entrypoint has an asset.
	 *
	 * @param string $entry_point The entry point to get the asset dependencies for.
	 * @param string $type        The type of asset - JS, PHP, CSS.
	 *
	 * @return string
	 */
	public function has_asset( $entry_point, $type = self::ASSET_EXT_JS ) {
		$file_name = $entry_point . $type;
		return ! empty( $this->asset_files[ $file_name ] );
	}
}
