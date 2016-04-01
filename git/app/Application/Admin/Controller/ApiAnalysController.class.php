<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Admin\Controller;

/**
 * 后台用户控制器
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class ApiAnalysController extends AdminController
{

    private $apiUrl = 'http://dc.20hn.cn/';

    /**
     * 接口采集内容获取
     * @return json
     */
    public function get()
    {
        $siteid = I('get.siteid');
        $model  = I('get.model');
        $url    = I('get.url');

        if (!$siteid || !$model || !$url) {
            resultFormat(0, '残缺的接口参数');
        }

        // 检查站点ID是否一致
        if ($siteid != C('APP_ID')) {
            resultFormat(0, '非法请求');
        }
        
        $apiUrl = $this->apiUrl . 'v'. C('API_VERSION') . '/data/get.json';

        $vars = [
            'token'     =>  token(),
            'siteid'    =>  $siteid,
            'model'     =>  $model,
            'url'       =>  $url,
        ];

        $apiUrl .= '?'.http_build_query($vars);
        echo getHttpResponseGET($apiUrl);
    }

    /**
     * 关键词标题列表
     * @return json
     */
    public function keywords()
    {
        $siteid    = I('get.siteid');
        $keywords  = I('get.keywords');
        $page      = I('get.page');
        $form      = I('get.form');

        if (!$keywords || !$page) {
            resultFormat(0, '残缺的接口参数');
        }

        // 检查站点ID是否一致
        if ($siteid != C('APP_ID')) {
            resultFormat(0, '非法请求');
        }

        $apiUrl = $this->apiUrl . 'v'. C('API_VERSION') . '/data/lists.json';

        $vars = [
            'token'     =>  token(),
            'siteid'    =>  $siteid,
            'keywords'  =>  urlencode($keywords),
            'page'      =>  $page,
            'form'      =>  $form,
        ];

        $apiUrl .= '?'.http_build_query($vars);
        echo getHttpResponseGET($apiUrl);
    }
}
