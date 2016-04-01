<?php

/**
 * Created by Evan Hou.
 * User: Evan <hjpking@126.com>
 * Date: 15/5/28 上午10:14
 */
namespace Common\Library;
namespace Common\Library\dynamic_rewrite_rule;
use Think\Think;

class gfw_dynamic_rewrite_rule extends dynamic_rewrite_rule
{
	/**
	 * 允许动态重写的模块
	 *
	 * @var array
	 */
	protected $allow_path = array(
		'Product', 'product_class', 'class', 'website', 'city',
	);


	/**
	 * 产品模块
	 *
	 * @return bool|string
	 */
	protected function product_rule_process()
	{
		$result = $this->tools_path_resolve_rule('detail');

		$data = M('Down')->field('id')->where(array('path_detail' => $result['allow_url_key']))->find();
		if (empty ($data))
			return false;


		$mobile_url = $this->is_mobile() ? '/t/gfwmobile' : '';

		return 'Down/Detail/Index/id/'.$data['id'].$mobile_url;
	}


	/**
	 * 产品分类模块
	 *
	 * @return bool|string
	 */
	protected function product_class_rule_process()
	{
		$result = $this->tools_path_resolve_rule('category');

		$data = M('Down_category')->field('id')->where(array('path_lists' => $result['allow_url_key']))->find();
		if (empty ($data))
			return false;


		$mobile_url = $this->is_mobile() ? '/t/gfwmobile' : '';

		return 'Down/Category/Index/cate/'.$data['id'].'/p/'.$result['page'].$mobile_url;
	}


	/**
	 * 网站模块
	 *
	 * @return bool|string
	 */
	protected function website_rule_process()
	{
		$result = $this->tools_path_resolve_rule('detail');

		$data = M('Package')->field('id')->where(array('path_detail' => $result['allow_url_key']))->find();
		if (empty ($data))
			return false;


		$mobile_url = $this->is_mobile() ? '/t/gfwmobile' : '';

		return 'Package/Detail/index/id/'.$data['id'].$mobile_url;
	}


	/**
	 * 网站分类模块
	 *
	 * @return bool|string
	 */
	public function class_rule_process()
	{
		$result = $this->tools_path_resolve_rule('category');

		$data = M('Package_category')->field('id')->where(array('path_lists' => $result['allow_url_key']))->find();
		if (empty ($data))
			return false;


		$mobile_url = $this->is_mobile() ? '/t/gfwmobile' : '';

		return 'Package/Category/index/cate/'.$data['id'].'/p/'.$result['page'].$mobile_url;
	}


	/**
	 * 地区网站大全模块
	 *
	 * @return bool|string
	 */
	public function city_rule_process()
	{
		$result = $this->tools_path_resolve_rule('city');
		$province = M('province')->field('id')->where(array('pinyin' => $result['allow_url_key']))->find();
		if (empty($province))
			return false;
        $mobile_url = $this->is_mobile() ? '/theme/gfwmobile/t/gfwmobile' : '';

		$cate = $result['cate_id'] ? '/cate/'.$result['cate_id'] : '';

		return '/Package/Widget/index/theme/gfw/method/websitePlace/province/'.$province['id'].$cate.'/p/'.$result['page'].$mobile_url;
	}

	/**
	 * 地区网站大全解析规则
	 *
	 * @return array
	 */
	protected function tools_rule_resolve_city()
	{
		$page = 1;
		$cate_id = 0;

		$tmp_url_key = explode('/', $this->server_path_info);

		(isset($tmp_url_key[1]) && !empty($tmp_url_key[1])) && $this->allow_url_key = $tmp_url_key[1];


		if (in_array('cate', $tmp_url_key))
		{
			$tmp_array_key = array_search('cate', $tmp_url_key);
			$cate_id = $tmp_url_key[$tmp_array_key+1];
		}


		$tmp_path = array_pop($tmp_url_key);
		if (false !== strpos($tmp_path, '_'))
		{
			$page = (int)array_pop(explode('_', $tmp_path));
		}


		return array('allow_url_key' => $this->allow_url_key, 'page' => $page, 'cate_id' => $cate_id);
	}
}