<?php
// +----------------------------------------------------------------------
// | 礼包分类管理模型
// +----------------------------------------------------------------------
// | date 2014-10-17
// +----------------------------------------------------------------------
// | Author: crohn <lllliuliu@163.com>
// +----------------------------------------------------------------------

namespace Admin\Model;
use Think\Model;

/**
 * 礼包分类管理模型
 */
class PackageCategoryModel extends BaseCategoryModel{
    //主模型名
    protected $document_name = 'Package';

    //分类模型名
    protected $cate_name = 'PackageCategory';

}
