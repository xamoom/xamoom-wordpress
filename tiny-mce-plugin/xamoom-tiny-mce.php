<?php

//SHORT CODE
add_shortcode( 'xamoom', 'xamoom_includePageShortCode' );

function xamoom_includePageShortCode( $atts ) {
    extract( shortcode_atts( array('lang' => 'nolanginshortcode',), $atts ) );
    extract( shortcode_atts( array('id' => 'noidinshortcode',), $atts ) );
    
    $response = CallAPI("POST",
	"https://xamoom-api-dot-xamoom-cloud-dev.appspot.com/_ah/api/xamoomIntegrationApi/v1/get_content_by_content_id",
	$data = array("content_id" => $id, "language" => $lang) );
    
    $content = json_decode($response, true);
    
    $html = "";
    for($i = 0; $i < count($content['content_blocks']); $i++){
	//TODO throw this into a seperate function
	$block = $content['content_blocks'][$i];
	$block_type = $block['content_block_type'];
	
	switch ($block_type) {
	    case "0": //TEXT
		if(array_key_exists("title",$block)){ $html .=  "<h2 class='xamoom_headline'>" . $block['title'] . "</h2>"; }
		if(array_key_exists("text",$block)){ $html .=  "<p class='xamoom_text'>" . $block['text'] . "</p>"; }
		break;
	    case "1": //AUDIO
		if(array_key_exists("title",$block)){ $html .=  "<h2 class='xamoom_headline'>" . $block['title'] . "</h2>"; }
		if(array_key_exists("artists",$block)){ $html .=  "<p class='xamoom_text'>" . $block['artists'] . "</p>"; }
		if(array_key_exists("file_id",$block)){
		    $html .=  "<audio controls><source src='" . $block['file_id'] . "'>Your browser does not support the audio element.</audio>";
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
		if(array_key_exists("title",$block)){ $html .=  "<h2>" . $block['title'] . "</h2>"; }
		if(array_key_exists("file_id",$block)){ $html .=  "<img class='xamoom_image' src='" . $block['file_id'] . "' />"; }
		break;
	    case "4": //LINK
		$link_title = "";
		$link_url = "";
		$link_type = "0";
		if(array_key_exists("title",$block)){ $link_title = $block['title']; }
		if(array_key_exists("link_url",$block)){ $link_url = $block['link_url']; }
		if(array_key_exists("link_type",$block)){ $link_type = $block['link_type']; }
		
		//TODO LOAD CORRECT ICON ACCORDING TO LINK TYPE
		$html .= "<p><i class='fa fa-globe'></i> <a href='" . $link_url . "' target='_blank'>" . $link_title . "</a></p>";
		if(array_key_exists("text",$block)){ $html .=  "<p class='xamoom_text'>" . $block['text'] . "</p>"; }
		break;
	    case "5": //EBOOK
		$ebook_url = "";
		if(array_key_exists("file_id",$block)){ $ebook_url = $block['file_id']; }
		
		if(array_key_exists("title",$block)){ $html .=  "<h2 class='xamoom_headline'>" . $block['title'] . "</h2>"; }
		if(array_key_exists("artists",$block)){ $html .=  "<p class='xamoom_text'>" . $block['artists'] . "</p>"; }
		
		$html .= "<p><i class='fa fa-book'></i> <a href='" . $ebook_url . "'>Download Ebook</a></p>";
		if(array_key_exists("text",$block)){ $html .=  "<p class='xamoom_text'>" . $block['text'] . "</p>"; }
		break;
	    case "6": //CONTENT BLOCK CONTENT GET'S IGNORED
		break;
	    case "7": //SOUNDCLOUD
		if(array_key_exists("title",$block)){ $html .=  "<h2 class='xamoom_headline'>" . $block['title'] . "</h2>"; }
		$html .= "<iframe width='100%' height='150' scrolling='no' frameborder='no' " .
			    "src='https://w.soundcloud.com/player/?url=" . $block['soundcloud_url'] . "&amp;auto_play=false&amp;hide_related=true&amp;show_comments=false&amp;show_user=false&amp;show_reposts=false&amp;visual=true'>" .
			    "</iframe>";
		
		break;
	    default:
		$html .= "<p style='color:#ff00ff;'>" . http_build_query($block) . "</p>";
	}
	
	$html .= "<hr />";
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
		    'Content-Length: ' . strlen($data_string))                                                                       
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