<?php

namespace Home\Widget;

use Home\Controller\BaseController;

class GfwWidget extends BaseController{

    public function featureCate(){
        $cate = M('FeatureCategory')->field('id,title')->where('pid = 0')->select();

        foreach($cate as $k=>&$v){
            $v['lists'] = M('Feature')->field('id,title,url_token,icon')->where('interface = 0 AND category_rootid = '.$v['id'])->order('id DESC')->limit('6')->select();
            if(empty($v['lists'])) unset($cate[$k]);

        }unset($v);//防止出意外……

        $this->assign(array(
            'cate'=>$cate,
            'SEO' =>WidgetSEO(array('special',null,'66')),
        ));


        $this->display(T('Home@gfw/Widget/featureCate'));
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
            case 'product':
                $seo['title'] = '产品大全'.'_'.C('SITE_NAME');
                $seo['keywords'] = '产品大全'.'_'.C('SITE_NAME');
                $seo['description'] = '产品大全'.'_'.C('SITE_NAME');
                return $seo;
                break;
    		case 'special':
    			$id=empty($id)?'1':$id;
    			$cate=D('StaticPage')->where("id='$id'")->find();
    			$title=empty($cate['title'])?$cate['title']:$cate['title'];
    			$seo['title'] =$title.'_'.C('SITE_NAME');
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
    					'DOWN' => 'DownCategory',
                        'FEATURE'=>'FeatureCategory'
    			);
    			$cate=D($cate_name[$module])->where("id='$id'")->find();
    			$title =empty($cate['meta_title'])?$cate['title']:$cate['meta_title'];
    			$seo['title'] =$title.'_'.C('SITE_NAME');
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

    			//检索
    			if(!strstr($seo['title'],C('SITE_NAME'))){
    				$seo['title'] = $seo['title'].'_'.C('SITE_NAME');
    			}
    			
                if(empty($detail['seo_keywords']) && !empty($detail['seo_key'])) $detail['seo_keywords'] = $detail['seo_key'];

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


    /**
     * @Author      肖书成
     * @comments    专题的推荐位
     */
    public function featurePosition($position = false){
        $position = $position?$position:I('position');
        if((int)$position<0) return false;
        $class = array(1=>1,2=>3);
        $title = array(1=>'热门',2=>'推荐');
        //推荐专题
        $lists = M('Feature')->field('id,title,url_token,icon')->where('interface = 0 AND position & '.$position)->order('update_time DESC')->limit(4)->select();
        $this->assign(array(
            'lists'=>$lists,
            'class'=>$class[$position],
            'title'=>$title[$position],
        ));
        $this->display(T('Home@gfw/Widget/hotFeature'));
    }

    /**
     * @Author      肖书成
     * @comments    相关专题
     */
    public function otherFeature($cate = false){
        $lists = M('Feature')->field('title,url_token,icon')->where(rand_where('feature',3,'interface = 0 AND '.($cate?'category_rootid = '.$cate:1)))->limit(4)->select();
        $this->assign('lists',$lists);
        $this->display(T('Home@gfw/Widget/otherFeature'));
    }

    /**
     * 产品分类数
     * @param 分类id  int $id
     */
    public function tree($id=0,$limit=5,$model = 'DownCategory'){
    	if(!in_array($model,array('DownCategory','PackageCategory'))){
    		return false;
    	}
    	if($model == 'PackageCategory'){
    		$m = 'Package';
    	}else{
    		$m = 'Down';
    	}
    	
    	$tree = array();
    	if($id > 0){
    		$tree = D($model)->getTree($id);//获取分类树
    	}else{
    		$parentList = D($model)->field('id,title')->where(array('status'=>1,'pid'=>0))->order('sort')->limit($limit)->select();
    		foreach($parentList as $key=>$value){
    			$id = $value['id'];
    			$tree[] = D($model)->getTree($id);//获取分类树
    		}
    	}
        //统计分类下的产品数量
        if($tree){
        	foreach($tree as $key=>$value){
        		$childIds = $this->getChildIds($value['id'],$model);//获取子分类id
        		$count = M($m)->where(array('status'=>1,'category_id'=>array('in',$childIds)))->count();//总记录数
        		$tree[$key]['listCount'] = $count;
        	}
        }
        $this->assign('tree', $tree);
        $this->assign('model', $m);
        $this->display(T('Home@gfw/Widget/tree'));
    }

