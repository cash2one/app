<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

// OneThink常量定义
const ONETHINK_VERSION = '1.1.140825';
const ONETHINK_ADDON_PATH = './Addons/';

/**
 * 系统公共库文件
 * 主要定义系统公共函数库
 */

/**
 * 检测用户是否登录
 * @return integer 0-未登录，大于0-当前登录用户ID
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function is_login()
{
    $user = session('user_auth');
    if (empty($user)) {
        return 0;
    } else {
        return session('user_auth_sign') == data_auth_sign($user) ? $user['uid'] : 0;
    }
}

/**
 * 检测当前用户是否为管理员
 * @return boolean true-管理员，false-非管理员
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function is_administrator($uid = null)
{
    $uid = is_null($uid) ? is_login() : $uid;
    return $uid && (intval($uid) === C('USER_ADMINISTRATOR'));
}

/**
 * 字符串转换为数组，主要用于把分隔符调整到第二个参数
 * @param  string $str 要分割的字符串
 * @param  string $glue 分割符
 * @return array
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function str2arr($str, $glue = ',')
{
    return explode($glue, $str);
}

/**
 * 系统非常规MD5加密方法
 * @param  string $str 要加密的字符串
 * @return string
 */
function think_ucenter_md5($str, $key = 'ThinkUCenter')
{
    return '' === $str ? '' : md5(sha1($str) . $key);
}

/**
 * 数组转换为字符串，主要用于把分隔符调整到第二个参数
 * @param  array $arr 要连接的数组
 * @param  string $glue 分割符
 * @return string
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function arr2str($arr, $glue = ',')
{
    return implode($glue, $arr);
}

/**
 * 字符串截取，支持中文和其他编码
 * @static
 * @access public
 * @param string $str 需要转换的字符串
 * @param string $start 开始位置
 * @param string $length 截取长度
 * @param string $charset 编码格式
 * @param string $suffix 截断显示字符
 * @return string
 */
function msubstr($str, $start = 0, $length = 0, $charset = "utf-8", $suffix = true)
{
    if (function_exists("mb_substr")) {
        $slice = mb_substr($str, $start, $length, $charset);
    } elseif (function_exists('iconv_substr')) {
        $slice = iconv_substr($str, $start, $length, $charset);
        if (false === $slice) {
            $slice = '';
        }
    } else {
        $re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("", array_slice($match[0], $start, $length));
    }
    return $suffix ? $slice . '...' : $slice;
}

/**
 * 系统加密方法
 * @param string $data 要加密的字符串
 * @param string $key 加密密钥
 * @param int $expire 过期时间 单位 秒
 * @return string
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function think_encrypt($data, $key = '', $expire = 0)
{
    $key = md5(empty($key) ? C('DATA_AUTH_KEY') : $key);
    $data = base64_encode($data);
    $x = 0;
    $len = strlen($data);
    $l = strlen($key);
    $char = '';

    for ($i = 0; $i < $len; $i++) {
        if ($x == $l) {
            $x = 0;
        }

        $char .= substr($key, $x, 1);
        $x++;
    }

    $str = sprintf('%010d', $expire ? $expire + time() : 0);

    for ($i = 0; $i < $len; $i++) {
        $str .= chr(ord(substr($data, $i, 1)) + (ord(substr($char, $i, 1))) % 256);
    }
    return str_replace(array('+', '/', '='), array('-', '_', ''), base64_encode($str));
}

/**
 * 系统解密方法
 * @param  string $data 要解密的字符串 （必须是think_encrypt方法加密的字符串）
 * @param  string $key 加密密钥
 * @return string
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function think_decrypt($data, $key = '')
{
    $key = md5(empty($key) ? C('DATA_AUTH_KEY') : $key);
    $data = str_replace(array('-', '_'), array('+', '/'), $data);
    $mod4 = strlen($data) % 4;
    if ($mod4) {
        $data .= substr('====', $mod4);
    }
    $data = base64_decode($data);
    $expire = substr($data, 0, 10);
    $data = substr($data, 10);

    if ($expire > 0 && $expire < time()) {
        return '';
    }
    $x = 0;
    $len = strlen($data);
    $l = strlen($key);
    $char = $str = '';

    for ($i = 0; $i < $len; $i++) {
        if ($x == $l) {
            $x = 0;
        }

        $char .= substr($key, $x, 1);
        $x++;
    }

    for ($i = 0; $i < $len; $i++) {
        if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1))) {
            $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
        } else {
            $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
        }
    }
    return base64_decode($str);
}

/**
 * 数据签名认证
 * @param  array $data 被认证的数据
 * @return string       签名
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function data_auth_sign($data)
{
    //数据类型检测
    if (!is_array($data)) {
        $data = (array) $data;
    }
    ksort($data); //排序
    $code = http_build_query($data); //url编码并生成query字符串
    $sign = sha1($code); //生成签名
    return $sign;
}

/**
 * 对查询结果集进行排序
 * @access public
 * @param array $list 查询结果
 * @param string $field 排序的字段名
 * @param array $sortby 排序类型
 * asc正向排序 desc逆向排序 nat自然排序
 * @return array
 */
function list_sort_by($list, $field, $sortby = 'asc')
{
    if (is_array($list)) {
        $refer = $resultSet = array();
        foreach ($list as $i => $data) {
            $refer[$i] = &$data[$field];
        }

        switch ($sortby) {
            case 'asc': // 正向排序
                asort($refer);
                break;
            case 'desc': // 逆向排序
                arsort($refer);
                break;
            case 'nat': // 自然排序
                natcasesort($refer);
                break;
        }
        foreach ($refer as $key => $val) {
            $resultSet[] = &$list[$key];
        }

        return $resultSet;
    }
    return false;
}

/**
 * 把返回的数据集转换成Tree
 * @param array $list 要转换的数据集
 * @param string $pid parent标记字段
 * @param string $level level标记字段
 * @return array
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function list_to_tree($list, $pk = 'id', $pid = 'pid', $child = '_child', $root = 0)
{
    // 创建Tree
    $tree = array();
    if (is_array($list)) {
        // 创建基于主键的数组引用
        $refer = array();
        foreach ($list as $key => $data) {
            $refer[$data[$pk]] = &$list[$key];
        }
        foreach ($list as $key => $data) {
            // 判断是否存在parent
            $parentId = $data[$pid];
            if ($root == $parentId) {
                $tree[$key] = &$list[$key];
            } else {
                if (isset($refer[$parentId])) {
                    $parent = &$refer[$parentId];
                    $parent[$child][$key] = &$list[$key];
                }
            }
        }
    }
    return $tree;
}

/**
 * 将list_to_tree的树还原成列表
 * @param  array $tree 原来的树
 * @param  string $child 孩子节点的键
 * @param  string $order 排序显示的键，一般是主键 升序排列
 * @param  array $list 过渡用的中间数组，
 * @param boolean $is_order 是否启用排序
 * @return array        返回排过序的列表数组
 * @author yangweijie <yangweijiester@gmail.com>
 */
function tree_to_list($tree, $child = '_child', $order = 'id', &$list = array(), $is_order = true)
{
    if (is_array($tree)) {
        $refer = array();
        foreach ($tree as $key => $value) {
            $reffer = $value;
            if (isset($reffer[$child])) {
                unset($reffer[$child]);
                tree_to_list($value[$child], $child, $order, $list);
            }
            $list[] = $reffer;
        }
        //新增是否启用排序，排序操作数据量大时耗时较长 sunjianhua 2016.1.8
        if ($is_order) {
            $list = list_sort_by($list, $order, $sortby = 'asc');
        }
    }
    return $list;
}

/**
 * 格式化字节大小
 * @param  number $size 字节数
 * @param  string $delimiter 数字和单位分隔符
 * @return string            格式化后的带单位的大小
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function format_bytes($size, $delimiter = '')
{
    $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
    for ($i = 0; $size >= 1024 && $i < 5; $i++) {
        $size /= 1024;
    }

    return round($size, 2) . $delimiter . $units[$i];
}

/**
 * 设置跳转页面URL
 * 使用函数再次封装，方便以后选择不同的存储方式（目前使用cookie存储）
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function set_redirect_url($url)
{
    cookie('redirect_url', $url);
}

/**
 * 获取跳转页面URL
 * @return string 跳转页URL
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function get_redirect_url()
{
    $url = cookie('redirect_url');
    return empty($url) ? __APP__ : $url;
}

/**
 * 清除字符串的空格，换行，HTML标签
 * @return string 字符串
 * @author crohn
 */
function clean_str($str)
{
    return preg_replace("/\s|　/", "", strip_tags(html_entity_decode($str)));
}

/**
 * 处理插件钩子
 * @param string $hook 钩子名称
 * @param mixed $params 传入参数
 * @return void
 */
function hook($hook, $params = array())
{
    \Think\Hook::listen($hook, $params);
}

/**
 * 获取插件类的类名
 * @param strng $name 插件名
 */
function get_addon_class($name)
{
    $class = "Addons\\{$name}\\{$name}Addon";
    return $class;
}

/**
 * 获取插件类的配置文件数组
 * @param string $name 插件名
 */
function get_addon_config($name)
{
    $class = get_addon_class($name);
    if (class_exists($class)) {
        $addon = new $class();
        return $addon->getConfig();
    } else {
        return array();
    }
}

/**
 * 插件显示内容里生成访问插件的url
 * @param string $url url
 * @param array $param 参数
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function addons_url($url, $param = array())
{
    $url = parse_url($url);
    $case = C('URL_CASE_INSENSITIVE');
    $addons = $case ? parse_name($url['scheme']) : $url['scheme'];
    $controller = $case ? parse_name($url['host']) : $url['host'];
    $action = trim($case ? strtolower($url['path']) : $url['path'], '/');

    /* 解析URL带的参数 */
    if (isset($url['query'])) {
        parse_str($url['query'], $query);
        $param = array_merge($query, $param);
    }

    /* 基础参数 */
    $params = array(
        '_addons' => $addons,
        '_controller' => $controller,
        '_action' => $action,
    );
    $params = array_merge($params, $param); //添加额外参数

    return U('Addons/execute', $params);
}

/**
 * 时间戳格式化
 * @param int $time
 * @return string 完整的时间显示
 * @author huajie <banhuajie@163.com>
 */
function time_format($time = null, $format = 'Y-m-d H:i')
{
    $time = $time === null ? NOW_TIME : intval($time);
    return date($format, $time);
}

/**
 * 根据用户ID获取用户名
 * @param  integer $uid 用户ID
 * @return string       用户名
 */
function get_username($uid = 0)
{
    static $list;
    if (!($uid && is_numeric($uid))) {
        //获取当前登录用户名
        return session('user_auth.username');
    }

    /* 获取缓存数据 */
    if (empty($list)) {
        $list = S('sys_active_user_list');
    }

    /* 查找用户信息 */
    $key = "u{$uid}";
    if (isset($list[$key])) {
        //已缓存，直接使用
        $name = $list[$key];
    } else {
        //调用接口获取用户信息
        $User = new User\Api\UserApi();
        $info = $User->info($uid);
        if ($info && isset($info[1])) {
            $name = $list[$key] = $info[1];
            /* 缓存用户 */
            $count = count($list);
            $max = C('USER_MAX_CACHE');
            while ($count-- > $max) {
                array_shift($list);
            }
            S('sys_active_user_list', $list);
        } else {
            $name = '';
        }
    }
    return $name;
}

/**
 * 根据用户ID获取用户昵称
 * @param  integer $uid 用户ID
 * @return string       用户昵称
 */
function get_nickname($uid = 0)
{
    static $list;
    if (!($uid && is_numeric($uid))) {
        //获取当前登录用户名
        return session('user_auth.username');
    }

    /* 获取缓存数据 */
    if (empty($list)) {
        $list = S('sys_user_nickname_list');
    }

    /* 查找用户信息 */
    $key = "u{$uid}";
    if (isset($list[$key])) {
        //已缓存，直接使用
        $name = $list[$key];
    } else {
        //调用接口获取用户信息
        $info = M('Member')->field('nickname')->find($uid);
        if ($info !== false && $info['nickname']) {
            $nickname = $info['nickname'];
            $name = $list[$key] = $nickname;
            /* 缓存用户 */
            $count = count($list);
            $max = C('USER_MAX_CACHE');
            while ($count-- > $max) {
                array_shift($list);
            }
            S('sys_user_nickname_list', $list);
        } else {
            $name = '';
        }
    }
    return $name;
}

/**
 * 获取分类信息并缓存分类
 * @param  integer $id 分类ID
 * @param  string $field 要获取的字段名
 * @return string         分类信息
 */
function get_category($id, $field = null, $model = 'Category')
{
    return get_category_by_model($id, $field, $model);
}

/* 根据ID获取分类标识 */
function get_category_name($id, $model = 'Category')
{
    return get_category($id, 'name', $model);
}

/* 根据ID获取分类名称 */
function get_category_title($id, $model = 'Category')
{
    return get_category($id, 'title', $model);
}

/**
 * 获取指定模型分类信息并缓存分类
 * @param $id integer 分类ID
 * @param $field string 查找的字段
 * @param $model string 模型
 * @param $all boolean 是否获取所有分类
 * @return string
 */

function get_category_by_model($id, $field = null, $model = 'Category', $all = false)
{
    static $list_arr;

    $list = &$list_arr[$model];

    /* 非法分类ID */
    if (!$all && (empty($id) || !is_numeric($id))) {
        return '';
    }

    /* 读取缓存数据 */
    if (empty($list)) {
        $list = S('sys_category_list_' . $model);
    }

    if (empty($list)) {
        $list_f = M($model)->where('status=1')->select();
        $list = [];
        foreach ($list_f as $key => $value) {
            $list[$value['id']] = $value;
        }
        S('sys_category_list_' . $model, $list); //更新缓存
    }

    if ($all) {
        return $list;
    } else {
        if (empty($list[$id])) {
            return '';
        }

        return is_null($field) ? $list[$id] : $list[$id][$field];
    }

}

/* 根据ID获取指定模型分类标识 */
function get_category_name_by_model($id, $model = 'Category')
{
    return get_category_by_model($id, 'name', $model);
}

/* 根据ID获取指定模型分类名称 */
function get_category_title_by_model($id, $model = 'Category')
{
    return get_category_by_model($id, 'title', $model);
}

/**
 * 根据数据基础数据id和module获取相应的标签信息
 * @param id
 * @param module
 * @param table type (标签类型)
 * @return array
 */

