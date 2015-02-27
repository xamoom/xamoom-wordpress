<?php

/**
 * The dashboard-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/admin
 */

/**
 * The dashboard-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the dashboard-specific stylesheet and JavaScript.
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/admin
 * @author     Your Name <email@example.com>
 */
class Plugin_Name_Admin {

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
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the Dashboard.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/plugin-name-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the dashboard.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/plugin-name-admin.js', array( 'jquery' ), $this->version, false );

	}
	
	public function addMenu() {
		add_menu_page( "xamoom page", "xamoom menu", "manage_options", "xamoom-admin", array($this,'showAdmin'), null, 6 );
	}
	
	public function showAdmin(){
		?>
		<div class="wrap">
			<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
			
			<table class="widefat fixed comments">
				<thead>
					<tr>
						<th scope="col" id="cb" class="manage-column column-cb check-column" style="">
							<label class="screen-reader-text" for="cb-select-all-1">Alle auswählen</label>
							<input id="cb-select-all-1" type="checkbox">
						</th>
						<th scope="col" id="author" class="manage-column column-author sortable desc" style="">
							<a href="http://vienna.pingeb.org/wp-admin/edit-comments.php?orderby=comment_author&amp;order=asc">
								<span>Autor</span><span class="sorting-indicator"></span>
							</a>
						</th>
						<th scope="col" id="comment" class="manage-column column-comment" style="">
							Kommentar
						</th>
						<th scope="col" id="response" class="manage-column column-response sortable desc" style="">
							<a href="http://vienna.pingeb.org/wp-admin/edit-comments.php?orderby=comment_post_ID&amp;order=asc">
								<span>Als Antwort auf</span><span class="sorting-indicator"></span>
							</a>
						</th>
					</tr>
				</thead>
			
				<tfoot>
				<tr>
					<th scope="col" class="manage-column column-cb check-column" style=""><label class="screen-reader-text" for="cb-select-all-2">Alle auswählen</label><input id="cb-select-all-2" type="checkbox"></th><th scope="col" class="manage-column column-author sortable desc" style=""><a href="http://vienna.pingeb.org/wp-admin/edit-comments.php?orderby=comment_author&amp;order=asc"><span>Autor</span><span class="sorting-indicator"></span></a></th><th scope="col" class="manage-column column-comment" style="">Kommentar</th><th scope="col" class="manage-column column-response sortable desc" style=""><a href="http://vienna.pingeb.org/wp-admin/edit-comments.php?orderby=comment_post_ID&amp;order=asc"><span>Als Antwort auf</span><span class="sorting-indicator"></span></a></th>	</tr>
				</tfoot>
			
				<tbody id="the-comment-list" data-wp-lists="list:comment">
					<tr class="no-items"><td class="colspanchange" colspan="4">Es wurden keine Kommentare gefunden.</td></tr>	</tbody>
			
				<tbody id="the-extra-comment-list" data-wp-lists="list:comment" style="display: none;">
				</tbody>
			</table>
		</div>
		<?php
	}
}
