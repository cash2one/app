<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Gallery\Widget;
use Think\Controller;

/**
 * Qbaobeiwidget
 */

class QbaobeiWidget extends Controller{
	
	
	
	
	
	
	
	
	
	
	
   /**
   * Description：    相关图片
   * Author:         Jeffrey Lau
   * Modify Time:    2015-8-28 15:36:22
   */
  
	public function relatePic($id){
		if(!is_numeric($id)){return;}
		 $p=get_base_by_tag($id,'Gallery','Gallery','tags',false);
		 foreach($p as $key=>$val){
			 if($val['id'] == $id){unset($val);}
		      $lists[]=$val; 
		 }
	     $lists=array_filter($lists);
	     $this->assign("lists",$lists);
		 $this->display(T('Gallery@qbaobei/Widget/relatePic'));
	}
	
	
	/**
   * Description：    热门标签
   * Author:         Jeffrey Lau
   * Modify Time:    2015-8-28 15:36:22
   */
  
	public function hotTags(){
		 $tags = M("TagsMap")->alias("__TAGSMAP")->join("INNER JOIN __TAGS__ ON __TAGSMAP.tid = __TAGS__.id")->field("title,name")->limit("26")->where(array("type"=>"gallery"))->select();
		 $tags = array_unique_fb($tags);
		 $this->assign("lists",$tags);
		 $this->display(T('Gallery@qbaobei/Widget/hotTags'));
	}
	
	
	/**
   * Description：    相关标签
   * Author:         Jeffrey Lau
   * Modify Time:    2015-8-28 15:36:22
   */
  
	public function relateTags($id){
		 if(!is_numeric($id)){return;}//
		 $tags = M("TagsMap")->alias("__TAGSMAP")->join("INNER JOIN __TAGS__ ON __TAGSMAP.tid = __TAGS__.id")->field("title,name")->where(array("did"=>$id,"type"=>"gallery"))->select();
		 $tags = array_unique_fb($tags);
		 $this->assign("lists",$tags);
		 $this->display(T('Gallery@qbaobei/Widget/relateTags'));
	}
	
	
	
	
	
	/**
   * Description：    图片点击排行
   * Author:         Jeffrey Lau
   * Modify Time:    2015-8-28 15:36:22
   */
  
	public function picRank(){
		 $lists = M("Gallery")->where("status=1")->order("view desc")->limit("5")->select();
		 $this->assign("lists",$lists);
		 $this->display(T('Gallery@qbaobei/Widget/picRank'));
	}
	
	
	/**
   * Description：    图片点击排行
   * Author:         Jeffrey Lau
   * Modify Time:    2015-8-28 15:36:22
   */
  
	public function picCommended(){
		 $lists = M("Gallery")->where("pc_position & 32 AND status=1")->order("update_time desc")->limit("6")->select();
		 $this->assign("lists",$lists);
		 $this->display(T('Gallery@qbaobei/Widget/picCommended'));
	}
	
	
	
	/**
   * Description：    面包屑导航
   * Author:         Jeffrey Lau
   * Modify Time:    2015-9-2 18:45:33
   */
	
	public function breadCrumb($cid,$root_id){
		 if(!is_numeric($cid)){return;}
		 $category = M("GalleryCategory")->where(array("id"=>$cid))->find();
		 $root_category = M("GalleryCategory")->where(array("id"=>$root_id))->find();
		 $cateUrl = staticUrl('lists',$cid, 'Gallery');
		 $fatherUrl = staticUrl('lists',$root_category['id'],'Gallery');
		 echo "<a href=\"".$fatherUrl."\">".$root_category['title']."</a>><a href=\"".$cateUrl."\">".$category['title']."</a>>";
   }
	
	
	  //展示二级菜单
   public function showChildMenu($cate){
	   if(!is_numeric($cate)){return;}
	   $lists=M("GalleryCategory")->where(array("pid"=>$cate,"display"=>"1"))->select();
	   $this->assign('lists',$lists);
       $this->display(T('Gallery@qbaobei/Widget/showChildMenu'));
  }
  
	    //展示二级菜单
   public function showMenuTag(){
	   $tags = M("TagsMap")->alias("__TAGSMAP")->join("INNER JOIN __TAGS__ ON __TAGSMAP.tid = __TAGS__.id")->field("title,name")->where(array("type"=>"gallery"))->select();
	   $tags = array_unique_fb($tags);
	   $this->assign('lists',$tags);
       $this->display(T('Document@qbaobei/Widget/showMenuTag'));
  }
  
  
 
	
	
	
}