function get_tags($did, $model, $table = 'tags')
{
    //处理参数
    if (empty($did) || empty($model)) {
        return false;
    }

    $model = strtolower($model);
    $table = strtolower($table);

    //根据参数初始化查询所用的表
    if ($table == 'product') {
        $tableMap = 'ProductTagsMap';
        $join = '__PRODUCT_TAGS__';
    } else {
        $tableMap = 'TagsMap';
        $join = '__TAGS__';
    }

    //查询数据
    return M($tableMap)->field('b.id,b.category,b.name,b.title,a.id map_id')->alias('a')->join($join . ' b ON a.tid = b.id')->where('a.did = ' . $did . ' AND a.type = "' . $model . '" AND b.display = 1')->order('a.id ASC')->select();
}

//根据标签ID查询相应的标签名称
function get_tag($id, $table)
{
    if (empty($id) || empty($table)) {
        return false;
    }

    return M($table)->field('name,title')->where('id = ' . $id)->find();
}

/**
 * 移除二维数组中重复的值 并保留键值(二维数组去重)
 * author : JeffreyLau
 * last edit : 2015-9-7 11:05:21
 * @param $array2D
 * @return array|bool
 */
function array_unique_fb($array2D)
{
    if (!is_array($array2D) || !is_array($array2D[0])) {
        return false;
    }

    $arrkey = array_keys($array2D[0]);

    foreach ($array2D as $k => $v) {
        $v = join(",", $v); //降维,也可以用implode,将一维数组转换为用逗号连接的字符串
        $temp[$k] = $v;
    }
    $temp = array_unique($temp); //去掉重复的字符串,也就是重复的一维数组
    $temp2 = array();
    foreach ($temp as $k => $v) {
        $array = explode(",", $v); //再将拆开的数组重新组装
        foreach ($arrkey as $k1 => $k2) {
            $temp2[$k][$k2] = $array[$k1];
        }
    }
    return $temp2;
}

function getBannerscore($id)
{
    $score = get_score($id);
    if (empty($score) || $score == '0') {
        $score = '5';
    }
    return $score;

}

/**
 * @@Comments  获取根据软件ID获取相应的评分
 * @param   int $id
 * @param   bool $bool
 * @return  bool|float
 */
function get_score($id, $bool = false)
{

    if (empty($id)) {
        return false;
    }
    $rel = M('downDmain')->where('id = ' . $id)->find();

    if ($bool) {
        return ($rel['picture_score'] + $rel['music_score'] + $rel['feature_score'] + $rel['run_score']) * 5; //100分制
    } else {
        return round(($rel['picture_score'] + $rel['music_score'] + $rel['feature_score'] + $rel['run_score']) / 4); //5分制
    }

}

/**
 * 获取根据ID获取相应的评分
 *
 */
function get_score_other($id, $module)
{
    static $scoreOther;
    if ($scoreOther[$id][$module]) {
        return $scoreOther[$id][$module];
    }

    $ids = get_tags($id, $module, 'product');
    if (empty($ids)) {
        return 0;
    }

    $rel = M('productTagsMap')->where('type = "down" AND tid = ' . $ids[0]['id'])->find();
    if (empty($rel)) {
        return 0;
    }

    $scoreOther[$id][$module] = get_score($rel['did'], true);
    return $scoreOther[$id][$module];
}

/**
 * 格式化存储大小 单位自动转换
 * @param $num
 * @param null $unit
 * @return bool|string
 */

function format_size($num, $unit = null)
{
    if (!is_numeric($num) || $num <= 0) {
        return $num;
    }
    $unit = strtoupper($unit);
    if ($unit) {
        switch ($unit) {
            case 'G':
                $num = round($num / 1048576, 1);
                if ((float) $num < 0.1) {
                    $num = 0.1;
                }
                break;
            case 'M':
                $num = round($num / 1024, 1);
                if ((float) $num < 0.1) {
                    $num = 0.1;
                }
                break;
            case 'K':
                break;
            default:
                return false;
        }
    } else {
        if ($num >= 1048576) {
            $num = round($num / 1048576, 1);
            $unit = 'G';
        } elseif ($num >= 1024) {
            $num = round($num / 1024, 1);
            $unit = 'M';
        } else {
            $unit = 'K';
        }
    }
    return $num . $unit;

}

/**
 * 根据数据表的id 和模型 获取相关联的表的数据
 * @param  int $id 当前表的id
 * @param  string $model 当前表的模型
 * @param  string $typemodel 目标表的模型
 * @param  string $type 关联目标模型
 * @param  bool $find 判断是否放回一条数据，默认true返回一条，false返回多条
 * @return bool   and array
 */
function get_base_by_tag($id, $model, $typemodel, $type = 'tags', $find = true)
{
    //初始化参数
    if (!is_numeric($id)) {
        return false;
    }

    $model = strtolower($model);
    $typemodel = strtolower($typemodel);
    $table = array('down', 'document', 'package', 'batch', 'gallery'); //新增gallery

    //检验参数是否符合要求
    if (!in_array($model, $table) || !in_array($typemodel, $table)) {
        return false;
    }

    if ($type == 'product') {
        $type = 'ProductTagsMap';
    } else {
        $type = 'TagsMap';
    }

    //查询产品标签或者标签
    $ids = M($type)->where("did = $id AND type = '$model'")->getField('tid', true);

    //初始化标签ID或者产品标签的ID准备下一次的查询
    if (empty($ids)) {
        return false;
    }

    $ids = implode(',', $ids);
    if ($type == 'ProductTagsMap') {
        $type = '__PRODUCT_TAGS_MAP__';
    } else {
        $type = '__TAGS_MAP__';
    }
    $lawTable = array('down', 'package');
    $where = (($typemodel == 'batch') ? 'a.enabled = 1 AND a.pid = 0' : 'a.status = 1') . " AND b.tid IN ($ids) AND b.type = '$typemodel'";

    //查询出符合要求的数据集并返回
    if (in_array($typemodel, $lawTable)) {
        $objModel = M($typemodel)->alias('a')->field('a.*,b.tid,c.*')->join($type . ' b ON a.id = b.did')->join(strtoupper('__' . $typemodel . '_' . substr($typemodel, 0, 1) . 'MAIN__') . ' c ON a.id = c.id')->where($where)->order("a.id desc");
    } else {
        $objModel = M($typemodel)->alias('a')->field('a.*,b.tid')->join($type . ' b ON a.id = b.did')->where($where)->order("a.id desc");
    }

    return $find ? $objModel->find() : $objModel->group('a.id')->limit(26)->select();
}

/**
 * 获取顶级模型信息
 */
function get_top_model($model_id = null)
{
    $map = array('status' => 1, 'extend' => 0);
    if (!is_null($model_id)) {
        $map['id'] = array('neq', $model_id);
    }
    $model = M('Model')->where($map)->field(true)->select();
    foreach ($model as $value) {
        $list[$value['id']] = $value;
    }
    return $list;
}

/**
 * 根据某个字段值获取一条模型信息
 * @param  string $field 模型字段
 * @param  string $value 字段值
 * @param  string $returnField 返回字段值
 * @return array
 */
function get_model_by($field, $value, $returnField = true)
{
    $map = array($field => $value);
    return M('Model')->where($map)->field($returnField)->find();
}

/**
 * 获取继承文档模型的模型信息
 * @param  integer $id 模型ID
 * @param  string $field 模型字段
 * @return array
 */
function get_document_model($id = null, $field = null)
{
    //extend应该根据具体模型来获取，而不是固定为1 sunjianhua 2016.3.30
    $extend = get_model_by('id', $id, 'extend');
    if (empty($extend)) {
        throw new RuntimeException("Get extend value failed.", 39);
    }

    return get_document_model_by_extend($id, $field, $extend['extend']);
}

/**
 * 根据模型继承类型获取模型信息
 * @param  integer $id 模型ID
 * @param  string $field 模型字段
 * @param  integer $extend 继承模型ID
 * @return array
 */
function get_document_model_by_extend($id = null, $field = null, $extend = 1)
{
    static $list_arr;

    $list = &$list_arr[$extend];

    /* 非法分类ID */
    if (!(is_numeric($id) || is_null($id))) {
        return '';
    }

    /* 读取缓存数据 */
    if (empty($list)) {
        $list = S('DOCUMENT_MODEL_LIST_' . $extend);
    }

    /* 获取模型名称 */
    if (empty($list)) {
        $map = array('status' => 1, 'extend' => $extend);
        $model = M('Model')->where($map)->field(true)->select();
        foreach ($model as $value) {
            $list[$value['id']] = $value;
        }
        S('DOCUMENT_MODEL_LIST_' . $extend, $list); //更新缓存
    }

    /* 根据条件返回数据 */
    if (is_null($id)) {
        return $list;
    } elseif (is_null($field)) {
        return $list[$id];
    } else {
        return $list[$id][$field];
    }
}

/**
 * 解析UBB数据
 * @param string $data UBB字符串
 * @return string 解析为HTML的数据
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function ubb($data)
{
    //TODO: 待完善，目前返回原始数据
    return $data;
}

/**
 * 记录行为日志，并执行该行为的规则
 * @param string $action 行为标识
 * @param string $model 触发行为的模型名
 * @param int $record_id 触发行为的记录id
 * @param int $user_id 执行行为的用户id
 * @return boolean
 * @author huajie <banhuajie@163.com>
 */
function action_log($action = null, $model = null, $record_id = null, $user_id = null)
{

    //参数检查
    if (empty($action) || empty($model) || empty($record_id)) {
        return '参数不能为空';
    }
    if (empty($user_id)) {
        $user_id = is_login();
    }

    //查询行为,判断是否执行
    $action_info = M('Action')->getByName($action);
    if ($action_info['status'] != 1) {
        return '该行为被禁用或删除';
    }

    //插入行为日志
    $data['action_id'] = $action_info['id'];
    $data['user_id'] = $user_id;
    $data['action_ip'] = ip2long(get_client_ip());
    $data['model'] = $model;
    $data['record_id'] = $record_id;
    $data['create_time'] = NOW_TIME;

    //解析日志规则,生成日志备注
    if (!empty($action_info['log'])) {
        if (preg_match_all('/\[(\S+?)\]/', $action_info['log'], $match)) {
            $log['user'] = $user_id;
            $log['record'] = $record_id;
            $log['model'] = $model;
            $log['time'] = NOW_TIME;
            $log['data'] = array('user' => $user_id, 'model' => $model, 'record' => $record_id, 'time' => NOW_TIME);
            foreach ($match[1] as $value) {
                $param = explode('|', $value);
                if (isset($param[1])) {
                    $replace[] = call_user_func($param[1], $log[$param[0]]);
                } else {
                    $replace[] = $log[$param[0]];
                }
            }
            $data['remark'] = str_replace($match[0], $replace, $action_info['log']);
        } else {
            $data['remark'] = $action_info['log'];
        }
    } else {
        //未定义日志规则，记录操作url
        $data['remark'] = '操作url：' . $_SERVER['REQUEST_URI'];
    }

    M('ActionLog')->add($data);

    if (!empty($action_info['rule'])) {
        //解析行为
        $rules = parse_action($action, $user_id);

        //执行行为
        $res = execute_action($rules, $action_info['id'], $user_id);
    }
}

/**
 * 解析行为规则
 * 规则定义  table:$table|field:$field|condition:$condition|rule:$rule[|cycle:$cycle|max:$max][;......]
 * 规则字段解释：table->要操作的数据表，不需要加表前缀；
 *              field->要操作的字段；
 *              condition->操作的条件，目前支持字符串，默认变量{$self}为执行行为的用户
 *              rule->对字段进行的具体操作，目前支持四则混合运算，如：1+score*2/2-3
 *              cycle->执行周期，单位（小时），表示$cycle小时内最多执行$max次
 *              max->单个周期内的最大执行次数（$cycle和$max必须同时定义，否则无效）
 * 单个行为后可加 ； 连接其他规则
 * @param string $action 行为id或者name
 * @param int $self 替换规则里的变量为执行用户的id
 * @return boolean|array: false解析出错 ， 成功返回规则数组
 * @author huajie <banhuajie@163.com>
 */
function parse_action($action = null, $self = 0)
{
    if (empty($action)) {
        return false;
    }

    //参数支持id或者name
    if (is_numeric($action)) {
        $map = array('id' => $action);
    } else {
        $map = array('name' => $action);
    }

    //查询行为信息
    $info = M('Action')->where($map)->find();
    if (!$info || $info['status'] != 1) {
        return false;
    }

    //解析规则:table:$table|field:$field|condition:$condition|rule:$rule[|cycle:$cycle|max:$max][;......]
    $rules = $info['rule'];
    $rules = str_replace('{$self}', $self, $rules);
    $rules = explode(';', $rules);
    $return = array();
    foreach ($rules as $key => &$rule) {
        $rule = explode('|', $rule);
        foreach ($rule as $k => $fields) {
            $field = empty($fields) ? array() : explode(':', $fields);
            if (!empty($field)) {
                $return[$key][$field[0]] = $field[1];
            }
        }
        //cycle(检查周期)和max(周期内最大执行次数)必须同时存在，否则去掉这两个条件
        if (!array_key_exists('cycle', $return[$key]) || !array_key_exists('max', $return[$key])) {
            unset($return[$key]['cycle'], $return[$key]['max']);
        }
    }

    return $return;
}

/**
 * 执行行为
 * @param array $rules 解析后的规则数组
 * @param int $action_id 行为id
 * @param array $user_id 执行的用户id
 * @return boolean false 失败 ， true 成功
 * @author huajie <banhuajie@163.com>
 */
function execute_action($rules = false, $action_id = null, $user_id = null)
{
    if (!$rules || empty($action_id) || empty($user_id)) {
        return false;
    }

    $return = true;
    foreach ($rules as $rule) {

        //检查执行周期
        $map = array('action_id' => $action_id, 'user_id' => $user_id);
        $map['create_time'] = array('gt', NOW_TIME - intval($rule['cycle']) * 3600);
        $exec_count = M('ActionLog')->where($map)->count();
        if ($exec_count > $rule['max']) {
            continue;
        }

        //执行数据库操作
        $Model = M(ucfirst($rule['table']));
        $field = $rule['field'];
        $res = $Model->where($rule['condition'])->setField($field, array('exp', $rule['rule']));

        if (!$res) {
            $return = false;
        }
    }
    return $return;
}

//基于数组创建目录和文件
function create_dir_or_files($files)
{
    foreach ($files as $key => $value) {
        if (substr($value, -1) == '/') {
            mkdir($value);
        } else {
            @file_put_contents($value, '');
        }
    }
}

