<?php
namespace Admin\Controller;

/**
 * 后台控制器
 * @author leha.com
 */

class SpecialController extends FeatureController {

	//文档模型名
	protected $document_name = 'Special';
	protected $base_view = 'Feature';
	protected $main_title='K页面';
	protected $model_id='17';
    protected $cate_name='FeatureCategory';
	
	function ids($ids){
		//var_dump($ids);
		$lists=M('down')->where('id in (\''.implode('\',\'',$ids).'\')')->limit('30')->select();
		foreach($lists as $k=>&$v){
			$v=$v+M('down_dmain')->where('id='.$v['id'])->find();
			$v['size']=round($v['size']/1024,2);
			$vv=M('down_address')->where('did='.$v['id'])->find();
			if(is_array($vv)) $v+=$vv;
		}
		return $lists;
	}
	
	function tags($tags){
		$tid=implode('\',\'',$tags);
		$maps=M('product_tags_map')->field('did')->where('tid in (\''.$tid.'\')')->limit('300')->select();
		foreach($maps as $k=>$v){
			if($k) $id.='\',\''.$v['did'];
			else $id.=$v['did'];
		}
		$lists=M('down')->where('id in (\''.$id.'\')')->limit('30')->select();
		foreach($lists as $k=>&$v){
			$v=$v+M('down_dmain')->where('id='.$v['id'])->find();
			$v['size']=round($v['size']/1024,2);
			$vv=M('down_address')->where('did='.$v['id'])->find();
			if(is_array($vv)) $v+=$vv;
		}
		return $lists;				
	}
	
	function lists($cats,$tags){
		$id='';
		//var_dump($cats,$tags);
		if($tags){
			$tid=implode('\',\'',$tags);
			$maps=M('tags_map')->field('did')->where('tid in (\''.$tid.'\')')->limit('300')->select();
			foreach($maps as $k=>$v){
				if($k) $id.='\',\''.$v['did'];
				else $id.=$v['did'];
			}
			$lists=M('down')->where('id in (\''.$id.'\')')->limit('30')->select();
		}else{
			$cid=implode('\',\'',$cats);
			$lists=M('down')->where('category_id in (\''.$cid.'\')')->limit('30')->select();
		}
		foreach($lists as $k=>&$v){
			$v=$v+M('down_dmain')->where('id='.$v['id'])->find();
			$v['size']=round($v['size']/1024,2);
			$vv=M('down_address')->where('did='.$v['id'])->find();
			if(is_array($vv)) $v+=$vv;
		}
		//$this->assign('lists',$lists);
		return $lists;
	}
	
	function image(){
		return;
	}
	
	function html(){
		return;
	}
	
	function selects(){
		
	}
	
	public function __call($method, $args){
		return;
	}	
}