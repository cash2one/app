<?php
namespace I\Controller;
use Think\Controller;

class BaseController extends Controller
{
	public function __construct()
	{
		parent::__construct();
        /* 读取后台站点配置 */
		$config = api('Config/lists');
		
		C($config); //添加配置
		
		
		
		
		
		//操作成功与失败模板定义
		$this->error_page = 'common/operate_error';
		$this->success_page = 'common/operate_success';
	}

	protected function _initialize(){
        if(!user_is_login()){// 还没登录 跳转到登录页面
           $this->redirect('Index/login');
       }
	   $array['uid']    =  session("uid");
       $array['username']   = session("username");
	   $this->assign($array);
	}

	/**
	 * 获取分类
	 *
	 * @param int $parent_id
	 *
	 * @return mixed|void
	 */
	public function get_category($parent_id = 0)
	{
		$category_id = I('category_id');
		$parent_id   = $category_id ? $category_id : $parent_id;

		$cate_data = M($this->table_category)->where(array('pid' => $parent_id))->getField('id, title, pid');

		return IS_AJAX ? $this->ajaxReturn(ajax_return_data_format($cate_data)) :  $cate_data;
	}
}