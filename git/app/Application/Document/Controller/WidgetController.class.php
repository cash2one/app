<?php

namespace Document\Controller;
/**
 * 前台工具控制器
 * 主要获取特殊页面和小窗口页面
 */
class WidgetController extends BaseController{

    //调用层
    public function index(){
        //指定widget控制器名
        $theme = I('theme');
        //指定方法
        $method = I('method');
        //调用
        W($theme .'/' . $method);
    }
}