if (!function_exists('array_column')) {
    function array_column(array $input, $columnKey, $indexKey = null)
    {
        $result = array();
        if (null === $indexKey) {
            if (null === $columnKey) {
                $result = array_values($input);
            } else {
                foreach ($input as $row) {
                    $result[] = $row[$columnKey];
                }
            }
        } else {
            if (null === $columnKey) {
                foreach ($input as $row) {
                    $result[$row[$indexKey]] = $row;
                }
            } else {
                foreach ($input as $row) {
                    $result[$row[$indexKey]] = $row[$columnKey];
                }
            }
        }
        return $result;
    }
}

/**
 * 获取表名（不含表前缀）
 * @param string $model_id
 * @return string 表名
 * @author huajie <banhuajie@163.com>
 */
function get_table_name($model_id = null)
{
    if (empty($model_id)) {
        return false;
    }
    $Model = M('Model');
    $name = '';
    $info = $Model->getById($model_id);
    if ($info['extend'] != 0) {
        $name = $Model->getFieldById($info['extend'], 'name') . '_';
    }
    $name .= $info['name'];
    return $name;
}

/**
 * 获取属性信息并缓存
 * @param  integer $id 属性ID
 * @param  string $field 要获取的字段名
 * @return string         属性信息
 */
function get_model_attribute($model_id, $group = true, $fields = true)
{
    static $list;

    /* 非法ID */
    if (empty($model_id) || !is_numeric($model_id)) {
        return '';
    }

    /* 获取属性 */
    if (!isset($list[$model_id])) {
        $map = array('model_id' => $model_id);
        $extend = M('Model')->getFieldById($model_id, 'extend');

        if ($extend) {
            $map = array('model_id' => array("in", array($model_id, $extend)));
        }
        $info = M('Attribute')->where($map)->field($fields)->select();
        $list[$model_id] = $info;
    }

    $attr = array();
    if ($group) {
        foreach ($list[$model_id] as $value) {
            $attr[$value['id']] = $value;
        }
        $sort = M('Model')->getFieldById($model_id, 'field_sort');

        if (empty($sort)) {
            //未排序
            $group = array(1 => array_merge($attr));
        } else {
            $group = json_decode($sort, true);

            $keys = array_keys($group);
            foreach ($group as &$value) {
                foreach ($value as $key => $val) {
                    $value[$key] = $attr[$val];
                    unset($attr[$val]);
                }
            }

            if (!empty($attr)) {
                $group[$keys[0]] = array_merge($group[$keys[0]], $attr);
            }
        }
        $attr = $group;
    } else {
        foreach ($list[$model_id] as $value) {
            $attr[$value['name']] = $value;
        }
    }
    return $attr;
}

/**
 * 处理文档列表显示
 *
 * @param      $list 列表数据
 * @param null $model_id 模型id
 *
 * @return array
 */
function parseDocumentList($list, $model_id = null)
{
    $attrList = get_model_attribute($model_id ? $model_id : 1, false, 'id,name,type,extra');
    if (!is_array($list)) {
        return $list;
    }

    foreach ($list as $k => $data) {
        foreach ($data as $key => $val) {
            if (!isset($attrList[$key])) {
                continue;
            }

            $extra = $attrList[$key]['extra'];
            $type = $attrList[$key]['type'];

            // 枚举/多选/单选/布尔型
            if ('select' == $type || 'checkbox' == $type || 'radio' == $type || 'bool' == $type) {
                $options = parse_field_attr($extra);
                if ($options && array_key_exists($val, $options)) {
                    $data[$key] = $options[$val];
                }

            } elseif ('date' == $type) {
                //日期型
                $data[$key] = date('Y-m-d', $val);
            } elseif ('datetime' == $type) {
                //时间型
                $data[$key] = date('Y-m-d H:i', $val);
            }
        }

        $list[$k] = $data;
    }

    return $list;
}

// 分析枚举类型字段值 格式 a:名称1,b:名称2
// 暂时和 parse_config_attr功能相同
// 但请不要互相使用，后期会调整
function parse_field_attr($string)
{
    if (0 === strpos($string, ':')) {
        // 采用函数定义
        return eval('return ' . substr($string, 1) . ';');
    } elseif (0 === strpos($string, '[')) {
        // 支持读取配置参数（必须是数组类型）
        return C(substr($string, 1, -1));
    }

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

/**
 * 获取子属性信息
 * @param  integer $id 属性ID
 * @param  string $field 要获取的字段名
 * @return string         属性信息
 */
function get_attribute($model_id, $group = true, $fields = true)
{
    static $list;

    /* 非法ID */
    if (empty($model_id) || !is_numeric($model_id)) {
        return '';
    }

    /* 获取属性 */
    if (!isset($list[$model_id])) {
        $map = array('model_id' => $model_id);

        $info = M('Attribute')->where($map)->field($fields)->select();
        $list[$model_id] = $info;
    }

    $attr = array();
    if ($group) {
        foreach ($list[$model_id] as $value) {
            $attr[$value['id']] = $value;
        }
        $sort = M('Model')->getFieldById($model_id, 'field_sort');

        if (empty($sort)) {
            //未排序
            $group = array(1 => array_merge($attr));
        } else {
            $group = json_decode($sort, true);

            $keys = array_keys($group);
            foreach ($group as &$value) {
                foreach ($value as $key => $val) {
                    $value[$key] = $attr[$val];
                    unset($attr[$val]);
                }
            }

            if (!empty($attr)) {
                $group[$keys[0]] = array_merge($group[$keys[0]], $attr);
            }
        }
        $attr = $group;
    } else {
        foreach ($list[$model_id] as $value) {
            $attr[$value['name']] = $value;
        }
    }
    return $attr;
}

/**
 * 调用系统的API接口方法（静态方法）
 * api('User/getName','id=5'); 调用公共模块的User接口的getName方法
 * api('Admin/User/getName','id=5');  调用Admin模块的User接口
 * @param  string $name 格式 [模块名]/接口名/方法名
 * @param  array|string $vars 参数
 */
function api($name, $vars = array())
{
    $array = explode('/', $name);
    $method = array_pop($array);
    $classname = array_pop($array);
    $module = $array ? array_pop($array) : 'Common';
    $callback = $module . '\\Api\\' . $classname . 'Api::' . $method;
    if (is_string($vars)) {
        parse_str($vars, $vars);
    }
    return call_user_func_array($callback, $vars);
}

/**
 * 根据条件字段获取指定表的数据
 * @param mixed $value 条件，可用常量或者数组
 * @param string $condition 条件字段
 * @param string $field 需要返回的字段，不传则返回整个数据
 * @param string $table 需要查询的表
 * @author huajie <banhuajie@163.com>
 */
function get_table_field($value = null, $condition = 'id', $field = null, $table = null)
{
    if (empty($value) || empty($table)) {
        return false;
    }

    //拼接参数
    $map[$condition] = $value;
    $info = M(ucfirst($table))->where($map);
    if (empty($field)) {
        $info = $info->field(true)->find();
    } else {
        $info = $info->getField($field);
    }
    return $info;
}

/**
 * 获取链接信息
 * @param int $link_id
 * @param string $field
 * @return 完整的链接信息或者某一字段
 * @author huajie <banhuajie@163.com>
 */
function get_link($link_id = null, $field = 'url')
{
    $link = '';
    if (empty($link_id)) {
        return $link;
    }
    $link = M('Url')->getById($link_id);
    if (empty($field)) {
        return $link;
    } else {
        return $link[$field];
    }
}

/**
 * 描述：获取文档封面图片（缩略图和水印是一起生成的，目前只对图片服务器上图片起作用,要对其他服务器图片起作用，可以配置服务器rewrite规则）
 * 生成缩略图时,宽度和高度不能为空或者0(暂时没有支持传入水印logo，需要更改文件服务器上的程序，设置默认水印图片) 此处有需求后可以扩展
 * @param $cover_id
 * @param null $field
 * @param int $is_thumb 是否生成缩略图（0为不生成，1为生成缩略，2表示裁剪）
 * @param string $width 缩略图宽度 （支持直接大小和百分比）
 * @param string $height 缩略图高度（支持直接大小和百分比）
 * @param int $is_water 是否生成水印（1为生成，0为不生成）
 * @param string $mime生成缩略图格式
 * @return bool|string
 * Author:谭坚
 * Version:1.0.0
 * Modify Time:
 * Modify Author:
 */
function get_cover($cover_id, $field = null, $is_thumb = 0, $width = '100%', $height = '100%', $mime = 'jpg', $is_water = 0, $quality = 100)
{
    // 主题名
    $theme = C('DEFAULT_THEME') ? C('DEFAULT_THEME') : C('THEME');

    if (empty($cover_id)) {
        if ($field == 'path') {
            return C('PIC_HOST') . __ROOT__ . '/Public/Home/' . $theme . '/images/default1.png';
        } else {
            return false;
        }
    }
    $picture = M('Picture')->where(array('status' => 1))->getById($cover_id);
    if ($field == 'path') {
        //默认图
        if (empty($picture)) {
            //return __ROOT__. 'Public/Home/'. C('DEFAULT_THEME') . '/images/default' . rand(1,3) . '.jpg';
            return C('PIC_HOST') . __ROOT__ . '/Public/Home/' . $theme . '/images/default1.png';
        }
        //图片处理
        if (!empty($picture['url'])) {
            $picture['path'] = $picture['url'];
        } else {
            if (if_url($picture['path']) === true) {
                $picture['path'] = trim($picture['path']);
            } else {
                //$path = trim($picture['path']);
                // $picture['path'] = substr($path, 0, 8) == '/Uploads'
                //                                 ? __ROOT__.trim($picture['path'])
                //                                 : C('PIC_HOST') . __ROOT__.trim($picture['path']);
                $thumb_dir = '/thumb';
                //获取图片路径信息
                $pic_infos = pathinfo($picture['path']);
                $is_thumb = ($is_thumb && (strtoupper($pic_infos['extension']) == 'JPG' || strtoupper($pic_infos['extension']) == 'JPEG' || strtoupper($pic_infos['extension']) == 'PNG'));
                /*自动分配图片服务器地址*/
                if (C('PIC_HOST_MORE')) {
                    $path_sign = data_auth_sign($picture['path']);
                    $is_url = substr($path_sign, 0, 1);
                    $pic_count = count(C('PIC_HOST_MORE')) - 1;
                    if (!is_int($is_url) || $is_url > $pic_count) {
                        $is_url = ord($is_url) % $pic_count;
                    }
                    if ($is_thumb) {
                        $picture['path'] = C("PIC_HOST_MORE.$is_url") . __ROOT__ . $thumb_dir . trim($picture['path']);
                    } else {
                        $picture['path'] = C("PIC_HOST_MORE.$is_url") . __ROOT__ . trim($picture['path']);
                    }

                } else {

                    if ($is_thumb) {
                        $picture['path'] = C('PIC_HOST') . __ROOT__ . $thumb_dir . trim($picture['path']);
                    } else {
                        $picture['path'] = C('PIC_HOST') . __ROOT__ . trim($picture['path']);
                    }

                }
                if ($is_thumb) {
                    $picture['path'] = set_thumb_path($picture['path'], $width, $height, $is_water, $mime, $quality, $is_thumb);
                }
                //图片服务器生成缩略图

            }
        }
    }
    return empty($field) ? $picture : trim($picture[$field]);
}

function get_thumb($cover_id, $original = '', $size = '')
{
    $details = get_cover($cover_id, 'path');

    if (basename($details) == 'default1.png' && $original && $size) {
        $size = explode('x', $size);
        if (is_numeric($size[0]) && is_numeric($size[1])) {
            return get_cover($original, 'path', 2, $size[0], $size[1]);
        }
    } else {
        return $details;
    }
}

/**
 * 描述：获取图片真实地址
 * Author:谭坚
 * Version:1.0.0
 * Modify Time:
 * Modify Author:
 */
function get_pic_host($path)
{
    if (empty($path)) {
        return null;
    }

    if (strstr($path, 'http://') || strstr($path, 'ftp://')) {
        return $path;
    }

    if (substr($path, 0, 1) !== "/") {
        $path = "/" . $path;
    }

    if (C('PIC_HOST_MORE')) {
        $path_sign = data_auth_sign($path);
        $is_url = substr($path_sign, 0, 1);
        $pic_count = count(C('PIC_HOST_MORE')) - 1;
        if (!is_int($is_url) || $is_url > $pic_count) {
            $is_url = ord($is_url) % $pic_count;
        }
        $picture_path = C("PIC_HOST_MORE.$is_url") . trim($path);
    } else {
        $picture_path = C('PIC_HOST') . trim($path);
    }
    return $picture_path;
}

/**
 * 描述：获取生成缩略图地址（主要用于图片优化）
 * 水印，格式和质量暂时没有用（预留的）
 * 质量默认100
 * @param string $url 原始图片路径
 * @param string $width 缩略图宽度
 * @param string $height 缩略图高度
 * @param int $is_water 是否打水印
 * @return bool|string
 * Author:谭坚
 * Version:1.0.0
 * Modify Time:
 * Modify Author:
 */
function set_thumb_path($src = '', $width = '100%', $height = '100%', $is_water = 0, $mime = 'jpg', $quality = 100, $is_thumb = 1)
{
    if (empty($src)) {
        return false;
    }

    if (empty($width) || empty($height)) {
        return $src;
    }
    //缩略宽度或者高度为空，返回原来url
    if (strstr($width, '%') || strstr($height, '%')) {
        $image = new \Think\Image(); //实力化图片对象
        $image->open($src); //打开文件
        if ($w = strstr($width, '%', true)) {
            $width = intval($image->width() * $w / 100);
        }
        if ($h = strstr($height, '%', true)) {
            $height = intval($image->height() * $h / 100);
        }
    }
    $src_params = pathinfo($src); //获取文件路径信息
    $basename = basename($src, '.' . $src_params['extension']); //文件基本名称
    //文件全路径
    if ($is_thumb == 1) {
        $name = $src_params['dirname'] . DIRECTORY_SEPARATOR . $basename . '_' . $width . '_' . $height . ($is_water ? '_water' : '') . '.' . $src_params['extension'];
    } else {
        $name = $src_params['dirname'] . DIRECTORY_SEPARATOR . $basename . '_' . $width . '_' . $height . ($is_water ? '_water' : '') . '_crop.' . $src_params['extension'];
    }

    return $name;
}

/**
 * 判断是否URL地址，并是否自动补全
 * @param string $url
 * @param boolean $completion 是否补全
 * @return boolean
 * @author crohn <lllliuliu@163.com>
 */
function if_url($url, $completion = false)
{
    if (substr($url, 0, 7) === 'http://' || substr($url, 0, 8) === 'https://') {
        return true;
    } elseif ($completion) {
        return 'http://' . $url;
    } else {
        return false;
    }
}

/**
 * 检查$pos(推荐位的值)是否包含指定推荐位$contain
 * @param number $pos 推荐位的值
 * @param number $contain 指定推荐位
 * @return boolean true 包含 ， false 不包含
 * @author huajie <banhuajie@163.com>
 */
function check_document_position($pos = 0, $contain = 0)
{
    if (empty($pos) || empty($contain)) {
        return false;
    }

    //将两个参数进行按位与运算，不为0则表示$contain属于$pos
    $res = $pos & $contain;
    if ($res !== 0) {
        return true;
    } else {
        return false;
    }
}

/**
 * 获取数据的所有子孙数据的id值
 * @author 朱亚杰 <xcoolcc@gmail.com>
 */

function get_stemma($pids, Model &$model, $field = 'id')
{
    $collection = array();

    //非空判断
    if (empty($pids)) {
        return $collection;
    }

    if (is_array($pids)) {
        $pids = trim(implode(',', $pids), ',');
    }
    $result = $model->field($field)->where(array('pid' => array('IN', (string) $pids)))->select();
    $child_ids = array_column((array) $result, 'id');

    while (!empty($child_ids)) {
        $collection = array_merge($collection, $result);
        $result = $model->field($field)->where(array('pid' => array('IN', $child_ids)))->select();
        $child_ids = array_column((array) $result, 'id');
    }
    return $collection;
}

/**
 * 验证分类是否允许发布内容
 * @param  integer $id 分类ID
 * @param  string $model 模型名
 * @return boolean     true-允许发布内容，false-不允许发布内容
 */
function check_category($id, $model = 'Category')
{
    if (is_array($id)) {
        $type = get_category_by_model($id['category_id'], 'type', $model);
        $type = explode(",", $type);
        return in_array($id['type'], $type);
    } else {
        $publish = get_category_by_model($id, 'allow_publish', $model);
        return $publish ? true : false;
    }
}

/**
 * 检测分类是否绑定了指定模型
 * @param  array $info 模型ID和分类ID数组
 * @param  string $model 模型名
 * @return boolean     true-绑定了模型，false-未绑定模型
 */
function check_category_model($info, $model = 'Category')
{
    $cate = get_category_by_model($info['category_id'], $model);
    $array = explode(',', $info['pid'] ? $cate['model_sub'] : $cate['model']);
    return in_array($info['model_id'], $array);
}

/**
 * 获取字符串拼音第一个字母
 * @param string $str 字符串
 * @param boolean $all 是否返回所有首字母 默认返回第一个
 * @return string 一个字母
 * @author crohn <lllliuliu@163.com>
 */
function get_pinyin_first($str, $all = false)
{
    //实例化拼音类，位于Library下的OT目录，注意类文件必须为.class.php结尾
    $pinyin = new \OT\PinYin();
    $rs = $pinyin->getFirstPY($str);
    if ($rs === '' || $rs === false) {
        return '';
    }

    $rs = !$all ? substr($rs, 0, 1) : $rs;
    return $rs;
}

/**
 * 使用curl远程请求
 * @param string $url 地址
 * @param string $method 方式
 * @param string $data 数据
 * @param integer $timeout 超时时间，秒
 * @return string
 * @author crohn <lllliuliu@163.com>
 */
function request_by_curl($url, $method = 'get', $data = '', $timeout = 30)
{
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, false);
    if ($method == 'post') {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //如果把这行注释掉的话，就会直接输出
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout); //连接超时时间
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    // 状态不为200则重新访问，上限$depth crohn 2015-6-16
    for ($depth = 3; $depth > 0; $depth--) {
        if ($httpCode == '200') {
            break;
        }
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    }
    curl_close($ch);
    // $depth次数完成，还是非200返回空
    return $depth <= 0 ? -1 : $result;
}

