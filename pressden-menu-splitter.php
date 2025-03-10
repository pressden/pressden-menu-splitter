<?php
/**
 * Plugin Name: PressDen Menu Splitter
 * Plugin URI: https://github.com/pressden/pressden-menu-splitter
 * Description: PressDen Menu Splitter.
 * Version: 1.0
 * Author: D.S. Webster
 * Author URI: https://dswebs.me/
 *
 * @package PDMS
 */

/**
 * Define constants.
 */
define( 'PDMS_PREFIX', 'pdms' );
define( 'PDMS_META_KEY', PDMS_PREFIX . '_segments' );
define( 'PDMS_VERSION', '1.0.0' );
define( 'PDMS_PATH', plugin_dir_path( __FILE__ ) );
define( 'PDMS_URL', plugin_dir_url( __FILE__ ) );

/**
 * Require classes.
 */
require_once PDMS_PATH . '/src/PDMS/Splitter/Controllers/Splitter.php';
require_once PDMS_PATH . '/src/PDMS/Splitter/Controllers/SplitterAdmin.php';
require_once PDMS_PATH . '/src/PDMS/Splitter/Controllers/SplitterHooks.php';
require_once PDMS_PATH . '/src/PDMS/Splitter/Controllers/SplitterAdminListTable.php';
require_once PDMS_PATH . '/src/PDMS/Splitter/Models/SegmentsModel.php';

/**
 * Initialize the plugin.
 *
 * @return void
 */
function pdms_init() {
	$pdms_splitter = new PDMS\Splitter\Controllers\Splitter();
}
add_action( 'plugins_loaded', 'pdms_init' );
