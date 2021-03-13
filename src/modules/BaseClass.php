<?php
/**
 * The base class of the module.
 *
 * @link       https://t.me/manzoorwanijk
 * @since      x.y.z
 *
 * @package    WPTelegram\Core
 * @subpackage WPTelegram\Core\includes
 */

namespace WPTelegram\Core\modules;

/**
 * The base class of the module.
 *
 * The base class of the module.
 *
 * @package    WPTelegram\Core
 * @subpackage WPTelegram\Core\includes
 * @author     Manzoor Wani <@manzoorwanijk>
 */
abstract class BaseClass {

	/**
	 * The module class instance.
	 *
	 * @since    x.y.z
	 * @access   protected
	 * @var      BaseModule $module The module class instance.
	 */
	protected $module;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since x.y.z
	 * @param BaseModule $module The module class instance.
	 */
	public function __construct( $module ) {

		$this->module = $module;
	}
}