/*********************
 * 前台公共库文件
 * 主要定义前台公共函数库
 **********************/
/**
 * 获取静态页面表数据
 * @param int $id
 * @return 完整的数据
 */
function get_staticpage($id)
{
    if (empty($id)) {
        return false;
    }

    return M('StaticPage')->getById($id);
}

/**
 * 基于配置显示多选单选数据对应的选项
 * @param integer 数据，KEY
 * @param string $field 字段名
 * @param boolean|array $checkbox ==false 不是多选  $checkbox==true 是多选,且显示全部选项 $checkbox==array('num'=>integer) 是多选,且显示指定数目选项  num-显示选项个数
 * @param string $module 模块名 默认当前模块
 * @return string
 */
function showText($key, $field, $checkbox = false, $module = MODULE_NAME)
{
    $pre = 'FIELD_' . strtoupper($module) . '_';
    if (!$field) {
        return '';
    }

    $config = C($pre . strtoupper($field));
    if (!is_array($config)) {
        return '';
    }

    if ($checkbox === false) {
        return $config[$key];
    } else {
        //位运算
        $rs = array();

        foreach ($config as $k => $v) {
            if ($k & $key) {
                $rs[] = $v;
            }
        }

        //显示数目
        $num = $checkbox['num'];
        if (!empty($num) && $num > 0) {
            return implode(',', array_slice($rs, 0, $num));
        } else {
            return implode(',', $rs);
        }
    }
}

/**
 * 基于配置显示数据格式化
 * @param integer 数据，KEY
 * @param string $config 配置名
 * @return string
 */
function showFormat($key, $config)
{
    $map = array(
        'card_type' => C('PACKAGE_CARD_TYPE'),
        'status' => array('1' => '可领取', '-1' => '不可领取'),
        'server_type' => C('PACKAGE_PARTICLE_SERVER_TYPE'),
    );
    if (!$map[$config]) {
        return;
    }

    $config = $map[$config];
    return $config[$key];
}

/**
 * 基于配置显示多选数据格式化
 * @param integer 数据，KEY
 * @param string $config 配置名
 * @param integer $num 显示数目，0表示全部
 * @return string
 */
function showCheckbox($key, $config, $num = 0)
{
    $map = array(
        'card_type' => C('PACKAGE_GAME_TYPE'),
        'conditions' => C('PACKAGE_CARD_CONDITIONS'),
    );

    if (!$map[$config]) {
        return;
    }

    //位运算
    $rs = array();
    foreach ($map[$config] as $k => $v) {
        if ($k & $key) {
            $rs[] = $v;
        }
    }

    if ($num > 0) {
        return implode(',', array_slice($rs, 0, $num));
    } else {
        return implode(',', $rs);
    }
}

/**
 * 显示卡总数
 * @param integer $id 数据id
 * @return string
 */
function showCardAll($id)
{
    if (!is_numeric($id)) {
        return null;
    }

    return M('Card')->where('did=' . $id)->count();
}

/**
 * 显示卡剩余数
 * @param integer $id 数据id
 * @return string
 */
function showCardSur($id)
{
    if (!is_numeric($id)) {
        return null;
    }

    $map = array(
        'did' => $id,
        'draw_status' => 0,
    );
    return M('Card')->where($map)->count();
}

/**
 * 获取静态生成后缀名
 * @return string
 */
function getStaticExt()
{
    $class = 'Common\\Library\\TempCreateLibrary';
    $staticInstance = new $class();
    return $staticInstance->ext;
}

/**
 * 根据主题获取widget文件前缀名
 * @param string $theme 主题名 默认配置中的默认主题
 * @return string
 */
function getWName($theme = null)
{
    $theme = $theme ? $theme : I('t');
    $theme = $theme ? $theme : C('DEFAULT_THEME');
    $theme = $theme ? $theme : C('THEME');
    if (is_numeric(substr($theme, 0, 1))) {
        return 'P' . strtolower($theme);
    } else {
        return ucfirst(strtolower($theme));
    }
}

/**
 * 获取当前主题的widget里面的SEO方法
 * @param array $args 参数
 * @param string $theme 主题名
 * @param string $name 默认SEO
 * @return string
 */
function WidgetSEO($args = array(), $theme = null, $name = 'SEO')
{
    $m = 'Home'; //widget文件默认所在模块

    return R($m . '/' . getWName($theme) . '/' . $name, $args, 'Widget');
}

/**
 * 获取当前主题的widget里面的Breadcrumb方法 （面包屑）
 * @param array $args 参数
 * @param null $theme 主题名
 * @param string $name 默认 Breadcrumb
 * @return mixed
 */
function getCrumbs($args = array(), $theme = null, $name = "Breadcrumb")
{
    $m = 'Home'; //widget文件默认所在模块
    return R($m . '/' . getWName($theme) . '/' . $name, $args, 'Widget');
}

/**
 * 生成静态URL地址
 * @param string $type 类型
 * @param integer $id 分类或者数据ID
 * @param string $model 模块名
 * @param string $page 分页显示传入页码
 * @param string $mobile 手机版
 * @return string
 */
function basestaticUrl($type, $id = null, $model = null, $page = null, $mobile = false, $hot = false)
{
    static $staticInstance;
    //默认模块参数配置
    $sturl = array(
        'Document' => array('model' => 'Document', 'module' => 'Document', 'category' => 'Category'),
        'Gallery' => array('model' => 'Gallery', 'module' => 'Gallery', 'category' => 'GalleryCategory'),
        'Package' => array('model' => 'Package', 'module' => 'Package', 'category' => 'PackageCategory'),
        'Down' => array('model' => 'Down', 'module' => 'Down', 'category' => 'DownCategory'),
        'Feature' => array('model' => 'Feature', 'module' => 'Feature', 'category' => 'FeatureCategory'),
    );
    if (!$model) {
        $t = MODULE_NAME;
        $params['model'] = $t;
        $params['module'] = $t;
        $params['category'] = ($t == 'Document') ? 'Category' : $t . 'Category';
    } else {
        $params = $sturl[ucfirst(strtolower($model))];
    }

    $s = md5(json_encode($params . $model));
    if ($mobile) {
        $s .= 'M';
    }

    if (!$staticInstance[$s]) {
        $class = 'Common\\Library\\TempCreateLibrary';
        $staticInstance[$s] = new $class($params);
        if ($mobile) {
            //获取手机版参数
            $config = C('MOBILE_' . strtoupper($model));
            $property = array(
                'the_lists_path' => $config['lists'],
                'the_detail_path' => $config['detail'],
                'the_moduleindex_path' => $config['index'],
                'theme' => C('MOBILE_THEME'),
            );
            //设置手机版固定属性
            $staticInstance[$s]->setProperty($property);
        }
    }

    switch (strtolower($type)) {
        case 'detail':
            if (!is_numeric($id)) {
                return;
            }

            $path = $staticInstance[$s]->getDetailUrl($id);
            break;
        case 'lists':
            if (!is_numeric($id)) {
                return;
            }

            $path = $staticInstance[$s]->getListsUrl($id, $page, $hot);
            //$path = str_replace('{page}', $page ? $page : 1 ,$path);
            break;
        case 'index':
            $path = $staticInstance[$s]->getIndexUrl();
            break;
    }
    return $path;
}

/**
 * 修改人：谭坚
 * 新增热门列表参数
 * 生成静态URL地址
 * @param string $type 类型
 * @param integer $id 分类或者数据ID
 * @param string $model 模块名
 * @param string $page 分页显示传入页码
 * @param string $mobile 手机版
 * @param string $hot 主要针对列表页，是否为最热列表
 * @return string
 */
function staticUrl($type, $id = null, $model = null, $page = null, $mobile = false, $hot = false)
{
    $url = basestaticUrl($type, $id, $model, $page, $mobile, $hot);
    return staticUrlRewrite($url, false);
}

/**
 * 手机版静态URL地址
 * @param string $type 类型
 * @param integer $id 分类或者数据ID
 * @param string $model 模块名
 * @param string $page 分页显示传入页码
 * @return string
 */
function staticUrlMobile($type, $id = null, $model = null, $page = null)
{
    $url = basestaticUrl($type, $id, $model, $page, true);
    return staticUrlRewrite($url, true);
}

/**
 * 描述：获取PC标签列表页路径
 * @param null $id
 * @param string $model
 * @param null $page
 * @return string
 * Author:谭坚
 * Version:1.0.0
 * Modify Time:
 * Modify Author:
 */
function staticTagsUrl($id = null, $model = 'Tags', $page = null, $is_mobile = false)
{
    if (is_numeric($id)) {
        $tagModelArray = array( //标签模型
            'Tags', 'ProductTags',
        );
        if (in_array($model, $tagModelArray)) {
            //实例化静态生成类
            $params = array(
                'static_url' => C('STATIC_CREATE_URL'),
            );
            $class = 'Common\\Library\\TempCreateLibrary';
            $staticInstance = new $class($params);
            $url = $staticInstance->getTagListsUrl($id, $model, $page, $is_mobile);
            return staticUrlRewrite($url, false);
        }
    }

}

/**
 * url地址根据配置rewrite
 * @param string $url 地址
 * @param boolean $mobile 是否为手机版
 * @return string
 */
function staticUrlRewrite($url, $mobile)
{
    if (empty($url)) {
        return '';
    }

    $preg = $mobile ? C('MOBILE_STATIC_URL_REWRITE') : C('STATIC_URL_REWRITE');
    $preg = is_array($preg) ? array_filter($preg) : $preg;
    if (empty($preg)) {
        return $url;
    }

    foreach ($preg as $key => $value) {
        $url = preg_replace($key, $value, $url);
    }
    return $url;
}

/**
 * Url地址规则化  Author 刘盼
 * @param string $url 地址
 * @return string
 */
function formatUrl($url)
{
    if (!preg_match("/^(http):/", $url)) {
        $url = 'http://' . $url;
    }
    if (isUrl($url) == '1') {
        return $url;
    } else {
        return "javascript:void(0)";
    }

}

/**
 * 解析专题挂件
 * @param string $url
 * @return array 挂件数组
 * @author crohn <lllliuliu@163.com>
 */
function widget($str)
{
    //parse_str('cat%5B%5D=8&cat%5B%5D=4&tag%5B%5D=6',$arrays);
    $str = html_entity_decode($str);
    parse_str($str, $arrays);
    $arrays['length'] = $arrays['length'] ? $arrays['length'] : 65535;
    if ($arrays['cat']) {
        $in = 'category_id in (\'' . implode('\',\'', $arrays['cat']) . '\')';
    }

    if ($arrays['tag']) {
        $tags = M('tags_map')->select();
        foreach ($tags as $k => $v) {
            if (in_array($v['tid'], $arrays['tag'])) {
                $maps[] = $v['did'];
            }

        }
        $in .= ' and id in (\'' . implode('\',\'', $maps) . '\')';
    }
    $results = M('down')->where($in)->select();
    return array_slice($results, intval($arrays['offset']), $arrays['length']);
}

