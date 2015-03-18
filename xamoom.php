<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * Dashboard. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://xamoom.com
 * @since             1.0.0
 * @package           xamoom
 *
 * @wordpress-plugin
 * Plugin Name:       xamoom
 * Plugin URI:        http://xamoom.com
 * Description:       This plugin allows you to sync xamoom pages to Wordpress
 * Version:           1.0.0
 * Author:            xamoom GmbH
 * Author URI:        http://xamoom.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       xamoom
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-plugin-name-activator.php
 */
function activate_xamoom() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-xamoom-activator.php';
	xamoom_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-plugin-name-deactivator.php
 */
function deactivate_xamoom() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-xamoom-deactivator.php';
	xamoom_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_xamoom' );
register_deactivation_hook( __FILE__, 'deactivate_xamoom' );

/**
 * The core plugin class that is used to define internationalization,
 * dashboard-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-xamoom.php';

/**
 * Load Tiny MCE Plugin
 */
require plugin_dir_path( __FILE__ ) .  'tiny-mce-plugin/xamoom-tiny-mce.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_xamoom() {
	$plugin = new xamoom();
	$plugin->run();

}
run_xamoom();
