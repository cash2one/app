<?php
// +----------------------------------------------------------------------
// | 描述:文章模块widget文件
// +----------------------------------------------------------------------
// | Author: 谭坚 <tanjian691406429@163.com>
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------
// | Date: 15-11-11 下午2:54    Version:1.0.0 
// +----------------------------------------------------------------------
namespace Document\Widget;
use Think\Controller;
class Jf96umobileWidget extends Controller{

   		//相关礼包
	public function relatePackage($id){
		if(!is_numeric($id)){return;}
		$package=get_base_by_tag($id,'Document','Package','',true);
		$this->assign("lists",$package);
		$this->display("Widget/relatePackage");
	}
	
	 //相关下载
	public function relateDown($id){
		if(!is_numeric($id)){return;}
		$tid = M('TagsMap')->where(array("did"=>$id,"type"=>"document"))->getField("tid");
		if(!is_numeric($tid)){return;}
		$down =M('TagsMap')->alias("__TAGS")->field("__TAGS.type,__TAGS.tid,__TAGS.did")->where("`tid`= $tid  AND __TAGS.type='down'")->join("__DOWN__ d ON d.id = __TAGS.did")->field("d.id,d.title,d.create_time,d.update_time")->order("d.update_time")->limit("5")->select();
		$lists = array();
        $game_lists=array();
		foreach($down as $key=>$val){
			$lists[] = M("Down")->alias("d")->where(array("d.id"=>$val['id'],"d.model_id"=>'21'))->join("LEFT JOIN __DOWN_DMAIN__ ON d.id = __DOWN_DMAIN__.id")->join("LEFT JOIN __DOWN_DSOFT__ ON d.id = __DOWN_DSOFT__.id")->select();
            if(empty($game_lists))
            $game_lists = M("Down")->alias("d")->where(array("d.id"=>$val['id'],"d.model_id"=>'13'))->join("LEFT JOIN __DOWN_DMAIN__ ON d.id = __DOWN_DMAIN__.id")->find();
        }
		$lists = array_filter($lists);
		if(!is_array($lists)){return;}
		$result= array();
		foreach($lists as $value){
			foreach($value as $v){
			    $result[] =$v;
	        }
	    }
        $this->assign('game_lists',$game_lists);
		$this->assign("lists",$result);
		$this->display("Widget/relateDown");
	}
	
	  //相同攻略
	public function sameGonglve($id){
		if(!is_numeric($id)){return;}
		$p=get_base_by_tag($id,'Document','Document','',false);
	    foreach($p as $key=>$val){
			 if($val['id'] == $id){unset($val);}
		      $lists[]=$val; 
		 }
	    $lists=array_filter($lists);
		$this->assign("lists",$lists);
		$this->display("Widget/sameGonglve");
	}
   //相同游戏
	public function sameGame($id){
		if(!is_numeric($id)){return;}
		$p=get_base_by_tag($id,'Document','Down','',false);
	    foreach($p as $key=>$val){
			 if($val['id'] == $id){unset($val);}
		      $lists[]=$val; 
		 }
	    $lists=array_filter($lists);
		$this->assign("lists",$lists);
		$this->display("Widget/sameGame");
	}
	
	//相同厂商
	public function sameCompany($id){
		if(!is_numeric($id)){return;}
		$down=get_base_by_tag($id,'Document','Down','',true);
		$company_id = $down['company_id'];
		if(!is_numeric($company_id) || $company_id=="0" ){return;}
		$company = M("Company")->where(array("id"=>$company_id,'status'=>'1'))->find();
		$lists=M("Down")->alias("__DOWN")->where(array("company_id"=>$company_id,'data_type'=>'1','status'=>'1'))->order("update_time desc")->join("INNER JOIN __DOWN_DMAIN__ ON __DOWN.id = __DOWN_DMAIN__.id")->limit("10")->field("*")->select();
		$this->assign("lists",$lists);
		$this->assign("company",$company);
		$this->display("Widget/sameCompany");
	}
	
	
	
	
	
	
} 