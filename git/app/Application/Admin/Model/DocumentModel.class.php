<?php
// +----------------------------------------------------------------------
// | 文章模型
// +----------------------------------------------------------------------
// | Author: crohn <lllliuliu@163.com>
// +----------------------------------------------------------------------

namespace Admin\Model;

/**
 * 文章模型
 */
class DocumentModel extends BaseDocumentModel{
    //主模型名
    public $document_name = 'Document';

    //分类模型名
    public $cate_name = 'Category';

    //前台模块名
    public $module = 'Document';

    /**
     * 获取文档的所有子文档id（只获取一级）
     * 
     * @param integer $id 主文档id
     * @return Array 子文档id数组
     * @author  sunjianhua
     */
    public function getDocIds($id)
    {
        $ids = array();     

        $doc = M('Document');
        $articles = $doc->field('id')->where('status = 1 and pid = ' . $id)->select();

        if ($articles) {
            foreach ($articles as $v) {
                $ids[] = $v['id'];
            }
        }

        return $ids;
    }

}