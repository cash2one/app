<?php
// +----------------------------------------------------------------------
// | 基础控制类
// +----------------------------------------------------------------------
// | Author: crohn <lllliuliu@crohn.com>
// +----------------------------------------------------------------------

namespace Tags\Controller;
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
        C('SHOW_PAGE_TRACE', false);

    }

    /**
     * 标签列表SEO
     * @param array $params SEO数组
     * @return void
     */
    protected function SEO($tag){
        if(is_numeric($tag))
        {
            $rs = M('Tags')->where('id='.$tag)->field(true)->find();
            $seo['title'] = $rs['meta_title'] ? $rs['meta_title'] : C('TAG_DEFAULT_TITLE');

            $seo['keywords'] = $rs['keywords'] ? $rs['keywords'] : C('TAG_DEFAULT_KEY');

            $seo['description'] = $rs['description'] ? $rs['description'] : C('TAG_DEFAULT_DESCRIPTION');

        }
        return $seo;
    }

}