/**
 * 创建目录
 * @param    string $path 路径
 * @param    string $mode 属性
 * @return    string    如果已经存在则返回true，否则为flase
 */
function dir_create($path, $mode = 0777)
{
    if (is_dir($path)) {
        return true;
    }

    $ftp_enable = 0;
    $path = str_replace('\\', '/', $path);
    if (substr($path, -1) != '/') {
        $path = $path . '/';
    }

    $temp = explode('/', $path);
    $cur_dir = '';
    $max = count($temp) - 1;
    for ($i = 0; $i < $max; $i++) {
        $cur_dir .= $temp[$i] . '/';
        if (@is_dir($cur_dir)) {
            continue;
        }

        @mkdir($cur_dir, 0777, true);
        @chmod($cur_dir, 0777);
    }
    return is_dir($path);
}

/**
 * Widget 路径获取
 * 作者  刘盼
 * @param    string $id 特殊页面ID
 * @param    boolean $if_url 是否生成url地址，生成url地址会加入host前缀
 * @param    boolean $suffix_url 前缀地址
 * @return    string
 */
function getWidgetPath($id, $if_url = false, $suffix_url = '')
{
    $info = M('StaticPage')->field('path,multipage_index,multipage')->where('id=' . $id)->find();
    if (empty($info)) {
        return '/';
    }

    //预定义字符替换
    $Path = str_replace('{page}', '1', $info['path']);

    //路径添加全站host
    // TO DO : 二级域名widget目前不支持，所有widget都为全站地址
    $SURL = C('STATIC_URL');
    if ($SURL && $if_url) {
        !empty($suffix_url) && $SURL = $suffix_url;
        $SURL = substr($SURL, -1) == '/' ? substr($SURL, 0, strrpos($SURL, '/')) : $SURL;
        $Path = $SURL . str_replace('//', '/', "/" . $Path);
    } else {
        $Path = str_replace('//', '/', "/" . $Path);
    }

    //路径处理
    if ($info['multipage_index'] && $info['multipage']) {
        //如果为多页而且生成了多页首页
        $Path = substr($Path, 0, strrpos($Path, '/') + 1);
        //如果为index不加入路径，使用web配置的默认页访问
        $Path = $info['multipage_index'] == 'index' ? $Path : $Path . $info['multipage_index'] . getStaticExt();
    } elseif (empty($info['multipage'])) {
        $suf = substr($Path, strrpos($Path, '/') + 1);
        $Path = substr($Path, 0, strrpos($Path, '/') + 1);
        $Path = $suf == 'index' ? $Path : $Path . $suf . getStaticExt();
    }

    return $Path;
}

/**
 * 厂商和专题 路径获取
 * 作者  刘盼
 * @param    string $id ID
 * @return    string
 */
function getCPath($id, $type)
{
    $type = strtolower($type);
    if (empty($type)) {
        return;
    }

    switch ($type) {
        case 'company':
            if (empty($id)) {
                return;
            }

            $getPath = M('Company')->where('id=' . $id)->getField('path');
            $getPath = str_replace("page_{page}", "", $getPath);
            break;
        case 'feature': //专题
            if (empty($id)) {
                return;
            }

            $getPath = C(FEATURE_ZT_DIR) . '/' . M('Feature')->where('id=' . $id)->getField('url_token');
            break;
        case 'special': //k页面
            if (empty($id)) {
                return;
            }

            $getPath = C(FEATURE_K_DIR) . '/' . M('Special')->where('id=' . $id)->getField('url_token');
            break;
        case 'batch': //专区
            if (empty($id)) {
                return;
            }

            $getPath = C(FEATURE_ZQ_DIR) . '/' . M('Batch')->where('id=' . $id)->getField('url_token');
            break;
    }
    if (substr($getPath, -1) != "/") {
        $getPath = $getPath . "/";
        return $getPath;
    }
    return $getPath;
}

/**
 * 判断是不是合法的URL链接
 * 作者  刘盼
 * @param    string $s URL
 * @return    bool
 */
function isUrl($s)
{
    return preg_match('/^http[s]?:\/\/' .
        '(([0-9]{1,3}\.){3}[0-9]{1,3}' . // IP形式的URL- 199.194.52.184
        '|' . // 允许IP和DOMAIN（域名）
        '([0-9a-z_!~*\'()-]+\.)*' . // 域名- www.
        '([0-9a-z][0-9a-z-]{0,61})?[0-9a-z]\.' . // 二级域名
        '[a-z]{2,6})' . // first level domain- .com or .museum
        '(:[0-9]{1,4})?' . // 端口- :80
        '((\/\?)|' . // a slash isn't required if there is no file name
        '(\/[0-9a-zA-Z_!~\'\(\)\[\]\.;\?:@&=\+\$,%#-\/^\*\|]*)?)$/', $s) == 1;
}

/**
 * 获取攻略专区链接
 * 作者  刘盼
 * @param    string $id 文章ID
 * @return    array
 */
function getRelationZone($id)
{
    $arr = get_base_by_tag($id, 'down', 'batch', 'product');
    if ($arr['url_token'] == "") {
        return $arr['url_token'];
    } else {
        return C(FEATURE_ZQ_DIR) . '/' . $arr['url_token'];
    }

    //

}

/**
 * 数组排序
 * 作者  刘盼
 * @param    array $multi_array 数组
 * @param    string $sort_key 排序项
 * @return    array
 */
function array_sort($multi_array, $sort_key, $sort = SORT_ASC)
{
    if (is_array($multi_array)) {
        foreach ($multi_array as $row_array) {
            if (is_array($row_array)) {
                $key_array[] = $row_array[$sort_key];
            } else {
                return false;
            }
        }
    } else {
        return false;
    }
    array_multisort($key_array, $sort, $multi_array);
    return $multi_array;
}

/**
 * 获取相关
 * 作者  刘盼
 * @param    string $id 文章ID
 * @return    array
 */
function getRelationArticle($id)
{
    $array = array();
    $relate = array();
    $article = array();
    $tags = M('TagsMap')->where("did='$id' AND type='document'")->select();
    foreach ($tags as $k => $val) {
        $array[] = M('Tags')->where('id=' . $val['tid'])->getField('id');
    }
    foreach ($array as $k => $val) {
        $relate[] = M('TagsMap')->where("tid='$val' AND type='document'")->getField('did');
    }
    $relate = array_unique($relate); //相同TAGS的文章ID
    foreach ($relate as $k => $val) {
        $article[] = M('Document')->where("id='$val'")->order('create_time')->limit(8)->select();
    }
    return array_multi2single($article);

}

function array_multi2single($array = null)
{
    $arr = array();
    foreach ($array as $value) {
        foreach ($value as $val) {
            $arr[] = $val;
        }
    }
    return $arr;

}

/**
 * 获取文章标签
 * 作者  刘盼
 * @param    string $id 文章ID
 * @return    array
 */
function getDownTag($id)
{
    // $id=!is_numeric($id)?'1':$id;
    $array = array();
    $tags = M('TagsMap')->where("did='$id' AND type='down'")->limit(2)->select();
    foreach ($tags as $k => $val) {
        $array[] = M('Tags')->where('id=' . $val['tid'])->getField('title');
    }
    $s = implode(',', $array);
    return $s;
}

/**
 * 获取文章标签
 * 作者  刘盼
 * @param    string $id 文章ID
 * @return    array
 */
function getArticleTag($id)
{
    $array = array();
    $tags = M('TagsMap')->where("did='$id' AND type='document'")->limit(20)->select();
    if ($tags) {
        $ids = array_column($tags, 'tid');
        $order = "FIELD(id," . join(',', $ids) . ")";
        $array = M('Tags')->where(array('id' => array('in', $ids)))->order($order)->select();
    }
    return $array;
}

/**
 * 面包屑导航
 * 作者  刘盼
 * @param    string $id 文章ID
 * @return    array
 */

function getCrumb($id, $module)
{
    $id = is_numeric($id) ? $id : '0';
    $module = strtolower($module);
    $module = empty($module) ? 'document' : $module;
    $cid = M('' . $module . '')->where('id = ' . $id)->getField('category_id');
    if ($module == 'down') {
        $category = M('DownCategory')->where('id = ' . $cid)->find();
    } else {
        $category = M('Category')->where('id = ' . $cid)->find();
    }

    $categoryTitle = $category['title'];
    $categoryUrl = $category['path_lists'];
    $categoryUrl = str_replace("{category_id}_{page}", $cid . "_1.html", $categoryUrl);
    $categoryUrl = str_replace("page_{page}", "page_1.html", $categoryUrl);
    $array['title'] = $categoryTitle;
    $array['url'] = $categoryUrl;
    return $array;

}

/**
 * 获取前一篇文章
 * 作者  刘盼
 * @param    string $id 文章ID
 * @return
 */
function getPrivLink($id)
{
    $priv = M('Document')->alias('a')->join('onethink_document_article b on a.id=b.id')->where('a.id < ' . $id)->order('a.id desc')->find();
    return $priv;
}

/**
 * 获取后一篇文章
 * 作者  刘盼
 * @param    string $id 文章ID
 * @return
 */
function getNextLink($id)
{
    $next = M('Document')->alias('a')->join('onethink_document_article b on a.id=b.id')->where('a.id > ' . $id)->order('a.id asc')->find();
    return $next;

}

/**
 * 获取下载地址 (谭坚 修改于 2015-7-21 完善功能,调用公共函数formatAddress)
 * Author : Jeffrey Lau
 * Edit :   2015-7-8 14:51:18
 * @param   string $id ID
 * @
 * return  string
 */
function getFileUrl($id)
{
    if (empty($id)) {
        return;
    }
    $address = M('DownAddress')->where('did=' . $id)->find();
    $site_id = $address['site_id'];
    $url = $address['url'];
    if (is_numeric($site_id)) {
        $url = formatAddress($url, $site_id);
    }
    return $url;
}

/**
 * KB TO MB
 * 作者  刘盼
 * @param    string $kb
 * @return   string
 */

function kbToMb($size)
{
    if (empty($size)) {
        return;
    }
    return round($size / 1024);
}

function get_rand($proArr)
{
    $result = '';
    //概率数组的总概率精度
    $proSum = array_sum($proArr);
    //概率数组循环
    foreach ($proArr as $key => $proCur) {
        $randNum = mt_rand(1, $proSum); //抽取随机数
        if ($randNum <= $proCur) {
            $result = $key; //得出结果
            break;
        } else {
            $proSum -= $proCur;
        }
    }
    unset($proArr);
    return $result;
}

/**
 * 二维码生成
 * 作者  刘盼
 * @param    string $str
 */

function builtQrcode($str)
{
    vendor("phpqrcode.phpqrcode");
    $str = empty($str) ? '下载地址不存在' : $str;
    $level = 'L';
    $size = 4;
    $name = md5($str);
    $filename = 'Static/qrcode/' . $name . '.png';
    if (!file_exists('Static/qrcode/')) {
        mkdir('Static/qrcode/', 0777);
    }
    QRcode::png($str, $filename, $level, $size, 2);
    return __ROOT__ . '/' . 'qrcode/' . $name . '.png';
}

/**
 * 获取分类地址
 * 作者  刘盼
 * @param    string $id
 */

function getCateUrl($id, $module)
{
    if (empty($id)) {
        return;
    }
    $module = strtolower($module);
    switch ($module) {
        case 'document':
            $cid = M('Document')->where('id=' . $id)->getField('category_id');
            $url = staticUrl('lists', $cid, 'Document');
            $url = empty($url) ? '' : $url;
            break;
        case 'down':
            $cid = M('Down')->where('id=' . $id)->getField('category_id');
            $url = staticUrl('lists', $cid, 'Document');
            $url = empty($url) ? '' : $url;
            break;

    }

    return $url;

}

/**
 * 获取分类名
 * @param    string $id
 */

function getCateName($id, $module)
{
    if (empty($id)) {
        return;
    }
    $module = strtolower($module);
    switch ($module) {
        case 'document':
            $cid = M('Document')->where('id=' . $id)->getField('category_id');
            $name = M('Category')->where('id=' . $cid)->getField('title');
            $name = empty($name) ? '未定义分类' : $name;
            break;
        case 'down':
            $cid = M('Down')->where('id=' . $id)->getField('category_id');
            $name = M('DownCategory')->where('id=' . $cid)->getField('title');
            $name = empty($name) ? '未定义分类' : $name;
            break;
        case 'package':
            $cid = M('Package')->where('id=' . $id)->getField('category_id');
            $name = M('PackageCategory')->where('id=' . $cid)->getField('title');
            $name = empty($name) ? '未定义分类' : $name;
            break;
    }

    return $name;

}

/**
 * 获取下载分类名
 * @param    string $id
 */

function getCateId($id, $module)
{
    if (empty($id)) {
        return;
    }
    $module = strtolower($module);
    switch ($module) {
        case 'document':
            $cid = M('Document')->where('id=' . $id)->getField('category_id');
            $name = empty($cid) ? '未定义ID' : $cid;
            break;
        case 'down':
            $cid = M('Down')->where('id=' . $id)->getField('category_id');
            $name = empty($cid) ? '未定义ID' : $cid;
            break;

    }

    return $name;

}

/**
 * 数组排序
 * 作者  刘盼
 * @param    array $multi_array
 * @param    astring $sort_key
 * @param    string  SORT_ASC,SORT_DESC
 */
function multi_array_sort($multi_array, $sort_key, $sort = SORT_ASC)
{
    if (is_array($multi_array)) {
        foreach ($multi_array as $row_array) {
            if (is_array($row_array)) {
                $key_array[] = $row_array[$sort_key];
            } else {
                return false;
            }
        }
    } else {
        return false;
    }
    array_multisort($key_array, $sort, $multi_array);
    return $multi_array;
}

/**
 * 获取移动端标签列表地址
 * 作者  刘盼
 * @param    string $id
 */
function mobileTagUrl($name)
{
    if (empty($name)) {
        return;
    }
    return C('MOBILE_STATIC_URL') . "/tag/" . $name . "/";

}

/**
 * 获取礼包类型
 * 作者  刘盼
 * @param    string $id
 */

