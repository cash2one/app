<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Document\Widget;
use Common\Controller\WidgetController;
use Think\Controller;

/**
 * 页面widget
 * 
 */

class QiliWidget extends WidgetController{

	/**
	* 时尚频道
	* @author Jeffrey Lau
	* @date   2015-10-20 15:02:55
	*/
   public function channelFashion(){
	   $this->assign("SEO",WidgetSEO(array('special',null,'64')));
	   $this->display("Category/fashion");
  }

	/**
	* 美容频道
	* @author Jeffrey Lau
	* @date   2015-10-20 15:02:55
	*/
   public function channelBeauty(){
	   $this->assign("SEO",WidgetSEO(array('special',null,'68')));
	   $this->display("Category/beauty");
   }

   /**
	* 文章内容图文推荐
	* @author zhudesheng
	*/
	function graphicBlack($typeid, $id){
		$blck = array(
			'fashion'	=>	array('1507', '1510', '1', '214', '1500', '1501', '1509', '1508'),
			'beauty'	=>	array('2', '99', '4', '1517', '1503'),
			'life'		=>	array('918', '917', '5', '951', '303', '277'),
			'recreation'=>	array('1516', '1511', '293', '1514'),
		);
		
		$rootid = M('category')->field('rootid')->where(array('id' => $typeid))->getField('rootid');
		if(!$rootid){
			return;
		}
		
		$typeid = '';
		foreach($blck as $params){
			if(!$typeid){
				if(in_array($rootid, $params)){
					$typeid = implode(',', $params);
				}
			}
		}
		
		$typeid = M('category')->field('id')->where("pid IN($typeid)")->select();
		if($typeid){
			$typeid   = implode(array_column($typeid, 'id'), ',');
			$document = M('document')->field('id')->where("category_id IN($typeid) AND status = 1 AND detail_recom & 1")->limit(4)->select();
			
			if($document){
				$count = count($document);
				if($count <= 4){
					$notid = implode(array_column($document, 'id'), ',');
					$fill  = M('document')->field('id')->where("category_id IN($typeid) AND status = 1 AND id NOT IN($notid)")->limit(4)->select();

					if($fill){
						$document = array_merge($document, $fill);
					}
				}
			}else{
				$document = M('document')->field('id')->where("category_id IN($typeid) AND status = 1")->order('id DESC')->limit(4)->select();
			}
		}

		if($document){
			$this->id = implode(array_column($document, 'id'), ',');
			$this->display("Widget/graphicBlack");
		}
	}

    /**
	* 详情页面相同标签标签文章排行
	* @author Jeffrey Lau
	* @date   2015-10-26 11:16:13
	*/
   public function detailTagRank($id){
	   $tag = M('CategoryMap')->alias("__MAP")->where("did = '$id' AND __MAP.type ='document'")->join("INNER JOIN __CATEGORY__ ON __MAP.cid = __CATEGORY__.id")->limit(2)->field("title,did,cid")->select();
	   if($tag){
			if(!empty($tag[0]['cid'])){
				$t1 = M('CategoryMap')->where(array('cid'=>$tag[0]['cid'],'type'=>'document'))->field("did,cid")->select();

				$ids1 = '';
				foreach($t1 as $key=>$val){
				   $ids1.= $val['did'].",";
				}
				$ids1 = rtrim($ids1,",");
			}
			
			if(!empty($tag[0]['cid'])){
				$t2 = M('CategoryMap')->where(array('cid'=>$tag[1]['cid'],'type'=>'document'))->field("did,cid")->select();
				
				$ids2 = '';
				foreach($t2 as $key=>$val){
				   $ids2.= $val['did'].",";
				}
				$ids2 = rtrim($ids2,",");
			}

			if($ids1){
				$doc1 = M('Document')->where("id IN ($ids1)")->order("create_time desc")->field("id,title,category_id")->limit(5)->select();
				if($doc1){
					$doc1 = implode(array_column($doc1, 'id'), ',');
				}
		   }

		   if($ids2){
				$doc2 =M('Document')->where("id IN ($ids2)")->order("create_time desc")->field("id,title")->limit(5)->select();
				if($doc2){
					$doc2 = implode(array_column($doc2, 'id'), ',');
				}
		   }
		   $this->assign("list1",$doc1);
		   $this->assign("list2",$doc2);
		  
		   $this->assign("tag",$tag);
		   $this->display("Widget/detailTagRank");
	   }
   }



