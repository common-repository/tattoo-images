<?php

/*
Plugin Name: gallery Images
Plugin URI: http://wordpress.org/
Description: Find images and add them to your blog with just a click. Backlink is required.
Version: 1.0
Author: Nerijus Oftas
Author URI: http://nerijuso.lt/
License: GPLv2
*/

define('_gallery_IMAGES_PLUGIN_VERSION', '1.1');
define('_gallery_IMAGES_STATIC_URL', plugin_dir_url(__FILE__).'static/' );
define('_gallery_IMAGES_DB_OPTION_NAME', 'gallery_images');

#~~~ CoAuthor, Plugin Info (metalinks added in plugins blog page)
global $gallery_mealink_plugin;
$gallery_mealink_plugin[] = array('prename' => 'Co Author', 'name' => 'Nerijus Oftas', 'uri' => 'http://nerijuso.lt/');
$gallery_mealink_plugin[] = array('prename' => 'Images Source', 'name' => 'gallery', 'uri' => 'http://tattmight.com/');

#~~~ init options
global $default_gallery_options;
$default_gallery_options = array(
	'version'=> _gallery_IMAGES_PLUGIN_VERSION,
);

global $byrev_gallery_options;
$byrev_gallery_options = false;
#~~~
add_action( 'admin_init', 'gallery_scripts_method' );
function gallery_scripts_method() {
	wp_enqueue_script(
		'gallery-lightbox',
		_gallery_IMAGES_STATIC_URL . 'gallery-lightbox.js',
		array( 'jquery' ),
		false,
		true
	);
}

#~~~ run script if post/get option
if (isset($_POST[_gallery_IMAGES_DB_OPTION_NAME])) {
	byrev_gallery_update_settings($_POST[_gallery_IMAGES_DB_OPTION_NAME]);
}

if (isset($_POST['gallery_upload'])) {
	byrev_gallery_load_plugin();
	include('wp-upload-image.php');
}

#~~ add mata info in plugins page - coauthor, images source, etc.
function gallery_plugin_links($links, $file) {  
	$plugin = plugin_basename(__FILE__);  
  
	if ($file == $plugin) :
		global $gallery_mealink_plugin;
		
		$_new_meta_links = array();
		foreach ($gallery_mealink_plugin as $metalink):
			$_new_meta_links[] = $metalink['prename'].': <a href="'.$metalink['uri'].'">' . __($metalink['name']) . '</a>';
		endforeach;
		
		return array_merge( $links, $_new_meta_links );  
	endif;
	
	return $links;  
}  
  
add_filter( 'plugin_row_meta', 'gallery_plugin_links', 10, 2 );  

#~~~ admin init
add_action('admin_init','byrev_gallery_load_plugin');
function byrev_gallery_load_plugin() {
	if(is_admin()) {
		byrev_gallery_activate();
		global $byrev_gallery_options;
	}
}


# add tab to media upload window
function gallery_upload_tab($tabs) {
    $tabs['gallerytab'] = 'Tattoo gallery';
    return $tabs;
}
add_filter('media_upload_tabs', 'gallery_upload_tab');

function gallery_media_button($editor_id = '') {
	$img = '<img style="vertical-align:middle;padding:0 3px" src="'._gallery_IMAGES_STATIC_URL.'favicon.ico" />';
	echo '<a href="' . add_query_arg('tab','gallerytab', esc_url( get_upload_iframe_src() ) ). '" class="thickbox add_media" id="' . esc_attr( $editor_id ) . '-add_media" title="' . esc_attr__( 'Search gallery', 'gallery' ) . '" onclick="return false;">' . sprintf( $img ) . '</a>';
}
add_action('media_buttons', 'gallery_media_button', 20);

function media_upload_to_gallery() {
	byrev_gallery_load_plugin();
    # wp_iframe() adds css for "media" when callback function has "media_" as prefix (media_dummy)
	function media_dummy() { echo media_upload_header(); include('gallery-publicdomain.php'); }
    wp_iframe('media_dummy');
}
add_action('media_upload_gallerytab', 'media_upload_to_gallery');


#~~~~~~~~ deactivation and uninstall hook
register_activation_hook( __FILE__ , 'byrev_gallery_activate' );
register_deactivation_hook( __FILE__ , 'byrev_gallery_deactivate' );
register_uninstall_hook( __FILE__ ,'byrev_gallery_uninstall');

function byrev_gallery_activate() {
global $byrev_gallery_options, $default_gallery_options;
	$byrev_gallery_options = byrev_gallery_get_settings();

	if ($byrev_gallery_options['version'] != _gallery_IMAGES_PLUGIN_VERSION) :
		$update_option = array_diff_key($default_gallery_options, $byrev_gallery_options);
		if (count($update_option)>0) {
			$byrev_gallery_options = array_merge($byrev_gallery_options, $update_option);
			byrev_gallery_update_settings($byrev_gallery_options);
		}
	endif;
}

function byrev_gallery_get_settings() {
global $default_gallery_options;
	$gallery_options = get_option(_gallery_IMAGES_DB_OPTION_NAME);
	if ($gallery_options === false) {
		update_option(_gallery_IMAGES_DB_OPTION_NAME, $default_gallery_options);
		return $default_gallery_options;
	}
	return $gallery_options;
}

function byrev_gallery_update_settings($store_data) {
	$gallery_options = get_option(_gallery_IMAGES_DB_OPTION_NAME);
	foreach ($store_data as $key=>$value):
		$gallery_options[$key] = $value;
	endforeach;
	update_option(_gallery_IMAGES_DB_OPTION_NAME, $gallery_options);
}

function byrev_gallery_deactivate() {
	$byrev_gallery_settings = byrev_gallery_get_settings();
	# ~~~ change settings for deactivate ... if needed, and save
	byrev_gallery_update_settings($byrev_gallery_settings);
}

function byrev_gallery_uninstall() {
	delete_option( _gallery_IMAGES_DB_OPTION_NAME );
}

?>