    /**
     * 产品模块导航
     */
    public function productNav(){
        $id  = I('cate');//当前分类id
        $dcate = getParentCategory($id, 'DownCategory');
        $sid = array_shift($dcate);
        $root_cate = D('DownCategory')->field('id,title')->where('pid=0 and status=1')->order('sort')->limit(5)->select();//获取分类树
        $this->assign('root_cate', $root_cate);
        $this->assign('id', $id);
        $this->assign('sid', $sid['id']);//判断选中分类
        $this->display(T('Home@gfw/Widget/productNav'));
    }
    
    /**
     * 首页产品分类
     * @date: 2015-5-18
     * @author: liujun
     * @param int $number 编号
     */
    public function productCategory($number = 1,$category_id = 0){
	    $cateInfo = M('DownCategory')->field(array('id','name','title','vertical_pic'))->where(array('status'=>1,'id'=>$category_id))->find();
	    $cateTree = array();
	    if($cateInfo){
	    	$cateTree = D("DownCategory")->getTree($category_id);
	    }
    	$result = array(
    		'number' => $number,
    		'cateInfo' => $cateInfo,
    		'cateTree' => $cateTree,
    	);
    	$this->assign($result);
        $this->display(T('Home@gfw/Widget/product_category'));
    	
    }
    
    /**
    * 首页产品推荐
    * @date: 2015-5-18
    * @author: liujun
    */
    public function product($position,$limit = 5){
    	$field = array('d.id','d.title','d.previewimg','p.price','p.market_price');
    	$order = 'update_time DESC';
    	$where = array('status'=>1);
    	$where['_string'] = "position & ".$position;
    	$productList = M('Down')->alias('d')->join('left join '.C(DB_PREFIX).'down_product as p ON d.id = p.id')->field($field)->where($where)->order($order)->limit($limit)->select();
    	$result = array(
    		'type' => 1,//自定义字段
    		'productList' => $productList,
    	);
    	$this->assign($result);
    	$this->display(T('Home@gfw/Widget/product'));
    }
    
    /**
    * 首页产品广告位图片
    * @date: 2015-5-18
    * @author: liujun
    */
    public function productAd($position = 1,$limit = 6){
    	$field = array('id','title','previewimg');
    	$where = array('status'=>1);
    	$where['_string'] = "home_position & ".$position;
    	$productList = M('Down')->field($field)->where($where)->limit($limit)->select();
    	$this->assign('productList',$productList);
    	$this->display('Widget/product_ad');
    }
    
    /**
     * 网站右侧会员模块
     * @date: 2015-5-20
     * @author: liujun
     * @param int $type 调用模块类型定义
     * @param int $website_cid 网站分类id
     * @param int $product_cid 产品分类id
     * @param int $limit 数据条数
     */
    public function rightUser($type = 'website',$website_cid = 0,$product_cid = 0,$limit = 5){
    	$result = array('type'=>$type,'websiteList'=>array(),'productList'=>array());
    	if($type == 'all'){
    		$websiteList = $this->getWebSiteListByCateId($website_cid,4,$limit);//$position = 4 最新会员发布
    		$productList = $this->getProductListByCateId($product_cid,4,$limit);//$position = 4 最新会员发布
    		$result['websiteList'] = $websiteList;
    		$result['productList'] = $productList;
    	}else if($type == 'website'){
	    	$websiteList = $this->getWebSiteListByCateId($website_cid,4,$limit);//$position = 4 最新会员发布
	    	$result['websiteList'] = $websiteList;
    	}else if($type == 'product'){
    		$productList = $this->getProductListByCateId($product_cid,4,$limit);//$position = 4 最新会员发布
    		$result['productList'] = $productList;
    	}
    	$this->assign($result);
    	$this->display(T('Home@gfw/Widget/right_user'));
    }
    
    /**
    * 获取产品分类所有子id
    * @date: 2015-5-18
    * @author: liujun
    */
    private function getChildIds($pid = 0,$model = 'DownCategory',&$lists = array()){
    	$field = array('id,title,pid');
    	$cateList = M($model)->field($field)->where(array('status'=>1,'pid'=>$pid))->select();
    	foreach($cateList as $key=>$value){
    		$lists[] = $value;
    		$this->getChildIds($value['id'],$model,$lists);
    	}
    	$childIds = array_column($lists, 'id');
    	$childIds = array_merge(array($pid),$childIds);
    	return $childIds;
    }
    
    /**
    * 根据分类Id获取网站记录
    * @date: 2015-5-20
    * @author: liujun
    */
    private function getWebSiteListByCateId($category_id = 0,$position = 4,$limit = 5){
    	$field = array('id','title','category_id','position','update_time');
    	$where = array('status'=>1);
    	if(intval($category_id) > 0){
    		$childIds = $this->getChildIds($category_id,'PackageCategory');//获取子分类id
    		$where['category_id'] = array('in',$childIds);
    	}
    	$where['_string'] = "position & ".$position;
    	$websiteList = M('Package')->field($field)->where($where)->order('update_time DESC')->limit($limit)->select();
    	return $websiteList;
    }
    
