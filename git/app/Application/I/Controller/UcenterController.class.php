<?php
// +----------------------------------------------------------------------
// | 用户中心控制类
// +----------------------------------------------------------------------
// | Author: liupan
// +----------------------------------------------------------------------

namespace I\Controller;
use Think\Controller;
/**
 * 前台首页控制器
*/
class UcenterController extends BaseController{
	//用户中心
    public function index()
	{
		$SEO['title']="官方网用户中心";
	    $SEO['keywords']="官方网用户中心";
		$SEO['description']="官方网用户中心";
	    $this->assign("SEO",$SEO);
		$limit = 10;

		$user = user_is_login();
		$tmp_p_data = M('down')->where(array('user_id' => $user['uid']))->field('id, title, category_id, status')->order('update_time desc')->limit($limit)->select();
		$product_data = parseDocumentList($tmp_p_data);


		$p_category_id = array_filter(array_unique(array_column($tmp_p_data, 'category_id')));
		$product_category = (empty ($p_category_id)) ? array() :
			M('down_category')->where(array('id'=>array('in',implode(',', $p_category_id))))->getField('id, name, title');



		$tmp_w_data = M('package')->field('id, title, category_id, status')->where(array('user_id' => $user['uid']))
			->order('update_time desc')->limit($limit)->select();
		$website_data = parseDocumentList($tmp_w_data);


		$w_category_id = array_filter(array_unique(array_column($tmp_w_data, 'category_id')));
		$website_category =  empty ($w_category_id) ? array() : M('package_category')->where(array('id'=>array('in',implode(',', $w_category_id))))
			->getField('id, name, title');


		$this->assign('product_data', $product_data);
		$this->assign('product_category', $product_category);

		$this->assign('website_data', $website_data);
		$this->assign('website_category', $website_category);

		$this->display('Index/index');
	
    }
	//修改密码
	public function editPwd(){
		$SEO['title']="官方网用户中心-修改密码";
	    $SEO['keywords']="官方网用户中心-修改密码";
		$SEO['description']="官方网用户中心-修改密码";
	    $this->assign("SEO",$SEO);
		$this->display('Index/updatePass');	
	}
	//修改资料
    public function updateInfo(){
		$SEO['title']="官方网用户中心-修改资料";
	    $SEO['keywords']="官方网用户中心-修改资料";
		$SEO['description']="官方网用户中心-修改资料";
	    $this->assign("SEO",$SEO);
		$user= session("uid");
		$res=A('I/User','Api')->getInfo($user);
		$this->assign('user',$res);
		$this->display('Index/updateInfo');	
	}
	
	
	
	
	
	
	
	
	
	
	
	
}