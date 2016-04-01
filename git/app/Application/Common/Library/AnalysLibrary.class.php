<?php
// +----------------------------------------------------------------------
// | 静态生成数据分析管理类
// +----------------------------------------------------------------------
// | date 2015-07-31
// +----------------------------------------------------------------------
// | Author: zhudesheng
// +----------------------------------------------------------------------
namespace Common\Library;
use Think\Think;

class AnalysLibrary extends CreateHtmlLibrary{
	
	// 入口文件名称
	private $entrance = 'index.php';

	// 动态模型
	private $model;
	
	// PC Url
	protected $brainUrl;
	
	// 手机Url
	protected $mobileUrl;

	// 统计
	private $count = array();

    /**
     * PC首页生成
     * @return boolean
     */
	function indexBrainHtml($method){
		return $this->brainUrl($method)->outHtml(C('HOME_INDEX_PATH'), 'index');
	}

    /**
     * 手机首页生成
     * @return boolean
     */
	function indexMobileHtml($method){
		return $this->mobileUrl($method)->outHtml(C('MOBILE_INDEX_PATH'), 'index');
	}

    /**
     * PC封面生成
     * @return boolean
     */
	function homeBrainHtml($model, $typeid){
		
		echo $model;
	}









































    /**
     * 模型切换
	 * @parse string $name 模型名称
     * @return class
     */
	private function changeModel($name){
		static $_model = array();

		if(isset($_model[$name])){
			$this->model = $_model[$name];
		}else{
			
		}
	}

    /**
     * 架构函数
     * @return void
     */
	function __construct(){
		$entrance = rtrim(C('STATIC_CREATE_URL'), '/').'/'.$this->entrance;

		// PC静态生成根文件夹
		$this->brainHtmlDir  = rtrim(C('STATIC_ROOT'), '/').'/';

		// PC Url
		$this->brainUrl  = $entrance.'?s=Home/';
		
		if(C('MOBILE_START')){

			// 手机静态生成根文件夹
			$this->mobileHtmlDir = rtrim(C('MOBILE_STATIC_ROOT'), '/').'/';

			// 手机 Url
			$this->mobileUrl = $entrance.'?t='.C('MOBILE_THEME').'&s=Home/';
		}
	}
}
?>