<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Widget;
use Common\Controller\WidgetController;
use Think\Controller;

/**
 * 页面widget
 * 
 */

class DongDongWidget extends WidgetController{
	
	
     /**
     * 首页排行部分Widget
    * @param string $rootID   类别根IP
	* @param string $orderCate  排序方式； 1日排行；2周排行；3；月排行
    * @return array
    */
	//首页排行数据
	public function indexRank($rootID,$orderCate){
		if(!is_numeric($rootID)||!is_numeric($orderCate)){
			return;
		}
		 $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
		 $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
		 
		 $beginLastweek=mktime(0,0,0,date('m'),date('d')-date('w')+1-7,date('Y')); 
		 $endLastweek=mktime(23,59,59,date('m'),date('d')-date('w')+7-7,date('Y'));
 
		 $beginThismonth=mktime(0,0,0,date('m'),1,date('Y'));
         $endThismonth=mktime(23,59,59,date('m'),date('t'),date('Y'));
		 
		 $map['category_rootid'] = $rootID;
		 switch($orderCate){
			 case 1:
			  $map['create_time'] = array('gt',$beginToday);
		      $map['create_time'] = array('lt',$endToday);
			 break;
			  case 2:
			   $map['create_time'] = array('gt',$beginLastweek);
		       $map['create_time'] = array('lt',$endLastweek);
			 break;
			  case 3:
			   $map['create_time'] = array('gt',$beginThismonth);
		       $map['create_time'] = array('lt',$endThismonth);
			 break;
			 
			}
		
		 $map['status'] ='1';
		 $down=M('Down')->where($map)->limit("7")->order('hits_today desc')->select();
		 $this->assign("down",$down);
	     $this->display(T('Home@dongdong/Widget/indexRank'));
		
		}


    public function indexCate($rootID){
	if(!is_numeric($rootID)){
			return;
		}
	  $map['status'] ='1';
	  $map['rootid'] = $rootID;
	  $map['pid'] = array('neq','0');
	  $cate=M('DownCategory')->where($map)->limit("8")->order('sort')->select();
	  $this->assign("cate",$cate);
	  $this->display(T('Home@dongdong/Widget/indexCate'));
	}


    public function cateList($rootID){
	if(!is_numeric($rootID)){
			return;
		}
	  $map['status'] ='1';
	  $map['rootid'] = $rootID;
	  $map['pid'] = array('neq','0');
	  $cate=M('DownCategory')->where($map)->limit("8")->order('sort')->select();
	  $this->assign("cate",$cate);
	  $this->display(T('Home@dongdong/Widget/cateList'));
	}

    public function cateCon($cid){
	if(!is_numeric($cid)){return;}
	   $game= M("Down")->alias("__DOWN")->where("status=1 AND category_id='$cid'")->order("update_time desc")->join("INNER JOIN __DOWN_DMAIN__ ON __DOWN.id = __DOWN_DMAIN__.id")->limit("20")->field("*")->select();
      $this->assign("game",$game);
	  $this->display(T('Home@dongdong/Widget/cateCon'));
	}





