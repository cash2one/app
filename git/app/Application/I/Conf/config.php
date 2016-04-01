<?php
/**
 * UCenter客户端配置文件
 * 注意：该配置文件请使用常量方式定义
 */
define('UC_APP_ID', 1); //应用ID
define('UC_API_TYPE', 'Model'); //可选值 Model / Service
define('UC_AUTH_KEY', 'Q`lohKMfdJma>_?=B6b:T%3N-1X.PzcFg]!I0w&r'); //加密KEY

return array (
	'SHOW_PAGE_TRACE' => false,
	'URL_MODEL'       => '0', //URL模式
	'DEFAULT_THEME'   =>  'gfw',  // 默认模板主题名称
//腾讯QQ登录配置
	'THINK_SDK_QQ'    => array (
		'APP_KEY'    => '101217302', //应用注册成功后分配的 APP ID
		'APP_SECRET' => 'fb4b3fed6c69f7649586f615f43fd6d9', //应用注册成功后分配的KEY
		'CALLBACK'   => 'http://www.guanfang123.com/i.php?c=Api&a=callback&type=qq',
	),
	//新浪微博配置
	'THINK_SDK_SINA'  => array (
		'APP_KEY'    => '1016505154', //应用注册成功后分配的 APP ID
		'APP_SECRET' => '85817ba7e5ca90e2de7903a1048343b1', //应用注册成功后分配的KEY
		'CALLBACK'   => 'http://www.guanfang123.com/index.php?m=i&c=Api&a=callback&type=sina',
	)

);
