<?php
// +----------------------------------------------
// |  东东助手接口公共类
// *----------------------------------------------
// |  Author: liuliu
// |  Time  : 2015-10-14
//+----------------------------------------------
namespace DDAPI\Controller;

use Think\Controller;

class CommonController extends Controller
{

    /**
     * 初始化公共方法
     * @param void
     * @return void
     */
    protected function _initialize()
    {
        // 结果关闭trace
        C('SHOW_PAGE_TRACE', false);

        // TODO:安全检查

        // CORS
        $cors = C('DDAPI_ALLOW_CORS');
        $referer = $_SERVER['HTTP_REFERER'];
        if ($referer) {
            $referer = parse_url($referer);
            $host = $referer['host'];
            if (in_array($host, $cors)) {
                header('Access-Control-Allow-Origin:http://' . $host);
            }
        }
        // 读取后台站点配置
        $config = api('Config/lists');
        C($config); //添加配置

        if (!C('WEB_SITE_CLOSE')) {
            $this->error('站点已经关闭，请稍后访问~');
        }
    }
}