    /**
     * 根据分类Id获取产品记录
     * @date: 2015-5-20
     * @author: liujun
     */
    private function getProductListByCateId($category_id = 0,$position = 4,$limit = 5){
    	$field = array('id','title','category_id','position','update_time');
    	$where = array('status'=>1);
    	if(intval($category_id) > 0){
    		$childIds = $this->getChildIds($category_id,'DownCategory');//获取子分类id
    		$where['category_id'] = array('in',$childIds);
    	}
    	$where['_string'] = "position & ".$position;
    	$productList = M('Down')->field($field)->where($where)->order('update_time DESC')->limit($limit)->select();
    	return $productList;
    }

    /**
     * @Author      肖书成
     * @comments    关于我们、免责声明、联系我们
     */
    public function gywm(){
        $sid    = I('sid');
        $tm     = I('tm');
        if(!is_numeric($sid)||!in_array($tm,array('gywm','mzsm','lxwm'))) return false;
        $this->assign('info',M('StaticPage')->where('id = '.$sid)->getField('custom_content'));
        $this->assign('SEO',WidgetSEO(array('special',null,$sid)));
        $this->assign('tm',$tm);
        $this->display('Widget/'.$tm);
    }

    /**
     * @Author      肖书成
     * @comments    关于我们、免责声明 的导航
     */
    public function navgywm($tm){
        $this->assign('tm',$tm);
        $this->display('Widget/navgywm');
    }

    /**
     * @Author      肖书成
     * @comments    网站地图
     */
    public function wzdt(){
        //网站大全
        $wzdq   = $this->getCate('PackageCategory');
        $wzdq   = $this->wzdtSort($wzdq);
        //产品信息
        $cpxx   = $this->getCate('DownCategory');

        //官网百科
        $gwbk   = $this->getCate('Category');
        $gwbk   = $this->wzdtSort($gwbk);

        //专题集合
        $zthj = M('FeatureCategory')->field('id,title')->where('pid = 0')->select();

        foreach($zthj as $k=>$v){
            $count = M('Feature')->where('category_rootid = '.$v['id'])->count();
            if($count<1) unset($zthj[$k]);
        }

        $this->assign(array(
            'wzdq'  =>  $wzdq,
            'cpxx'  =>  $cpxx,
            'gwbk'  =>  $gwbk,
            'zthj'  =>  $zthj,
            'SEO'   =>WidgetSEO(array('special',null,72))
        ));

        $this->display('Widget/wzdt');
    }

    //此方法仅供 wzdt 方法调用
    private function wzdtSort($list){
        if(empty($list)) return $list;
        foreach($list as $k=>$v){
            if(count($v['_'])>22){
                $v['sort'] = 2;
                $max[] = $v;
            }else{
                $v['sort'] = 1;
                $min[] = $v;
            }
        }
        return array_filter(array_merge((array)$min,(array)$max));
    }

    //此方法仅供 wzdt 方法调用，
    private function getCate($table){
        return list_to_tree(M($table)->field('id,title,pid')->where('status = 1 AND depth IN(1,2)')->order('id DESC')->select(),'id','pid','_',0);
    }

    /**
     * @Author      肖书成
     * @comments    帮助中心
     */
    public function bzzx(){
        $sid    = I('sid');
        if(!is_numeric($sid)) return false;

        $tree = M('StaticPage')->field('id,name,keywords,title,description,custom_content')->where("`group` = '帮助中心'")->select();
        foreach($tree as $k=>$v){
            if($v['id'] == $sid){
                $info   = $v;
                $SEO['title']    = $v['title'];
                $SEO['keywords'] = $v['keywords'];
                $SEO['description']    = $v['description'];
                break;
            }
        }
        $this->assign('tree',$tree);
        $this->assign('info',$info);
        $this->assign('SEO',$SEO);
        $this->display('Widget/bzzx');
    }

    public function ztNav(){
        $navArr = explode('_',I('nav'));
        $nav = implode(',',$navArr);

        $lists = M('FeatureCategory')->field('id,title')->where("id IN($nav)")->select();

        $navList = array();

        foreach($navArr as $k1=>$v1){
            foreach($lists as $k=>$v){
                if($v1 == $v['id']){
                    $navList[] = $v;
                    continue;
                }
            }
        }

        $this->assign('lists',$navList);
        $this->display('Widget/ztNav');
    }
}