<?php
/**
 * Plugin Name: Publish & View
 * Plugin URI: https://wordpress.org/plugins/publish-view
 * Description: Adds a button so you can Publish and View Pages, Posts etc. in one step.
 * Version: 2
 * Author: Launch Interactive
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

class PublishView {
	public function __construct() {
    
    if(is_admin()) {

	    add_action( 'show_user_profile', array($this,'extra_profile_fields') );
	    add_action( 'edit_user_profile', array($this,'extra_profile_fields') );
	    
	    add_action( 'personal_options_update', array($this,'save_extra_profile_fields') );
			add_action( 'edit_user_profile_update', array($this,'save_extra_profile_fields') );

			add_action( 'admin_enqueue_scripts', array($this,'enqueue') );
			add_action( 'post_submitbox_start', array($this,'submitbox_start') );
			add_filter('redirect_post_location', array($this,'redirect'));
	  
	  } 
	}
	
	public function extra_profile_fields( $user ) {
	    $newwindow = get_user_meta( $user->ID, 'pv_newwindow', true );
	    ?>
	    <h3>Publish View Options</h3>
	    <table class="form-table"><tr><th>New Window</th><td><label><input type="checkbox" value="checked" name="pv_newwindow" <?php echo ( $newwindow=='' ? 'checked ' :'' ); ?>/> Open new window for <?php echo __('Preview Changes'); ?> or <?php echo __('View Page'); ?> links.</label></td></tr></table>
	    <?php
	}
	
	public function save_extra_profile_fields( $user_id ) {

		if ( !current_user_can( 'edit_user', $user_id ) )
			return false;
			
		if(isset($_POST['pv_newwindow'])) {
			delete_usermeta( $user_id, 'pv_newwindow');
		} else {
			update_usermeta( $user_id, 'pv_newwindow', 'disable' );
		}
	}
	
	public function enqueue() {
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
	
	public function submitbox_start(){
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
		
		$newwindow = get_user_meta( get_current_user_id(), 'pv_newwindow', true );
		if($newwindow == 'disable') {
			//remove preview new tab/page
			echo '<script>jQuery(\'#post-preview\').attr(\'target\',\'_self\');jQuery(\'#message.updated a,#view-post-btn a\').removeAttr(\'target\');</script>';
		}
	}

	public function redirect($location) {
    global $post;
    if (isset($_POST['publishview'])) {
			$location = get_permalink($post->ID);
    }
    return $location;
	}
	
}
$publishview = new PublishView();