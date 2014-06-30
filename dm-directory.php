<?php

/*
Plugin Name: DM Directory
Plugin URI: http://www.designmissoula.com/
Description: This is not just a plugin, it makes WordPress better.
Author: Bradford Knowlton
Version: 1.6.8
Author URI: http://bradknowlton.com/
*/

function init_directory(){
	
	$result = add_role(
	    'member',
	    __( 'Member' ),
	    array('read' => true )
	);
	$result = add_role(
	    'non-member',
	    __( 'Non Member' ),
	    array('read' => false )
	);	
	
}

register_activation_hook( __FILE__, 'init_directory' );

function user_directory_shortcode( $atts ){
	extract( shortcode_atts( array(
		'user_level' => 'member',
		'title' => 'Member List',
	), $atts ) );
	
	
	$html = "";
	
	 $html .= "<h2>$title</h2>";
	
	$html .= '<div class="membership-directory"> <!-- end directory -->';
		
	if(isset($_GET['position_title']) && "" != $_GET['position_title'] && isset($_GET['course_company']) && "" != $_GET['course_company']){

		$args = array( 
						'orderby' => 'meta_value', 
						'meta_key' => 'last_name',
						'role' => 'member',
						'number' => '999',
						'fields' => 'all_with_meta',
						'meta_query' => array(
							'relation' => 'AND',
					        array(
					            'key' => 'position_title',
					            'value' => $_GET['position_title'],
					            'compare' => '='
					        ),
					        array(
					            'key' => 'course_company',
					            'value' => $_GET['course_company'],
					            'compare' => '='
					        )
					    )
						
						 );
		$blogusers = get_users($args); //subscriber		
	}else if(isset($_GET['position_title']) && "" != $_GET['position_title']){
		$blogusers = get_users('orderby=meta_value&meta_key=last_name&role=member&number=999&fields=all_with_meta&meta_key=position_title&meta_value='.$_GET['position_title']); //subscriber		
	}else if(isset($_GET['course_company']) && "" != $_GET['course_company']){
		$blogusers = get_users('orderby=meta_value&meta_key=last_name&role=member&number=999&fields=all_with_meta&meta_key=course_company&meta_value='.$_GET['course_company']); //subscriber		
	}else {
		$blogusers = get_users('orderby=meta_value&meta_key=last_name&role=member&number=999&fields=all_with_meta'); //subscriber		
	}
	
	global $wpdb;

	$position_values = $wpdb->get_col("SELECT DISTINCT meta_value FROM $wpdb->usermeta WHERE meta_key = 'position_title' ORDER BY meta_value" );
	
	$company_values = $wpdb->get_col("SELECT DISTINCT meta_value FROM $wpdb->usermeta WHERE meta_key = 'course_company' ORDER BY meta_value" );
	// var_dump($values);
	
	?>
	<form action="/member-directory/" method="get">
		<label for="position_title">Position</label>
		<select id="position_title" name="position_title">
			<?php foreach($position_values as $value){
			echo '<option value="'.$value.'">'.$value.'</option>';
			}?>
		</select>
		<label for="course_company">Company</label>
		<select id="course_company" name="course_company">
			<?php foreach($company_values as $value){
			echo '<option value="'.$value.'">'.$value.'</option>';
			}?>
		</select>
		<input type="submit" value="Filter" />
	</form>
	<?php
	
    foreach ($blogusers as $user) {
    	$html .= '<div class="entry clearfix">';
    	$html .= '<h2>'.$user->display_name.'</h2>';
    	// get_avatar( $id_or_email, $size, $default, $alt );
    	$html .= '<div class="gravatar alignleft">' . get_avatar( $user->ID, 128, null, $user->display_name ) . '</div>';
        $html .= '<div class="entry-details">';
       //  $html .= '' . $user->last_name . '<br/>';
       // $html .= '' . $user->user_email . '</div>';
  
       $html .= '<span>Member Class:</span> '.str_replace('.','',$user->member_class).'<br/>';
       $html .= '<span>Course/Company:</span> '.$user->course_company.'<br/>';
       
       $html .= '<span>Preferred Address:</span> '.str_replace('*','',$user->primary_address_1).(($user->primary_address_2)?' '.$user->primary_address_2:'').' '.$user->primary_city.', '.$user->primary_state.' '.$user->primary_zip.'<br/>';
  
       $html .= '<span>Position:</span> '.$user->position_title.'<br/>';
       $html .= '<span>Preferred work number #1 and #2:</span> <a href="tel:'.$user->work_tele_1.'">'.format_phone($user->work_tele_1).' </a> <a href="tel:'.$user->work_tele_2.'">'.format_phone($user->work_tele_2).'</a><br/>';
       $html .= '<span>Cell Phone:</span> <a href="tel:'.$user->cell_phone.'">'.format_phone($user->cell_phone).'</a><br/>';
       $html .= '<span>Fax Number:</span> <a href="tel:'.$user->fax_number.'">'.format_phone($user->fax_number).'</a><br/>';
       
       $domain = str_ireplace('www.', '', parse_url(get_site_url(), PHP_URL_HOST));
       
       if( !stristr($user->user_email, '@'.$domain) ){
	   	$html .= '<span>Email Address:</span> <a href="mailto:'.$user->user_email.'">'.$user->user_email.'</a><br/>';    
       }
       
       $html .= '<span>GCSAA Member:</span> '.(("" != $user->gcsaa_member)?'yes':'no').'<br/>';
       $html .= '<span>Pesticide License:</span> '.$user->pesticide_license.'<br/>';
       $html .= '<span>Services Offered:</span> '.$user->services_offered.'<br/>';
       
  
       $html .= '</div><!-- end details -->';
        $html .= '</div><!-- end entry -->';
    }
	
	$html .= "</div> <!-- end directory -->";
	
	return $html;
	
}
add_shortcode( 'user_directory', 'user_directory_shortcode' );


