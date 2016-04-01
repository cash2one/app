<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Document\Widget;
use Think\Controller;

/**
 * 页面widget
 * 
 */

class AfsmobileWidget extends Controller{


    /**
     * 方法不存在时调用
     *@return void
     */
    public function __call($method,$args) {
        //斜杠会被解析，所以用下划线代替
        $method = str_replace('_', '/', $method);
        $this->display(T($method));
    }
	
	    public function newsList(){
	    //根据传入static_page表ID查找数据
        $page_id = I('page_id');
        $page_info = get_staticpage($page_id);
		$where = array('category_rootid'=>'80');
		$whereMap = array('map' => array('category_rootid' => '80'));
        //分页获取数据
        $row = 10;
        $count  = D('Document')->where($where)->count();// 查询满足要求的总记录数
        //是否返回总页数
        if(I('gettotal')){
            echo ceil($count/$row);
            exit();
        }
        $p = intval(I('p'));
        if (!is_numeric($p) || $p<=0 ) $p = 1;
        if ($p > $count ) $p = $count; //容错处理

        $lists = D('Document')->page($p, $row)->listsWhere($whereMap, true);
        $this->assign('lists',$lists);// 赋值数据集
        $Page = new \Think\Page($count, $row, '', false, $page_info['path']. getStaticExt());// 实例化分页类 指定路径规则

        $Page->setConfig('prev','上一页');
        $Page->setConfig('next','下一页');
        $Page->setConfig('first','首页');
        $Page->setConfig('last','尾页');
		$Page->setConfig('theme','%UP_PAGE%  %DOWN_PAGE%');
        $show = $Page->show();// 分页显示输出
        $this->assign('page',$show);// 赋值分页输出

        //SEO
        $seo['title'] = $page_info['title'];
        $seo['keywords'] =  $page_info['keywords'];
        $seo['description'] = $page_info['description'];
        $this->assign('SEO',$seo);
        //模板选择
        $this->display(T('Document@afsmobile/Category/index'));
		
	}
	public function cateList(){
	     //根据传入static_page表ID查找数据
        $page_id = I('page_id');
		$cate= I('cate');
        $page_info = get_staticpage($page_id);
		 $where = array('category_id'=>$cate);
		 $whereMap = array('map' => array('category_id' => $cate));
        //分页获取数据
        $row = 10;
        $count  = D('Document')->where($where)->count();// 查询满足要求的总记录数
        //是否返回总页数
        if(I('gettotal')){
            echo ceil($count/$row);
            exit();
        }
        $p = intval(I('p'));
        if (!is_numeric($p) || $p<=0 ) $p = 1;
        if ($p > $count ) $p = $count; //容错处理

        $lists = D('Document')->page($p, $row)->listsWhere($whereMap, true);
        $this->assign('lists',$lists);// 赋值数据集
        $Page = new \Think\Page($count, $row, '', false, $page_info['path']. getStaticExt());// 实例化分页类 指定路径规则
        $Page->setConfig('prev','上一页');
        $Page->setConfig('next','下一页');
        $Page->setConfig('first','首页');
        $Page->setConfig('last','尾页');
		$Page->setConfig('theme','%UP_PAGE%  %DOWN_PAGE%');
        $show = $Page->show();// 分页显示输出
        $this->assign('page',$show);// 赋值分页输出
        $this->assign('cate',$cate);
        //SEO
        $seo['title'] = $page_info['title'];
        $seo['keywords'] =  $page_info['keywords'];
        $seo['description'] = $page_info['description'];
        $this->assign('SEO',$seo);
    	 $this->display(T('Document@afsmobile/Category/cate'));
		
	}

