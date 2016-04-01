<?php
/**
 * Created by Evan Hou.
 * User: Evan <hjpking@126.com>
 * Date: 15/5/28 上午11:03
 */
namespace Common\Library;
namespace Common\Library\dynamic_rewrite_rule;
use Think\Think;

abstract class dynamic_rewrite_rule
{
	/**
	 * 务必实现(定义此成员变量)
	 * @var array
	 */
	protected $allow_path = array();


	/**
	 * 全局变量SERVER的path_info
	 *
	 * @var string
	 */
	protected $server_path_info = '';


	/**
	 * 当前访问url串
	 *
	 * @var string
	 */
	protected $allow_url_key = '';


	/**
	 * 路径信息
	 *
	 * @var string
	 */
	private $path_info = '';


	/**
	 * 方法后缀名
	 *
	 * @var string
	 */
	private $method_ext = '_rule_process';


	/**
	 * 当前访问URL前缀部分 用于检查是否需要动态重写
	 *
	 * @var string
	 */
	private $current_path = '';



	public function __construct()
	{
		$this->server_path_info = str_replace(C('STATIC_FILE_EXT'), '', trim($_SERVER['PATH_INFO'], '/'));
	}


	/**
	 * 执行整体
	 *
	 * @return bool
	 */
	public function exec()
	{
		$this->init();

		$checked = $this->check();
		if (!$checked)
			return false;


		$this->call_method();

		$this->setting();
	}


	/**
	 * 初始化(分割PATH_INFO)
	 *
	 * @return bool
	 */
	private function init()
	{
		if (empty($this->server_path_info) || false === strpos($this->server_path_info, '/'))
			return false;


		$this->current_path = strstr($this->server_path_info, '/', true);
	}


	/**
	 * 检查
	 *
	 * @return bool
	 */
	private function check()
	{
		if (empty ($this->allow_path) || !is_array($this->allow_path))
			return false;


		if (empty ($this->current_path))
			return false;


		/* 转换数组所有值为小写 */
		$this->allow_path = explode(',', strtolower(implode(',', $this->allow_path)));


		if (!in_array($this->current_path, $this->allow_path))
			return false;


		return true;
	}


	/**
	 * 调用具体实现方法
	 *
	 * @return bool
	 */
	private function call_method()
	{
		$method = $this->current_path.$this->method_ext;

		if (!method_exists($this, $method))
			return false;

		$res = $this->$method();

		$this->path_info = (false === $res) ? '' : $res;
	}


	/**
	 * 设置PATH_INFO
	 */
	private function setting()
	{
		if (empty ($this->path_info))
			return false;

		$_SERVER['PATH_INFO'] = $this->path_info;
	}


	/**
	 * 检测是否为手机端(二逼方法，只为快速实现功能)
	 *
	 * @return bool
	 */
	protected function is_mobile()
	{
		$http_host = $_SERVER['HTTP_HOST'];

		$sub_domain = strtolower(strstr($http_host, '.', true));

		return 'm' === $sub_domain ? true : false;
	}









	/**
	 * 路径配置规则解析(可能通用性没有那么强) 此类中只写通用的一些解析规则，其他的请写在各自业务类中
	 *
	 * @param $resolve_type 解析类型
	 *
	 * @return bool
	 */
	protected function tools_path_resolve_rule($resolve_type)
	{
		$tools_method_prefix = 'tools_rule_resolve_';

		if (empty ($resolve_type))
			return false;


		$method = $tools_method_prefix.$resolve_type;
		if (!method_exists($this, $method))
			return false;


		$res = $this->$method();
		if (false === $res)
			return false;


		return $res;
	}


	/**
	 * 详情模块解析规则
	 *
	 * @return array
	 */
	protected function tools_rule_resolve_detail()
	{
		$tmp_path_info       = array_filter(explode('/',$this->server_path_info));
		$this->allow_url_key = implode('/', $tmp_path_info);

		return array('allow_url_key' => $this->allow_url_key);
	}


	/**
	 * 分类模块解析规则
	 *
	 * @return array
	 */
	protected function tools_rule_resolve_category()
	{
		$ext = '/index_{page}';
		$page = 1;

		$tmp_url_key = explode('/', $this->server_path_info);


		if (false !== strpos($this->server_path_info, 'index'))
		{
			$ext_path = array_pop($tmp_url_key);

			(false !== strpos($ext_path, '_')) && $page = (int)array_pop(explode('_', $ext_path));
		}


		$this->allow_url_key = implode('/', $tmp_url_key).$ext;

		return array('allow_url_key' => $this->allow_url_key, 'page' => $page);
	}
}