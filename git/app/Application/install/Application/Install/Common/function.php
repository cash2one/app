<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

// 检测环境是否支持可写
define('IS_WRITE',APP_MODE !== 'sae');

/**
 * 系统环境检测
 * @return array 系统环境数据
 */
function check_env(){
    $items = array(
        'os'      => array('操作系统', '不限制', '类Unix', PHP_OS, 'success'),
        'php'     => array('PHP版本', '5.6', '5.6+', PHP_VERSION, 'success'),
        'upload'  => array('附件上传', '不限制', '2M+', '未知', 'success'),
        'gd'      => array('GD库', '2.0', '2.0+', '未知', 'success'),
        'disk'    => array('磁盘空间', '5M', '不限制', '未知', 'success'),
    );

    //PHP环境检测
    if($items['php'][3] < $items['php'][1]){
        $items['php'][4] = 'error';
        session('error', true);
    }

    //附件上传检测
    if(@ini_get('file_uploads'))
        $items['upload'][3] = ini_get('upload_max_filesize');

    //GD库检测
    $tmp = function_exists('gd_info') ? gd_info() : array();
    if(empty($tmp['GD Version'])){
        $items['gd'][3] = '未安装';
        $items['gd'][4] = 'error';
        session('error', true);
    } else {
        $items['gd'][3] = $tmp['GD Version'];
    }
    unset($tmp);

    //磁盘空间检测
    if(function_exists('disk_free_space')) {
        $items['disk'][3] = floor(disk_free_space(INSTALL_APP_PATH) / (1024*1024)).'M';
    }

    return $items;
}

/**
 * 目录，文件读写检测
 * @return array 检测数据
 */
function check_dirfile(){
    $items = array(
        array('dir',  '可写', 'success', './Uploads/Download'),
        array('dir',  '可写', 'success', './Uploads/Picture'),
        array('dir',  '可写', 'success', './Uploads/Editor'),
        array('dir',  '可写', 'success', './Runtime'),
        array('dir',  '可写', 'success', './Data'),

        // array('dir',  '可写', 'success', './Static'),
        // array('file',  '可读', 'success', './Static/index.php'),
        // array('file',  '可读', 'success', './Static/dynamic.php'),
        // array('dir',  '可写', 'success', './Mobile'),
        // array('file',  '可读', 'success', './Mobile/index.php'),
        // array('file',  '可读', 'success', './Mobile/dynamic.php'),

        // array('dir', '可写', 'success', './Application/User/Conf'),
        // array('dir', '可写', 'success', './Application/Common/Conf'),
        // array('dir', '可写', 'success', './Application/Admin/Conf'),
        // array('dir', '可写', 'success', './Application/Dynamic/Conf'),
        // array('dir', '可写', 'success', './Application/IAPI/Conf'),
        // array('dir', '可写', 'success', './Application/API/Conf'),
        // array('dir', '可写', 'success', './Application/DDAPI/Conf'),

    );

    foreach ($items as &$val) {
		$item =	INSTALL_APP_PATH . $val[3];
        if('dir' == $val[0]){
            //是否存在
            if (is_dir($item)) {
                //是否可写
                if (!is_writable($item)) {
                    $val[1] = '可读';
                    $val[2] = 'error';
                    session('error', true);
                }
            } else {
                //目录不存在则尝试创建目录
                if (!mkdir($item,0770,true)) {
                    $val[1] = '不存在';
                    $val[2] = 'error';
                    session('error', true);
                }
            }
        } else {
            if(file_exists($item)) {
                if(!is_writable($item) && $val[1]=='可写') {
                    $val[1] = '不可写';
                    $val[2] = 'error';
                    session('error', true);
                }
            } else {
                if(!is_writable(dirname($item))) {
                    $val[1] = '不存在';
                    $val[2] = 'error';
                    session('error', true);
                }
            }
        }
    }

    return $items;
}

/**
 * 函数检测
 * @return array 检测数据
 */
function check_func(){
    $items = array(
        array('pdo','支持','success','类'),
        array('pdo_mysql','支持','success','模块'),
        array('file_get_contents', '支持', 'success','函数'),
        array('mb_strlen',		   '支持', 'success','函数'),
    );

    foreach ($items as &$val) {
        if(('类'==$val[3] && !class_exists($val[0]))
            || ('模块'==$val[3] && !extension_loaded($val[0]))
            || ('函数'==$val[3] && !function_exists($val[0]))
            ){
            $val[1] = '不支持';
            $val[2] = 'error';
            session('error', true);
        }
    }

    return $items;
}