  /**
	* 详情页面标签文章排行
	* @author Jeffrey Lau
	* @date   2015-10-27 16:46:16
	*/

   public function relateContent($id){
	    $tag = M('CategoryMap')->alias("__MAP")->where("did = '$id' AND __MAP.type ='document'")->join("INNER JOIN __CATEGORY__ ON __MAP.cid = __CATEGORY__.id")->limit(5)->field("title,did,cid")->select();
		
		$document = M('Document');

		if($tag){
			
		}

		$this->assign("list1",$doc1);
	    $this->assign("list2",$doc2);
	    $this->display("Widget/relateContent");
   }

  /**
	* 详情页面推荐
	* @author Jeffrey Lau
	* @date   2015-10-28 15:30:12
	*/

public function commendedNews($typeid){
	    $category = M('category');
		$details  = $category->find($typeid);
		$look	  = $category->field('id,title,template_index,path_index,template_lists,path_lists,path_detail,path_lists_index,rootid,recommend_view_name')->where("status = 1 AND rootid = $details[rootid] AND recommend_view_name != ''")->limit('10')->select();
		
		if($look){
			foreach($look as $k => $v){
				$look[$k]['url'] = parseModuleUrl($v);
			}
			$this->loop = $look;
			$this->assign("list",$look);
			$this->display("Widget/commendedNews");
		}
}


  /**
	* 详情页面大家都在搜
	* @author Jeffrey Lau
	* @date   2015-10-29 16:31:29
	*/

public function detailSearch($typeid){
	$category = M('category');
	$rootid   = $category->field('rootid')->where(array('status' => 1, 'id' => $typeid))->getField('rootid');
	if($rootid){
		$list = $category->where("status = 1 AND rootid = '$rootid' AND poly_name != ''")->limit(4)->select();

		if($list){
			$ids = implode(array_column($list, 'id'), ',');
			$result = array();

			foreach($list as $k => $value){
				$result[$k]['root'] = $value;
				if($ids){
					$son = $category->where("status = 1 AND poly_name = '$value[poly_name]' AND id NOT IN($ids)")->order('id DESC')->limit(8)->select();
				}

				if($son){
					$_ids = implode(array_column($son, 'id'), ',');
					if($ids){
						$_ids = ','.$_ids;
					}
					$ids .= $_ids;

					foreach($son as $e => $val){
						$son[$e]['module'] = array('Category', 'Document', 'DocumentArticle');
						$son[$e]['url']	   = parseModuleUrl($son[$e]);
					}

					$result[$k]['son'] = $son;
				}
			}

			if($result){
				foreach($result as $k => $value){
					if(empty($value['son'])){
						unset($result[$k]['root'], $result[$k]['son']);
					}
				}
				$result = array_filter($result);
				if($result){
					$this->theme = $result;
					$this->display("Widget/detailSearch");
				}
			}
		}
	}
}


  /**
	* 详情页面猜你喜欢
	* @author Jeffrey Lau
	* @date   2015-10-29 16:31:29
	*/

	public function guessuLike($rootid){
		if(!is_numeric($rootid)){
			return;
		}

		$lists = M('Category')->field('id')->where("status=1 AND rootid=$rootid AND pid!=0")->field("id,title")->order("sort desc")->limit("10")->select();
		if($lists){
			$this->id = implode(array_column($lists, 'id'), ',');
			$this->display("Widget/guessuLike");
		}
	}
}