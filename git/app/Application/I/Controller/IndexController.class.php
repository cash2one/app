<?php
// +----------------------------------------------------------------------
// | 基础控制类
// +----------------------------------------------------------------------
// | Author: liupan
// +----------------------------------------------------------------------

namespace I\Controller;
use Think\Controller;
use ORG\ThinkSDK\ThinkOauth;
/**
 * 前台首页控制器
*/
class IndexController extends Controller {
	protected function _initialize(){
       $config = api('Config/lists');
		C($config); //添加配置
	}
	//系统首页
    public function index(){
	   if(user_is_login()){//用户已经登陆跳转到用户中心
		   $this->redirect('Ucenter/index');
		}else{
		  $this->redirect('Index/login');
		}
    }
	//用户登录
	public function login(){
	    $SEO['title']="官方网用户登录";
	    $SEO['keywords']="官方网用户登录";
		$SEO['description']="官方网用户登录";
	    $this->assign("SEO",$SEO);
		$this->assign("pageName",'登录');
		$this->display('Index/login');
		
	}
	
    //注册页面
	public function register(){
		$SEO['title']="官方网新用户注册";
	    $SEO['keywords']="官方网新用户注册";
		$SEO['description']="官方网新用户注册";
	    $this->assign("SEO",$SEO);
		$this->assign("pageName",'新用户注册');
		$this->display();
	}

	 //找回密码
	public function findpass(){
		$SEO['title']="官方网找回密码第一步";
	    $SEO['keywords']="官方网找回密码第一步";
		$SEO['description']="官方网找回密码第一步";
	    $this->assign("SEO",$SEO);
		$this->display('Index/findpass1');
	}
	
	
	
	 //登录绑定
	public function bindLogin(){
		$SEO['title']="官方网绑定第三方登录";
	    $SEO['keywords']="官方网绑定第三方登录";
		$SEO['description']="官方网绑定第三方登录";
	    $this->assign("SEO",$SEO);
		
		$type = cookie("bind_type");
		$tokenID = cookie("bind_token");
	    $this->assign('type',$type);
	    $this->assign('token',$tokenID);
		$this->display('Index/bindLogin');
	}
	
	
	
	
	
	
	
	
	

}