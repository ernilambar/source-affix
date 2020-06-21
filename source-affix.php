<?php
/**
 * Source Affix Plugin.
 *
 * Plugin Name: Source Affix
 * Plugin URI: https://www.nilambar.net/2013/10/source-affix-wordpress-plugin.html
 * Description: Plugin to add sources in your posts, pages or custom post types
 * Version: 2.0.0
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

define( 'SOURCE_AFFIX_VERSION', '2.0.0' );
define( 'SOURCE_AFFIX_BASENAME', basename( dirname( __FILE__ ) ) );
define( 'SOURCE_AFFIX_DIR', rtrim( plugin_dir_path( __FILE__ ), '/' ) );
define( 'SOURCE_AFFIX_URL', rtrim( plugin_dir_url( __FILE__ ), '/' ) );

// Load helper.
require_once SOURCE_AFFIX_DIR . '/includes/helper.php';

// Include plugin classes.
require_once SOURCE_AFFIX_DIR . '/class-source-affix.php';
require_once SOURCE_AFFIX_DIR . '/class-source-affix-admin.php';

// Register hooks that are fired when the plugin is activated or deactivated.
register_activation_hook( __FILE__, array( 'Source_Affix', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Source_Affix', 'deactivate' ) );

add_action( 'plugins_loaded', array( 'Source_Affix', 'get_instance' ) );
add_action( 'plugins_loaded', array( 'Source_Affix_Admin', 'get_instance' ) );
