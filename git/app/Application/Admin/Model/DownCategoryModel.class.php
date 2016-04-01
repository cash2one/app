<?php
// +----------------------------------------------------------------------
// | 下载分类管理模型
// +----------------------------------------------------------------------
// | date 2014-10-17
// +----------------------------------------------------------------------
// | Author: crohn <lllliuliu@163.com>
// +----------------------------------------------------------------------

namespace Admin\Model;
use Think\Model;

/**
 * 下载分类管理模型
 */
class DownCategoryModel extends BaseCategoryModel{
    //主模型名
    protected $document_name = 'Down';

    //分类模型名
    protected $cate_name = 'DownCategory';

}
