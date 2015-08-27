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
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the dashboard-specific stylesheet and JavaScript.
 *
 * @package    xamoom
 * @subpackage xamoom/public
 * @author     xamoom GmbH
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
	 * @param      string    $api_endpoint    The url of the backend api
	 */
	public function __construct( $plugin_name, $version, $api_endpoint ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->api_endpoint = $api_endpoint;
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

	/**
	 * Register the shortcode for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function add_shortcode() {
		add_shortcode( 'xamoom', array($this,'include_page_shortcode'));
	}

	/**
	 * Renders the xamoom content according to the shortcode
	 *
	 * @since    1.0.0
	 */
	public function include_page_shortcode( $atts ) {
		//load shortcode attributes
		extract( shortcode_atts( array('lang' => 'nolanginshortcode',), $atts ) );
		extract( shortcode_atts( array('id' => 'noidinshortcode',), $atts ) );

		//call backend apiu
		$response = $this->call_api("GET",$this->api_endpoint . "content/" . $id . "/" . $lang);

		$content = json_decode($response, true);

	  //extract custom marker if there is one
	  $custom_map_marker = false;
	  if(array_key_exists("custom_marker",$content['system_style'])){ $custom_map_marker = base64_decode($content['system_style']['custom_marker']); }

	  //add custom css
	  $html = "<style type='text/css'>" . get_option('xamoom_custom_css') . "</style>";

		$map_id = 1; //used to give seperate ids to seperate spotmaps

		//render block HTML for each content block
	  for($i = 0; $i < count($content['content_blocks']); $i++){
			$block = $content['content_blocks'][$i];
			$block_type = $block['content_block_type']; //get block type

			$html .= "<div class='xamoom_block'>"; //initializes content block div container

			switch ($block_type) {
		    case "0": //TEXT
					if(array_key_exists("title",$block) && $block['title'] != ""){ $html .=  "<h2 class='xamoom_headline'>" . $block['title'] . "</h2>"; }
					if(array_key_exists("text",$block)){ $html .=  "<p>" . $block['text'] . "</p>"; }
					break;

		    case "1": //AUDIO
					if(array_key_exists("title",$block)){ $html .=  "<p class='xamoom_title'>" . $block['title'] . "</p>"; }
					if(array_key_exists("artists",$block)){ $html .=  "<p class='xamoom_smalltext'>" . $block['artists'] . "</p>"; }
					if(array_key_exists("file_id",$block)){
					    $html .=  "<audio class='xamoom_audio' controls><source src='" . $block['file_id'] . "'>Your browser does not support the audio element.</audio>";
					}
					break;

		    case "2": //VIDEO
					if (strpos($block['video_url'],'youtube.com') == false && strpos($block['video_url'],'youtu.be') == false) { //VIDEO FILE
						//html5 video player
						$html .= "<video width='100%' controls>
												<source src='" . $block['video_url'] . "'>
											  Your browser does not support the video tag.
											</video>";
					} else { //YOUTUBE
						//extract youtube id
						parse_str( parse_url( $block['video_url'], PHP_URL_QUERY ), $query_vars );
						$youtube_id = $query_vars['v'];

						$html .= "<div class='xamoom-videoWrapper'>" .
							    		"<iframe width='560' height='349' src='https://www.youtube.com/embed/" . $youtube_id . "' frameborder='0' allowfullscreen></iframe>" .
										 "</div>";
					}
					if(array_key_exists("title",$block) && $block['title'] != ""){ $html .=  "<p class='xamoom_caption'>" . $block['title'] . "</p>"; }

					break;

		    case "3": //IMAGE
					$scale = 100;
					if(array_key_exists("scale_x",$block) && $block['scale_x'] != ""){ $scale =  $block['scale_x']; }

					if(array_key_exists("file_id",$block)){ $html .=  "<img class='xamoom_image' style='width:" . $scale . "%;' src='" . $block['file_id'] . "' />"; }
					if(array_key_exists("title",$block) && $block['title'] != ""){ $html .=  "<p class='xamoom_caption'>" . $block['title'] . "</p>"; }

					break;

		    case "4": //LINK
					$link_title = "";
					$link_url = "";
					$link_type = "0";
					if(array_key_exists("title",$block)){ $link_title = $block['title']; }
					if(array_key_exists("link_url",$block)){ $link_url = $block['link_url']; }
					if(array_key_exists("link_type",$block)){ $link_type = $block['link_type']; }

					//find fitting icon
					$icon = "fa-globe";
					switch ($link_type) {
					  case "0": //FACEBOOK
							$icon = "fa-facebook-square";
							break;
					  case "1": //TWITTER
							$icon = "fa-twitter-square";
							break;
					  case "2": //WEB
							$icon = "fa-globe";
							break;
					  case "3": //AMAZON
							$icon = "fa-shopping-cart";
							break;
					  case "4": //WIKIPEDIA
							$icon = "fa-globe";
							break;
					  case "5": //LINKEDIN
							$icon = "fa-linkedin";
							break;
					  case "6": //FLICKR
							$icon = "fa-flickr";
							break;
					  case "7": //SOUNDCLOUD
							$icon = "fa-soundcloud";
							break;
					  case "8": //ITUNES
							$icon = "fa-music";
							break;
					  case "9": //YOUTUBE
							$icon = "fa-youtube-play";
							break;
					  case "10": //GOOGLE+
							$icon = "fa-google-plus";
							break;
						case "11": //TEL
							$icon = "fa-phone";
							break;
						case "12": //EMAIL
							$icon = "fa-envelope-o";
							break;
						case "13": //SPOTIFY
							$icon = "fa-spotify";
							break;
						case "14": //GOOGLE_MAPS
							$icon = "fa-location-arrow";
							break;
					}

					//render block
					$html .= "<p class='xamoom_link'><i class='fa " . $icon . "'></i> <a href='" . $link_url . "' target='_blank'>" . $link_title . "</a></p>";
					if(array_key_exists("text",$block)){ $html .=  "<p class='xamoom_smalltext'>" . $block['text'] . "</p>"; }
					break;

		    case "5": //EBOOK
					$ebook_url = "";
					if(array_key_exists("file_id",$block)){ $ebook_url = $block['file_id']; }

					if(array_key_exists("title",$block)){
						$html .= "<p class='xamoom_link'><i class='fa fa-book'></i> <a href='" . $ebook_url . "'>" . $block['title'] . "</a></p>";
					} else {
						$html .= "<p class='xamoom_link'><i class='fa fa-book'></i> <a href='" . $ebook_url . "'>Download Ebook</a></p>";
					}

					if(array_key_exists("artists",$block)){ $html .=  "<p class='xamoom_smalltext'>" . $block['artists'] . "</p>"; }
					break;

		    case "6": //CONTENT BLOCK CONTENT GET'S IGNORED, BECAUSE IT MAKES NO SENSE TO LINK TO XAMOOM CONTENT PAGES
					break;

		    case "7": //SOUNDCLOUD
					if(array_key_exists("title",$block) && $block['title'] != ""){ $html .=  "<h2 class='xamoom_headline'>" . $block['title'] . "</h2>"; }
					$html .= "<iframe width='100%' height='150' scrolling='no' frameborder='no' " .
						    "src='https://w.soundcloud.com/player/?url=" . $block['soundcloud_url'] . "&amp;auto_play=false&amp;hide_related=true&amp;show_comments=false&amp;show_user=false&amp;show_reposts=false&amp;visual=true'>" .
						    "</iframe>";

					break;

		    case "8": //DOWNLOAD
					$download_title = "";
					$download_url = "";
					$download_type = "0";
					if(array_key_exists("title",$block)){ $download_title = $block['title']; }
					if(array_key_exists("file_id",$block)){ $download_url = $block['file_id']; }
					if(array_key_exists("download_type",$block)){ $download_type = $block['download_type']; }

					$icon = "fa-user-plus";
					switch ($download_type) {
					    case "0": //VCF
						$icon = "fa-user-plus";
						break;
					    case "1": //ICAL
						$icon = "fa-calendar";
						break;
					}

					$html .= "<p class='xamoom_link'><i class='fa " . $icon . "'></i> <a href='" . $download_url . "'>" . $download_title . "</a></p>";
					if(array_key_exists("text",$block)){ $html .=  "<p class='xamoom_smalltext'>" . $block['text'] . "</p>"; }
					break;

		    case "9": //SPOTMAP
					if(array_key_exists("title",$block) && $block['title'] != ""){ $html .=  "<p class='xamoom_title'>" . $block['title'] . "</p>"; }
					$this_map_id = "xamoom-map-" . $id . "-" . $map_id; //geet new map id that is unique on this page

					//get spot map
					$spot_map_response = $this->call_api("GET",$this->api_endpoint . "spotmap?map_tag=" . $block['spot_map_tag'] . "&language=" . $lang);
					$spot_map = json_decode($spot_map_response, true);

					//render map
					$html .= "<div class='xamoom-map' id='" . $this_map_id . "'></div>";

					//initialize script
					$html .= "<script language='JavaScript'>\n
						    		function renderMap_" . $map_id . "(width,height){\n
											var map = L.map('" . $this_map_id . "').setView([0,0], 13);\n

											// add OpenStreetMap tile layer
											L.tileLayer('http://{s}.tile.openstreetmap.se/hydda/full/{z}/{x}/{y}.png', { attribution: '&copy; <a href=\"http://leafletjs.com\" title=\"A JS library for interactive maps\">Leaflet</a> | Tiles courtesy of <a href=\"http://hot.openstreetmap.se/\" target=\"_blank\">OpenStreetMap Sweden</a> — Map data © <a href=\"http://openstreetmap.org\">OpenStreetMap</a> contributors, <a href=\"http://creativecommons.org/licenses/by-sa/2.0/\">CC-BY-SA</a>'}).addTo(map);\n

											var bounds = [];\n";

					//if there is a custom marker, set it up.
					if($custom_map_marker){
							$html .= "\nvar factor = 33 / height;";
							$html .= "\nvar new_height = parseInt(height * factor);";
							$html .= "\nvar new_width = parseInt(width * factor);";
					    $html .= "\nvar LeafIcon = L.Icon.extend({options: {iconSize:[new_width, new_height],iconAnchor:[new_height / 2, new_height - 1],popupAnchor:  [0, -new_height]}}); ";
					    $html .= "\nvar custom_marker = new LeafIcon({iconUrl: '" . $custom_map_marker ."'});";
					}

					//render marker script
					for($j = 0; $j < count($spot_map['items']); $j++){
					    $marker = $spot_map['items'][$j];

					    //kill line breaks from marker descriptions and display_name
							$marker['description'] = str_replace(array("\r", "\n"), "<br>", $marker['description']);
					    $marker['display_name'] = str_replace(array("\r", "\n"), "<br>", $marker['display_name']);

							$marker['description'] = str_replace(array('"'), '\"', $marker['description']);
					    $marker['display_name'] = str_replace(array("'"), "\'", $marker['display_name']);

					    //extract image
					    $image = "";
					    if(array_key_exists("image",$marker)){ $image =  "<img style='width:100%;' src='" . $marker['image'] . "' /><br />"; }

					    // add a markers
					    if($custom_map_marker){
								$html .= "\nL.marker([" . $marker['location']['lat'] . ", " . $marker['location']['lon'] . "],{icon: custom_marker}).addTo(map).bindPopup(\"<b>" . $marker['display_name'] . "</b><br>" . $image . $marker['description'] . "\");";
					    } else {
								$html .= "\nL.marker([" . $marker['location']['lat'] . ", " . $marker['location']['lon'] . "]).addTo(map).bindPopup(\"<b>" . $marker['display_name'] . "</b><br>" . $image . $marker['description'] . "\");";
					    }

					    $html .= "\nbounds.push([" . $marker['location']['lat'] . "," . $marker['location']['lon'] . "]);";
					}

					//finalze JavaScript function to render map block
					$html .= "map.fitBounds(bounds);\n
										map.zoomOut();\n
				    			}";

					//start map rendering in JS
					if($custom_map_marker){
					    $html .= "var img = new Image();
							img.onload = function() {
							    renderMap_" . $map_id . "(this.width,this.height);
							}
							img.src = '" . $custom_map_marker . "';";
					} else { //render without custom makrer
					    $html .= "renderMap_" . $map_id . "(this.width,this.height);";
					}

					$html .= "</script>"; //end script

					$map_id++; //increment map_id
					break;

		    default: // show unknow blocks
					$html .= "<p style='color:#ff00ff;'>" . http_build_query($block) . "</p>";
			}

			$html .= "</div>"; //finalize content block div

	  }

	    return $html;
	}

	/**
	 * Calls the backend API
	 *
	 * @since    1.0.0
	 */
	public function call_api($method, $url, $data = false){
	    $data_string = json_encode($data);
	    $curl = curl_init();

	    switch ($method)
	    {
	        case "POST":
	          curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");

	          if ($data){
	              curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);

								curl_setopt($curl, CURLOPT_HTTPHEADER, array(
										'Content-Type: application/json',
										'Content-Length: ' . strlen($data_string),
										'Authorization: ' . get_option('xamoom_api_key'))
								);
						}

		        break;

					case "GET":
	          curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");

						curl_setopt($curl, CURLOPT_HTTPHEADER, array(
								'Authorization: ' . get_option('xamoom_api_key'))
						);

	          break;

	        default:
	          if ($data)
	            $url = sprintf("%s?%s", $url, http_build_query($data));
	    }



	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($curl, CURLOPT_URL, $url);

	    $result = curl_exec($curl);

	    curl_close($curl);

	    return $result;
	}

}
