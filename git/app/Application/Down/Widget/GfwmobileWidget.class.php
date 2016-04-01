<?php
/**
 * 手机下载模块widget
 * gfw主题
 **/

namespace Down\Widget;

use Down\Controller\BaseController;

class GfwmobileWidget extends BaseController{

    /**
     * 产品导航
     */
    public function nav(){
        $root_cate = D('DownCategory')->field('id,title')->where('pid=0 and status=1')->order('sort')->limit(5)->select();//获取分类树
        $this->assign('root_cate', $root_cate);
        $this->display('Widget/nav');
    }

    /**
     * 产品分类
     */
    public function cateList(){
        $cate = I('cate');
        $DownCategory = D('DownCategory');
        $tree = $DownCategory->getTree($cate);//获取热门推荐分类
        $this->assign('tree', $tree);
        $this->display('Widget/cateList');
    }
    /**
     * 描述：获取分页地址
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function categoryPage($row,$count,$cate)
    {

        $count = $count ? $count : 0;
        $row = $row ? $row : 10;
        $cate = $cate ? $cate : 1;
        $path = staticUrlMobile('lists',$cate,'Down',2);
        $Page       = new \Think\Page($count, $row, '', $cate,$path);// 实例化分页类 传入总记录数和每页显示的记录数 和静态分类id
        $Page->setConfig('prev',"上一页");
        $Page->setConfig('next','下一页');
        $Page->setConfig('theme',' %UP_PAGE% %DOWN_PAGE%');
        $show   = $Page->show();// 分页显示输出
        $this->assign('page',$show);// 赋值分页输出
        $this->display(T('Down@gfwmobile/Widget/catepage'));
    }

}