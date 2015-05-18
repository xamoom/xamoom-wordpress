<?php

//SHORT CODE
add_shortcode( 'xamoom', 'xamoom_includePageShortCode' );

function xamoom_includePageShortCode( $atts ) {
    extract( shortcode_atts( array('lang' => 'nolanginshortcode',), $atts ) );
    extract( shortcode_atts( array('id' => 'noidinshortcode',), $atts ) );
    
    $response = CallAPI("POST",
	"https://xamoom-api-dot-xamoom-cloud.appspot.com/_ah/api/xamoomIntegrationApi/v1/get_content_by_content_id",
	$data = array("content_id" => $id, "language" => $lang) );
    
    $content = json_decode($response, true);
    
    //extract custom marker if there is one
    $custom_map_marker = false;
    if(array_key_exists("custom_marker",$content['system_style'])){ $custom_map_marker = base64_decode($content['system_style']['custom_marker']); }
    
    //add custom css
    $html = "<style type='text/css'>" . get_option('xamoom_custom_css') . "</style>";
    
    
    for($i = 0; $i < count($content['content_blocks']); $i++){
	//TODO throw this into a seperate function
	$block = $content['content_blocks'][$i];
	$block_type = $block['content_block_type'];
	
	$html .= "<div class='xamoom_block'>";
	
	$map_id = 1; //used to give seperate ids to seperate spotmaps
	
	switch ($block_type) {
	    case "0": //TEXT
		if(array_key_exists("title",$block)){ $html .=  "<h2 class='xamoom_headline'>" . $block['title'] . "</h2>"; }
		if(array_key_exists("text",$block)){ $html .=  "<p>" . $block['text'] . "</p>"; }
		break;
	    case "1": //AUDIO
		if(array_key_exists("title",$block)){ $html .=  "<h2 class='xamoom_headline'>" . $block['title'] . "</h2>"; }
		if(array_key_exists("artists",$block)){ $html .=  "<p class='xamoom_smalltext'>" . $block['artists'] . "</p>"; }
		if(array_key_exists("file_id",$block)){
		    $html .=  "<audio class='xamoom_audio' controls><source src='" . $block['file_id'] . "'>Your browser does not support the audio element.</audio>";
		}
		break;
	    case "2": //YOUTUBE
		parse_str( parse_url( $block['youtube_url'], PHP_URL_QUERY ), $query_vars );
		$youtube_id = $query_vars['v'];
		
		if(array_key_exists("title",$block)){ $html .=  "<h2 class='xamoom_headline'>" . $block['title'] . "</h2>"; }
		$html .= "<div class='xamoom-videoWrapper'>" .
			    "<iframe width='560' height='349' src='https://www.youtube.com/embed/" . $youtube_id . "' frameborder='0' allowfullscreen></iframe>" .
			"</div>";
		
		break;
	    case "3": //IMAGE
		if(array_key_exists("title",$block)){ $html .=  "<h2 class='xamoom_headline'>" . $block['title'] . "</h2>"; }
		if(array_key_exists("file_id",$block)){ $html .=  "<img class='xamoom_image' src='" . $block['file_id'] . "' />"; }
		break;
	    case "4": //LINK
		$link_title = "";
		$link_url = "";
		$link_type = "0";
		if(array_key_exists("title",$block)){ $link_title = $block['title']; }
		if(array_key_exists("link_url",$block)){ $link_url = $block['link_url']; }
		if(array_key_exists("link_type",$block)){ $link_type = $block['link_type']; }
		
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
		}
		
		$html .= "<p class='xamoom_link'><i class='fa " . $icon . "'></i> <a href='" . $link_url . "' target='_blank'>" . $link_title . "</a></p>";
		if(array_key_exists("text",$block)){ $html .=  "<p class='xamoom_smalltext'>" . $block['text'] . "</p>"; }
		break;
	    case "5": //EBOOK
		$ebook_url = "";
		if(array_key_exists("file_id",$block)){ $ebook_url = $block['file_id']; }
		
		if(array_key_exists("title",$block)){ $html .=  "<h3 class='xamoom_headline'>" . $block['title'] . "</h3>"; }
		if(array_key_exists("artists",$block)){ $html .=  "<p class='xamoom_smalltext'>" . $block['artists'] . "</p>"; }
		
		$html .= "<p class='xamoom_link'><i class='fa fa-book'></i> <a href='" . $ebook_url . "'>Download Ebook</a></p>";
		if(array_key_exists("text",$block)){ $html .=  "<p class='xamoom_smalltext'>" . $block['text'] . "</p>"; }
		break;
	    case "6": //CONTENT BLOCK CONTENT GET'S IGNORED
		break;
	    case "7": //SOUNDCLOUD
		if(array_key_exists("title",$block)){ $html .=  "<h2 class='xamoom_headline'>" . $block['title'] . "</h2>"; }
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
		if(array_key_exists("title",$block)){ $html .=  "<h3 class='xamoom_headline'>" . $block['title'] . "</h2>"; }
		$this_map_id = "xamoom-map-" . $id . "-" . $map_id;
		
		//get spot map
		$spot_map_response = CallAPI("GET",
		"https://xamoom-api-dot-xamoom-cloud.appspot.com/_ah/api/xamoomIntegrationApi/v1/spotmap/" . get_option('xamoom_api_key') . "/" . $block['spot_map_tag'] . "/" . $lang);
		$spot_map = json_decode($spot_map_response, true);
		
		//render map
		$html .= "<div class='xamoom-map' id='" . $this_map_id . "'></div>";
		
		$html .= "<script language='JavaScript'>
			    function renderMap_" . $map_id . "(width,height){
				var map = L.map('" . $this_map_id . "').setView([0,0], 13);
				
				// add OpenStreetMap tile layer
				L.tileLayer('http://{s}.tile.openstreetmap.se/hydda/full/{z}/{x}/{y}.png', {
				    attribution: '&copy; <a href=\"http://leafletjs.com\" title=\"A JS library for interactive maps\">Leaflet</a> | Tiles courtesy of <a href=\"http://hot.openstreetmap.se/\" target=\"_blank\">OpenStreetMap Sweden</a> — Map data © <a href=\"http://openstreetmap.org\">OpenStreetMap</a> contributors, <a href=\"http://creativecommons.org/licenses/by-sa/2.0/\">CC-BY-SA</a>'
				}).addTo(map);
				
				var bounds = [];";
		
		if($custom_map_marker){
		    $html .= "\nvar LeafIcon = L.Icon.extend({options: {iconSize:[width, height],iconAnchor:[height / 2, height - 1],popupAnchor:  [0, -height]}}); ";
		    $html .= "\nvar custom_marker = new LeafIcon({iconUrl: '" . $custom_map_marker ."'});";
		}
		
			
		for($j = 0; $j < count($spot_map['items']); $j++){
		    $marker = $spot_map['items'][$j];
		    
		    //kill line breaks from marker descriptions and display_name
		    $marker['description'] = str_replace(array("\r", "\n"), "<br>", $marker['description']);
		    $marker['display_name'] = str_replace(array("\r", "\n"), "<br>", $marker['display_name']);
		    
		    
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
		
		$html .= "
			map.fitBounds(bounds);
			map.zoomOut();
		    }";
		
		
		
		//build custom marker
		if($custom_map_marker){
		    $html .= "var img = new Image();
				img.onload = function() {
				    renderMap_" . $map_id . "(this.width,this.height);
				}
				img.src = '" . $custom_map_marker . "';";
		} else { //render without custom makrer
		    $html .= "renderMap_" . $map_id . "(null);";
		}
		
		$html .= "</script>";
			  
		
		$map_id++; //increment map_id
		break;
	    default:
		$html .= "<p style='color:#ff00ff;'>" . http_build_query($block) . "</p>";
	}
	
	$html .= "</div>";
    }
    
    return $html;
}