function getPackageType($id)
{
    if (empty($id)) {
        return;
    }
    $name = M('PackageCategory')->where('id=' . $id)->getField('title');
    if (empty($name)) {
        return "";
    } else {
        return $name;
    }

}

/**
 * 统计指定类型的时间格式
 * 作者  刘盼
 * @param    string $str
 */

function specialDate($str)
{
    if (empty($str)) {
        return;
    }
    return date('Y/m/d', $str);
}

/**
 * 根据下载ID返回平台
 * 作者  刘盼
 * @param    string $str
 */

function getSystem($id)
{
    if (empty($id)) {
        return;
    }
    $d = M('DownDmain')->where("id='$id'")->getField('system');
    switch ($d) {
        case '1':
            return "安卓";
            exit();
        case '2':
            return "IOS";
            exit();
    }

}

/**
 * 根据ID返回语言
 * 作者  刘盼
 * @param    string $str
 */
function getLanguage($id, $type = '')
{
    if (empty($id)) {
        return;
    }

    if ($type == 'soft') {
        $d = M('down_dsoft')->where("id='$id'")->getField('language');
    } else {
        $d = M('DownDmain')->where("id='$id'")->getField('language');
    }
    $language = "";

    switch ($d) {
        case '1':
            return "简体";
            exit();
        case '2':
            return "繁体";
            exit();
        case '3':
            return "英文";
            exit();
        case '4':
            return "多国语言[中文]";
            exit();
        case '5':
            return "日文";
            exit();
        case '6':
            return "其他";
            exit();
    }
}

/*
[{'id':'down__product_tags','title':'下载产品标签'},
{'id':'down__tags','title':'下载标签'},
{'id':'down__down_category','title':'下载分类'},
{'id':'feature__product_tags','title':'专题产品标签'},
{'id':'feature__tags','title':'专题标签'},
{'id':'special__product_tags','title':'K页面产品标签'},
{'id':'special__tags','title':'K页面产品标签'},
{'id':'batch__product_tags','title':'专区产品标签'},
{'id':'batch__tags','title':'专区标签'}]
 * 作者 周良才
 * @leha.com
 */
function checked($item)
{
    $item = str_replace('&amp;', '&', $item);
    parse_str($item, $items);

    $results = array();
    foreach ($items as $k => &$v) {
        //echo $k,'===';
        $in = implode('\',\'', $v);
        $temps = R('Feature/' . $k, array('in' => $in)); //M($tables[0])->where($tables[1].' in (\''.$in.'\')')->select();
        $temps && $results = array_merge($results, $temps);
    }

    return $results;
}

/**
 * 防止XSS攻击函数
 * @param string $val 需要过滤字符串
 * @return string
 */
function remove_xss($val)
{
    // remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed
    // this prevents some character re-spacing such as <java\0script>
    // note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs
    $val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val);
    // straight replacements, the user should never need these since they're normal characters
    // this prevents like <IMG SRC=@avascript:alert('XSS')>
    $search = 'abcdefghijklmnopqrstuvwxyz';
    $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $search .= '1234567890!@#$%^&*()';
    $search .= '~`";:?+/={}[]-_|\'\\';
    for ($i = 0; $i < strlen($search); $i++) {
        // ;? matches the ;, which is optional
        // 0{0,7} matches any padded zeros, which are optional and go up to 8 chars
        // @ @ search for the hex values
        $val = preg_replace('/(&#[xX]0{0,8}' . dechex(ord($search[$i])) . ';?)/i', $search[$i], $val); // with a ;
        // @ @ 0{0,7} matches '0' zero to seven times
        $val = preg_replace('/(�{0,8}' . ord($search[$i]) . ';?)/', $search[$i], $val); // with a ;
    }
    // now the only remaining whitespace attacks are \t, \n, and \r
    $ra1 = array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
    $ra2 = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
    $ra = array_merge($ra1, $ra2);
    $found = true; // keep replacing as long as the previous round replaced something
    while ($found == true) {
        $val_before = $val;
        for ($i = 0; $i < sizeof($ra); $i++) {
            $pattern = '/';
            for ($j = 0; $j < strlen($ra[$i]); $j++) {
                if ($j > 0) {
                    $pattern .= '(';
                    $pattern .= '(&#[xX]0{0,8}([9ab]);)';
                    $pattern .= '|';
                    $pattern .= '|(�{0,8}([9|10|13]);)';
                    $pattern .= ')*';
                }
                $pattern .= $ra[$i][$j];
            }
            $pattern .= '/i';
            $replacement = substr($ra[$i], 0, 2) . '<x>' . substr($ra[$i], 2); // add in <> to nerf the tag
            $val = preg_replace($pattern, $replacement, $val); // filter out the hex tags
            if ($val_before == $val) {
                // no replacements were made, so exit the loop
                $found = false;
            }
        }
    }
    return $val;
}

/**
 * @Author 肖书成 (谭坚 修改于 2015-7-21 完善功能)
 * @createTime 2015/3/11
 * @Comments 下载地址容错处理
 * @param string $url
 * @param int $sit_id
 * @return string $url
 */
function formatAddress($url, $sit_id)
{
    if (empty($url)) {
        return null;
    }

    if (is_numeric($sit_id) && strtolower(substr($url, 0, 8)) != "https://" && strtolower(substr($url, 0, 7)) != "http://" && strtolower(substr($url, 0, 6)) != "ftp://" && strtolower(substr($url, 0, 16) != "itms-services://")) {
        if (substr($url, 0, 1) == '/') {
            $url = substr($url, 1);
        }

        $down = M('presetSite')->where('id =' . $sit_id)->getField('site_url');
        if (strtolower(substr($down, 0, 7)) != "http://" && strtolower(substr($down, 0, 8)) != "https://" && strtolower(substr($down, 0, 6)) != "ftp://" && strtolower(substr($down, 0, 16) != "itms-services://")) {
            $down = "http://" . $down;
        }

        if (substr($down, -1, 1) != '/') {
            $down = $down . '/';
        }

        $url = $down . $url;
    }
    return $url;
}

/**
 * 是否启用专区详情页
 * @param integer $id 数据ID
 * @param string $model 模型类型
 * @return boolean | integer
 */
function ifBatchDetail($id, $model = 'document')
{
    $temp = C('FEATURE_ZQ_D_DTEMP');
    if (empty($temp)) {
        return false;
    }

    if (!is_numeric($id)) {
        return false;
    }

    $map = array(
        'type' => $model,
        'did' => $id,
    );
    $tid = M('ProductTagsMap')->where($map)->getField('tid');
    if (!$tid) {
        return false;
    }

    // 含有产品标签的专区首页
    $batch_map = array(
        'm.type' => 'batch',
        'm.tid' => $tid,
        'b.pid' => 0,
    );
    $rs = M('ProductTagsMap')->alias('m')->join('onethink_batch b on m.did=b.id')->where($batch_map)->getField('did');
    return $rs;
}

/**
 * @Author 肖书成
 * @createTime 2015/3/11
 * @Comments 用来获取Common模块Widget控制器里的方法
 * @param $method
 * @param $arr
 * @return mixed
 */
function commonWidget($method, $arr)
{
    return R('Common/Widget/' . $method, $arr, 'Controller');
}

/**
 * ajax请求返回数据格式化
 *
 * 这个方法挺二逼的，希望以后可以完整地把统一实现
 * @param array $data 返回的数据
 * @param string $message 消息
 * @param int $status_code 状态码 1成功，0失败
 *
 * @return array
 */
function ajax_return_data_format(array $data, $message = '操作成功', $status_code = 1)
{
    $message = $status_code ? $message : '操作失败';
    $_tmp_array = array('data' => $data, 'message' => $message, 'status_code' => $status_code);

    return $_tmp_array;
}

/**
 * 代码调试
 */
function p()
{
    header("Content-type:text/html;charset=utf-8");
    $argc = func_get_args();
    echo '<pre>';
    foreach ($argc as $var) {
        print_r($var);
        echo '<br/>';
    }
    echo '</pre>';
    exit;
    return;
}

/**
 * 代码调试
 */
function vd()
{
    header("Content-type:text/html;charset=utf-8");
    $argc = func_get_args();
    foreach ($argc as $var) {
        var_dump($var);
        echo '<br/>';
    }
    exit;
    return;
}

/**
 * 判断手机操作系统
 * @param true ,模版调用，false,代码处理
 * @return string
 */
function get_device_type($type = false)
{
    $agent = $_SERVER['HTTP_USER_AGENT'];
    if (stripos($agent, "iPhone") || strpos($agent, "iPad")) {
        $platform = 'iPhone';
        $where = '&&system=2';
    } else if (stripos($agent, "Android")) {
        $platform = 'Android';
        $where = '&&system=1';
    } else {
        $platform = '';
        $where = '';
    }
    if ($type) {
        return $platform;
    } else {
        return $where;
    }

}

/**@author 肖书成
 * @comment 用来替换字符串中的目标字符串，只替换一次
 * @param $needle
 * @param $replace
 * @param $haystack
 * @return mixed
 */
function str_replace_once($needle, $replace, $haystack)
{

    $pos = strpos($haystack, $needle);

    if ($pos === false) {
        return $haystack;
    }
    return substr_replace($haystack, $replace, $pos, strlen($needle));
}

/**
 * @author  肖书成
 * @comment 用来获取随机数据的条件（只能获取单个表的数据）
 * @param string $model 模型
 * @param int $row 行数
 * @param string $field 字段
 * @return string
 */

function rand_where($model, $row = 1, $where = 1, $field = 'id')
{
    if (empty($model)) {
        return;
    }

    $model = C('DB_PREFIX') . strtolower($model);
    $num = M()->query("select (((SELECT min($field) FROM (SELECT $field FROM $model WHERE $where order by $field DESC limit $row) as rtg) - (SELECT MIN($field) FROM $model WHERE $where))*RAND() + (SELECT MIN($field) FROM $model WHERE $where)) k");
    return "$field >= " . round($num[0]['k']) . ' AND ' . $where;
}

/**
 * 根据用户输入的Email跳转到相应的电子邮箱首
 * @return string
 */
function gotoMail($mail)
{
    $t = explode('@', $mail);
    $t = strtolower($t[1]);
    if ($t == '163.com') {
        return 'mail.163.com';
    } else if ($t == 'vip.163.com') {
        return 'vip.163.com';
    } else if ($t == '126.com') {
        return 'mail.126.com';
    } else if ($t == 'qq.com' || $t == 'vip.qq.com' || $t == 'foxmail.com') {
        return 'mail.qq.com';
    } else if ($t == 'gmail.com') {
        return 'mail.google.com';
    } else if ($t == 'sohu.com') {
        return 'mail.sohu.com';
    } else if ($t == 'tom.com') {
        return 'mail.tom.com';
    } else if ($t == 'vip.sina.com') {
        return 'vip.sina.com';
    } else if ($t == 'sina.com.cn' || $t == 'sina.com') {
        return 'mail.sina.com.cn';
    } else if ($t == 'tom.com') {
        return 'mail.tom.com';
    } else if ($t == 'yahoo.com.cn' || $t == 'yahoo.cn') {
        return 'mail.cn.yahoo.com';
    } else if ($t == 'tom.com') {
        return 'mail.tom.com';
    } else if ($t == 'yeah.net') {
        return 'www.yeah.net';
    } else if ($t == '21cn.com') {
        return 'mail.21cn.com';
    } else if ($t == 'hotmail.com') {
        return 'www.hotmail.com';
    } else if ($t == 'sogou.com') {
        return 'mail.sogou.com';
    } else if ($t == '188.com') {
        return 'www.188.com';
    } else if ($t == '139.com') {
        return 'mail.10086.cn';
    } else if ($t == '189.cn') {
        return 'webmail15.189.cn/webmail';
    } else if ($t == 'wo.com.cn') {
        return 'mail.wo.com.cn/smsmail';
    } else if ($t == '139.com') {
        return 'mail.10086.cn';
    } else {
        return '';
    }
}

/**
 * 根据分类Id获取当前分类所有父级分类
 *
 * @author: liujun
 * @param int $category_id 当前分类Id
 * @param string $table 数据表名
 *
 * @return array
 */
function getParentCategory($category_id = 0, $table = 'PackageCategory')
{
    $result = array();
    $where = array();
    $where['status'] = 1;
    $where['id'] = $category_id;
    $cateInfo = M($table)->field(array('id', 'name', 'title', 'pid', 'path_lists'))->where($where)->find();
    if ($cateInfo) {
        $result[] = $cateInfo;
        if (intval($cateInfo['pid']) > 0) {
            $result = array_merge(getParentCategory($cateInfo['pid'], $table), $result);
        }
    }
    return $result;
}

/**
 * 获取省份记录
 * @date: 2015-5-20
 * @author: liujun
 * @param int $province_id 省份Id
 */
function getProvince($province_id = 0)
{
    if ($province_id > 0) {
        $province = M('Province')->where(array('id' => $province_id))->find();
    } else {
        $province = M('Province')->select();
    }
    return $province;
}

/**
 * 获取城市记录
 * @date: 2015-5-20
 * @author: liujun
 * @param int $city_id 城市Id
 */
function getCity($city_id = 0)
{
    if ($city_id > 0) {
        $city = M('City')->where(array('id' => $city_id))->find();
    } else {
        $city = M('City')->select();
    }
    return $city;
}

/**
 * 获取区域记录
 * @date: 2015-5-20
 * @author: liujun
 * @param int $area_id 区域Id
 */
function getArea($area_id = 0)
{
    if ($area_id > 0) {
        $area = M('Area')->where(array('id' => $area_id))->find();
    } else {
        $area = M('Area')->select();
    }
    return $area;
}

/**
 * 创建地方网站大全静态文件地址
 * @date: 2015-5-14
 * @author: liujun
 */
function createdPlacePath($pinyin, $category_id)
{
    //处理省份静态地址
    $path_province = C('PLACE_PROVINCE_STATIC_PATH');
    $path_province = str_replace('{pinyin}', $pinyin, $path_province);
    $dir = C('STATIC_ROOT') . '/' . $path_province;
    $dir = substr($dir, 0, strrpos($dir, '/'));
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $path = $path_province;

    //省份和分类静态地址
    $path_cate = C('PLACE_CATE_STATIC_PATH');
    $path_cate = str_replace('{pinyin}', $pinyin, $path_cate);
    $path_cate = str_replace('{category_id}', $category_id, $path_cate);
    $dir = C('STATIC_ROOT') . '/' . $path_cate;
    $dir = substr($dir, 0, strrpos($dir, '/'));
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

}

