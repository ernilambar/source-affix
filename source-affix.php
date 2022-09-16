<?php
/**
 * Source Affix Plugin.
 *
 * Plugin Name: Source Affix
 * Plugin URI: https://www.nilambar.net/2013/10/source-affix-wordpress-plugin.html
 * Description: Plugin to add sources in your posts, pages or custom post types
 * Version: 2.0.3
 * Author: Nilambar Sharma
 * Author URI: https://www.nilambar.net
 * Text Domain: source-affix
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * @package Source_Affix
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'SOURCE_AFFIX_VERSION', '2.0.3' );
define( 'SOURCE_AFFIX_SLUG', 'source-affix' );
define( 'SOURCE_AFFIX_BASENAME', basename( dirname( __FILE__ ) ) );
define( 'SOURCE_AFFIX_DIR', rtrim( plugin_dir_path( __FILE__ ), '/' ) );
define( 'SOURCE_AFFIX_URL', rtrim( plugin_dir_url( __FILE__ ), '/' ) );

// Init autoload.
if ( file_exists( SOURCE_AFFIX_DIR . '/vendor/autoload.php' ) ) {
	require_once SOURCE_AFFIX_DIR . '/vendor/autoload.php';
	require_once SOURCE_AFFIX_DIR . '/vendor/ernilambar/optioner/optioner.php';
}

// Load helper.
require_once SOURCE_AFFIX_DIR . '/includes/helpers/helper.php';

// Include plugin classes.
require_once SOURCE_AFFIX_DIR . '/includes/classes/class-source-affix.php';
require_once SOURCE_AFFIX_DIR . '/includes/classes/class-source-affix-admin.php';

add_action( 'plugins_loaded', array( 'Source_Affix', 'get_instance' ) );
add_action( 'plugins_loaded', array( 'Source_Affix_Admin', 'get_instance' ) );
