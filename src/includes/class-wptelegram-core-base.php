<?php

/**
 * The most common things
 *
 * @link       https://t.me/manzoorwanijk
 * @since      1.0.0
 *
 * @package    WPTelegram
 * @subpackage WPTelegram/includes
 */

/**
 * The most common things
 *
 * Defines the plugin name, version, and
 * enqueues the stylesheet and JavaScript.
 *
 * @package    WPTelegram
 * @subpackage WPTelegram/includes
 * @author     Manzoor Wani <@manzoorwanijk>
 */
class WPTelegram_Core_Base {

    /**
     * The unique identifier of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * Title of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_title    Title of the plugin
     */
    protected $plugin_title;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * The sub directory to which the file belongs
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $sub_dir    The plugin sub directory
     */
    protected $sub_dir;

	/**
	 * The suffix to be used for JS and CSS files
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $suffix    The suffix to be used for JS and CSS files
	 */
	protected $suffix;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since	1.0.0
	 * @param 	string    $plugin_title	Title of the plugin
	 * @param	string    $plugin_name	The name of the plugin.
	 * @param	string    $version		The version of this plugin.
	 */
	public function __construct( $plugin_title, $plugin_name, $version ) {
		
        $this->plugin_name  = $plugin_name;
        $this->plugin_title = $plugin_title;
        $this->version      = $version;

        // Use minified libraries if SCRIPT_DEBUG is turned off
        $this->suffix       = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
	}

    /**
     * Register a stylesheet or JavaScript
     *
     * @since    1.0.0
     */
    final protected function enqueue( $type, $handle, $file_name, $path, $deps = array() ) {

        $base_url   = WPTELEGRAM_URL . "/{$this->sub_dir}";

        switch ( $type ) {

            case 'style':
                
                $src = "{$base_url}/{$path}/{$file_name}{$this->suffix}.css";

                wp_enqueue_style( $handle, $src, $deps, $this->version, 'all' );
                break;

            case 'script':
                
                $src = "{$base_url}/{$path}/{$file_name}{$this->suffix}.js";

                wp_enqueue_script( $handle, $src, $deps, $this->version, true );
                break;
        }
    }

    /**
     * Register the stylesheet
     *
     * @since    1.0.0
     */
    protected function enqueue_style( $handle, $file_name, $path = 'css', $deps = array() ) {
        $this->enqueue( 'style', $handle, $file_name, $path, $deps );
    }

    /**
     * Register the JavaScript
     *
     * @since    1.0.0
     */
    protected function enqueue_script( $handle, $file_name, $path = 'js', $deps = array() ) {
        $this->enqueue( 'script', $handle, $file_name, $path, $deps );
    }
}