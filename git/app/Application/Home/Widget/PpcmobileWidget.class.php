<?php

namespace Home\Widget;
use Think\Controller;

/**
 * 页面widget
 * 
 */

class PpcmobileWidget extends Controller{
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
                $seo['title'] =empty($cate['title'])?$cate['title']:$cate['title'];
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
                $seo['title'] =empty($cate['meta_title'])?$cate['title']:$cate['meta_title'];
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
                if($module=='Package'){
                    //礼包的规则 
                    //1、seotitle
                    //2、礼包名字_礼包名字领取_PC6礼包中心
                    if(!empty($detail['seo_title'])){
                        $title = $detail['seo_title']; 
                    }else{
                        $title = $detail['title'] . '_' . $detail['title'] .'领取_PC6礼包中心'; 
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
                    $t = array();                
                    $t[abs((int)C('SEO_PRE_SUF') - 1)] = $title; 
                    $t[(int)C('SEO_PRE_SUF')] = C('SEO_STRING');
                    ksort($t);
                    $seo['title'] = implode(' - ', $t);                      
                }else{
                    $seo['title'] = $title; 
                }

                //关键词
                if(empty($detail['seo_keywords'])){
                    //其他 主标题
                    $seo['keywords'] = $detail['title'];
                }else{
                    $seo['keywords'] = $detail['seo_keywords'];
                }

                //描述
                if(empty($detail['seo_description'])){
                    if ($module=='Package') {
                        // 礼包
                        $seo['description'] = "{$detail['title']}的领取,{$detail['title']}使用说明,{$detail['title']}使用说明注册激活的帮助由跑跑车礼包中心免费为您提供";
                    }else{
                        // 其他
                        $seo['description'] = substr(str_replace('"', '', str_replace(PHP_EOL, '', strip_tags($detail['content']))),0,500);
                    }
                }else{
                    $seo['description'] = strip_tags($detail['seo_description']);
                }

                return $seo;
                break;
        }
    }

}