add_filter('pp_eu_exclude_data', 'exclude_data', 15);

function exclude_data(){

	return array( 'user_pass', 'tribe_setdefaultnavmenuboxes', 'user_activation_key', 'user_url', 'user_registered', 'user_status', 'ID', 'admin_color', 'closedpostboxes_page', 'closedpostboxes_tribe_events', 'comment_shortcuts', 'description', 'dismissed_wp_pointers', 'edit_page_per_page', 'display_name', 'user_nicename',  'managenav-menuscolumnshidden', 'metaboxhidden_nav-menus', 'metaboxhidden_page', 'metaboxhidden_tribe_events', 'nav_menu_recently_edited', 'nickname', 'rich_editing', 'show_admin_bar_front', 'show_welcome_panel', 'tribe_setdefaultnavmenuboxes', 'user_dribbble', 'user_facebook', 'user_gplus', 'user_linkedin', 'user_position', 'user_twitter', 'use_ssl', 'wp_capabilities', 'wp_dashboard_quick_press_last_post_id', 'wp_user-settings', 'wp_user-settings-time', 'wp_user_avatar', 'wp_user_level', 'tribe_setDefaultNavMenuBoxes' );
}


    function format_phone($phone)
    {
    $phone = preg_replace("/[^0-9]/", "", $phone);
     
    if(strlen($phone) == 7)
    return preg_replace("/([0-9]{3})([0-9]{4})/", "$1-$2", $phone);
    elseif(strlen($phone) == 10)
    return preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "($1) $2-$3", $phone);
    else
    return $phone;
    }
    
    
if( $_GET['update_plugins'] == "github" ){
	define( 'WP_GITHUB_FORCE_UPDATE', true );
}else{
	define( 'WP_GITHUB_FORCE_UPDATE', false );
}

add_action( 'init', 'dm_github_plugin_updater_directory_init' );

function dm_github_plugin_updater_directory_init() {

	include_once plugin_dir_path( __FILE__ ) . 'includes/github-updater.php';

	if ( is_admin() ) { // note the use of is_admin() to double check that this is happening in the admin

		$config = array(
			'slug' => plugin_basename( __FILE__ ),
			'proper_folder_name' => 'DM-directory',
			'api_url' => 'https://api.github.com/repos/DesignMissoula/DM-directory/contents/',
			'github_url' => 'https://github.com/DesignMissoula/DM-directory',
			'zip_url' => 'https://api.github.com/repos/DesignMissoula/DM-directory/zipball/gcsaa-groups',
			'sslverify' => true,
			'requires' => '3.8',
			'tested' => '3.9.1',
			'readme' => 'README.md',
			'access_token' => '', 
			'branch' => 'gcsaa-groups',
		);

		new WP_GitHub_Updater( $config );

	}

}    