<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace Common\Api;
class ConfigApi {
    /**
     * 获取数据库中的配置列表
     * @return array 配置数组
     */
    public static function lists(){
        $map    = array('status' => 1);
        $data   = M('Config')->where($map)->field('type,name,value')->select();

        $config = array();
        if($data && is_array($data)){
            foreach ($data as $value) {
                $config[$value['name']] = self::parse($value['type'], $value['value']);
            }
        }
        return $config;
    }


	/**
	 * 根据配置类型解析配置name|value;name|value;
	 *
	 * @param $type  配置类型
	 * @param $value 配置值
	 *
	 * @return array
	 */
    private static function parse($type, $value)
	{
        switch ($type)
		{
            case 3: //解析数组
                $array = preg_split('/[,\r\n]+/', trim($value, ",;\r\n"));
                if(strpos($value,':'))
				{
                    $value  = array();
                    foreach ($array as $val)
					{
                        list($k, $v) = explode(':', $val);

						$sub_value = preg_split('/[;]/', trim($v, ";"));

						if (false !== strpos($v, ';'))
						{
							foreach ($sub_value as $sub_val)
							{
								if (false !== strpos($sub_val, '|'))
								{
									list($key, $vals) = explode('|', $sub_val);
									$sub_array = array($key => $vals);
								}
								else
								{
									$sub_array = array($sub_val);
								}

								$value[$k][] = $sub_array;
							}
						}
						else
						{
							$value[$k] = array_shift($sub_value);
						}
                    }
                }
				else
				{
                    $value =    $array;
                }
                break;
        }
        return $value;
    }	
}