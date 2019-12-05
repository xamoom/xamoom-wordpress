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
		wp_enqueue_style( $this->plugin_name . "-FONTAWESOME", plugin_dir_url( __FILE__ ) . 'css/font-awesome.min.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name . "-LEAFLET", plugins_url('leaflet/leaflet.css', __FILE__), array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name . "-OWL", plugins_url('owl-carousel/assets/owl.carousel.min.css', __FILE__), array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name . "-OWL2", plugins_url('owl-carousel/assets/owl.theme.default.min.css', __FILE__), array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name . "-OWL3", plugins_url('owl-carousel/assets/ajax-loader.gif', __FILE__), array(), $this->version, 'all' );
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/xamoom-public.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name . "-LEAFLET", plugins_url('leaflet/leaflet.js', __FILE__), array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name . "-OWL", plugins_url('owl-carousel/owl.carousel.min.js', __FILE__), array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name . "-MOMENT", plugins_url('moment/moment.js', __FILE__), array( 'jquery' ), $this->version, false );
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
		//call backend api
		$response = $this->call_api($this->api_endpoint . "contents/" . $id . "?lang=" . $lang);
		$content = json_decode($response, true);
		//seperate includes into blocks and style
		$content_blocks = array();
		$style = false;
		$custom_map_marker = false;
		if ($content == null) {
			return '';
		}
		// HANDLE ERROR
		if(array_key_exists('errors', $content) && $content['errors']) {
			$errCode = $content['errors'][0]['code'];
			if ($errCode == 92 || $errCode == 93) { // password protected or spot only
				$html = '<div class="not-available-content"><i class="fa fa-lock"></i><span>This content is password protected<span></div>';
				return $html;
			} else { // any other error
				return '';
			}
		}

		if(array_key_exists('data', $content) && $content['data']['relationships']['system']['data']['id']) {
			$styles = $this->call_api($this->api_endpoint . "styles/" . $content['data']['relationships']['system']['data']['id']);

							//extract custom marker if there is one
							if(array_key_exists("map-pin",json_decode($styles, true)['data']['attributes'])){

								$custom_map_marker = json_decode($styles, true)['data']['attributes']['map-pin'];
							}
		}
		for($i = 0; $i < count($content['included']); $i++){
			$inc = $content['included'][$i];

		
		if($inc['type'] == "contentblocks"){
				array_push($content_blocks,$inc);
			}

			if($inc['type'] == "styles"){
				$style = $inc;

				//extract custom marker if there is one
				if(array_key_exists("map-pin",$style['attributes'])){

					$custom_map_marker = $style['attributes']['map-pin'];
				}
			}
		}


		//add custom css
		$html = "<style type='text/css'>" . get_option('xamoom_custom_css') . "</style>";
		

		//render block HTML for each content block
		for($i = 0; $i < count($content_blocks); $i++){
			$block = $content_blocks[$i]['attributes'];
			$block_type = $block['block-type']; //get block type

			$html .= "<div class='xamoom_block'>"; //initializes content block div container


			$html = $this->generate_blocks_html($block, $block_type, $html, $lang, $id, $custom_map_marker);
		$html .= "</div>"; //finalize content block div

	  }

	 $time_location_html = $this->generate_time_location_html($content, $lang);

	 return $time_location_html . $html;

	}

	public function generate_time_location_html($content, $lang) {
		$related_spot = null;
		if (isset($content['data']['relationships']['related-spot']['data']['id'])) {
			$api = $this->encodeURI($this->api_endpoint . "spots?id=". $content['data']['relationships']['related-spot']['data']['id'] ."&lang=" . $lang);
			$spot = $this->call_api($api);
			$related_spot = json_decode($spot, true);
		}
		$html = "<script>  \n
		 function downloadCalendar(title, description, dateFrom, dateTo) {  \n
			const anchor = document.createElement('a');  \n
			anchor.target = '_blank';  \n
			anchor.setAttribute('href', `data:text/calendar;charset=utf-8,\${encodeURIComponent(this.generateCalendar(title, description, dateFrom, dateTo))}`);  \n
			anchor.setAttribute('download', `\${title.replace(/\s/g, '_')}.ics`);  \n
			document.body.appendChild(anchor);  \n
			anchor.click();  \n
			document.body.removeChild(anchor);  \n
		  };  \n
	   function generateCalendar(subject, description, begin, stop) {  \n
		  const SEPARATOR = (navigator.appVersion.indexOf('Win') !== -1) ? '\\r\\n' : '\\n';  \n
		  const calendarStart = [  \n
			'BEGIN:VCALENDAR',  \n
			'PRODID:Calendar',  \n
			'VERSION:2.0',  \n
		  ].join(SEPARATOR);  \n
		  const calendarEnd = `\${SEPARATOR}END:VCALENDAR`;  \n
	    \n
		  const startDate = new Date(begin);  \n
		  const endDate = new Date(stop);  \n
		  const nowDate = new Date();  \n
	    \n
		  const startYear = (`0000\${startDate.getFullYear().toString()}`).slice(-4);  \n
		  const startMonth = (`00\${(startDate.getMonth() + 1).toString()}`).slice(-2);  \n
		  const startDay = (`00\${(startDate.getDate()).toString()}`).slice(-2);  \n
		  const startHours = (`00\${startDate.getHours().toString()}`).slice(-2);  \n
		  const startMinutes = (`00\${startDate.getMinutes().toString()}`).slice(-2);  \n
		  const startSeconds = (`00\${startDate.getSeconds().toString()}`).slice(-2);  \n
	    \n
		  const endYear = (`0000\${endDate.getFullYear().toString()}`).slice(-4);  \n
		  const endMonth = (`00\${(endDate.getMonth() + 1).toString()}`).slice(-2);  \n
		  const endDay = (`00\${(endDate.getDate()).toString()}`).slice(-2);  \n
		  const endHours = (`00\${endDate.getHours().toString()}`).slice(-2);  \n
		  const endMinutes = (`00\${endDate.getMinutes().toString()}`).slice(-2);  \n
		  const endSeconds = (`00\${endDate.getSeconds().toString()}`).slice(-2);  \n
	    \n
		  const nowYear = (`0000\${nowDate.getFullYear().toString()}`).slice(-4);  \n
		  const nowMonth = (`00\${(nowDate.getMonth() + 1).toString()}`).slice(-2);  \n
		  const nowDay = (`00\${(nowDate.getDate()).toString()}`).slice(-2);  \n
		  const nowHours = (`00\${nowDate.getHours().toString()}`).slice(-2);  \n
		  const nowMinutes = (`00\${nowDate.getMinutes().toString()}`).slice(-2);  \n
		  const nowSeconds = (`00\${nowDate.getSeconds().toString()}`).slice(-2);  \n
	    \n
		  // Since some calendars don't add 0 second events, we need to remove time if there is none...  \n
		  let startTime = '';  \n
		  let endTime = '';  \n
		  if (startHours + startMinutes + startSeconds + endHours + endMinutes + endSeconds !== 0) {  \n
			startTime = `T\${startHours}\${startMinutes}\${startSeconds}`;  \n
			endTime = `T\${endHours}\${endMinutes}\${endSeconds}`;  \n
		  }  \n
		  const nowTime = `T\${nowHours}\${nowMinutes}\${nowSeconds}`;  \n
	    \n
		  const start = startYear + startMonth + startDay + startTime;  \n
		  const end = endYear + endMonth + endDay + endTime;  \n
		  const now = nowYear + nowMonth + nowDay + nowTime;  \n
		  let descriptionCopy = description;  \n
		  if (!description || description === 'None') {  \n
			descriptionCopy = '';  \n
		  }  \n
		  let location = null; \n";
		  if ($related_spot['data']['attributes']['location']) {
			  $html .= "location = '". $related_spot['data']['attributes']['name'] ."';";
		  }
			$html .= "let calendarEvent = [  \n
			'BEGIN:VEVENT',  \n
			'UID:1@default',  \n
			'CLASS:PUBLIC',  \n
			`DESCRIPTION:\${descriptionCopy}`,  \n
			`LOCATION:\${location}`,  \n
			`DTSTAMP;VALUE=DATE-TIME:\${now}`,  \n
			`DTSTART;VALUE=DATE-TIME:\${start}`,  \n
			`DTEND;VALUE=DATE-TIME:\${end}`,  \n
			`SUMMARY;LANGUAGE=" . $lang . ":\${subject}`,  \n
			'TRANSP:TRANSPARENT',  \n
			'END:VEVENT',  \n
		  ];  \n
		if (!location) {  \n
		  calendarEvent.splice(4, 1);  \n
			if (!stop || stop === 'None') {  \n
		  calendarEvent.splice(6, 1);  \n
		}  \n
		}  \n
		if (!stop || stop === 'None') {  \n
		  calendarEvent.splice(7, 1);  \n
		}  \n
		  calendarEvent = calendarEvent.join(SEPARATOR);  \n
	    \n
		  return calendarStart + SEPARATOR + calendarEvent + calendarEnd;  \n
		};  \n
	  </script>";
		$html .= "<div class=\"time-and-location\">";
		if(isset($content['data']['attributes']['meta-datetime-from'])) {
			$html .= "<a onclick='downloadCalendar(\"". $content['data']['attributes']['display-name'] ."\", \"". $content['data']['attributes']['description'] ."\", \"". $content['data']['attributes']['meta-datetime-from'] ."\", \"". (isset($content['data']['attributes']['meta-datetime-to']) ? $content['data']['attributes']['meta-datetime-to'] : 'None') ."\")' class=\"time\"> \n
					<div> \n
						<p> \n
						". $content['data']['attributes']['meta-datetime-from'] ." ";
						if(isset($content['data']['attributes']['meta-datetime-to'])) {
				$html .= "&ndash; ". $content['data']['attributes']['meta-datetime-to'] ."";
			}
			$html .= "</p></div></a>"; 
		}
		if ($related_spot['data']['attributes']['location']) {
			
			$html .="<a target=\"_blank\" href=\"https://maps.google.com/maps?z=12&t=m&q=" . $related_spot['data']['attributes']['location']['lat'] .",". $related_spot['data']['attributes']['location']['lon'] . "\"  class=\"location\"> \n
			<div> \n
				<p><i class=\"fa fa-map-marker\"></i>". $related_spot['data']['attributes']['name'] ."</p> \n
			</div>    \n
		</a> ";


		}
			$html .= "</div>"; 
			if(isset($content['data']['attributes']['meta-datetime-from'])) {
			$html .= "<script>
			moment.locale('" . get_locale() ."'); \n
			document.querySelector(\"div.time-and-location > a.time > div > p\").innerHTML = `<i class=\"fa fa-clock-o\"></i>\${moment('". $content['data']['attributes']['meta-datetime-from'] ."').format('dd., DD. MMM, LT')}";
			if(isset($content['data']['attributes']['meta-datetime-to'])) {
					$html .= "&ndash; \${moment('" . $content['data']['attributes']['meta-datetime-to'] . "').format('dd., DD. MMM, LT')}";
				}
			$html .= "`;</script>";
			}
		return $html;
	}

	public function generate_blocks_html($block, $block_type, $html, $lang, $id, $custom_map_marker) {
		$map_id = (isset($_SESSION['map_id']) ? $_SESSION['map_id'] : 1 ); //used to give seperate ids to seperate spotmaps

		switch ($block_type) { 
		    case "0": //TEXT
			if(array_key_exists("title",$block) && $block['title'] != ""){ $html .=  "<h2 class='xamoom_headline'>" . $block['title'] . "</h2>"; }
			if(array_key_exists("text",$block)){ $html .=  "<p>" . $block['text'] . "</p>"; }
			break;

		    case "1": //AUDIO
			if(array_key_exists("title",$block)){ $html .=  "<p class='xamoom_title'>" . $block['title'] . "</p>"; }
			if(array_key_exists("artists",$block)){ $html .=  "<p class='xamoom_smalltext'>" . $block['artists'] . "</p>"; }
			if(array_key_exists("file-id",$block)){
			    $html .=  "<audio class='xamoom_audio' controls><source src='" . $block['file-id'] . "'>Your browser does not support the audio element.</audio>";
			}
			break;

			case "2": //VIDEO
			if(array_key_exists("title",$block) && $block['title'] != ""){ $html .=  "<p class='xamoom_caption'>" . $block['title'] . "</p>"; }

				if (strpos($block['video-url'],'vimeo.com') == true) { //vimeo video
					$urlSegments = explode('/', $block['video-url']);
					$vimeoVideoID =  $urlSegments[sizeof($urlSegments)-1];
						$html .= "<div class='xamoom-videoWrapper'>";
						$html .= '<iframe src="//player.vimeo.com/video/'. $vimeoVideoID .'" width="100%" height="auto" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen>' .'</iframe>';
						$html .= "<div class='swipe-overlay1'></div>" . "<div class='swipe-overlay2'></div>" . "<div class='swipe-overlay3'></div>";
						$html .= "</div>";
					
						
				}	else if (strpos($block['video-url'],'youtube.com') == false && strpos($block['video-url'],'youtu.be') == false) { //VIDEO FILE
				//html5 video player
				$html .= "<video width='100%' controls>
										<source src='" . $block['video-url'] . "'>
									  Your browser does not support the video tag.
									</video>";
			} else { //YOUTUBE
				//extract youtube id
				parse_str( parse_url( $block['video-url'], PHP_URL_QUERY ), $query_vars );
				$youtube_id = $query_vars['v'];

				$html .= "<div class='xamoom-videoWrapper'>" .
							"<iframe width='100%' height='auto' src='https://www.youtube.com/embed/" . $youtube_id . "' frameborder='0' allowfullscreen></iframe>" .
								"<div class='swipe-overlay1'></div>" .
								"<div class='swipe-overlay2'></div>" .
								"<div class='swipe-overlay3'></div>" .
								 "</div>";
			}
			break;

		    case "3": //IMAGE
			$scale = 100;
			if(array_key_exists("scale-x",$block) && $block['scale-x'] != ""){ $scale =  $block['scale-x']; }

			$alt_text = "";
			if(array_key_exists("alt-text",$block) && $block['alt-text'] != ""){ $alt_text =  $block['alt-text']; }

			$link_url = null;
			if(array_key_exists("link-url",$block) && $block['link-url'] != ""){ $link_url =  $block['link-url']; }

			if($link_url != null){ $html .= "<a href='" . $link_url . "' target='_blank'>"; }

            
			if(array_key_exists("file-id",$block)){ $html .=  "<img class='xamoom_image owl-lazy' alt='". $alt_text . "' style='width:" . $scale . "%;' src='" . $block['file-id'] . "' />"; }
            		
			if((array_key_exists("copyright",$block) && $block['copyright'] != "") || (array_key_exists("title",$block) && $block['title'] != "")){
				$html .=  "<div class='clearfix' style='width:100%;'>";

				$html .=  "<div style='float:left;'>";
				if(array_key_exists("title",$block) && $block['title'] != ""){
					$html .=  "<p style='margin:0px;' class='xamoom_caption'>" . $block['title'] . "</p>";
				} else {
					$html .=  "&nbsp;";
				}
				$html .=  "</div>";

				$html .=  "<div style='float:right;'>";
				if(array_key_exists("copyright",$block) && $block['copyright'] != ""){
					$html .=  "<p class='xamoom_copyright'>" . $block['copyright'] . "</p>";
				} else {
					$html .=  "&nbsp;";
				}
				$html .=  "</div>";

				$html .=  "</div>";
			}

			if($link_url != null){ $html .= "</a>"; }

			break;

		    case "4": //LINK
			$link_title = "";
			$link_url = "";
			$link_type = "0";
			if(array_key_exists("title",$block)){ $link_title = $block['title']; }
			if(array_key_exists("link-url",$block)){ $link_url = $block['link-url']; }
			if(array_key_exists("link-type",$block)){ $link_type = $block['link-type']; }

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
			if(array_key_exists("file-id",$block)){ $ebook_url = $block['file-id']; }
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
				    "src='https://w.soundcloud.com/player/?url=" . $block['soundcloud-url'] . "&amp;auto_play=false&amp;hide_related=true&amp;show_comments=false&amp;show_user=false&amp;show_reposts=false&amp;visual=true'>" .
				    "</iframe>";
			break;

			case "8": //DOWNLOAD
			$download_title = "";
			$download_url = "";
			$download_type = "0";
			if(array_key_exists("title",$block)){ $download_title = $block['title']; }
			if(array_key_exists("file-id",$block)){ $download_url = $block['file-id']; }
			if(array_key_exists("download-type",$block)){ $download_type = $block['download-type']; }
			
			$icon = "fa-user-plus";
			switch ($download_type) {
				case "0": //VCF
				$icon = "fa-user-plus";
				break;
			    case "1": //ICAL
				$icon = "fa-calendar";
				break;
			}

			$css_class = "";
			switch ($download_type) {
				case "0": //VCF
				$css_class = "vcf";
				break;
			    case "1": //ICAL
				$css_class = "ical";
				break;
			    case "2": //gpx
				$css_class = "gpx";
				break;
			}

			// $html .= '<a href="' . $download_url . '" class="download-block ' . $css_class .' button-background" >';
			// $html .= '<div class="download-block-icon">';
			// if($download_type == "2") { // GPX
			// 	$html .= '<div class="button-icon gpx-icon"></div> ';			
			// } else {
			// 	$html .= '<i class="button-icon fa '. $icon .'"></i>';
			// }
			// $html .= '</div>';

			// $html .= '<div class="download-block-content">';
			// $html .= '<h4 class="button-text">' . $download_title . '</h4>';
			// if(array_key_exists("text",$block)){ $html .= '<p class="button-text">' . $block['text'] . '</p>'; }
			// $html .= '</div>';			
			// $html .= '</a>';

			$html .= "<p class='xamoom_link'>";
			if($download_type == "2") { // GPX
				$html .= '<div class="button-icon gpx-icon"></div> ';			
			} else {
				$html = "<i class='fa " . $icon . "'></i>";
			}
			
			$html = "<a href='" . $download_url . "'>" . $download_title . "</a></p>";
			if(array_key_exists("text",$block)){ $html.=  "<p class='xamoom_smalltext'>" . $block['text'] . "</p>"; }
			break;

		    case "9": //SPOTMAP
			if(array_key_exists("title",$block) && $block['title'] != ""){ $html .=  "<p class='xamoom_title'>" . $block['title'] . "</p>"; }
			$this_map_id = "xamoom-map-" . $id . "-" . $map_id; //get new map id that is unique on this page


			//get spot map
			$cursor = "";
			$total_num_results = 0;
			$has_more = true;
			$spot_map = array('items' => array());
			while($has_more){
				$api = $this->encodeURI($this->api_endpoint . "spots?filter[tags]=[\"" . implode("\",\"", $block['spot-map-tags']) . "\"]&page[cursor]=" . $cursor . "&filter[has-location]=true&page[size]=100&lang=" . $lang);
				$spot_map_response = $this->call_api($api);
				$resp = json_decode($spot_map_response, true);
				if ($resp['data']) {
					$spot_map['items'] = array_merge($spot_map['items'], $resp['data']);
				}
				$total_num_results = $resp['meta']['total'];
				$cursor = $resp['meta']['cursor'];
				$has_more = $resp['meta']['has-more'];
			}
			//render map
			$html .= "<div class='xamoom-map' id='" . $this_map_id . "'><div class='spotmap-popup'></div><button class=\"expand\"><i class=\"fa fa-expand\"></i></button></div>";
			$html .= "<script language='JavaScript'>  function createNewPopup" . $map_id . "(spot, marker, map) { \n
				const self = this;\n
				marker.addTo(map); \n
					marker.getElement().addEventListener('click', function(e) {\n
						document.querySelectorAll('#" . $this_map_id . " .leaflet-marker-icon').forEach((el) => {\n
							el.style.opacity = 0.5;\n
						});\n
						// clicked marker opacity 1 \n
						marker.getElement().style.opacity = 1;\n
						\n
						// center marker with padding\n
						const latlng = marker.getLatLng();\n
						const bounds = latlng.toBounds(250); // 250 = meter\n
						map.panTo(latlng).fitBounds(bounds, {\n
							paddingBottomRight: [0, 150],\n
						});\n
						document.querySelector('#" . $this_map_id . " > .spotmap-popup').innerHTML = self.createInfoWindowPopup(spot);\n

						const calculatedheight = document.querySelector('#" . $this_map_id . " > div.spotmap-popup').offsetHeight - 20 - document.querySelector('#" . $this_map_id . " > div.spotmap-popup > h2').offsetHeight;\n
						const imagepart = document.querySelector('#" . $this_map_id . " > div.spotmap-popup > div.image-part > *');\n
						if (imagepart) {\n
							imagepart.style.height = calculatedheight + 'px';\n
							imagepart.style.maxHeight = calculatedheight + 'px';\n
							imagepart.style.width = calculatedheight + 'px';\n
						}\n
						const description = document.querySelector('#" . $this_map_id . " > div.spotmap-popup .description');\n 
						if (description) { \n
							const btnheight = document.querySelector('#" . $this_map_id . " > div.spotmap-popup > div.spotmap-buttons').offsetHeight; \n
							description.style.height = calculatedheight - btnheight + 'px';  \n
						} \n
						\n
						// show popup\n
						self.jQuery('#" . $this_map_id . " > .spotmap-popup').animate({\n
							bottom: 0,\n
						}, 300);\n
						\n
						e.stopPropagation();\n
				});\n
				};\n
				function hidePopup" . $map_id . "() { \n
					this.jQuery('#" . $this_map_id . " > .spotmap-popup').animate({ \n
					  bottom: '-50%', \n
					}, 300); \n
					document.querySelectorAll('#" . $this_map_id . " .leaflet-marker-icon').forEach((el) => { \n
					  el.style.opacity = 1; \n
				   }); \n
				  }; \n
				function createInfoWindowPopup(spot) {  \n
				  let imageSection = '';  \n
				  let spotNameSection = '';  \n
					  let descriptionSection = '';  \n
					  let openSection = '';  \n
					  ({ imageSection, spotNameSection, descriptionSection } = this._generateSections(spot.image_url, spot.display_name, spot.description));  \n
					  \n
					  const navLinkSection =  \n
					    `<a class=\"link\" href=\"https://maps.google.com/maps?z=12&t=m&q=\${spot.location.lat},\${spot.location.lon}\">Route</a>`;  \n
					  \n
					  const htmlRes = this._concatenateInfoWindowSections(  \n
					    imageSection,  \n
					    spotNameSection,  \n
					    descriptionSection,  \n
					    navLinkSection,  \n
					    openSection,  \n
					  );  \n
					  return htmlRes;  \n
					};  \n
					  function _generateSections(image, name, description) {  \n
					      let imageSection = '<div></div>';  \n
					      let descriptionSection = '<p style=\"height:59%;\" class=\"description\"> </p>';  \n
						\n
					      if (image) {  \n
					        imageSection = `<img style=\"width: 114px;height: 114px;max-height: 114px;\" src=\"\${image}\"/>`;  \n
					      }  \n
					    \n
					      const spotNameSection = `<h2>\${name}</h2>`;  \n
					      if (description) {  \n
					        descriptionSection = `<p style=\"height:59%;\" class=\"description\">\${description}</p>`;  \n
					      }  \n
					    \n
					      return { imageSection, spotNameSection, descriptionSection };  \n
					  };  \n
					   function _concatenateInfoWindowSections(  \n
					    imageSection,  \n
					    spotNameSection,  \n
					    descriptionSection,  \n
					    navLinkSection,  \n
					    openSection,  \n
					  ) {  \n
					    return `\${spotNameSection}<div class=\"image-part\">\${imageSection}</div>\${descriptionSection}<div class=\"spotmap-buttons\">  \n
					    \${navLinkSection}  \n
					    \${openSection}  \n
					    </div>`;  \n
					};</script>";
			//initialize script
			$html .= "<script language='JavaScript'>\n
						function renderMap_" . $map_id . "(width,height){\n
									var map = L.map('" . $this_map_id . "').setView([0,0], 13);\n

									// add OpenStreetMap tile layer
									L.tileLayer('https://api.mapbox.com/styles/v1/xamoom-bruno/cjtjxdlkr3gr11fo5e72d5ndg/tiles/256/{z}/{x}/{y}@2x?access_token=pk.eyJ1IjoieGFtb29tLWJydW5vIiwiYSI6ImNqcmc1MWxqbTFsNms0Nm1yZGcycTFqbjAifQ.sDuEiFnBOHNoS-o7uTHvdA', { attribution: '<a href=\"https://xamoom.com\" target=\"_blank\" title=\"xamoom mobile platform\">xamoom</a>' }).addTo(map);\n
									const self = this; \n
										map.on('click', function() { \n
											self.hidePopup" . $map_id . "(); \n
									}); \n
								
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
			for($j = 0; $j < $total_num_results; $j++){
			    $marker = $spot_map['items'][$j]['attributes'];
				//kill line breaks from marker descriptions and display_name
				if(array_key_exists("description",$marker)) {
					$marker['description'] = str_replace(array("\r", "\n"), "<br>", $marker['description']);
					$marker['description'] = str_replace(array('"'), '\"', $marker['description']);
					$marker['description'] = str_replace(array('`'), '\`', $marker['description']);
					$marker['description'] = str_replace(array("'"), "\'", $marker['description']);
				}

				if(array_key_exists("name",$marker)) {

			    $marker['name'] = str_replace(array("\r", "\n"), "<br>", $marker['name']);
				$marker['name'] = str_replace(array('"'), '\"', $marker['name']);

					$marker['name'] = str_replace(array("'"), "\'", $marker['name']);
					$marker['name'] = str_replace(array("`"), "\`", $marker['name']);
				}
			    //extract image
			    $image = null;
			    if(array_key_exists("image",$marker)){ $image = $marker['image']; }

			    // add a markers
			    if($custom_map_marker){
						$html .= "\n this.createNewPopup" . $map_id . "({'image_url': '" . $image . "', 'display_name': '" . $marker['name'] . "', description: '" . $marker['description'] . "', location : { lat : " . $marker['location']['lat'] . ", lon: " . $marker['location']['lon'] . " }}, L.marker([" . $marker['location']['lat'] . ", " . $marker['location']['lon'] . "],{icon: custom_marker}), map )";
			    } else {
						$html .= "\n this.createNewPopup" . $map_id . "({'image_url': '" . $image . "', 'display_name': '" . $marker['name'] . "', description: '" . $marker['description'] . "', location : { lat : " . $marker['location']['lat'] . ", lon: " . $marker['location']['lon'] . " }}, L.marker([" . $marker['location']['lat'] . ", " . $marker['location']['lon'] . "]), map );";
			    }

			    $html .= "\nbounds.push([" . $marker['location']['lat'] . "," . $marker['location']['lon'] . "]);";
			}
		
			//finalze JavaScript function to render map block
			$html .= "map.fitBounds(bounds);\n
				  //map.zoomOut();\n
				  map.scrollWheelZoom.disable();\n
				
					document.querySelector('#" . $this_map_id . " > .expand').addEventListener('click', function(e) {\n
					map.flyToBounds(bounds);\n
					self.hidePopup" . $map_id . "(); \n
					e.stopPropagation();\n
				});\n
				};";
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
			$_SESSION['map_id'] = $map_id; // Save current map id to session 
			break;

			case "12":

			//call backend api
			$res = $this->call_api($this->api_endpoint . "contents/" . $block['content-id'] . "?lang=" . $lang);
			$gallery = json_decode($res, true);
			//seperate includes into blocks and style
			$gallery_items = array();

			for($i = 0; $i < count($gallery['included']); $i++){
				$inc = $gallery['included'][$i];
	
			
			if($inc['type'] == "contentblocks" && in_array($inc['attributes']['block-type'], array(0,1,2,3), true) ) {
					array_push($gallery_items,$inc);
				}

			}
			$html .= "<div class='gallery-container'>";
			$html .= "<div class='owl-carousel owl-theme'>";
			foreach ($gallery_items as $block) {
			$html .= "<div class='gallery-block-item' id='gallery-block-item-". $block['attributes']['block-type']. "'>";
			$html .= $this->generate_blocks_html($block['attributes'], $block['attributes']['block-type'], '', NULL, NULL, NULL);
			$html .= "</div>";
			}
			$html .= "</div></div>";
			$html .= "<style>
			
			</style>";
			$html .= "<script>jQuery('.owl-carousel').owlCarousel({
				loop:true,
				items:1,
				nav:true,
				lazyLoad:true,
				dots:true,
				navText: ['<i class=\"carousel-nav-icons fa fa-chevron-left\"></i>', '<i class=\"carousel-nav-icons fa fa-chevron-right\"></i>'],
				autoHeight: true,
				autoHeightClass: 'owl-height'
			})</script>";
			break;
		    default: // show unknow blocks
				$html .= ""; //"<p style='color:#ff00ff;'>" . http_build_query($block) . "</p>";
		}
	return $html;
	}
	/**
	 * Calls the backend API
	 *
	 * @since    1.0.0
	 */
	public function call_api($url){
		$header = array('headers' => array( 'ApiKey' => get_option('xamoom_api_key'), 'X-Reason' => 2, 'user-agent' => 'xamoom wordpress plugin' ));
		$result = wp_remote_get( $url ,	array('headers' => array( 'ApiKey' => get_option('xamoom_api_key'), 'X-Reason' => 2, 'user-agent' => 'xamoom wordpress plugin' ) ));
		return wp_remote_retrieve_body($result);
	}

	/**
	 * encodes special characters in uri
	 */
	public function encodeURI($uri)
{
    return preg_replace_callback("{[^0-9a-z_.!~*'();,/?:@&=+$#-]}i", function ($m) {
        return sprintf('%%%02X', ord($m[0]));
    }, $uri);
}

}
