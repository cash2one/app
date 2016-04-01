<?php
namespace Gallery\Controller;
use Think\Controller;
class IndexController extends Controller {
    public function index(){
        //SEO
        $this->assign("SEO",WidgetSEO(array('moduleindex','Gallery')));  //为模块加入SEO功能
        $this->display();
    }
	
	
	
	
	
	
	
	
	
	
	
	
}