/**
 * 格式化分类数据
 *
 * @param     $cateArray
 * @param int $id
 *
 * @return mixed
 */
function sort_cate_data($cateArray, $id = 0)
{
    static $formatCat = array();
    static $floor = 0;

    foreach ($cateArray as $key => $val) {
        if ($val['pid'] == $id) {
            $val['title'] = $val['title'];

            $val['floor'] = $floor;
            $formatCat[$val['id']] = $val;

            unset($cateArray[$key]);

            $floor++;
            sort_cate_data($cateArray, $val['id']);
            $floor--;
        }
    }
    return $formatCat;
}

/**
 * 获取中文拼音
 *
 * @param        $_String
 * @param string $_Code
 *
 * @return mixed
 */
function get_pinyin($_String, $_Code = 'UTF8')
{
    $_DataKey = "a|ai|an|ang|ao|ba|bai|ban|bang|bao|bei|ben|beng|bi|bian|biao|bie|bin|bing|bo|bu|ca|cai|can|cang|cao|ce|ceng|cha" .
        "|chai|chan|chang|chao|che|chen|cheng|chi|chong|chou|chu|chuai|chuan|chuang|chui|chun|chuo|ci|cong|cou|cu|" .
        "cuan|cui|cun|cuo|da|dai|dan|dang|dao|de|deng|di|dian|diao|die|ding|diu|dong|dou|du|duan|dui|dun|duo|e|en|er" .
        "|fa|fan|fang|fei|fen|feng|fo|fou|fu|ga|gai|gan|gang|gao|ge|gei|gen|geng|gong|gou|gu|gua|guai|guan|guang|gui" .
        "|gun|guo|ha|hai|han|hang|hao|he|hei|hen|heng|hong|hou|hu|hua|huai|huan|huang|hui|hun|huo|ji|jia|jian|jiang" .
        "|jiao|jie|jin|jing|jiong|jiu|ju|juan|jue|jun|ka|kai|kan|kang|kao|ke|ken|keng|kong|kou|ku|kua|kuai|kuan|kuang" .
        "|kui|kun|kuo|la|lai|lan|lang|lao|le|lei|leng|li|lia|lian|liang|liao|lie|lin|ling|liu|long|lou|lu|lv|luan|lue" .
        "|lun|luo|ma|mai|man|mang|mao|me|mei|men|meng|mi|mian|miao|mie|min|ming|miu|mo|mou|mu|na|nai|nan|nang|nao|ne" .
        "|nei|nen|neng|ni|nian|niang|niao|nie|nin|ning|niu|nong|nu|nv|nuan|nue|nuo|o|ou|pa|pai|pan|pang|pao|pei|pen" .
        "|peng|pi|pian|piao|pie|pin|ping|po|pu|qi|qia|qian|qiang|qiao|qie|qin|qing|qiong|qiu|qu|quan|que|qun|ran|rang" .
        "|rao|re|ren|reng|ri|rong|rou|ru|ruan|rui|run|ruo|sa|sai|san|sang|sao|se|sen|seng|sha|shai|shan|shang|shao|" .
        "she|shen|sheng|shi|shou|shu|shua|shuai|shuan|shuang|shui|shun|shuo|si|song|sou|su|suan|sui|sun|suo|ta|tai|" .
        "tan|tang|tao|te|teng|ti|tian|tiao|tie|ting|tong|tou|tu|tuan|tui|tun|tuo|wa|wai|wan|wang|wei|wen|weng|wo|wu" .
        "|xi|xia|xian|xiang|xiao|xie|xin|xing|xiong|xiu|xu|xuan|xue|xun|ya|yan|yang|yao|ye|yi|yin|ying|yo|yong|you" .
        "|yu|yuan|yue|yun|za|zai|zan|zang|zao|ze|zei|zen|zeng|zha|zhai|zhan|zhang|zhao|zhe|zhen|zheng|zhi|zhong|" .
        "zhou|zhu|zhua|zhuai|zhuan|zhuang|zhui|zhun|zhuo|zi|zong|zou|zu|zuan|zui|zun|zuo";
    $_DataValue = "-20319|-20317|-20304|-20295|-20292|-20283|-20265|-20257|-20242|-20230|-20051|-20036|-20032|-20026|-20002|-19990" .
        "|-19986|-19982|-19976|-19805|-19784|-19775|-19774|-19763|-19756|-19751|-19746|-19741|-19739|-19728|-19725" .
        "|-19715|-19540|-19531|-19525|-19515|-19500|-19484|-19479|-19467|-19289|-19288|-19281|-19275|-19270|-19263" .
        "|-19261|-19249|-19243|-19242|-19238|-19235|-19227|-19224|-19218|-19212|-19038|-19023|-19018|-19006|-19003" .
        "|-18996|-18977|-18961|-18952|-18783|-18774|-18773|-18763|-18756|-18741|-18735|-18731|-18722|-18710|-18697" .
        "|-18696|-18526|-18518|-18501|-18490|-18478|-18463|-18448|-18447|-18446|-18239|-18237|-18231|-18220|-18211" .
        "|-18201|-18184|-18183|-18181|-18012|-17997|-17988|-17970|-17964|-17961|-17950|-17947|-17931|-17928|-17922" .
        "|-17759|-17752|-17733|-17730|-17721|-17703|-17701|-17697|-17692|-17683|-17676|-17496|-17487|-17482|-17468" .
        "|-17454|-17433|-17427|-17417|-17202|-17185|-16983|-16970|-16942|-16915|-16733|-16708|-16706|-16689|-16664" .
        "|-16657|-16647|-16474|-16470|-16465|-16459|-16452|-16448|-16433|-16429|-16427|-16423|-16419|-16412|-16407" .
        "|-16403|-16401|-16393|-16220|-16216|-16212|-16205|-16202|-16187|-16180|-16171|-16169|-16158|-16155|-15959" .
        "|-15958|-15944|-15933|-15920|-15915|-15903|-15889|-15878|-15707|-15701|-15681|-15667|-15661|-15659|-15652" .
        "|-15640|-15631|-15625|-15454|-15448|-15436|-15435|-15419|-15416|-15408|-15394|-15385|-15377|-15375|-15369" .
        "|-15363|-15362|-15183|-15180|-15165|-15158|-15153|-15150|-15149|-15144|-15143|-15141|-15140|-15139|-15128" .
        "|-15121|-15119|-15117|-15110|-15109|-14941|-14937|-14933|-14930|-14929|-14928|-14926|-14922|-14921|-14914" .
        "|-14908|-14902|-14894|-14889|-14882|-14873|-14871|-14857|-14678|-14674|-14670|-14668|-14663|-14654|-14645" .
        "|-14630|-14594|-14429|-14407|-14399|-14384|-14379|-14368|-14355|-14353|-14345|-14170|-14159|-14151|-14149" .
        "|-14145|-14140|-14137|-14135|-14125|-14123|-14122|-14112|-14109|-14099|-14097|-14094|-14092|-14090|-14087" .
        "|-14083|-13917|-13914|-13910|-13907|-13906|-13905|-13896|-13894|-13878|-13870|-13859|-13847|-13831|-13658" .
        "|-13611|-13601|-13406|-13404|-13400|-13398|-13395|-13391|-13387|-13383|-13367|-13359|-13356|-13343|-13340" .
        "|-13329|-13326|-13318|-13147|-13138|-13120|-13107|-13096|-13095|-13091|-13076|-13068|-13063|-13060|-12888" .
        "|-12875|-12871|-12860|-12858|-12852|-12849|-12838|-12831|-12829|-12812|-12802|-12607|-12597|-12594|-12585" .
        "|-12556|-12359|-12346|-12320|-12300|-12120|-12099|-12089|-12074|-12067|-12058|-12039|-11867|-11861|-11847" .
        "|-11831|-11798|-11781|-11604|-11589|-11536|-11358|-11340|-11339|-11324|-11303|-11097|-11077|-11067|-11055" .
        "|-11052|-11045|-11041|-11038|-11024|-11020|-11019|-11018|-11014|-10838|-10832|-10815|-10800|-10790|-10780" .
        "|-10764|-10587|-10544|-10533|-10519|-10331|-10329|-10328|-10322|-10315|-10309|-10307|-10296|-10281|-10274" .
        "|-10270|-10262|-10260|-10256|-10254";
    $_TDataKey = explode('|', $_DataKey);
    $_TDataValue = explode('|', $_DataValue);
    $_Data = array_combine($_TDataKey, $_TDataValue);
    arsort($_Data);
    reset($_Data);
    if ($_Code != 'gb2312') {
        $_String = _U2_Utf8_Gb($_String);
    }

    $_Res = '';
    for ($i = 0; $i < strlen($_String); $i++) {
        $_P = ord(substr($_String, $i, 1));
        if ($_P > 160) {
            $_Q = ord(substr($_String, ++$i, 1));
            $_P = $_P * 256 + $_Q - 65536;
        }
        $_Res .= _Pinyin($_P, $_Data);
    }

    return preg_replace("/[^a-z0-9]*/", '', $_Res);
}

function _Pinyin($_Num, $_Data)
{
    if ($_Num > 0 && $_Num < 160) {
        return chr($_Num);
    } elseif ($_Num < -20319 || $_Num > -10247) {
        return '';
    } else {
        foreach ($_Data as $k => $v) {
            if ($v <= $_Num) {
                break;
            }

        }

        return $k;
    }
}

function _U2_Utf8_Gb($_C)
{
    $_String = '';
    if ($_C < 0x80) {
        $_String .= $_C;
    } elseif ($_C < 0x800) {
        $_String .= chr(0xC0 | $_C >> 6);
        $_String .= chr(0x80 | $_C & 0x3F);
    } elseif ($_C < 0x10000) {
        $_String .= chr(0xE0 | $_C >> 12);
        $_String .= chr(0x80 | $_C >> 6 & 0x3F);
        $_String .= chr(0x80 | $_C & 0x3F);
    } elseif ($_C < 0x200000) {
        $_String .= chr(0xF0 | $_C >> 18);
        $_String .= chr(0x80 | $_C >> 12 & 0x3F);
        $_String .= chr(0x80 | $_C >> 6 & 0x3F);
        $_String .= chr(0x80 | $_C & 0x3F);
    }

    return iconv('UTF-8', 'GB2312', $_String);
}

function guid()
{
    if (function_exists('com_create_guid')) {
        return com_create_guid();
    } else {
        mt_srand((double) microtime() * 10000); //optional for php 4.2.0 and up.
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45); // "-"
        $uuid = chr(123) // "{"
         . substr($charid, 0, 8) . $hyphen
        . substr($charid, 8, 4) . $hyphen
        . substr($charid, 12, 4) . $hyphen
        . substr($charid, 16, 4) . $hyphen
        . substr($charid, 20, 12)
        . chr(125); // "}"
        return $uuid;
    }
}

/**
 * 检测动态重写规则
 *
 * @return bool
 */
function check_dynamic_rewrite()
{
    $dr = C('DYNAMIC_REWRITE');
    $drp = C('DYNAMIC_REWRITE_PROJECT');

    $name_rule = '_dynamic_rewrite_rule';
    $dynamic_rewrite_rule_class = $drp . $name_rule;
    $dynamic_rewrite_rule_file = COMMON_PATH . 'Library/dynamic_rewrite_rule/' . $dynamic_rewrite_rule_class . EXT;

    if (!$dr || empty($drp)) {
        return true;
    }

    if (!is_file($dynamic_rewrite_rule_file)) {
        return true;
    }

    include $dynamic_rewrite_rule_file;

    $class = "\\Common\\Library\\dynamic_rewrite_rule\\{$dynamic_rewrite_rule_class}";
    (new $class())->exec();
}

/**
 * 检测动态生成
 *
 * @param $content
 *
 * @return bool
 */
function check_dynamic_generate($content)
{
    static $object = null;
    $dg = C('DYNAMIC_GENERATE');
    $dgp = C('DYNAMIC_GENERATE_PROJECT');

    $name_rule = '_dynamic_generate_rule';
    $dynamic_generate_rule_class = $dgp . $name_rule;
    $dynamic_generate_rule_file = COMMON_PATH . 'Library/dynamic_generate_rule/' . $dynamic_generate_rule_class . EXT;

    if (!$dg || empty($dgp)) {
        return true;
    }

    if (!is_file($dynamic_generate_rule_file)) {
        return true;
    }

    if (null === $object) {
        include $dynamic_generate_rule_file;

        $class = "\\Common\\Library\\dynamic_generate_rule\\{$dynamic_generate_rule_class}";

        $object = new $class($content);
    }

    $object->exec();
}

/**
 * 描述：自定义二维数组无规则排序
 * 作者：肖书成
 * @param array $arr1 自定义规则数组
 * @param array $arr2 需要排序的数组
 * @param string $key 排序数组标识
 * @return array
 */
function custom_sort($arr1, $arr2, $key = 'id')
{
    if (empty($key)) {
        return $arr2;
    }

    foreach ($arr1 as $k => $v) {
        foreach ($arr2 as $k1 => $v1) {
            if ($v == $v1[$key]) {
                $arr3[] = $v1;
                break;
            }
        }
    }
    return $arr3;
}

/**
 * 获取内容第一张图片
 * @date: 2015-6-10
 * @author: liujun
 */
function getContentFirstImg($imgId = 0, $content = '')
{
    if (intval($imgId) > 0) {
        return get_cover($imgId, 'path');
    } else {
        preg_match("<img.*src=[\"](.*?)[\"].*?>", $content, $match);
        if (!empty($match)) {
            return $match[1];
        } else {
            return get_cover(0, 'path');
        }
    }
}

/**
 * 处理内容分步阅读
 * 标题格式：<h2>标题</h2>
 * @date: 2015-8-24
 * @author: liujun
 */
function stepContent($content = '')
{
    $result = array();
    if (!empty($content)) {
        preg_match_all('/<h2.*>([\s\S]*)<\/h2>/iU', $content, $matches);
        if (!empty($matches[0])) {
            $matche = $matches[0];
            foreach ($matche as $i => $value) {
                $start_str = $value;
                if ($i + 1 <= count($matche)) {
                    $end_str = $matche[$i + 1];
                } else {
                    $end_str = '';
                }
                //------------------------------------------------------------处理数据
                $start_pos = strpos($content, $start_str) + strlen($start_str);
                if (!empty($end_str)) {
                    $end_pos = strpos($content, $end_str);
                    $c_str_l = $end_pos - $start_pos;
                    $stepStr = substr($content, $start_pos, $c_str_l);
                } else {
                    $stepStr = substr($content, $start_pos);
                }
                //------------------------------------------------------------处理数据
                $result[$i]['number'] = $i + 1; //编号
                $result[$i]['title'] = trim($matches[1][$i]); //标题
                $result[$i]['content'] = $stepStr; //分步内容
            }
        }
    }
    return $result;
}

