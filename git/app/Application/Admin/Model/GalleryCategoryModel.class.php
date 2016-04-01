<?php
// +----------------------------------------------------------------------
// | 图库分类管理模型
// +----------------------------------------------------------------------
// | date 2015-07-17
// +----------------------------------------------------------------------
// | Author: zhudesheng
// +----------------------------------------------------------------------

namespace Admin\Model;
use Think\Model;

/**
 * 图片分类管理模型
 */
class GalleryCategoryModel extends BaseCategoryModel{

    //主模型名
    protected $document_name = 'Gallery';

    //分类模型名
    protected $cate_name = 'GalleryCategory';

}
