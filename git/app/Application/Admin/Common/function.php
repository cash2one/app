<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

/**
 * 后台公共文件
 * 主要定义后台公共函数库
 */

/* 解析列表定义规则*/

function get_list_field($data, $grid)
{

    // 获取当前字段数据
    foreach ($grid['field'] as $field) {
        $array = explode('|', $field);
        $temp = $data[$array[0]];
        // 函数支持
        if (isset($array[1])) {
            $temp = call_user_func($array[1], $temp);
        }
        $data2[$array[0]] = $temp;
    }
    if (!empty($grid['format'])) {
        $value = preg_replace_callback('/\[([a-z_]+)\]/', function ($match) use ($data2) {
            return $data2[$match[1]];
        }, $grid['format']);
    } else {
        $value = implode(' ', $data2);
    }

    // 链接支持
    if (!empty($grid['href'])) {
        $links = explode(',', $grid['href']);
        foreach ($links as $link) {
            $array = explode('|', $link);
            $href = $array[0];
            if (preg_match('/^\[([a-z_]+)\]$/', $href, $matches)) {
                $val[] = $data2[$matches[1]];
            } else {
                $show = isset($array[1]) ? $array[1] : $value;
                // 替换系统特殊字符串
                $href = str_replace(
                    array('[DELETE]', '[EDIT]', '[LIST]'),
                    array('del?ids=[id]&model=[model_id]', 'edit?id=[id]&model=[model_id]&cate_id=[category_id]', 'index?pid=[id]&model=[model_id]&cate_id=[category_id]'),
                    $href
                );
                // 替换数据变量
                $href = preg_replace_callback(
                    '/\[([a-z_]+)\]/',
                    function ($match) use ($data) {
                        return $data[$match[1]];
                    },
                    $href
                );
                //第三个参数为a标签的target
                if (!empty($array[2])) {
                    if ($array[2] == 'confirm') {
                        $target = 'onclick="return confirm(\'确定要执行此操作吗?\')"';
                    } else {
                        $target = 'target="' . $array[2] . '"';
                    }
                }
                $val[] = '<a href="' . U($href) . '" ' . $target . '>' . $show . '</a>';
            }
        }
        $value = implode(' ', $val);
    }
    return $value;
}

/**
 * 后台公共文件
 * 主要定义后台公共函数库
 */

/* 解析列表定义规则*/

function get_addonlist_field($data, $grid, $addon)
{
    // 获取当前字段数据
    foreach ($grid['field'] as $field) {
        $array = explode('|', $field);
        $temp = $data[$array[0]];
        // 函数支持
        if (isset($array[1])) {
            $temp = call_user_func($array[1], $temp);
        }
        $data2[$array[0]] = $temp;
    }
    if (!empty($grid['format'])) {
        $value = preg_replace_callback(
            '/\[([a-z_]+)\]/',
            function ($match) use ($data2) {
                return $data2[$match[1]];
            },
            $grid['format']
        );
    } else {
        $value = implode(' ', $data2);
    }

    // 链接支持
    if (!empty($grid['href'])) {
        $links = explode(',', $grid['href']);
        foreach ($links as $link) {
            $array = explode('|', $link);
            $href = $array[0];
            if (preg_match('/^\[([a-z_]+)\]$/', $href, $matches)) {
                $val[] = $data2[$matches[1]];
            } else {
                $show = isset($array[1]) ? $array[1] : $value;
                // 替换系统特殊字符串
                $href = str_replace(
                    array('[DELETE]', '[EDIT]', '[ADDON]'),
                    array('del?ids=[id]&name=[ADDON]', 'edit?id=[id]&name=[ADDON]', $addon),
                    $href
                );

                // 替换数据变量
                $href = preg_replace_callback(
                    '/\[([a-z_]+)\]/',
                    function ($match) use ($data) {
                        return $data[$match[1]];
                    },
                    $href
                );

                $val[] = '<a href="' . U($href) . '">' . $show . '</a>';
            }
        }
        $value = implode(' ', $val);
    }
    return $value;
}

// 获取模型名称
function get_model_by_id($id)
{
    return $model = M('Model')->getFieldById($id, 'title');
}

