<?php
/**
 * Created by Evan Hou.
 * User: Evan <hjpking@126.com>
 * Date: 15/6/5 上午9:08
 */

namespace Common\Library;
namespace Common\Library\dynamic_generate_rule;


use Think\Storage\Driver\File;

class gfw_dynamic_generate_rule extends dynamic_generate_rule
{
	/**
	 * 允许动态生成的模块
	 *
	 * @var array
	 */
	protected $allow_path = array(
		'product', 'product_class', 'class', 'website', 'city',
	);


}