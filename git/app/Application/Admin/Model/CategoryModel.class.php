<?php
// +----------------------------------------------------------------------
// | 文章分类管理模型
// +----------------------------------------------------------------------
// | date 2014-10-17
// +----------------------------------------------------------------------
// | Author: crohn <lllliuliu@163.com>
// +----------------------------------------------------------------------

namespace Admin\Model;
use Think\Model;

/**
 * 文章分类管理模型
 */
class CategoryModel extends BaseCategoryModel{
    //主模型名
    protected $document_name = 'Document';

    //分类模型名
    protected $cate_name = 'Category';

}
