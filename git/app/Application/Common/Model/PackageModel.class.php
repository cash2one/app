<?php
// +----------------------------------------------------------------------
// | 礼包基础模型
// +----------------------------------------------------------------------
// | Author: crohn <lllliuliu@163.com>
// +----------------------------------------------------------------------

namespace Common\Model;

/**
 * 礼包基础模型
 */
class PackageModel extends BaseDocumentModel{
    //主模型名
    protected $document_name = 'Package';

    //分类模型名
    protected $cate_name = 'PackageCategory';
   

}