/**
 * 写入配置文件
 * @param  array $config 配置信息
 */
function write_config($config, $auth){
    if(is_array($config)){
        //读取配置内容
        $templates = [
            'Common' => MODULE_PATH . 'Data/Common.tpl',
            'User' => MODULE_PATH . 'Data/User.tpl',
            'Admin' => MODULE_PATH . 'Data/Admin.tpl',
            'Dynamic' => MODULE_PATH . 'Data/Dynamic.tpl',
            'IAPI' => MODULE_PATH . 'Data/IAPI.tpl',
            //'API' => MODULE_PATH . 'Data/API.tpl',
            //'DDAPI' => MODULE_PATH . 'Data/DDAPI.tpl',
        ];
        foreach ($templates as $module => $tpl) {
            $files[$module] = file_get_contents($tpl);
        }
        
        //替换配置项
        foreach ($config as $name => $value) {
            foreach ($files as &$file) {
                $file = str_replace("[{$name}]", $value, $file);
            }
        }

        // 替换KEY
        foreach ($files as &$file) {
            $file = str_replace('[AUTH_KEY]', $auth, $file);
        }

        //写入应用配置文件
        if(!IS_WRITE){
            return '由于您的环境不可写，请修改权限。';
        }else{
            $r = false;
            foreach ($templates as $module => $tpl) {
                $dir = APP_PATH . $module . '/Conf';
                if(!is_dir($dir)) mkdir($dir,0770,true);
                if (!file_put_contents($dir . '/config.php', $files[$module])) {
                    $r .= $module . '模块配置文件：' . $dir . '/config.php 写入失败。</br>';
                }
            }

            if (!$r) {
                show_msg('配置文件写入成功');
            } else {
                show_msg($r, 'error');
                session('error', true);
            }

            return;
        }

    }
}

/**
 * 写入或移动其他文件和文件夹
 * @param  array $config 配置信息
 */
function write_df(){
    // 需要创建的文件夹
    $dirs = [
        INSTALL_APP_PATH . 'Static',
        INSTALL_APP_PATH . 'Mobile',
    ];
    // 需要移动的文件
    $mfiles = [
        [MODULE_PATH . 'Data/index.php.tpl', INSTALL_APP_PATH . 'Static/index.php'],
        [MODULE_PATH . 'Data/dynamic.php.tpl', INSTALL_APP_PATH . 'Static/dynamic.php'],
        [MODULE_PATH . 'Data/index.php.tpl', INSTALL_APP_PATH . 'Mobile/index.php'],
        [MODULE_PATH . 'Data/dynamic.php.tpl', INSTALL_APP_PATH . 'Mobile/dynamic.php'],
        [MODULE_PATH . 'Data/tags.php.tpl', APP_PATH . 'Common/Conf/tags.php'],
    ];

    $r = false; 
    // 创建文件夹
    foreach ($dirs as $dir) {
        if(!is_dir($dir)) {
            if (!mkdir($dir,0770,true)) {
                $r .= '创建文件夹 ' .$dir. ' 失败。</br>';
            }
        }
    }
    // 移动文件
    foreach ($mfiles as $mfile) {
        if (!file_exists($mfile[1])) {
            if (!copy($mfile[0], $mfile[1])) {
                $r .= '移动文件 ' .$mfile[0]. ' 到 ' .$mfile[1]. ' 失败。</br>';
            }
        }
    } 

    if (!$r) {
        show_msg('其他文件和文件夹写入或移动成功');
    } else {
        show_msg($r, 'error');
        session('error', true);
    }

    return;      

}

/**
 * 创建数据表
 * @param  resource $db 数据库连接资源
 */
