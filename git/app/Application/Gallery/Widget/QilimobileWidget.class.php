<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Document\Widget;
use Common\Controller\WidgetController;
use Think\Controller;

/**
 * 页面widget
 * 
 */

class QilimobileWidget extends WidgetController{



	/**
	* 详情页相关文章
	* @author Jeffrey Lau
	* @date   2015-7-1 09:09:42
	* @return array
	*/
   public function detailRelate($id){
	   if(!is_numeric($id)){return;}
	   $p=get_base_by_tag($id,'Document','Document','product',false);
	   $p=array_slice($p,0,4);
	   $this->assign("relate",$p);
	   $this->display("Widget/detailRelate");
  }










}

