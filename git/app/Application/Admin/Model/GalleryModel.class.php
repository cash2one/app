<?php
// +----------------------------------------------------------------------
// | 图片模型
// +----------------------------------------------------------------------
// | Author: zhudesheng
// +----------------------------------------------------------------------

namespace Admin\Model;

/**
 * 图片模型
 */
class GalleryModel extends BaseDocumentModel{

    //主模型名
    public $document_name = 'Gallery';

    //分类模型名
    public $cate_name = 'GalleryCategory';

    //前台模块名
    public $module = 'Gallery';
}