<?php
//$tags=M('tags')->where('id in (select distinct(tid) from onethink_tags_map) and category=6')->select();
$tags=M('product_tags')->select();
$menu='资讯=news&资料=info&攻略=guide&问答=ask&视频=video&下载=down';
//$menu='news=资讯&info=资料&guide=攻略&ask=问答&video=视频&down=下载';
echo json_encode(
	array('select'=>
		array(
		'name'=>'专区标签',
		'table'=>'tag_id',
		'menu'=>$menu,
		'tags'=>$tags
		),
		'content'=>file_get_contents(I('fun'))
));