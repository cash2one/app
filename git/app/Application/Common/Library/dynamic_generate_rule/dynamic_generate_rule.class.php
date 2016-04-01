<?php
/**
 * Created by Evan Hou.
 * User: Evan <hjpking@126.com>
 * Date: 15/6/5 上午9:08
 */

namespace Common\Library;
namespace Common\Library\dynamic_generate_rule;

use Think\Storage\Driver\File;


/**
 * 动态生成静态文件(包含手机端)[只在生成模式下生成]
 *
 * Class dynamic_generate_rule
 * @package Common\Library\dynamic_generate_rule
 */
abstract class dynamic_generate_rule
{

	/**
	 * 内容
	 *
	 * @var string
	 */
	protected $content = '';


	/**
	 * 请求地址
	 *
	 * @var string
	 */
	protected $request_uri = '';


	/**
	 * 当前路径
	 *
	 * @var string
	 */
	protected $current_path = '';


	/**
	 * 生成文件目录(PC端)
	 *
	 * @var string
	 */
	private $generate_path_pc = '';


	/**
	 * 生成文件目录(手机端)
	 *
	 * @var string
	 */
	private $generate_path_mobile = '';


	/**
	 * 构造函数(初始化基础信息)
	 *
	 * @param $content
	 */
	public function __construct($content)
	{
		$this->generate_path_pc     = THINK_PATH.'../Static';
		$this->generate_path_mobile = THINK_PATH.'../Mobile';


		$this->request_uri  = trim($_SERVER['REQUEST_URI']);
		$this->current_path = strstr(ltrim($this->request_uri, '/'), '/', true);


		$this->content = trim(($content));
	}


	/**
	 * 外部调用函数(执行整体)
	 *
	 * @return bool|void
	 */
	public function exec()
	{
		if (!$this->check())
			return false;


		if (empty ($this->content))
			return false;


		return $this->write();
	}


	/**
	 * 检测当前系统配置是否需要生成
	 *
	 * @return bool
	 */
	private function check()
	{
		if (APP_DEBUG)
			//return false;


		/** 系统整体配置项，判断是否开始动态生成 **/
		if (true !== C('DYNAMIC_GENERATE'))
			return false;


		/** 请求URI，用以生成目录名 **/
		if (empty($this->request_uri))
			return false;


		if (!in_array($this->current_path, $this->allow_path))
			return false;


		return true;
	}


	/**
	 * 写入内容
	 *
	 * @return bool
	 */
	private function write()
	{
		$file = new File();


		$path     = $this->is_mobile() ? $this->generate_path_mobile : $this->generate_path_pc;
		$filename = $path.$this->request_uri;


		//用于分类的生成
		if (false === strpos($filename, 'html'))
		{
			$filename .= 'index.html';
		}


		if ($file->has($filename))
			$file->unlink($filename);


		return $file->put($filename, $this->content);
	}


	/**
	 * 检测是否为手机端(二逼方法，只为快速实现功能)
	 *
	 * @return bool
	 */
	private function is_mobile()
	{
		$http_host = $_SERVER['HTTP_HOST'];

		$sub_domain = strtolower(strstr($http_host, '.', true));

		return 'm' === $sub_domain ? true : false;
	}
}