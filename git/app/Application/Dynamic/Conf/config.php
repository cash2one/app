<?php
/**
 * 配置文件
 * 所有除开系统级别的前台配置
 */
return array(
    /* URL配置 */
    'URL_CASE_INSENSITIVE' => true, //默认false 表示URL区分大小写 true则表示不区分大小写
    'URL_MODEL'            => 3, //URL模式
    'VAR_URL_PARAMS'       => '', // PATHINFO URL参数变量
    'URL_PATHINFO_DEPR'    => '/', //PATHINFO URL分割符
    
    /* 数据库配置 */
  /*  'DB_TYPE'   => 'mysqli', // 数据库类型
    'DB_HOST'   => '192.168.2.120', // 服务器地址
    'DB_NAME'   => '7230', // 数据库名
    'DB_USER'   => '7230', // 用户名
    'DB_PWD'    => 'qdBQYUh49PGz27xW',  // 密码
    'DB_PORT'   => '3306', // 端口
    'DB_PREFIX' => 'onethink_', // 数据库表前缀*/
    'DB_TYPE'   => 'mysqli', // 数据库类型
    'DB_HOST'   => 'localhost', // 服务器地址
    'DB_NAME'   => 'anfensi', // 数据库名
    'DB_USER'   => 'root', // 用户名
    'DB_PWD'    => 'root',  // 密码
    'DB_PORT'   => '3306', // 端口
    'DB_PREFIX' => 'onethink_', //
    /*CORS*/
    'DYNAMIC_SERVER_ALLOW_CORS' =>array('libao.liuliu.com', '7230.liuliu.com'),
);
