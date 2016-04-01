<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Administrator
 * Date: 14-11-11
 * Time: 下午5:52
 * To change this template use File | Settings | File Templates.
 */

namespace Down\Controller;

class DetailController extends BaseController
{
    public function index()
    {
        $id = I('id');
        if (!is_numeric($id)) {
            $this->error('页面不存在！');
        }

        //数据查询
        $info = D('Down')->downAll($id);
        if (!$info) {
            $this->error('页面不存在！');
        }

        /**
         * 修改详情页分页逻辑到前台，让动态访问也可分页
         *
         * 第一次请求获取分页
         *
         * Author:liuliu
         * Time:2015-11-4
         */
        if (I('get.gettotal') == 'true' && !empty($info['content'])) {
            preg_match_all('/\[page\](.*?)\[\/page\]/', $info['content'], $match);
            $total = count($match[0]);
            if ($total > 2) {
                echo $total - 1;
                exit();
            }
        }
        // 标签支持变量 zhudesheng 2015-9-16
        $_GET['MODELID'] = $info['model_id'];

        // 点击量自增 zhudesheng 2015-9-17
        $info['view_sc'] = "<font id='hash-view_sc'></font><script>window.onload = function(){ var _filer_ef = document.createElement('script');
		_filer_ef.setAttribute('src', '" . C('DYNAMIC_SERVER_URL') . "/dynamic.php?s=Base/getClickAmount/modelid/{$info['model_id']}/id/{$info['id']}');document.getElementsByTagName('head')[0].appendChild(_filer_ef); }</script>";

        //插入数据处理
        $ic = new \Common\Library\InsertContentLibrary($info['content']);
        $temp_t = I('get.t');
        if (!empty($temp_t)) {
            $ic->setProperty('path_field', 'mobile_path');
        }
        $info['content'] = $ic->init();

        //SEO
        $this->assign("SEO", WidgetSEO(array('detail', 'Down', $id)));
        $mobile_url = staticUrlMobile('detail', $id, 'Down'); //获取手机版地址
        $this->assign('MOBLIE_URL', $mobile_url);

        $this->assign('qrcode', builtQrcode($info['down']));
        $category = D('DownCategory')->info($info['category_id']);
        $this->assign('cateName', $category['title']);//当前分类名称
        $temp = $category['template_detail']
            ? $category['template_detail']
            : ($category['template_detail'] ? $category['template_detail'] : 'index');
        ###
        //判断是否是软件专题
        if ($temp == 'specialsoft') {
            $down_arr = $info['relation_down'];
            $down_arr = explode(',', $down_arr);
            foreach ($down_arr as $k => $v) {
                $down_arr[$k] = explode('|', $v);
            }
            $res = array();
            foreach ($down_arr as $k => $v) {
                if($v['1'] == '安卓下载'){
                    $res['az'] = $v;
                }elseif($v['1'] == '苹果下载'){
                    $res['pg'] = $v;
                }else{
                    $res['pc'] = $v;
                }
            }
            $this->assign('relation_down',$res);
        }
        ###
        //判断是否是手机版
        $theme = I('t');
        $key = true;
        if (substr($theme, -6) == 'mobile') {
            $key = false;
        }

        //--------文本内容处理------------ By Jeffrey Lau,Date: 2016-3-3 15:00:17
        $ih = new \Common\Library\ContentHandleLibrary($info['content']);
        $ih->setProperty('enlink', $info['enlink']);
        $ih->setProperty('mobile', $key);
        $content_done = $ih->init();
        $info['content'] = $content_done;
        //-------------end---------------

        //选择对于模板
        if (C('DEFAULT_THEME') == 'gfw') {
            $m_id = $info['model_id'];
            if ($m_id == 20) {//实体产品详情
                $this->display('productDetail');
            } else {
                if ($m_id == 13) {//网络产品详情
                    $this->display('softDetail');
                }
            }
        } else {
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
                $page_handle_str = contentHandle($info, $p, $theme, $key ? staticUrl('detail', $id, 'Document') : staticUrlMobile('detail', $id, 'Document'));
                $this->assign('info', $info);
                if ($page_handle_str === false) {
                    $this->display($temp);
                } else {
                    $ec = $this->fetch($temp);
                    $ec = preg_replace('/<title>.*?<\/title>/', '<title>' . $page_handle_str['title'] . '</title>', $ec);
                    echo $ec;
                }
            } else {
                //不分页情况下，开启第一页和第二页，第二页跳转标签关联文章
                /*下载详情页不页情况下永远不开启-开启第一页和第二页，第二页跳转标签关联文章功能
                if(C('CONTENT_ADD_PAGE')) //需要配置开启
                {
                    content_add_page($info,$theme,strtolower(MODULE_NAME));

                }
                */
                // 不分页
                // 不分页
                $this->assign('info', $info);
                $this->display($temp);
            }
        }

    }

}
