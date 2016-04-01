<?php
// +----------------------------------------------------------------------
// | Author: crohn <lllliuliu@163.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;

/**
 * 礼包分类控制器
 */
class PackageCategoryController extends BaseCategoryController {

    //模型名
    protected $cate_name = 'PackageCategory';

    //数据模型名
    protected $document_name = 'Package';

    //加载视图选择控制
    protected $view_select = array(
        'add' => 'BaseCategory:edit',

        );

}