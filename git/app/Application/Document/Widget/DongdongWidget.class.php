<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Document\Widget;
use Think\Controller;

/**
 * 页面widget
 * 
 */

class DongdongWidget extends Controller{


    /**
     * 方法不存在时调用
     *@return void
     */
    public function __call($method,$args) {
        //斜杠会被解析，所以用下划线代替
        $method = str_replace('_', '/', $method);
        $this->display(T($method));
    }
	
	
    /**
     * 常见问题
	 *author:Jeffrey Lau
     *@return void
     */
    public function faq(){
	    $this->assign("SEO",WidgetSEO(array('special',null,'5')));
    	$this->display(T('Document@dongdong/Category/faq'));//模板选择
		
	}
	
	/**
     * 帮助中心
	 *author:Jeffrey Lau
     *@return void
     */
    public function help(){
	     $this->assign("SEO",WidgetSEO(array('special',null,'6')));
    	$this->display(T('Document@dongdong/Category/help'));//模板选择
		
	}

    /**
     * 作者:肖书成
     * 描述:教程的分类导航
     * @param int $cate
     */
	public function courseCate($cate){
        $lists = M('Category')->field('id,title')->where('status = 1 AND pid = 1')->select();

        $this->assign(array(
            'lists' => $lists,
            'cate'  => $cate
        ));

        $this->display('Widget/courseCate');
    }
	
	
	
	
}
