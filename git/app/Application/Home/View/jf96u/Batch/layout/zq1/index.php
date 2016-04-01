<?php
//$tags=M('tags')->where('id in (select distinct(tid) from onethink_tags_map) and category=6')->select();
$tags=M('product_tags')->select();
$menus='攻略=gl&资讯=zx&评测=pc&问答=wd&视频=sp';
//$menu='news=资讯&info=资料&guide=攻略&ask=问答&video=视频&down=下载';
$temps=C('TMPL_PARSE_STRING');
$content=file_get_contents(I('fun').'/index.html');
$regular='/<include file="([^"]+)"\/>/is';

if(preg_match_all($regular,$content,$results)){
	$file=str_replace('@','/View/',$results[1][0]);
	$content=str_replace($results[0][0],file_get_contents('./Application/'.$file.'.html'),$content);
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