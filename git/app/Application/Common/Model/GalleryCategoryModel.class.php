<?php
// +----------------------------------------------------------------------
// | 礼包分类管理模型
// +----------------------------------------------------------------------
// | date 2014-10-24
// +----------------------------------------------------------------------
// | Author: crohn <lllliuliu@163.com>
// +----------------------------------------------------------------------

namespace Common\Model;
use Think\Model;

/**
 * 礼包分类管理模型
 */
class GalleryCategoryModel extends BaseCategoryModel{
    //主模型名
    protected $document_name = 'Album';

    //分类模型名
    protected $cate_name = 'GalleryCategory';

}