	public function categoryList(){
	     //根据传入static_page表ID查找数据
        $page_id = I('page_id');
		$cate= I('cate');
        $page_info = get_staticpage($page_id);
		 $where = array('category_id'=>$cate);
		 $whereMap = array('map' => array('category_id' => $cate));
        //分页获取数据
        $row = 10;
        $count  = D('Document')->where($where)->count();// 查询满足要求的总记录数
        //是否返回总页数
        if(I('gettotal')){
            echo ceil($count/$row);
            exit();
        }
        $p = intval(I('p'));
        if (!is_numeric($p) || $p<=0 ) $p = 1;
        if ($p > $count ) $p = $count; //容错处理

        $lists = D('Document')->page($p, $row)->listsWhere($whereMap, true);
        $this->assign('lists',$lists);// 赋值数据集
        $Page = new \Think\Page($count, $row, '', false, $page_info['path']. getStaticExt());// 实例化分页类 指定路径规则
        $Page->setConfig('prev','上一页');
        $Page->setConfig('next','下一页');
        $Page->setConfig('first','首页');
        $Page->setConfig('last','尾页');
		$Page->setConfig('theme','%UP_PAGE%  %DOWN_PAGE%');
        $show = $Page->show();// 分页显示输出
        $this->assign('page',$show);// 赋值分页输出
        $this->assign('cate',$cate);
        //SEO
        $seo['title'] = $page_info['title'];
        $seo['keywords'] =  $page_info['keywords'];
        $seo['description'] = $page_info['description'];
        $this->assign('SEO',$seo);
    	 $this->display(T('Document@afsmobile/Category/category'));
		
	}
	 /**
     * 文章详情相同风格
     */
    public function sameStyle($id){
		$array=array();
		$pcount=0;//产品标签匹配出的相关文章数目
		$count=0;//标签匹配出的相关文章数目
		$p=get_base_by_tag($id,'Document','Down','product',false);
		$p=multi_array_sort($p,'update_time',SORT_DESC);
		foreach($p as $k=>$val){
			if($val['data_type']=='1'){
				$c[]=$val;
				
				}
		}
		$pcount=count($c);
		$pcount=count($p);
		if($pcount<20){
		   $t=get_base_by_tag($id,'Document','Down','tags',false);
		   $count=count($t);
		}else{
			$result=$p;
		}
	    if($pcount+$count<20){
		$cate=M('Down')->where("id='$id'")->getField('category_id');
	    $cateNews=M('Down')->where("category_id='$cate' AND status=1")->order('create_time desc')->limit(20)->select();
	    }
	    foreach($c as $v){
		   $array[]=$v; 
	    }
	    foreach($t as $v){
		   $array[]=$v; 
	    }
	   foreach($cateNews as $v){
		   $array[]=$v; 
	    }
	    $this->assign("result",$array);
        $this->display('Widget/relateGame');
    }
	
	 /**
     * 文章详情相关文章
     */
    public function relateArticle($id){
	    $array=array();
		$pcount=0;//产品标签匹配出的相关文章数目
		$count=0;//标签匹配出的相关文章数目
		$p=get_base_by_tag($id,'Document','Document','product',false);
		$pcount=count($p);
		if($pcount<10){
		   $t=get_base_by_tag($id,'Document','Document','tags',false);
		   $count=count($t);
		}else{
			$result=$p;
		}
	   if($pcount+$count<10){
		$cate=M('Document')->where("id='$id'")->getField('category_id');
	    $cateNews=M('Document')->where("category_id='$cate'")->order('create_time desc')->limit(10)->select();
	   }
	    foreach($p as $v){
		   $array[]=$v; 
	    }
	    foreach($t as $v){
		   $array[]=$v; 
	    }
	   foreach($cateNews as $v){
		   $array[]=$v; 
	    }
	    $this->assign("result",$array);;
        $this->display('Widget/relateArticle');
    }

	
	public function floatBox($id){
		$p=get_base_by_tag($id,'Document','Down','product',false);
		$p=multi_array_sort($p,'update_time',SORT_DESC);
		foreach($p as $k=>$val){
			if($val['data_type']=='1'){
				$c[]=$val;
				
				}
		}
		$this->assign("d",$c);
		$this->display('Widget/floatBox');
		
   }
	
	/**
	 * @description 内容简介处理
	 * @author JeffreyLau
	 * @date  2016-1-23 14:33:37
	 */
	public function answerDescription($content,$wd_content){
	    $str = empty($wd_content) ? $content :$wd_content;
		echo $str;
	}
	/**
	 * @description 内容处理
	 * @author JeffreyLau
	 * @date 2016-1-23 14:33:57
	 */
	public function contentHandle($content){
		if($content==""){
			return;
		}
		preg_match_all('/<author>(.*?)<\/author>/',$content,$author);
	    preg_match_all('/<content>(.*)<\/content>/iUs',$content,$con);
        preg_match_all('/<time>(.*?)<\/time>/',$content,$time);
		$lists = array();
		foreach($time[1] as $key=>$val){
			$lists[$key]['time'] = $val;
		}
		foreach($author[1] as $key=>$val){
			$lists[$key]['author'] = $val;
		}
		foreach($con[1] as $key=>$val){
			$lists[$key]['content'] = $val;
			$lists[$key]['avatar'] = rand(0,5);
		}
		$this->assign("lists",$lists);
		$this->display('Widget/answers');
	}
	
	
	
	
	
	
	
	
	
	
	
	
	

}
