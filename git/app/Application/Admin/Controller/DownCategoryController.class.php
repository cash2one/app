<?php
// +----------------------------------------------------------------------
// | Author: crohn <lllliuliu@163.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;

/**
 * 下载分类控制器
 */
class DownCategoryController extends BaseCategoryController {

    //模型名
    protected $cate_name = 'DownCategory';

    //数据模型名
    protected $document_name = 'Down';

    //加载视图选择控制
    protected $view_select = array(
        'add' => 'BaseCategory:edit',

        );

}