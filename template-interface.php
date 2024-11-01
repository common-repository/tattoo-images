<?php
$tpl_form = '
    <link rel="stylesheet" href="'._gallery_IMAGES_STATIC_URL.'base.css" />
    <div id="tpl_form">{html_form}</div>
';

$tpl_item = '
	<div class="tpl-item">
			<img alt="{tags}" src="{previewURL}" />
		<div class="info">
            <a href="#" class="insert_640 direct" data-post_id="{post_id}" data-media_tab="{media_tab}" data-image_src="{webformatURL}" data-image_tags="{tags}" data-page_url="{webformatURL}" data-web_url="{webURL}" data-image_user="{user}">Insert {webformatWidth}x{webformatHeight}</a>
		</div>
	</div>
';

$tpl_navi = '<div class="tpl-navi">{tpl_navi}</div>';

$tpl_info_init_search ='<div class="tpl_info_init_search">{info_init_search}</div>';

function tpl_get_html( $tpl, $search_replace ) {
	$html = $tpl;
	foreach ($search_replace as $search=>$replace) :
		$str_search = '{'.$search.'}';
		$html = str_replace($str_search, $replace, $html);
	endforeach;
	return $html;
}
?>
