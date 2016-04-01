<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Controller;

use OT\DataDictionary;

/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class IndexController extends BaseController
{

    //系统首页
    public function index()
    {
        $this->assign("SEO", WidgetSEO(array('index')));
        $this->assign("system", get_device_type());//手机系统判断
        $this->assign('class', 'index');
        $this->display();
    }

    /**
     * 描述：html内容特殊单页面生成
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function create()
    {
        $id = I('page_id');
        if (is_numeric($id)) {
            $w['id'] = $id;
            $rs = M('StaticPage')->where($w)->field('custom_content')->find();
            if (!empty($rs)) {
                $this->show($rs['custom_content']);
            }
        }
    }
}
