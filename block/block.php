<?php

/*
xamoom Wordpress Plugin
Copyright (C) 2018  xamoom GmbH

This file is part of xamoom-wordpress.

xamoom-wordpress is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
(at your option) any later version.

xamoom-wordpress is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with xamoom-wordpress.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * The dashboard-specific functionality of the plugin.
 *
 * @package    xamoom
 * @subpackage xamoom/block
 * @author     xamoom GmbH
 */
class xamoom_Block {

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 * @param      string    $api_endpoint    The url of the backend api
	 */
	public function __construct( $plugin_name, $version, $api_endpoint ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->api_endpoint = $api_endpoint;

	}

	/**
	 * Register the stylesheets for the Dashboard.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		//wp_enqueue_style( $this->plugin_name . '-block', plugin_dir_url( __FILE__ ) . 'css/xamoom-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the dashboard.
	 *
	 * @since    2.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name . '-block', plugin_dir_url( __FILE__ ) . 'js/block.js', array( 'wp-blocks', 'wp-i18n', 'wp-element', 'jquery' ), $this->version, false );
	}
}
