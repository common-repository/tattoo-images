<?php
define('_gallery_IMAGES_RESULT_PER_PAGE', 26);

/************* CREATE CACHE FOLDER ***************/
$_wp_upload_dir = wp_upload_dir();
$_wp_upload_basedir = $_wp_upload_dir['basedir'];
define('_gallery_IMAGES_QUERY_CACHE_FOLDER', $_wp_upload_basedir.'/cache~gallery');

if (!is_dir(_gallery_IMAGES_QUERY_CACHE_FOLDER)) {
    @mkdir(_gallery_IMAGES_QUERY_CACHE_FOLDER, 0705);
}

/************* INIT REQUEST *************/
global $gallery_api_default_query;
$gallery_api_default_query = array(
	'username' => 'WPPlugin',
	'key' => 'dGF0dG1pZ2h0MjAxNA==',
	'search_term' => '',
	'order' => 'popular',
	'page' => 1,
	'per_page' => _gallery_IMAGES_RESULT_PER_PAGE,
);

global $gallery_api_request;
if (isset($_REQUEST['gallery-request'])) {
	$gallery_api_request = $_REQUEST['gallery-request'];
	foreach ($gallery_api_default_query as $name=>$value):
		if (!isset($gallery_api_request[$name])) {
			$gallery_api_request[$name] =  $value;
		}
		//
	endforeach;
	define('_gallery_NEW_REQUEST', true);
} else {
	$gallery_api_request = $gallery_api_default_query;
	define('_gallery_NEW_REQUEST', false);
}

/************* LAST QUERY **************/
global $byrev_api_gallery_last_query;
$byrev_api_s_last_query = "";

/************* PRINT PARAMETERS **************/
global $request_parameters_info;
$request_parameters_info = array (
	'search_term' => array('type'=>'text', 'values' => "", 'title'=> 'Search'),
	#'lang' => array('type'=>'select', 'values' => array('en', 'id','cs','de','es','fr','it','nl','no','hu','ru','pl','pt','ro','fi','sv','tr','ja','ko','zh') , 'title'=> 'Language'),
	#'image_type' => array(),
	#'orientation' => array() ,
);

/************* POST ID **************/
if (!isset($_REQUEST['post_id'])) $_REQUEST['post_id'] = '0';
define('_EDIT_POST_ID', $_REQUEST['post_id']);
?>