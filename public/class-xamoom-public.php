<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://xamoom.com
 * @since      1.0.0
 *
 * @package    xamoom
 * @subpackage xamoom/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the dashboard-specific stylesheet and JavaScript.
 *
 * @package    xamoom
 * @subpackage xamoom/public
 * @author     Bruno Hautzenberger <bruno@xamoom.com>
 */
class xamoom_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/xamoom-public.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name . "-FONTAWESOME", "//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css", array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name . "-LEAFLET", "//cdn.leafletjs.com/leaflet-0.7.3/leaflet.css", array(), $this->version, 'all' );
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/xamoom-public.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name . "-LEAFLET", "//cdn.leafletjs.com/leaflet-0.7.3/leaflet.js", array( 'jquery' ), $this->version, false );
	}

}
