<?php

namespace Down\Controller;
/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class IndexController extends BaseController{

    //系统首页
    public function index(){
        //SEO
        if(C('DEFAULT_THEME') == 'gfw'){
            $this->assign("SEO",WidgetSEO(array('product')));
        }else{
            $this->assign("SEO",WidgetSEO(array('index')));
        }
        $this->display();
    }
}