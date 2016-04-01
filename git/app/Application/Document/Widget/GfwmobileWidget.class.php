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

class GfwmobileWidget extends Controller{


    /**
     * 方法不存在时调用
     *@return void
     */
    public function __call($method,$args) {
        //斜杠会被解析，所以用下划线代替
        $method = str_replace('_', '/', $method);
        $this->display(T($method));
    }
   
    /**
    *    分类下幻灯
    *    Author:liupan
    */
	public function cateSlider($cid,$type){
		if(!is_numeric($type)){
			return;
		}
		$where=$type=='1' ? "status=1 &&position & 2" : "status=1 AND category_id=$cid &&position & 2";
		$result = M("Document")->alias("__DOCUMENT")->where($where)->order("update_time desc")->join("INNER JOIN __DOCUMENT_ARTICLE__ ON __DOCUMENT.id = __DOCUMENT_ARTICLE__.id")->limit("3")->field("*")->select();
		$this->assign("lists",$result);
		$this->display(T('Document@gfwmobile/Widget/cateSlider'));
		
		
	}
	/**
    *    手机百科内容自动处理
    *    Author:liupan
    */
	public function handleContent($content){  
	  $arr=array(
	     "[div class=\"content-title\"]"=>"</div></div><div class=\"Introd\"><div class=\"title\">",
		 "[/div]"=>"</div><div class=\"baike-news-main\">",
		 "[span]"=>"<h2>",
		 "[/span]"=>"</h2>",
		 "[i]"=>"",
	     "[/i]"=>"",
	   );
	    foreach($arr as $val=>$k){
		   $content=str_replace($val,$k,$content);
	    }
		$content="<section class=\"baike-news\">".$content;
		$content=str_replace("<section class=\"baike-news\"><p>
	</div></div>","<section class=\"baike-news\"><p>",$content);
	   echo $content;
	}
	
	
    public function categoryPage($row,$count,$cate)
    {

        $count = $count ? $count : 0;
        $row = $row ? $row : 10;
        $cate = $cate ? $cate : 1;
        $path = staticUrlMobile('lists',$cate,'Document',2);
        $Page       = new \Think\Page($count, $row, '', $cate,$path);// 实例化分页类 传入总记录数和每页显示的记录数 和静态分类id
        $Page->setConfig('prev',"上一页");
        $Page->setConfig('next','下一页');
        $Page->setConfig('theme',' %UP_PAGE% %DOWN_PAGE%');
        $show   = $Page->show();// 分页显示输出
        $this->assign('page',$show);// 赋值分页输出
        $this->display(T('Document@qbaobeimobile/Widget/catepage'));
    }
  /**
     * SEO
     * @param  string $type 类型
     * @param  string $module 模块名
     * @param  integer $cid or $id
     *@return array
     */
    public function SEO($type, $module = null, $id = null){
		
		var_dump($type);
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
    			$seo['title'] =$title." - ".C('SITE_NAME');
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
    			$seo['title'] =$title." - ".C('SITE_NAME');
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
    					$title = $detail['sub_title'] .'|'. $detail['title'] . '下载' . $detail['version'];
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
    					$seo['title'] = $title . ' - ' . C('PACKAGE_SEO_LAST');
    				}else{
    					$t = array();
    					$t[abs((int)C('SEO_PRE_SUF') - 1)] = $title;
    					$t[(int)C('SEO_PRE_SUF')] = C('SEO_STRING');
    					ksort($t);
    					$seo['title'] = implode(' - ', $t);
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
    				if(empty($detail[$des[$module]])){
    					$seo['description'] = substr(str_replace('"', '', str_replace(PHP_EOL, '', strip_tags($detail['content']))),0,500);
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






}
