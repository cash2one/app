<?php
// +----------------------------------------------------------------------
// | 基础控制类
// +----------------------------------------------------------------------
// | Author: crohn <lllliuliu@crohn.com>
// +----------------------------------------------------------------------

namespace Package\Controller;
use OT\DataDictionary;

/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class DetailController extends BaseController {

    //系统首页
    public function index(){
        $id = I('id');
        if (!is_numeric($id)) $this->error('页面不存在！');

        //数据查询
        $info = D('Package')->detail($id);
        if (!$info) $this->error('页面不存在！');
        /**
         * 修改详情页分页逻辑到前台，让动态访问也可分页
         *
         * 第一次请求获取分页
         * 
         * Author:liuliu 
         * Time:2015-11-4
         */
        if (I('get.gettotal')=='true' && !empty($info['content'])) {
            preg_match_all('/\[page\](.*?)\[\/page\]/',$info['content'],$match);
            $total = count($match[0]);
            if ($total>2) {
                echo $total-1;
                exit(); 
            }
        }
        
		// 标签支持变量 zhudesheng 2015-9-16
		$_GET['MODELID'] = $info['model_id'];

		// 点击量自增 zhudesheng 2015-9-17
		$info['view_sc'] = "<font id='hash-view_sc'></font><script>window.onload = function(){ var _filer_ef = document.createElement('script');
		_filer_ef.setAttribute('src', '".C('DYNAMIC_SERVER_URL')."/dynamic.php?s=Base/getClickAmount/modelid/{$info['model_id']}/id/{$info['id']}');document.getElementsByTagName('head')[0].appendChild(_filer_ef); }</script>";

        //插入数据处理
        $ic = new \Common\Library\InsertContentLibrary($info['content']);
        $temp_t = I('get.t');
        if(!empty($temp_t)) $ic->setProperty('path_field', 'mobile_path');
        $info['content'] = $ic->init();

        $this->assign('info', $info);
        //卡号数目查询
        $card_num['sur'] = showCardSur($info['id']);
        $card_num['all'] = showCardAll($info['id']);
        $this->assign('card_num', $card_num);
        //已领取卡号查询
        $card_draw = D('Card')->where('did='. $info['id'] . ' AND draw_status=1 AND status=1 ')->limit(10)->select();
        $this->assign('card_draw', $card_draw);
		$doc= get_base_by_tag($id,'Package','Document','product',true);
	    $downLink= get_base_by_tag($id,'Package','Down','product',true);
   
		$this->assign('doc_link',staticUrlMobile('detail',$doc['id'],'Document'));
		$this->assign('batch_link',getRelationZone($id));
		$this->assign('down_link', getFileUrl($downLink['id']));
        //SEO
        $this->assign("SEO",WidgetSEO(array('detail','Package', $id)));
        $category = D('PackageCategory')->info($info['category_id']);
        $temp = $category['template_detail']
            ? $category['template_detail']
            : ($category['template_detail'] ? $category['template_detail'] : 'index');
        //判断是否是手机版
        $theme = I('t');
        $key = true;
        if(substr($theme,-6) == 'mobile') $key = false;
        //处理图片 ADD BY TANJIAN 2016.1.20
        $info['content'] = str_replace('src="Uploads','src="/Uploads',$info['content']);
        $info['content'] = str_replace('src="/Uploads','src="'.C('PIC_HOST').'/Uploads',$info['content']);
        $info['content'] = str_replace('src="'.C('STATIC_URL'),'src="'.C('PIC_HOST'),$info['content']); //本域名地址换成图片域名地址
        $info['content'] = str_replace('src="'.C('MOBILE_STATIC_URL'),'src="'.C('PIC_HOST'),$info['content']);
        if(C('OPEN_THUMB')) //判断是否开启图片缩略功能
        {
            preg_match_all('/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.png|\.GIF|\.JPG|\.JPEG|\.PNG|\.jpeg]))[\'|\"].*?[\/]?>/',$info['content'],$match);
            if(!$key && (C('MOBILE_THUMB_WIDTH') || C('MOBILE_THUMB_HEIGHT'))) //判断是否配置手机缩略
            {
                $width = C('MOBILE_THUMB_WIDTH') ?:0;
                $height = C('MOBILE_THUMB_HEIGHT') ?: 0;
                if($match[1]){
                    $search = $replace = array();

                    foreach($match[1] as $img){
                        $filename = substr($img, 0, strrpos($img, '.'));

                        $search[]  = $img;

                        if(C('OPEN_WATER')){ //判断是否打水印
                            $replace[] = $filename.'_'.$width.'_'.$height.'_water'.strrchr($img, '.');
                        }else{
                            $replace[] = $filename.'_'.$width.'_'.$height.strrchr($img, '.');
                        }
                    }
                    $info['content'] = str_replace($search, $replace, $info['content']);
                }
            }
            else if($key &&  (C('THUMB_WIDTH') || C('THUMB_HEIGHT'))) //判断是否开启pc版缩略
            {
                $width = C('THUMB_WIDTH') ?:0;
                $height = C('THUMB_HEIGHT') ?: 0;
                if($match[1]){
                    $search = $replace = array();

                    foreach($match[1] as $img){
                        $filename = substr($img, 0, strrpos($img, '.'));

                        $search[]  = $img;

                        if(C('OPEN_WATER')){ //判断是否打水印
                            $replace[] = $filename.'_'.$width.'_'.$height.'_water'.strrchr($img, '.');
                        }else{
                            $replace[] = $filename.'_'.$width.'_'.$height.strrchr($img, '.');
                        }
                    }
                    $info['content'] = str_replace($search, $replace, $info['content']);
                }
            }

        }
        /**
         * 修改详情页分页逻辑到前台，让动态访问也可分页
         *
         * 分页内容处理
         * 
         * Author:liuliu 
         * Time:2015-11-4
         */
        if (is_numeric($p = I('get.p')) && !empty($info['content'])) {
            // 分页
            $page_handle_str = contentHandle(
                $info,
                $p,
                $theme, 
                $key ? staticUrl('detail',$id,'Document') : staticUrlMobile('detail',$id,'Document')
                );  
            $this->assign('info', $info);
            if ($page_handle_str===False) {
                $this->display($temp);
            } else {
                $ec = $this->fetch($temp);
                $ec = preg_replace('/<title>.*?<\/title>/', '<title>'. $page_handle_str['title'] . '</title>', $ec);
                echo $ec;
            }
        } else {
            //不分页情况下，开启第一页和第二页，第二页跳转标签关联文章
            /*礼包内容页在不分页情况下永远不开启不开启--开启第一页和第二页，第二页跳转标签关联文章功能
            if(C('CONTENT_ADD_PAGE')) //需要配置开启
            {
                content_add_page($info,$theme,strtolower(MODULE_NAME));

            }
            */
            // 不分页
            $this->assign('info', $info);
            $this->display($temp);
        }
    }

}