// 获取属性类型信息
function get_attribute_type($type = '')
{
    // TODO 可以加入系统配置
    static $_type = array(
        'num' => array('数字', 'int(10) UNSIGNED NOT NULL'),
        'string' => array('字符串', 'varchar(255) NOT NULL'),
        'textarea' => array('文本框', 'text NOT NULL'),
        'date' => array('日期', 'int(10) NOT NULL'),
        'datetime' => array('时间', 'int(10) NOT NULL'),
        'bool' => array('布尔', 'tinyint(2) NOT NULL'),
        'select' => array('枚举', 'char(50) NOT NULL'),
        'radio' => array('单选', 'char(10) NOT NULL'),
        'checkbox' => array('多选', 'varchar(100) NOT NULL'),
        'editor' => array('编辑器', 'text NOT NULL'),
        'picture' => array('上传图片', 'int(10) UNSIGNED NOT NULL'),
        'multipicture' => array('多图上传', 'varchar(255) NOT NULL'),
        'file' => array('上传附件', 'int(10) UNSIGNED NOT NULL'),
        'multifile' => array('多附件上传', 'varchar(255) NOT NULL'),
        'stringForConfig' => array('预配置的字符串', 'varchar(255) NOT NULL'),
        'play_sp' => array('预览播放的视频地址', 'varchar(255) NOT NULL'),
        'idForTable' => array('表内容选择', 'int(10) UNSIGNED NOT NULL'),
    );
    return $type ? $_type[$type][0] : $_type;
}

/**
 * 分析表内容选择字段类型的参数，获得选项
 * @param int $status
 * @return string 状态文字 ，false 未获取到
 * @author huajie <banhuajie@163.com>
 */
function parse_field_idForTable($string)
{
    $array = explode(':', $string);
    if (count($array) !== 3) {
        return false;
    }

    $lists = M($array[0])->field($array[1] . ',' . $array[2])->select();
    $rs = array();
    //添加默认项
    $rs[0] = '无';
    foreach ($lists as $key => $value) {
        $rs[$value[$array[1]]] = $value[$array[2]];
    }
    return $rs;
}

// 获取属性存储处理类型信息
function get_attribute_save_type($type = '')
{
    // TODO 可以加入系统配置
    static $_type = array(
        'function' => array('函数处理', 'function|params|true'),
    );
    return $type ? $_type[$type][0] : $_type;
}

/**
 * 获取对应状态的文字信息
 * @param int $status
 * @return string 状态文字 ，false 未获取到
 * @author huajie <banhuajie@163.com>
 */
function get_status_title($status = null)
{
    if (!isset($status)) {
        return false;
    }
    switch ($status) {
        case -1:
            return '已删除';
            break;
        case 0:
            return '禁用';
            break;
        case 1:
            return '正常';
            break;
        case 2:
            return '待审核';
            break;
        default:
            return false;
            break;
    }
}

// 获取数据的状态操作
function show_status_op($status)
{
    switch ($status) {
        case 0:
            return '启用';
            break;
        case 1:
            return '禁用';
            break;
        case 2:
            return '审核';
            break;
        default:
            return false;
            break;
    }
}

/**
 * 获取文档的类型文字
 * @param string $type
 * @return string 状态文字 ，false 未获取到
 * @author huajie <banhuajie@163.com>
 */
function get_document_type($type = null)
{
    if (!isset($type)) {
        return false;
    }
    switch ($type) {
        case 1:
            return '目录';
            break;
        case 2:
            return '主题';
            break;
        case 3:
            return '段落';
            break;
        default:
            return false;
            break;
    }
}

/**
 * 获取配置的类型
 * @param string $type 配置类型
 * @return string
 */
function get_config_type($type = 0)
{
    $list = C('CONFIG_TYPE_LIST');
    return $list[$type];
}

/**
 * 获取配置的分组
 * @param string $group 配置分组
 * @return string
 */
function get_config_group($group = 0)
{
    $list = C('CONFIG_GROUP_LIST');
    return $group ? $list[$group] : '';
}

/**
 * select返回的数组进行整数映射转换
 *
 * @param array $map 映射关系二维数组  array(
 *                                          '字段名1'=>array(映射关系数组),
 *                                          '字段名2'=>array(映射关系数组),
 *                                           ......
 *                                       )
 * @author 朱亚杰 <zhuyajie@topthink.net>
 * @return array
 *
 *  array(
 *      array('id'=>1,'title'=>'标题','status'=>'1','status_text'=>'正常')
 *      ....
 *  )
 *
 */
function int_to_string(array &$data, $map = array())
{
    if (empty($data) || false == $data || null === $data) {
        return $data;
    }

    empty($map) && $map = array('status' => array(-1 => '删除', 0 => '禁用', 1 => '正常', 2 => '未审核', 3 => '草稿'));

    foreach ($data as $key => $row) {
        foreach ($map as $col => $pair) {
            if (isset($row[$col]) && isset($pair[$row[$col]])) {
                $data[$key][$col . '_text'] = $pair[$row[$col]];
            }

        }
    }

    return $data;
}

/**
 * 动态扩展左侧菜单,base.html里用到
 * @author 朱亚杰 <zhuyajie@topthink.net>
 */
function extra_menu($extra_menu, &$base_menu)
{
    foreach ($extra_menu as $key => $group) {
        if (isset($base_menu['child'][$key])) {
            $base_menu['child'][$key] = array_merge($base_menu['child'][$key], $group);
        } else {
            $base_menu['child'][$key] = $group;
        }
    }
}