function create_tables($db, $prefix = ''){
    //读取SQL文件
    $sql = file_get_contents(MODULE_PATH . 'Data/install.sql');
    $sql = str_replace("\r", "\n", $sql);
    $sql = explode(";\n", $sql);

    //替换表前缀
    $orginal = C('ORIGINAL_TABLE_PREFIX');
    $sql = str_replace(" `{$orginal}", " `{$prefix}", $sql);

    //开始安装
    show_msg('开始安装数据库...');
    foreach ($sql as $value) {
        $value = trim($value);
        if(empty($value)) continue;
        if(substr($value, 0, 12) == 'CREATE TABLE') {
            $name = preg_replace("/^CREATE TABLE `(\w+)` .*/s", "\\1", $value);
            $msg  = "创建数据表{$name}";
            if(false !== $db->execute($value)){
                show_msg($msg . '...成功');
            } else {
                show_msg($msg . '...失败！', 'error');
                session('error', true);
            }
        } else {
            $db->execute($value);
        }

    }
}

function register_administrator($db, $prefix, $admin, $auth){
    show_msg('开始注册创始人帐号...');
    $sql = "INSERT INTO `[PREFIX]ucenter_member` VALUES " .
           "('1', '[NAME]', '[PASS]', '[EMAIL]', '', '[TIME]', '[IP]', 0, 0, '[TIME]', '1')";

    $password = user_md5($admin['password'], $auth);
    $sql = str_replace(
        array('[PREFIX]', '[NAME]', '[PASS]', '[EMAIL]', '[TIME]', '[IP]'),
        array($prefix, $admin['username'], $password, $admin['email'], NOW_TIME, get_client_ip(1)),
        $sql);
    //执行sql
    $db->execute($sql);

    $sql = "INSERT INTO `[PREFIX]member` VALUES ".
           "('1', '[NAME]', '管理员', '1', '0000-00-00', '', '0', '1', '0', '[TIME]', '0', '[TIME]', '1');";
    $sql = str_replace(
        array('[PREFIX]', '[NAME]', '[TIME]'),
        array($prefix, $admin['username'], NOW_TIME),
        $sql);
    $db->execute($sql);
    show_msg('创始人帐号注册完成！');
}

/**
 * 更新数据表
 * @param  resource $db 数据库连接资源
 * @author lyq <605415184@qq.com>
 */
function update_tables($db, $prefix = ''){
    //读取SQL文件
    $sql = file_get_contents(MODULE_PATH . 'Data/update.sql');
    $sql = str_replace("\r", "\n", $sql);
    $sql = explode(";\n", $sql);

    //替换表前缀
    $sql = str_replace(" `onethink_", " `{$prefix}", $sql);

    //开始安装
    show_msg('开始升级数据库...');
    foreach ($sql as $value) {
        $value = trim($value);
        if(empty($value)) continue;
        if(substr($value, 0, 12) == 'CREATE TABLE') {
            $name = preg_replace("/^CREATE TABLE `(\w+)` .*/s", "\\1", $value);
            $msg  = "创建数据表{$name}";
            if(false !== $db->execute($value)){
                show_msg($msg . '...成功');
            } else {
                show_msg($msg . '...失败！', 'error');
                session('error', true);
            }
        } else {
            if(substr($value, 0, 8) == 'UPDATE `') {
                $name = preg_replace("/^UPDATE `(\w+)` .*/s", "\\1", $value);
                $msg  = "更新数据表{$name}";
            } else if(substr($value, 0, 11) == 'ALTER TABLE'){
                $name = preg_replace("/^ALTER TABLE `(\w+)` .*/s", "\\1", $value);
                $msg  = "修改数据表{$name}";
            } else if(substr($value, 0, 11) == 'INSERT INTO'){
                $name = preg_replace("/^INSERT INTO `(\w+)` .*/s", "\\1", $value);
                $msg  = "写入数据表{$name}";
            }
            if(($db->execute($value)) !== false){
                show_msg($msg . '...成功');
            } else{
                show_msg($msg . '...失败！', 'error');
                session('error', true);
            }
        }
    }
}

/**
 * 及时显示提示信息
 * @param  string $msg 提示信息
 */
function show_msg($msg, $class = ''){
    echo "<script type=\"text/javascript\">showmsg(\"{$msg}\", \"{$class}\")</script>";
    flush();
    ob_flush();
}

/**
 * 生成系统AUTH_KEY
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function build_auth_key(){
    $chars  = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $chars .= '`~!@#$%^&*()_+-=[]{};:"|,.<>/?';
    $chars  = str_shuffle($chars);
    return substr($chars, 0, 40);
}

/**
 * 系统非常规MD5加密方法
 * @param  string $str 要加密的字符串
 * @return string
 */
function user_md5($str, $key = ''){
    return '' === $str ? '' : md5(sha1($str) . $key);
}
