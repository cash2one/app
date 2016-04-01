<?php
// +----------------------------------------------------------------------
// | 静态生成抽象类
// +----------------------------------------------------------------------
// | date 2015-08-06
// +----------------------------------------------------------------------
// | Author: zhudesheng
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Think\Controller;

class StaticController extends AdminController{
	
	private $method = array();












































    /**
     * 模块队列分析
     * @return void
     */
	public function module($model){
		
	}

    /**
     * 模型栏目
	 * @param string $model 模型名称
     * @return array
     */
	protected function listCategoryGet($model){
		// 栏目分类模型名称
		$categMod = D($model)->cate_name;
		
		// 栏目记录列表
		$category = D($categMod)->getTree(0, 'id,title,pid', 1);
		
		return $category;
	}

    /**
     * 架构函数
     * @return void
     */
    public function __call($method, $args) {
		static $analys;

		if(!$analys){
			$analys = new \Common\Library\HtmlLibrary();
			$this->method = array_slice(get_class_methods($this), 0, -30);
		}

		if(in_array(ACTION_NAME, $this->method) && method_exists($analys, $method)){
			return call_user_func_array(array($analys, $method), $args);
		}else{
			E(__CLASS__.':'.$method.L('_METHOD_NOT_EXIST_'));
		}

		return;
    }

}
?>