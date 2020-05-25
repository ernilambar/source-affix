<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package Source_Affix
 */

// If uninstall, not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Remove plugin options.
delete_option( 'sa_plugin_options' );
