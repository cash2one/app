<?php
// +----------------------------------------------------------------------
// | 描述：亲！宝贝wiget文件
// +----------------------------------------------------------------------
// | Author: 谭坚 <tanjian691406429@163.com>
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------
// | Date: 15-5-22 下午3:36    Version:1.0.0 
// +----------------------------------------------------------------------


namespace Gallery\Widget;
use Think\Controller;


class QbaobeimobileWidget extends Controller{


    /**
     * 描述：获取分页地址
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function categoryPage($row,$count,$cate,$p)
    {

        $count = $count ? $count : 0;
        $row = $row ? $row : 10;
        $cate = $cate ? $cate : 1;
        $Page       = new \Think\Page($count, $row, '', $cate);// 实例化分页类 传入总记录数和每页显示的记录数 和静态分类id
        $Page->setConfig('prev',"上一页");
        $Page->setConfig('next','下一页');
        $Page->setConfig('theme',' %UP_PAGE% %DOWN_PAGE%');
        $show   = $Page->show();// 分页显示输出
        $show = str_replace(C('STATIC_URL'),C('MOBILE_STATIC_URL'),$show);
        $this->assign('page',$show);// 赋值分页输出
        $this->display(T('Gallery@qbaobeimobile/Widget/page'));
    }
} 