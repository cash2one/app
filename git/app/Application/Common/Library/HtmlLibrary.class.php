<?php
// +----------------------------------------------------------------------
// | 静态文件生成抽象类
// +----------------------------------------------------------------------
// | date 2015-07-31
// +----------------------------------------------------------------------
// | Author: zhudesheng
// +----------------------------------------------------------------------
namespace Common\Library;
use Think\Think;

class HtmlLibrary {
	
	// 驱动
	private $drive;

    /**
     * 架构函数
     * @return void
     */
	function __construct(){
		$this->drive = new \Common\Library\AnalysLibrary();
	}

    /**
     * 首页生成
     * @return void
     */
	function indexHtml($site, $sync){
		$this->drive->indexBrainHtml($site);

		if($sync){
			$this->drive->indexMobileHtml($site);
		}
	}

    /**
     * 模块首页生成
     * @return void
     */
	function homeHtml($model, $typeid, $sync){
		$this->drive->homeBrainHtml($model, $typeid);
		
		if($sync){
			$this->drive->homeMobileHtml($site, $typeid);
		}
	}
}
?>