<?php
//$tags=M('tags')->where('id in (select distinct(tid) from onethink_tags_map) and category=6')->select();
$tags=M('product_tags')->select();
$menus='列表=list';
//$menu='news=资讯&info=资料&guide=攻略&ask=问答&video=视频&down=下载';
$temps=C('TMPL_PARSE_STRING');
//die(var_dump($temps));
$content=file_get_contents(I('fun').'/index.html');
$regular='/<include file="([^"]+)"\/>/is';

if(preg_match_all($regular,$content,$results)){
	$file=str_replace('@','/view/',$results[1][0]);
	$content=str_replace($results[0][0],file_get_contents('./Application/'.$file.'.html'),$content);
	//var_dump($results,$file);
}
echo json_encode(
	array('select'=>
		array(
		'name'=>'专区标签',
		'table'=>'tag_id',
		'menu'=>$menus,
		'tags'=>$tags
		),
		'content'=>str_replace('__PUBLIC__',$temps['__PUBLIC__'],$content)
));