<?php
/**
 * 配置文件
 * 所有除开系统级别的前台配置
 */
return array(
	'APP_ID'				=>	1,
	'APP_TOKEN'				=>	'1e94f0bb5a129af3',

	// 允许数据调用IP组
	'API_IP_LIMIT'			=> array(
		'127.0.0.1'
	),

	'SHOW_PAGE_TRACE'		=> false, 
    /* URL配置 */
	'URL_ROUTER_ON'			=> true,
    'URL_CASE_INSENSITIVE'	=> true, //默认false 表示URL区分大小写 true则表示不区分大小写
    'URL_MODEL'				=> 3, //URL模式
    'VAR_URL_PARAMS'		=> '', // PATHINFO URL参数变量
    'URL_PATHINFO_DEPR'		=> '/', //PATHINFO URL分割符
);
