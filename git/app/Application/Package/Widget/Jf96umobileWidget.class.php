<?php


namespace Package\Widget;
use Package\Controller\BaseController;






class Jf96umobileWidget extends BaseController{
	
		//相关礼包
	public function relatePackage($id){
		if(!is_numeric($id)){return;}
		$package=get_base_by_tag($id,'Package','Package','',false);
		$this->assign("lists",$package);
		$this->display("Widget/relatePackage");
	}


   //礼包聚合页面
  public function packageAggregation (){
	    $id=I('get.id');
	    if(!is_numeric($id)){return;}
		$down = M('Down')->where(array("id"=>$id))->find();
	    $package=get_base_by_tag($id,'Down','Package','',false);
		$batch=get_base_by_tag($id,'Down','Batch','',false);
		$this->assign("down",$down);
		$this->assign("batch",$batch);
		$this->assign("lists",$package);
		$SEO['title'] = $down['title']."相关礼包";
		$SEO['keywords'] = $down['title']."相关礼包";
		$SEO['description'] = $down['title']."相关礼包";
		$this->assign("SEO",$SEO);
		$this->assign("count",count($package));
		$this->display("Widget/giftgame");
	}














	
}