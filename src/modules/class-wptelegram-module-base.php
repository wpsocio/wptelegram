<?php

/**
 * The module specific functionality
 *
 * @link       https://t.me/manzoorwanijk
 * @since      1.0.0
 *
 * @package    WPTelegram
 * @subpackage WPTelegram/module
 */

/**
 * The module specific functionality
 *
 * @package    WPTelegram
 * @subpackage WPTelegram/module
 * @author     Manzoor Wani <@manzoorwanijk>
 */
class WPTelegram_Module_Base {

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
	 * The unique identifier of this module.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $module_name    The string used to uniquely identify this module.
	 */
	protected $module_name;

    /**
     * Title of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $module_title    Title of the module
     */
    protected $module_title;

    /**
     * Module options
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $module_options    Module options
     */
    protected $module_options;

	/**
	 * The module slug
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $slug    The module slug
	 */
	protected $slug;

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
	 * @param	string    $module_name	The name of the module.
	 */
	public function __construct( $module_name, $module_title ) {

        $this->plugin_name      = WPTG()->get_plugin_name();
        $this->plugin_title     = WPTG()->get_plugin_title();
        $this->version          = WPTG()->get_version();

        $this->module_name      = $module_name;
        $this->module_title     = $module_title;
        $this->module_options   = WPTG()->options( $this->module_name );
        $this->slug             = str_replace( '_', '-', $this->module_name );

        // Use minified libraries if SCRIPT_DEBUG is turned off
        $this->suffix           = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
	}

    /**
     * Register a stylesheet or JavaScript
     *
     * @since    1.0.0
     */
    final protected function enqueue( $type, $handle, $file_name, $path, $deps = array() ) {

        $handle     = "{$this->plugin_name}_{$handle}";
        $base_url   = WPTELEGRAM_MODULES_URL . "/{$this->slug}";

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