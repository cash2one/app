<?php
// +----------------------------------------------------------------------
// | 文章分类管理控制器
// +----------------------------------------------------------------------
// | Author: crohn <lllliuliu@163.com>
// +----------------------------------------------------------------------

namespace Admin\Controller;

/**
 * 文章分类管理控制器
 */
class CategoryController extends BaseCategoryController {

    //模型名
    protected $cate_name = 'Category';

    //数据模型名
    protected $document_name = 'Document';

    //加载视图选择控制
    protected $view_select = array(
        'add' => 'BaseCategory:edit',

        );
}