//API CALL
function CallAPI($method, $url, $data = false)
{
    $data_string = json_encode($data);  
    $curl = curl_init();

    switch ($method)
    {
        case "POST":
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");  

            if ($data)
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(                                                                          
		    'Content-Type: application/json',                                                                                
		    'Content-Length: ' . strlen($data_string),
		    'Authorization: ' . get_option('xamoom_api_key'))                                                                       
		);   

            break;
	case "GET":
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
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

//TINY MCE PLUGIN
add_action( 'init', 'xamoom_addTinymceButtonFilter' );

function xamoom_addTinymceButtonFilter() {
    add_filter( "mce_external_plugins", "xamoom_addTinymceButtonJs" );
    add_filter( 'mce_buttons', 'xamoom_registerTinymceButtons' );
}

function  xamoom_addTinymceButtonJs( $plugin_array ) {
    print "xamoom_addTinymceButtonJs";
    $plugin_array['xamoom'] = plugins_url() .  '/wp-xamoom/tiny-mce-plugin/xamoom-tiny-mce.js';
    return $plugin_array;
}

function xamoom_registerTinymceButtons( $buttons ) {
    print "xamoom_registerTinymceButtons";
    array_push( $buttons, 'xamoom', 'xamoom-insert-content' ); 
    return $buttons;
}

?>