/**
 * 模块Url
 * @date: 2015-8-24
 * @author: zhudesheng
 */
function parseModuleUrl($params)
{
    defined('THEME_NAME') || define('THEME_NAME', C('THEME'));
    $widget = A('Home/' . THEME_NAME, 'Widget');
    if (method_exists($widget, 'parseModuleUrl')) {
        return call_user_func(array($widget, 'parseModuleUrl'), $params);
    }

    if (isset($params['category_id'])) {
        // 文档
        $url = str_replace(array('{id}'), array($params['id']), $params['path_detail']) . '.html';
    } else {
        if ($params['template_index']) {
            // 频道
            $filename = strtolower(basename($params['path_index']));

            if ($filename == 'index') {
                $params['path_index'] = dirname($params['path_index']) . '/';
            }

            $url = $params['path_index'];
        } else {
            // 列表
            $filename = strtolower($params['path_lists_index']);

            if ($filename == 'index' || !$filename) {
                $url = dirname($params['path_lists']) . '/';
            } else {
                $url = dirname($params['path_lists']) . '/' . $filename . '.html';
            }
        }
    }

    if ($url) {
        return '/' . $url;
    }
}

/**
 * 数组中查询子孙
 * @param $array array 数组
 * @param $catid int 父id
 * @date: 2015-8-14
 * @author: zhudesheng
 */
function parseRelation($array, $catid = 0, $field = array('id', 'pid'))
{
    $menu = array();
    foreach ($array as $key => $data) {
        if ($data[$field[1]] == $catid) {
            $menu[] = $data;
            $list = parseRelation($array, $data[$field[0]], $field);
            $menu = !empty($list) ? array_merge($menu, $list) : $menu;
        }
    }
    return $menu;
}

/**
 * 数组中查询主
 * @param $array array 数组
 * @param $catid int 父id
 * @date: 2015-8-14
 * @author: zhudesheng
 */
function parseMenuRoot($array, $catid = 0, $field = array('id', 'pid'))
{
    $menu = array();
    foreach ($array as $key => $data) {
        if ($data[$field[0]] == $catid) {
            $menu[] = $data;
            $list = parseMenuRoot($array, $data['pid'], $field);
            $menu = !empty($list) ? array_merge($menu, $list) : $menu;
        }
    }
    return $menu;
}

/* 获取新手游获取游戏类型
 * author：ganweili
 */
function get_game_type($type_id)
{
    $game_type = C(FIELD_GAME_TYPE);
    return $game_type[$type_id];
}

/* 获取手游评测的分数
 * author：ganweili
 */
function get_soft_socre($tid)
{
    $data = M('tags_map')->alias('a')->join('__DOWN_DMAIN__ b')
        ->where("a.tid=$tid   and a.did=b.id")
        ->getfield('soft_socre');
    if (empty($data)) {
        $data = M('tags_map')->alias('a')->join('__DOWN_DSOFT__ b')
            ->where("a.tid=$tid   and a.did=b.id")
            ->getfield('soft_socre');
    }
    if (empty($data)) {
        $data = 3;
    }
    return $data * 2;

}

/**
 * 前端分页内容处理
 * @param $info array 内容页查询结果
 * @param $theme string 主题名
 * @param $i integer 页码
 * @param $url string 当前详情页的url地址
 * @author: liuliu
 */
function contentHandle(&$info, $i, $theme, $url)
{
    if (!is_numeric($i) || $i < 1) {
        $i = 1;
    }

    $r = [];
    $pathinfo = pathinfo($url);
    $ext = $pathinfo['extension'] ? '.' . $pathinfo['extension'] : '';
    $page_flag = C('CONTENT_PAGE_FLAG') ? C('CONTENT_PAGE_FLAG') : "_"; //内容分页内容之间的分隔标识符
    // 分页内容
    $m_content = preg_split('/(?:<(?<fo>[^>]+?)>\W*?)?\[page\](.*?)\[\/page\](?(1)\W*?<\/\k<fo>>)/', $info['content']);
    unset($m_content[0]);
    unset($m_content[count($m_content)]);
    // 分页标题
    preg_match_all('/(?:<(?<fo>[^>]+?)>\W*?)?\[page\](?<title>.*?)\[\/page\](?(1)\W*?<\/\k<fo>>)/', $info['content'], $m_title_match);
    $m_title = $m_title_match['title'];

    if (count($m_title) <= 2) {
        return false;
    }

    $count = count($m_content);
    if ($i > $count) {
        $i = $count;
    }

    // 标题获取
    $r['title'] = $m_title[$i];
    if (!empty($r['title'])) {
        //处理SEO
        if (C('SEO_STRING') && C('DETAIL_AUTO_SEO_TITLE_COM')) {
            $t = array();
            $t[abs((int) C('SEO_PRE_SUF') - 1)] = $r['title'];
            $t[(int) C('SEO_PRE_SUF')] = C('SEO_STRING');
            ksort($t);
            $r['title'] = implode(' - ', $t);
        }
    } else {
        // 内容继续分页处理，SEO标题不处理
        $r = false;
    }
    // 分页DOM
    if ($theme != C('MOBILE_THEME')) {
        for ($j = 1; $j <= $count; $j++) {
            $p = $j;
            if ($j == 1) {
                $page .= "<a id='href_$p'  class=\"";
                if ($i == $j) {
                    $page .= 'cur';
                }

                $page .= "\" href=\"./" . $pathinfo['basename'] . "\">" . $p . "</a>";
            } else {
                $page .= "<a id='href_$p'  class=\"";
                if ($i == $j) {
                    $page .= 'cur';
                }

                $page .= "\" href=\"./" . $pathinfo['filename'] . $page_flag . $p . $ext . "\" >" . $p . '</a>';
            }
        }
    } else {
        // 手机分页格式  谭坚 2015-6-30
        if ($i == 1 && $count > 1) {
            $p = $i + 1;
            $page = "<a id='href_$p' class=\"\" href=\"./" . $pathinfo['filename'] . $page_flag . $p . $ext . "\" >下一页</a>";
        } else if ($i == $count && $i != 1) {
            $p = $i - 1;
            if ($p == 1) {
                $page = "<a id='href_$p' class=\"\" href=\"./" . $pathinfo['basename'] . "\" >上一页</a>";
            } else {
                $page = "<a id='href_$p' class=\"\" href=\"./" . $pathinfo['filename'] . $page_flag . $p . $ext . "\" >上一页</a>";
            }

        } else {
            $p = $i - 1;
            $l = $i + 1;
            if ($p == 1) {
                $page = "<a id='href_$p' class=\"\" href=\"./" . $pathinfo['basename'] . "\" >上一页</a><a id='href_$l' class=\"\" href=\"./" . $pathinfo['filename'] . $page_flag . $l . $ext . "\" >下一页</a>";
            } else {
                $page = "<a id='href_$p' class=\"\" href=\"./" . $pathinfo['filename'] . $page_flag . $p . $ext . "\" >上一页</a><a id='href_$l' class=\"\" href=\"./" . $pathinfo['filename'] . $page_flag . $l . $ext . "\" >下一页</a>";
            }

        }
    }

    // 内容处理
    $info['content'] = $m_content[$i] . '<div class="detail_page">' . $page . '</div>';
    return $r;

}

/**
 * 描述：对于没有分页的文章，采用默认分两页功能（目前96u需要使用该功能）
 * 分页第二页链接标签关联文章或者文章分类下的文章
 * 使用该功能需要配置系统全局变量 CONTENT_ADD_PAGE
 * @param $info
 * @param $theme
 * @param string $model
 * Author:谭坚
 * Version:1.0.0
 * Modify Time:
 * Modify Author:
 */
function content_add_page(&$info, $theme, $model = 'document')
{
    if (is_numeric($info['id'])) {
        $where = array();
        $where['did'] = $info['id'];
        $where['type'] = $model;
        $where['status'] = 1;
        $tags = M('tags_map')->where($where)->field('tid')->find(); //获取标签id
        unset($where);
        if (is_numeric($tags['tid'])) {
            $where['t.tid'] = $tags[tid];
            $where['t.type'] = $model;
            $where['a.id'] = array('neq', $info['id']);
            $where['a.status'] = 1;
            $rs = M($model)->alias('a')->join('__TAGS_MAP__ t on t.did=a.id')->where($where)->field('a.id as id')->find();
        }
        //如果标签不存在或者相关联的标签数据不存在,调用相关文章分类下的文章
        if (!is_numeric($rs['id'])) {
            unset($where);
            $where['id'] = array('neq', $info['id']);
            $where['category_id'] = $info['category_id'];
            $where['status'] = 1;
            $rs = M($model)->field('id')->where($where)->find();
            unset($where);
        }
        if (is_numeric($rs['id'])) {
            if ($theme != C('MOBILE_THEME')) {
                $page = "<a id='href_1'  class='";
                $page .= 'cur';
                $page .= "' href='#'>1</a>";
                $page .= "<a id='href_2' href='" . staticUrl('detail', $rs['id'], ucfirst($model)) . "' >2</a>";
            } else {
                // 手机分页格式  谭坚 2015-6-30
                $page = "<a id='href_2' class='' href='" . staticUrlMobile('detail', $rs['id'], ucfirst($model)) . "' >下一页</a>";
            }
            // 内容处理
            $info['content'] .= '<div class="detail_page">' . $page . '</div>';
        }
    }
}

/**
 * @description 个性化时间提示
 * @author Jeffrey Lau
 * @time 2016-1-8 17:25:07
 */
function formatTime($timer)
{
    $diff = time() - $timer;
    $day = floor($diff / 86400);
    $free = $diff % 86400;
    if ($day > 0) {
        return $day . "天前";
    } else {
        if ($free > 0) {
            $hour = floor($free / 3600);
            $free = $free % 3600;
            if ($hour > 0) {
                return $hour . "小时前";
            } else {
                if ($free > 0) {
                    $min = floor($free / 60);
                    $free = $free % 60;
                    if ($min > 0) {
                        return $min . "分钟前";
                    } else {
                        if ($free > 0) {
                            return $free . "秒前";
                        } else {
                            return '刚刚';
                        }
                    }
                } else {
                    return '刚刚';
                }
            }
        } else {
            return '刚刚';
        }
    }
}

/*
 *内链高亮
 *
 * @param $string 内容
 * @param $words 词语
 * @param $pre 表达式
 * @param $cfg_replace_num 替换次数
 */
function linkHighlight($string, $words, $result, $pre, $cfg_replace_num)
{
    $string = str_replace('\"', '"', $string);
    if ($cfg_replace_num > 0) {
        foreach ($words as $key => $word) {
            if ($GLOBALS['replaced'][$word] == 1) {
                continue;
            }
            $string = preg_replace("#" . preg_quote($word) . "#", $result[$key], $string, $cfg_replace_num);
            if (strpos($string, $word) !== false) {
                $GLOBALS['replaced'][$word] = 1;
            }
        }
    } else {
        $string = str_replace($words, $result, $string);
    }
    return $pre . $string;
}

/*
 * 生成token
 *
 * @return string
 */
function token()
{
    return md5(md5(C('APP_ID'). C('APP_KEY') . C('API_VERSION')) . time());
}

/**
 * checkToken
 *
 * token验证
 *
 * @param interge $token  token
 * @return bool
 */
function checkToken($token)
{
    $str  = md5(C('APP_ID') . C('APP_KEY') . C('API_VERSION'));
    $time = time() - 30;

    for ($i = 0; $i <= 60; $i++) {
        $md5 = md5($str . ($time + $i));
        
        if ($md5 == $token) {
            return true;
        }
    }
    
    return false;
}

/**
 * resultFormat
 *
 * 结果返回
 *
 * @param string $status 状态
 * @param string $error  错误信息
 * @param string $data   数据
 * @return array
 */
function resultFormat($status = 0, $error = '', $data = array(), $type = '')
{
    if (!$status) {
        $status = 0;
    }
    
    if ($error) {
        $error = 'Error：'.$error;
    }
    
    if (!$type) {
        $label = C('RESULT_TYPE');
        if ($label) {
            $type = $label;
        } else {
            $type = 'json';
        }
    }
    
    $data = array('status' => $status, 'error' => $error, 'data' => $data);
    switch (strtolower($type)) {
        case 'json':
            header('Content-Type:application/json; charset=utf-8');
            exit(json_encode($data));
        break;

        case 'eval':
            return $data;
        break;
    }
}


/**
 * getHttpResponsePOST
 *
 * 远程获取数据，POST模式
 *
 * @param string $url  指定URL完整路径地址
 * @param string $para 请求的数据
 * @return void 远程请求结果
 */
function getHttpResponsePOST($url, $para)
{
    $curl = curl_init($url);

    curl_setopt($curl, CURLOPT_TIMEOUT, 10);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);   // SSL证书认证
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);      // 严格认证
    curl_setopt($curl, CURLOPT_HEADER, 0);             // 过滤HTTP头
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);      // 显示输出结果
    curl_setopt($curl, CURLOPT_POST, true);             // post传输数据
    curl_setopt($curl, CURLOPT_POSTFIELDS, $para);      // post传输数据
    $responseText = curl_exec($curl);
    curl_close($curl);
    return $responseText;
}

/**
 * getHttpResponseGET
 *
 * 远程获取数据，GET模式
 *
 * @param string $url  指定URL完整路径地址
 * @param string $para 请求的数据
 * @return void
 */
function getHttpResponseGET($url)
{
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_TIMEOUT, 10);
    curl_setopt($curl, CURLOPT_HEADER, 0);         // 过滤HTTP头
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);  // 显示输出结果
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);  // 严格认证
    $responseText = curl_exec($curl);
    curl_close($curl);
    return $responseText;
}

/**
 * json
 *
 * json数据互转
 *
 * @param string $data json数据
 * @return string
 */
function json($data = '')
{
    if (!$data) {
        return;
    }

    if (is_string($data)) {
        $data = json_decode($data, true);
    } elseif (is_array($data)) {
        return $data = json_encode($data);
    }

    foreach ($data as $name => $value) {
        if (is_object($value)) {
            $result[$name] = json($value);
        } else {
            $result[$name] = $value;
        }
    }

    return $result;
}
