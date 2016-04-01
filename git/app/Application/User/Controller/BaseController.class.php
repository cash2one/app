<?php
// +----------------------------------------------------------------------
// | 基础控制类
// +----------------------------------------------------------------------
// | Author: crohn <lllliuliu@crohn.com>
// +----------------------------------------------------------------------

namespace User\Controller;
use Think\Controller;

/**
 * 前台公共控制器
 * 为防止多分组Controller名称冲突，公共Controller名称统一使用分组名称
 */
class BaseController extends Controller {

    /**
     * 空操作，用于输出404页面
     * @return void
     */
    public function _empty(){
        header("HTTP/1.0 404 Not Found");
        $this->error('404');
    }

    /**
     * 初始化
     * @return void
     */
    protected function _initialize(){
        /* 读取后台站点配置 */
        $config = api('Config/lists');
        C($config); //添加配置

        if(!C('WEB_SITE_CLOSE')){
            $this->error('站点已经关闭，请稍后访问~');
        }
        C('SHOW_PAGE_TRACE', false);

    }

}
