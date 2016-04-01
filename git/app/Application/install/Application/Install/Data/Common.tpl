<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

/**
 * 系统配文件
 * 所有系统级别的配置
 */
return array(
    /* 主题设置 */
    'THEME' =>  '[THEME]',  // 默认模板主题名称,某些后台程序需要，非原生配置

    'DEFAULT_THEME' =>  '[THEME]',  // 默认模板主题名称，原生配置
    
    /* 模块相关配置 */
    'AUTOLOAD_NAMESPACE' => array('Addons' => ONETHINK_ADDON_PATH), //扩展模块列表
    'DEFAULT_MODULE'     => 'Home',
    'MODULE_DENY_LIST'   => array('Common','User','Install'),
    'MODULE_ALLOW_LIST'  => array('Home','Admin','Package','Dynamic','Document','Down','API','DDAPI','IAPI'),

    /* 系统数据加密设置 */
    'DATA_AUTH_KEY' => '[AUTH_KEY]', //默认数据加密KEY

    /* 调试配置 */
    'SHOW_PAGE_TRACE' => true,

    /* 用户相关设置 */
    'USER_MAX_CACHE'     => 1000, //最大缓存用户数
    'USER_ADMINISTRATOR' => 1, //管理员用户ID

    /* URL配置 */
    'URL_CASE_INSENSITIVE' => false, //默认false 表示URL区分大小写 true则表示不区分大小写
    'URL_MODEL'            => 3, //URL模式
    'VAR_URL_PARAMS'       => '', // PATHINFO URL参数变量
    'URL_PATHINFO_DEPR'    => '/', //PATHINFO URL分割符

    /* 全局过滤配置 */
    'DEFAULT_FILTER' => '', //全局过滤函数

    /* 数据库配置 */
    'DB_TYPE'   => '[DB_TYPE]', // 数据库类型
    'DB_HOST'   => '[DB_HOST]', // 服务器地址
    'DB_NAME'   => '[DB_NAME]', // 数据库名
    'DB_USER'   => '[DB_USER]', // 用户名
    'DB_PWD'    => '[DB_PWD]',  // 密码
    'DB_PORT'   => '[DB_PORT]', // 端口
    'DB_PREFIX' => '[DB_PREFIX]', // 数据库表前缀

    /* 文档模型配置 (文档模型核心配置，请勿更改) */
    'DOCUMENT_MODEL_TYPE' => array(2 => '主题', 1 => '目录', 3 => '段落'),

    /* 数据缓存设置 */
    'DATA_CACHE_PREFIX'    => 'cache_', // 缓存前缀
    'DATA_CACHE_TYPE'      => 'File', // 数据缓存类型

    
    // 预先加载的标签库
    'TAGLIB_PRE_LOAD'     =>    'OT\\TagLib\\Article,OT\\TagLib\\Think,OT\\TagLib\\IndexTag',
    // 内置标签库
    'TAGLIB_BUILD_IN'       =>  'cms,cx',

    
    /* 模板相关配置 */
    'TMPL_PARSE_STRING' => array(
        '__STATIC__' => __ROOT__ . '/Public/static',
        '__PUBLIC__' => __ROOT__ . '/Public',
    ),

    /* 图片host */
    'PIC_HOST' => '[PIC_HOST]',
    'PIC_HOST_MORE' => [PIC_HOST_MORE],
    
    // 是否开启模板编译缓存,设为false则每次都会重新编译
    'TMPL_CACHE_ON'         =>  false, 

    /* SESSION 和 COOKIE 配置 */
    'SESSION_PREFIX' => 'mys_common_', //session前缀
    'COOKIE_PREFIX'  => 'myc_common_', // Cookie前缀 避免冲突

    // 显示错误信息
    'SHOW_ERROR_MSG'  =>  false, 

    // PC版本静态生成文件夹
    'STATIC_ROOT' => './Static',

    // 移动版静态生成文件夹 
    'MOBILE_STATIC_ROOT' => 'Mobile',  

    //静态生成地址
    'STATIC_CREATE_URL'=>'[STATIC_CREATE_URL]',

    // 自动生成刷新时间
    'GENERATE_TIMEOUT' => 7200,
);
