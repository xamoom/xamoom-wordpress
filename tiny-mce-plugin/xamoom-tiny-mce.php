<?php

//SHORT CODE
add_shortcode( 'xamoom', 'xamoom_includePageShortCode' );

function xamoom_includePageShortCode( $atts ) {
    extract( shortcode_atts( array('numbers' => '5',), $atts ) );
		
    $hmtl = "<ul>";
    for ($x = 0; $x < $numbers; $x++) {
            $html .= "<li>Just a xamoom short code test #" . ($x + 1) . "</li>";
    }
    $html .= "</ul>";
    
    return $html;
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