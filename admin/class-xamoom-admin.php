<?php

/**
 * The dashboard-specific functionality of the plugin.
 *
 * @link       http://xamoom.com
 * @since      1.0.0
 *
 * @package    xamoom
 * @subpackage xamoom/admin
 */

/**
 * The dashboard-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the dashboard-specific stylesheet and JavaScript.
 *
 * @package    xamoom
 * @subpackage xamoom/admin
 * @author     Bruno Hautzenberger <bruno@xamoom.com>
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

	public function addMenu() {
		//add_menu_page( "xamoom page", , "manage_options", "xamoom-admin", array($this,'showAdmin'), null, 6 );
		add_options_page( "xamoom", "xamoom", "manage_options", "xamoom-settings", array($this,'showAdmin'));
	}

	public function publishAPIKeyToJavaScript() {
		print "<script type='text/javascript'>var xamoom_api_key = '" . get_option('xamoom_api_key') . "';</script>";
	}

	public function publishSystemEndpointToJavaScript() {
		print "<script type='text/javascript'>var xamoom_api_endpoint = '" . $this->api_endpoint . "';</script>";
	}

	public function showAdmin(){
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
								<p class="description">This API key is bound to your xamoom System. If you do not have a xamoom system you can order one on <a href="http://xamoom.com">xamoom.com</a>. If you have forgotten your API key please contact <a href="mailto:support@xamoom.com">support@xamoom.com</a>.</p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="xamoom_custom_css">Custom-CSS</label></th>
							<td>
								<textarea name="xamoom_custom_css" id="xamoom_custom_css" class="large-text code" rows="4"><?php echo get_option('xamoom_custom_css'); ?></textarea>
								<p class="description">The following CSS classes are used by xamoom to display and format the content: .xamoom_link, .xamoom_smalltext, .xamoom_audio, .xamoom_headline, .xamoom_image, .xamoom-videoWrapper and .xamoom-videoWrapper iframe.<br/> Feel free to add your custom styling here.</p>
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
