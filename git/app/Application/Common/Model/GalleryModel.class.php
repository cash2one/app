<?php
// +----------------------------------------------------------------------
// | 图库基础模型
// +----------------------------------------------------------------------
// | Author: crohn <lllliuliu@163.com>
// +----------------------------------------------------------------------

namespace Common\Model;

/**
 * 礼包基础模型
 */
class GalleryModel extends BaseDocumentModel{

    //主模型名
    protected $document_name = 'Gallery';

    //分类模型名
    protected $cate_name = 'GalleryCategory';
   
}