/**
 * 获取参数的所有父级分类
 * @param int $cid 分类id
 * @return array 参数分类和父类的信息集合
 * @author huajie <banhuajie@163.com>
 */
function get_parent_category($cid)
{
    return get_parent_category_by_model($cid, 'Category');
}

/**
 * 获取指定模型的上级分类
 * @param int $cid 分类id
 * @param staring $model 模型名
 * @return array 参数分类和父类的信息集合
 * @author crohn <lllliuliu@163.com>
 */
function get_parent_category_by_model($cid, $model = 'Category')
{
    if (empty($cid)) {
        return false;
    }
    $cates = M($model)->where(array('status' => 1))->field('id,title,pid')->order('sort')->select();

    $child = get_category_by_model($cid, null, $model); //获取参数分类的信息
    $pid = $child['pid'];
    $temp = array();

    $res[] = $child;
    while (true) {
        foreach ($cates as $key => $cate) {
            if ($cate['id'] == $pid) {
                $pid = $cate['pid'];
                array_unshift($res, $cate); //将父分类插入到数组第一个元素前
            }
        }
        if ($pid == 0) {
            break;
        }
    }
    return $res;
}

/**
 * 检测验证码
 * @param  integer $id 验证码ID
 * @return boolean     检测结果
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function check_verify($code, $id = 1)
{
    $verify = new \Think\Verify();
    return $verify->check($code, $id);
}

/**
 * 获取当前分类的文档类型
 * @param int $id
 * @param string $model 模型名
 * @return array 文档类型数组
 * @author huajie <banhuajie@163.com>
 */
function get_type_bycate($id = null, $model = 'Category')
{
    if (empty($id)) {
        return false;
    }
    $type_list = C('DOCUMENT_MODEL_TYPE');
    $model_type = M($model)->getFieldById($id, 'type');
    $model_type = explode(',', $model_type);
    foreach ($type_list as $key => $value) {
        if (!in_array($key, $model_type)) {
            unset($type_list[$key]);
        }
    }
    return $type_list;
}

// 分析枚举类型配置值 格式 a:名称1,b:名称2
function parse_config_attr($string)
{
    $array = preg_split('/[,;\r\n]+/', trim($string, ",;\r\n"));
    if (strpos($string, ':')) {
        $value = array();
        foreach ($array as $val) {
            list($k, $v) = explode(':', $val);
            $value[$k] = $v;
        }
    } else {
        $value = $array;
    }
    return $value;
}

// 获取子文档数目
function get_subdocument_count($id = 0)
{
    return M('Document')->where('pid=' . $id)->count();
}

function get_subDownload_count($id = 0)
{
    return M('Download')->where('pid=' . $id)->count();
}

/**
 * 获取行为数据
 * @param string $id 行为id
 * @param string $field 需要获取的字段
 * @author huajie <banhuajie@163.com>
 */
function get_action($id = null, $field = null)
{
    if (empty($id) && !is_numeric($id)) {
        return false;
    }
    $list = S('action_list');
    if (empty($list[$id])) {
        $map = array('status' => array('gt', -1), 'id' => $id);
        $list[$id] = M('Action')->where($map)->field(true)->find();
    }
    return empty($field) ? $list[$id] : $list[$id][$field];
}

/**
 * 根据条件字段获取数据
 * @param mixed $value 条件，可用常量或者数组
 * @param string $condition 条件字段
 * @param string $field 需要返回的字段，不传则返回整个数据
 * @author huajie <banhuajie@163.com>
 */
function get_document_field($value = null, $condition = 'id', $field = null)
{
    if (empty($value)) {
        return false;
    }

    //拼接参数
    $map[$condition] = $value;
    $info = M('Model')->where($map);
    if (empty($field)) {
        $info = $info->field(true)->find();
    } else {
        $info = $info->getField($field);
    }
    return $info;
}

/**
 * 获取行为类型
 * @param intger $type 类型
 * @param bool $all 是否返回全部类型
 * @author huajie <banhuajie@163.com>
 */
function get_action_type($type, $all = false)
{
    $list = array(
        1 => '系统',
        2 => '用户',
    );
    if ($all) {
        return $list;
    }
    return $list[$type];
}

/**
 * 模型存储数据类型处理
 * @param intger $model_id 模型id
 * @param array $data 数据
 * @author huajie <banhuajie@163.com>
 */