	function wbreadCrumb($id,$module,$type){
	    if(empty($id)) return;
		$module=ucfirst($module);
		$type=strtolower($type);
		$siteUrl=C('STATIC_URL');
		$tpl="";
		   switch ($type) {
            case 'detail'://详情页面
			if(empty($type)) return;
			$info=M($module)->where("id='$id'")->find();
			$cateAlias=$module=='Document'?'Category':$module.'Category';
			$cid=$info['category_id'];
			$cateName=M($cateAlias)->where("id='$cid'")->find();
			if($module=='Down'){//下载面包屑特殊处理
			    $SURL = C('STATIC_URL');
                $SURL =substr($SURL, -1) == '/' ? substr($SURL, 0, strrpos($SURL,'/')) : $SURL; 
				$cateUrl=$SURL."/game/".$cateName['rootid']."_".$cateName['id']."_0_1_1.html";
			}else{
				$cateUrl= staticUrl('lists',$cateName['id'],$module);
			}
			
			
			$tpl="<a href=\"".$siteUrl."\">首页</a>><a href=\"".$cateUrl."\">".$cateName['title']."</a>><a href=\"".staticUrl('detail',$info['id'],$module)."\">".$info['title']."</a>";
			
			
			
			echo $tpl;
			break;
			
			case 'cate'://栏目页面
			if(empty($type)) return;
			$cateAlias=$module=='Document'?'Category':$module.'Category';
			$cateName=M($cateAlias)->where("id='$id'")->find();
			$tpl="<a href=\"".$siteUrl."\">首页</a>><a href=\"".staticUrl('lists',$cateName['id'],$module)."\">".$cateName['title']."</a>";
			echo $tpl;
			break;
			
			
			
			
			
			
			
			
			
			
			
		   }
	}
      /**
      * SEO
      * @param  string $type 类型
      * @param  string $module 模块名
      * @param  integer $cid or $id
      *@return array
      */
    public function SEO($type, $module = null, $id = null){
        $seo = array();
        switch ($type) {
            case 'index':
                $seo['title'] =C('WEB_SITE_TITLE');
                $seo['keywords'] = C('WEB_SITE_KEYWORD');
                $seo['description'] =C('WEB_SITE_DESCRIPTION');
                return $seo;
                break;
            case 'moduleindex':
                if(empty($module)) return;
                $module = strtoupper($module);
                $seo['title'] =C(''.$module.'_DEFAULT_TITLE');
                $seo['keywords'] = C(''.$module.'_DEFAULT_KEY');
                $seo['description'] =C(''.$module.'_DEFAULT_DESCRIPTION');
                return $seo;
                break;
	       case 'special':
                $id=empty($id)?'1':$id;
	   $cate=D('StaticPage')->where("id='$id'")->find();
                $title=empty($cate['title'])?$cate['title']:$cate['title'];
				$seo['title'] =$title."_".C('SITE_NAME');
                $seo['keywords'] = empty($cate['keywords'])?'':$cate['keywords'];
                $seo['description'] =empty($cate['description'])?'':$cate['description'];
                return $seo;
                break;
            case 'category':
                $id=empty($id)?'1':$id;
                if(empty($module)) return;
                $module = strtoupper($module);
                $cate_name = array(
                    'DOCUMENT' => 'Category',
                    'PACKAGE' => 'PackageCategory',
                    'DOWN' => 'DownCategory'
                );
                $cate=D($cate_name[$module])->where("id='$id'")->find();
                $title =empty($cate['meta_title'])?$cate['title']:$cate['meta_title'];
				$seo['title'] =$title." _ ".C('SITE_NAME');
                $seo['keywords'] = empty($cate['keywords'])?C(''.$module.'_DEFAULT_KEY'):$cate['keywords'];
                $seo['description'] =empty($cate['description'])?C(''.$module.'_DEFAULT_KEY'):$cate['description'];
                return $seo;
                break;
            case 'detail':
                $id=empty($id)?'1':$id;
                if(empty($module)) return;
                $module = ucfirst(strtolower($module));                
                $detail = D($module)->detail($id);

                //标题
                if($module=='Down'){
                    //下载的规则 
                    //1、seotitle+版本号
                    //2、副标题|主标题+下载+版本号
                    if(!empty($detail['seo_title'])){
                        $title = $detail['seo_title'];
                    }else{
						$version=empty($detail['version']) ? "" : " V".$detail['version'];
						$first=$detail['sub_title']==$detail['title'] ? "" : $detail['title']."_"; 
                        $title = $first.$detail['sub_title'] .'电脑版_'.  $detail['sub_title'] . '手游_'.$detail['sub_title']. '下载'; 
                    }
                }else{
                    //其他的规则 
                    //1、seo title
                    //2、主标题
                    if(!empty($detail['seo_title'])){
                        $title = $detail['seo_title']; 
                    }else{
                        $title = $detail['title']; 
                    }
                }
                //标题需要加前后缀
                if(C('SEO_STRING')){
                    if($module == 'Package' && C('PACKAGE_SEO_LAST')){
                        $seo['title'] = $title . '_' . C('PACKAGE_SEO_LAST');
                    }else{
                        $t = array();
                        $t[abs((int)C('SEO_PRE_SUF') - 1)] = $title;
                        $t[(int)C('SEO_PRE_SUF')] = C('SEO_STRING');
                        ksort($t);
                        $seo['title'] = implode('_', $t);
                    }
                }else{
                    $seo['title'] = $title; 
                }

                //关键词
                if(empty($detail['seo_keywords'])){
                    if($module=='Document'){
                        //文章 标签
                        $tag_ids = M('TagsMap')->where('did='.$id.' AND type="document"')->getField('tid', true);
                        if(empty($tag_ids)){
                            $seo['keywords'] = $detail['title']; 
                        }else{
                            $tags = M('Tags')->where(array('id'=>array('in', $tag_ids)))->getField('title', true);
                            $seo['keywords'] = empty($tags) ? $detail['title'] : implode(',', $tags);                            
                        }
                    }else{
                        //其他 主标题+副标题
                        $seo['keywords'] = empty($detail['sub_title']) 
                                                        ? $detail['title'] 
                                                        : $detail['title'] . ',' . $detail['sub_title'];
                    }
                }else{
                    $seo['keywords'] = $detail['seo_keywords'];
                }

                //描述分模块处理
                if(empty($detail['seo_description'])){
                    $des = array('Document'=>'description','Down'=>'conductor','Package'=>'content');
                    if($module=='Down'){
						$daodu=empty($detail['conductor']) ? "" : ",".$detail['conductor'];
                        $seo['description'] = $detail['sub_title']."提供".$detail['sub_title']."电脑版,".$detail['sub_title']."手游,".$detail['sub_title']."下载".$daodu;
                    }else{
                        $seo['description'] = str_replace('"', '', str_replace(PHP_EOL, '', strip_tags($detail[$des[$module]])));
                    }
                }else{
                    $seo['description'] = strip_tags($detail['seo_description']);
                }

                return $seo;
                break;
        }
    }


    /**
    * content字段图片和内置插入标签处理
    * @param string $content 内容
    * @return string 处理过后的内容
    */
    function contentProcess($content){
        //图片暂时兼容处理
      $content = preg_replace('/src\=\"(\/up\/.+?)/i', 'src="'. C('PIC_HOST') .'$1', $content);
      $content = preg_replace('/src\=\"(up\/.+?)/i', 'src="'. C('PIC_HOST') .'/$1', $content);
      $content = preg_replace('/src\=\"(\/Uploads\/.+?)/i', 'src="'. C('PIC_HOST') .'$1', $content);
      $content = preg_replace('/src\=\"(Uploads\/.+?)/i', 'src="'. C('PIC_HOST') .'/$1', $content);
      $content = preg_replace('/src\=\"http:\/\/(www.)??7230.com\/(up\/.+?)/i', 'src="'. C('PIC_HOST') .'/$2', $content);
        //内置标签处理
      echo $content;
    }


    /**
     * 作者:肖书成
     * 描述:东东助手协议
     * 时间:2016-1-15
     */
    public function agreement(){
        $page_id    =   (int)I('page_id');

        if($page_id > 0){

            $info   =   M('StaticPage')->where('id = '.$page_id)->find();

            $this->assign('info',$info);
            $this->display('Widget/agreement');
        }
    }
 
}

