<?php
// +----------------------------------------------------------------------
// | 基础控制类
// +----------------------------------------------------------------------
// | Author: crohn <lllliuliu@crohn.com>
// +----------------------------------------------------------------------

namespace Package\Controller;

/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class IndexController extends BaseController {

	//系统首页
    public function index(){

        //SEO
        $this->assign("SEO",WidgetSEO(array('moduleindex','Package')));
        $this->assign('class','package');
        $this->display();
    }

}