<?php
/**
 * The file that defines the activator class of the plugin.
 *
 * A class definition that holds the code that would execute on plugin activation.
 *
 * @link       https://github.com/vermadarsh/
 * @since      1.0.0
 *
 * @package    Core_Functions
 * @subpackage Core_Functions/includes
 */

/**
 * The activation class.
 *
 * A class definition that holds the code that would execute on plugin activation.
 *
 * @since      1.0.0
 * @package    Core_Functions
 * @author     Adarsh Verma <adarsh.srmcem@gmail.com>
 */
class Cf_Core_Functions_Activator {
	/**
	 * Enqueue scripts for admin end.
	 */
	public static function run() {
		// Create a log directory within the WordPress uploads directory.
		$_upload     = wp_upload_dir();
		$_upload_dir = $_upload['basedir'];
		$_upload_dir = "{$_upload_dir}/wc-logs/";

		if ( ! file_exists( $_upload_dir ) ) {
			mkdir( $_upload_dir, 0755, true );
		}
	}
}
