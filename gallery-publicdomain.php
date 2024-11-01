<?php
	include('gallery-initvar.php');
	include('template-interface.php');
	include('template-input-form.php');
	include('paginate-functions.php');

    function byrev_api_gallery_run_query($query) { 
    global $byrev_api_gallery_last_query;
        $byrev_api_gallery_last_query = $query;
        $query_file_cache = _gallery_IMAGES_QUERY_CACHE_FOLDER.'/'.md5(strtolower(serialize($query))).'.json';

        if (file_exists($query_file_cache) && (time() - 86400 < filemtime($query_file_cache))):
            $cached_results = @file_get_contents($query_file_cache);
            if ($cached_results) return $cached_results;
        endif;

        $gallery_api_url = add_query_arg($query, 'http://tattmight.com/api/search');
        
        $gallery_api_data = wp_remote_retrieve_body( wp_remote_get($gallery_api_url) );
      
        if ($gallery_api_data !== false) {
            @file_put_contents($query_file_cache, $gallery_api_data);
            return $gallery_api_data;
        } else {
            return 'API request error';
        }
    }

	#~~~ the page must have a title, library tab does not work otherwise
	$_MEDIA_TAB = 'library';
	if (_EDIT_POST_ID != 0) {
		$post_tile = get_the_title(_EDIT_POST_ID);
		if ($post_tile == "Auto Draft"):
			echo '<div style="margin:1px 0 10px;color:#b94a48;background:#f3f3f3;border-bottom:1px solid #eee;padding:8px;text-align:center;font-weight:bold">Note: Before inserting images with this plugin, please enter a title to the corresponding post or page.</div>';
			$_MEDIA_TAB = 'gallery';
		endif;
	}
	define('_gallery_LOAD_MEDIA_TAB', $_MEDIA_TAB);
	#~~~

    $post_id = absint($_REQUEST['post_id']);
	$var_form = array(
		'html_form'=> byrev_print_form("media-upload.php?type=image&tab=gallerytab&post_id=$post_id", $request_parameters_info, $gallery_api_request)
	);

	$tpl_items = '';
	if (_gallery_NEW_REQUEST) :
	
		$response_data = byrev_api_gallery_run_query($gallery_api_request);

		$data = json_decode($response_data, true);
		
		if ($data == ''):
            $tpl_items = '<br style="clear: both;" /><div style="margin:15px 2px;color:#b94a48;font-weight:bold;">Request/Server error: '.$response_data.'</div>';
		else:
            $total_hits = $data['images_count'];
            $total_pages = ceil($total_hits/_gallery_IMAGES_RESULT_PER_PAGE);
            $current_page = $gallery_api_request['page'];
          
            $paginate_limit=5;
            $base_url = curPageURL_gallery();
            $_navigation_html = get_paginate($base_url, 'gallery-request[page]', $total_pages, $current_page, $paginate_limit);

			$tpl_items = '';
            if ($data['images_count']):
				$add_var_data = array('post_id' => _EDIT_POST_ID, 'media_tab' => _gallery_LOAD_MEDIA_TAB);
                foreach ($data['images_list'] as $hits):
					$hits = array_merge($hits, $add_var_data);
                	$hits = array_merge($hits, $add_var_data);
                	$hits['previewURL'] = 'http://tattmight.com/albums/'.$hits['filepath'].'thumb_'.$hits['filename'];
                	$hits['tags'] = $hits['filename'];
                	$hits['webformatWidth'] = $hits['pwidth'];
                	$hits['webformatHeight'] = $hits['pheight'];
                	$hits['webURL'] = 'http://tattmight.com/gallery/image/'.$hits['pid'];
                	$hits['webformatURL'] = 'http://tattmight.com/albums/'.$hits['filepath'].$hits['filename'];
                	$hits['user'] = 'tattmight.com';
                	
                    $tpl_items .= tpl_get_html($tpl_item, $hits);
                endforeach;
                if (absint($gallery_api_request['page']) == ceil(240/_gallery_IMAGES_RESULT_PER_PAGE)):
                    if ($gallery_api_request['search_term'] != ''):
                        $tpl_items .= '<br style="clear:both" /><div style="font-weight:bold;font-size:14px;text-align:center;margin:20px 5px 10px 0"><a target="_blank" href="http://tattmight.com/gallery/search?doSearch=&SearchText='.urlencode($gallery_api_request['search_term']).'">Find more images about "'.htmlspecialchars($gallery_api_request['search_term']).'" on gallery</a></div>';
                    else:
                        $tpl_items .= '<br style="clear:both" /><div style="font-weight:bold;font-size:14px;text-align:center;margin:20px 5px 10px 0"><a target="_blank" href="http://tattmight.com/">Find more images on gallery</a></div>';
                    endif;
                endif;

            else:
                $tpl_items = '<br style="clear: both;" /><div style="margin:15px 2px;color:#777;font-weight:bold;">Sorry, no hits! Please check the spelling or use generic or synonymous terms.</div>';
            endif;
		endif;
	else:
		$_navigation_html = '';
	endif;

	$var_navigation = array(
		'tpl_navi'=> $_navigation_html
	);

	$var_info_init_search = array(
		'info_init_search' => '<a href="http://tattmight.com/" target="_blank"><img src="'._gallery_IMAGES_STATIC_URL.'logo.jpg" style="width:124px;height:43px" /></a>
			<p style="margin:4px 0 15px">Tattoo images gallery</p>
			Plugin by<br /><a style="color:#2c89bc" href="http://nerijuso.lt/" target="_blank">nerijuso.lt</a>
			<form target="_blank" style="margin-left: 5px; display: inline-block; zoom: 1; *display: inline; opacity: 0.8; filter: alpha(opacity=80);" action="https://www.paypal.com/cgi-bin/webscr" method="post">
                <input type="hidden" name="cmd" value="_s-xclick" />
                <input type="hidden" name="hosted_button_id" value="EXF6Y5HMU64F2" />
				<input type="submit" style="line-height:20px;height:21px;padding:0 8px 1px" title="PayPal" value="Donate" />
			</form>
			'
	);
?>
<div id="tpl_wrapper">
	<?=tpl_get_html($tpl_form, $var_form );?>
	<?php if (!_gallery_NEW_REQUEST) : echo tpl_get_html($tpl_info_init_search, $var_info_init_search ); endif;	?>
	<?=$tpl_items;?>
	<div id="tpl-end">
        <?php if ($_navigation_html != '') : echo tpl_get_html($tpl_navi, $var_navigation ); endif;	?>
		<div style="clear: both;"></div>
	</div>
	<div style="clear: both;"></div>
</div>
<div id="lightbox"><div id="content"><img id="imgsource" src="<?=_gallery_IMAGES_STATIC_URL;?>loadingAnimation.gif" /></div></div>