function model_save_type($model_id, $data)
{
    if (empty($model_id)) {
        return '该分类未绑定模型！';
    }
    $fields = get_model_attribute($model_id, false); //获取所有字段设置
    foreach ($fields as $key => $value) {
        if ($value['save_type'] == 'function') {
            $extra = explode('|', $value['save_extra']);

            $func = $extra[0];
            if (!function_exists($func)) {
                //判断函数是否存在
                return $key . ' 字段指定函数 ' . $func . ' 不存在！';
            }

            if (count($extra) > 1) {
                $param = $extra[2] ? $data[$extra[1]] : $extra[1]; //第三个参数存在且为真则使用表单的指定数据为函数参数
                $data[$key] = $func($param);
            } else {
                $data[$key] = $func();
            }
        }
    }

    return $data;
}

/**
 * 多选的存储处理
 * @return integer $data 值
 * @author crohn <lllliuliu@163.com>
 */
function checkboxSave($data)
{
    if (!is_array($data)) {
        return 0;
    } else {
        $pos = 0;
        foreach ($data as $key => $value) {
            $pos += $value; //将各个推荐位的值相加
        }
        return $pos;
    }
}

/**
 * 基于APC的卡号缓存刷新
 * @return integer $id
 * @author crohn <lllliuliu@163.com>
 */
function flushCard($id)
{
    R('Dynamic/Card/setCardsAPC', array($id));
    R('Dynamic/Card/setAllCardsCount', array($id));
    R('Dynamic/Card/setlistsDraw', array($id));
}

/**
 * 基于APC的卡号缓存删除，删除数据时删除共享内存
 * @return integer $id
 * @author crohn <lllliuliu@163.com>
 */
function deleteCard($id)
{
    R('Dynamic/Card/clearById', array($id));
}

/**
 * 描述：下载图片到图片服务器
 * 下载的时候请到对应系统图片服务器上部署相对于的图片下载程序
 * @param $url
 * Author:谭坚
 * Version:1.0.0
 * Modify Time:
 * Modify Author:
 */
function down_pic($remote_url, $file = "", $timeout = 60)
{
    if (empty($remote_url) || empty($file)) {
        return false;
    }

    if (!C('PIC_HOST')) {
        return false;
    }

    $url = C('PIC_HOST') . '/downpic.php';
    $data['url'] = urlencode($remote_url);
    $data['file'] = urlencode($file);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    if (!curl_error($ch)) {
        return true;
    } else {
        return false;
    }
}

/**
 * 描述：判断地址是否有效
 * @param $url
 * @return bool
 * Author:谭坚
 * Version:1.0.0
 * Modify Time:
 * Modify Author:
 */
function url_exists($url)
{
    if ($url) {
        return false;
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_NOBODY, 1); // 不下载
    curl_setopt($ch, CURLOPT_FAILONERROR, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    return (curl_exec($ch) !== false) ? true : false;
}

/**
 * 描述：生成命令存放文件（这边需要shell脚本支持，定时脚本，建议每两秒中运行shell脚本读取该问题，运行对应php命令）
 * 该接口预留，目前没有实现
 * 主要防止生成超时以及列表页只能生成30页的情况
 * 后台命令运行不影响前端操作
 * @param $param
 * Author:谭坚
 * Version:1.0.0
 * Modify Time:
 * Modify Author:
 */
function createHtml($param)
{
    $filename = $_SERVER['DOCUMENT_ROOT'] . '/create_html.ini'; //文件存放位置需要商量，或者配置个常量
    $php_cmd = "php admin.php " . CONTROLLER_NAME . DIRECTORY_SEPARATOR . $param . "\n"; //php命令运行 php运行文件要放在/usr/bin目录下php系统命令才能生效，一把centos都是自带了php系统命令
    $content = file_get_contents($filename); //获取文件中的内容
    if (empty($content)) {
        $cmd = "cd " . $_SERVER['DOCUMENT_ROOT'] . "\n";
        file_put_contents($filename, $cmd);
    }
    if (!strstr($content, $php_cmd)) {
        file_put_contents($filename, $php_cmd, FILE_APPEND);
    }
}

/**
 * 获取标签关联表的类型
 * @date: 2015-7-10
 * @author: liujun
 */
function getTagsMapType($key = '')
{
    $types = array('down' => '下载', 'document' => '文章', 'package' => '礼包', 'batch' => '专区', 'feature' => '专题', 'special' => 'K页面');
    if (!empty($key)) {
        if (in_array($key, array_keys($types))) {
            return $types[$key];
        } else {
            return '';
        }
    }
    return $types;
}

/*
 * 编辑id换取name
 */
function edit_id2name($edit_id)
{
    $nickname = M('member')->where(array('uid' => $edit_id))->getField('nickname');
    return $nickname;
}

/*
 * 分类id换取分类名
 */
function categoryId2Name($category_id, $parent = '0')
{
    if($parent == '1'){
        $pid = M('down_category')->where(array('id' => $category_id))->getField('pid');
        $name =  M('down_category')->where(array('id' => $pid))->getField('title');
    }else{
        $name = M('down_category')->where(array('id' => $category_id))->getField('title');
    }
    return $name;
}