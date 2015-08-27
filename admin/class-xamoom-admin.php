<?php

/*
xamoom Wordpress Plugin
Copyright (C) 2015  xamoom GmbH

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
 * @subpackage xamoom/admin
 * @author     xamoom GmbH
 */
class xamoom_Admin {

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
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/xamoom-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the dashboard.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/xamoom-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Register the Admin Settings Page
	 *
	 * @since    1.0.0
	 */
	public function register_settings_page() {
		add_options_page( "xamoom", "xamoom", "manage_options", "xamoom-settings", array($this,'show_settings_page'));
	}

	/**
	 * Publish the API Key from settings as a javascript var. (Admin Backend Only)
	 *
	 * @since    1.0.0
	 */
	public function publish_api_key_to_js() {
		print "<script type='text/javascript'>var xamoom_api_key = '" . get_option('xamoom_api_key') . "';</script>";
	}

	/**
	 * Publish the API Endpoint URL as a javascript var. (Admin Backend Only)
	 *
	 * @since    1.0.0
	 */
	public function publish_api_endpoint_to_js() {
		print "<script type='text/javascript'>var xamoom_api_endpoint = '" . $this->api_endpoint . "';</script>";
	}

	/**
	 * Publish the API Endpoint URL as a javascript var. (Admin Backend Only)
	 *
	 * @since    1.0.0
	 */
	public function publish_i18n_strings_to_js() {
		print "<script type='text/javascript'>";
		print "var i18n_api_key_error = '" . __('Something went wrong. Please check you API Key on Settings xamoom.','xamoom') . "';";
		print "var i18n_search_pages = '" . __('Search Pages','xamoom') . "';";
		print "var i18n_load_more = '" . __('Load more','xamoom') . "';";
		print "var i18n_generic_error = '" . __('Something went wrong.','xamoom') . "';";
		print "var i18n_name = '" . __('Name','xamoom') . "';";
		print "var i18n_languages = '" . __('Language','xamoom') . "';";
		print "var i18n_insert = '" . __('Insert','xamoom') . "';";
		print "</script>";
	}

	/**
	 * Renders the actual settings page.
	 *
	 * @since    1.0.0
	 */
	public function show_settings_page(){
		?>

		<div class="wrap">
			<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
			<form method="post" action="options.php">
				<?php settings_fields( 'xamoom-settings-group' ); ?>

				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row"><label for="xamoom_api_key">API Key</label></th>
							<td>
								<input name="xamoom_api_key" type="text" id="xamoom_api_key" value="<?php echo get_option('xamoom_api_key'); ?>" class="regular-text code">
								<p class="description">
									<?php _e('This API key is bound to your xamoom System. If you do not have a xamoom system you can order one on','xamoom'); ?> <a href="http://xamoom.com">xamoom.com</a>. <?php _e('If you have forgotten your API key please contact','xamoom'); ?> <a href="mailto:support@xamoom.com">support@xamoom.com</a>.
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="xamoom_custom_css">Custom-CSS</label></th>
							<td>
								<textarea name="xamoom_custom_css" id="xamoom_custom_css" class="large-text code" rows="4"><?php echo get_option('xamoom_custom_css'); ?></textarea>
								<p class="description">
									<?php _e('The following CSS classes are used by xamoom to display and format the content: .xamoom_caption, .xamoom_title, .xamoom_link, .xamoom_smalltext, .xamoom_audio, .xamoom_headline, .xamoom_image, .xamoom-videoWrapper and .xamoom-videoWrapper-iframe.','xamoom'); ?><br/>
								</p>
							</td>
						</tr>
					</tbody>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
