<?php
// +----------------------------------------------------------------------
// | 静态文件生成管理类
// +----------------------------------------------------------------------
// | date 2015-07-31
// +----------------------------------------------------------------------
// | Author: zhudesheng
// +----------------------------------------------------------------------
namespace Common\Library;
use Think\Think;

class CreateHtmlLibrary {
	
	// PC静态生成根文件夹
	protected $brainHtmlDir;

	// 手机静态生成根文件夹
	protected $mobileHtmlDir;

	// 静态文件生成后缀
	protected $htmlSuffix = '.html';
	
	// 动态初始化路径
	private $path;
	
	// 动态Url请求地址
	private $url;

    /**
     * 页面生成
	 * @parse string  $file  文件目标
	 * @param boolean $count 进行统计
     * @return boolean
     */
	protected function outHtml($file, $count = ''){
		if($file && $this->path){
			$content = request_by_curl($this->url);
			
			if($content){
				$status = $this->createFile($file, $content);
			}
		}
		
		$this->path = $this->url = '';

		if($status){
			return true;
		}else{

			return false;
		}
	}

    /**
     * 静态文件生成
	 * @param $path    生成路径
	 * @param $content 内容
     * @return boolean true 失败 false 成功
     */
	protected function createFile($file, $content){
		$file = $this->path.$file.$this->htmlSuffix;
			
		// 创建目录
		$path = dirname($file);

		if(!is_dir($path)){
			$this->createDir($path);
		}
		
		// 文件锁定
		flock($file, LOCK_EX);
		
		// 生成静态文件
		$status = file_put_contents($file, $content);

        if($status){
			$status = true;
		}else{
			$status = false;
		}

        flock($file, LOCK_UN);
		return $status;
	}

    /**
     * 生成目录
	 * @param $path  目录路径
	 * @param $chmod 目录权限
     * @return boolean
     */
	private function createDir($path = '', $chmod = '0775'){
		if(empty($path)){
			return false;
		}

		$path = str_replace('\/', '/', $path);

		if(substr($path, -1) != '/'){
			$path = $path.'/';
		}

		$temp	 = explode('/', $path);
		$max	 = count($temp) - 1;
		$cur_dir = '';

		for($i=0; $i < $max; $i++) {
			$cur_dir .= $temp[$i].'/';
			if (@is_dir($cur_dir)){
				continue;
			}

			@mkdir($cur_dir, $chmod, true);
			@chmod($cur_dir, $chmod);
		}

		return is_dir($path);
	}

    /**
     * 架构函数
     * @return void
     */
 	function __call($method, $args){
		if(in_array($method, array('brainUrl', 'mobileUrl'), true)){
			if($method == 'brainUrl'){
				$this->url  = $this->brainUrl.$args[0];
				$this->path = $this->brainHtmlDir;
			}else{
				$this->url  = $this->mobileUrl.$args[0];
				$this->path = $this->mobileHtmlDir;
			}
			return $this;
		}else{
			E(__CLASS__.':'.$method.L('_METHOD_NOT_EXIST_'));
		}
	}
}
?>