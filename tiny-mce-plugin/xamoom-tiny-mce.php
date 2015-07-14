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
along with Foobar.  If not, see <http://www.gnu.org/licenses/>.
*/

//TINY MCE PLUGIN
add_action( 'init', 'xamoom_addTinymceButtonFilter' );

function xamoom_addTinymceButtonFilter() {
    add_filter( "mce_external_plugins", "xamoom_addTinymceButtonJs" );
    add_filter( 'mce_buttons', 'xamoom_registerTinymceButtons' );
}

function  xamoom_addTinymceButtonJs( $plugin_array ) {
    $plugin_array['xamoom'] = plugins_url() .  '/wp-xamoom/tiny-mce-plugin/xamoom-tiny-mce.js';
    return $plugin_array;
}

function xamoom_registerTinymceButtons( $buttons ) {
    array_push( $buttons, 'xamoom', 'xamoom-insert-content' );
    return $buttons;
}

?>
