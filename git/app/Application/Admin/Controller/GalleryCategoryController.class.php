<?php
// +----------------------------------------------------------------------
// | 图库分类管理控制器
// +----------------------------------------------------------------------
// | Author: crohn <lllliuliu@163.com>
// +----------------------------------------------------------------------

namespace Admin\Controller;

/**
 * 图库分类管理控制器
 */
class GalleryCategoryController extends BaseCategoryController {

    //模型名
    protected $cate_name = 'GalleryCategory';

    //数据模型名
    protected $document_name = 'Gallery';

    //加载视图选择控制
    protected $view_select = array(
        'add' => 'BaseCategory:edit',
    );
}
