<?php

namespace Home\Widget;

use Home\Controller\BaseController;

class GfwmobileWidget extends BaseController{

	/**
	 * 手机版首页
	 * @date: 2015-5-30
	 * @author: liujun
	 */
	public function index(){
		$seo = I('seo');
		$this->assign("SEO",WidgetSEO(array('special',null,$seo)));
		$this->display(T('Home@gfwmobile/Widget/index'));
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
                $id = I('cate');
                if(!empty($id)){
                    $res = D('down_category')->where('id='.$id)->find();
                    $seo['title'] = $res['meta_title'];
                    $seo['keywords'] = $res['keywords'];
                    $seo['description'] = $res['description'];
                }else{
                    $seo['title'] = '产品大全 - 官方网';
                    $seo['keywords'] = '产品大全 - 官方网';
                    $seo['description'] = '产品大全 - 官方网';
                }
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
                    'DOWN' => 'DownCategory',
                    'FEATURE'=>'FeatureCategory'
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
	* 根据分类Id获取分类信息
	* @date: 2015-5-27
	* @author: liujun
	*/
	public function getWebSiteParentCateById($idArr = array(5,1,2,3,6,4)){
		$where = array('status'=>1);
		$order = '';
		if(!empty($idArr)){
			$ids = join(',', $idArr);
			$where['id'] = array('in',$ids);
			$order = 'FIELD(id,'.$ids.')';
		}
		$cateList = M('PackageCategory')->field(array('id','title'))->where($where)->order($order)->select();
		$this->assign('cateList',$cateList);
		$this->display(T('Home@gfwmobile/Widget/website_cate'));
	}

    /**
     * 专题合集
     */
    public function zthj(){
        //导航
        $nav = explode('_',I('nav'));
        $sid = I('sid');

        //专题分类
        $lists = M('FeatureCategory')->field('id,title,icon')->where('pid = 0')->select();

        foreach($nav as $k=>$v){
            foreach($lists as $k1=>$v1){
                if($v == $v1['id']){
                    $navList[]=$v1;
                    break;
                }
            }
        }

        foreach($lists as $k=>$v){
            $count = M('Feature')->where('interface = 1 AND category_rootid = '.$v['id'])->count();
            if($count<1){
                unset($lists[$k]);
            }
        }

        //推荐专题
        //$tj = M('Feature')->field('id,title,url_token,icon')->where('interface = 1 AND position & 2')->limit('5')->order('update_time DESC')->select();

        //热门专题
        $rm = M('Feature')->field('id,title,url_token')->where('interface = 1 AND position & 1')->limit('5')->order('update_time DESC')->select();

        $this->assign(array(
            'nav'=>$navList,
            'lists'=>$lists,
            //'tj'=>$tj,
            'rm'=>$rm,
        ));
        $this->assign('SEO',WidgetSEO(array('special',null,$sid)));
        $this->display('Widget/zthj');
    }

    //专题随机推荐 @Author 肖书成
    public function zttj($cate){
        //推荐专题
        $lists = M('Feature')->field('id,title,url_token')->where(rand_where('Feature',5,'interface = 1'.((int)$cate>0?' AND category_rootid = '.$cate:'')))->limit(5)->select();
        $this->assign('lists',$lists);
        $this->display(T('Home@gfwmobile/Widget/zttj'));
    }
}