<?php
/**
 * Plugin Name: Publish & View
 * Plugin URI: http://launchinteractive.com.au/wordpress/publish-view.zip
 * Description: Adds a button so you can Publish and View Pages, Posts etc. in one step.
 * Version: 1.6
 * Author: Marc Castles
 * Author URI: http://launchinteractive.com.au
 * License: GPL2
*/
/*
Copyright 2014  Marc Castles  (email : marc@launchinteractive.com.au)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

function publish_view_enqueue() {
	global $post;
	if(isset($post)){
		$type = get_post_type_object( $post->post_type );
		if(isset($post) && $type->public && in_array($post->post_status,array('auto-draft','draft','publish'))) {
    	global $wp_version;
    	if($wp_version < 3.8) {
	    	wp_register_style( 'publish-view-dashicons', plugins_url('publish-view-dashicons.css', __FILE__) );
	    	wp_enqueue_style( 'publish-view-dashicons' );
    	}
			wp_register_style( 'publish-view', plugins_url('publish-view.css', __FILE__) );
    	wp_enqueue_style( 'publish-view' );
		}
	}
}
add_action( 'admin_enqueue_scripts', 'publish_view_enqueue' );
	 
function publish_view_submitbox_start(){
	global $post;
	global $wp_version;

	$type = get_post_type_object( $post->post_type );
	if($type->public) {
		
		if($post->post_status == 'auto-draft' || $post->post_status == 'draft') {
			submit_button('&#xf177;','primary','publish',false, array('onclick'=>"jQuery(this).after('<input type=\"hidden\" name=\"publishview\" value=\"Y\" />')",'title'=>'Publish & View','id'=>'publishview'));
		} else if($post->post_status == 'publish') {
			submit_button('&#xf177;','primary','publish',false, array('onclick'=>"jQuery(this).after('<input type=\"hidden\" name=\"publishview\" value=\"Y\" />')",'title'=>'Update & View','id'=>'publishview'));
		}
	}
}
add_action( 'post_submitbox_start', 'publish_view_submitbox_start' );

function publish_view_redirect($location)
{
    global $post;
    if (isset($_POST['publishview'])) {
			$location = get_permalink($post->ID);
    }
    return $location;
}
add_filter('redirect_post_location', 'publish_view_redirect');
