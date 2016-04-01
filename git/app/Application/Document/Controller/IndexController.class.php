<?php
namespace Document\Controller;
use Think\Controller;
class IndexController extends Controller {
    public function index(){
        //SEO
        $this->assign("SEO",WidgetSEO(array('moduleindex','Document')));  //为模块加入SEO功能
        $this->display();
    }
	
	
	
	
	
	
	
	
	
	
	
	
}