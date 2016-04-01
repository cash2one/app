<?php
// +----------------------------------------------------------------------
// | 标签分类管理
// +----------------------------------------------------------------------
// | date 2014-9-4
// +----------------------------------------------------------------------
// | Author: crohn <lllliuliu@163.com>
// +----------------------------------------------------------------------

/*
创建onethink_product_tags_category表

CREATE TABLE `onethink_product_tags_category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '分类ID',
  `name` varchar(30) NOT NULL COMMENT '标志',
  `title` varchar(50) NOT NULL COMMENT '标题',
  `template` varchar(100) NOT NULL COMMENT '模板',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '数据状态',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='产品标签分类表' 
*/

namespace Admin\Controller;

/**
 * 后台标签分类管理控制器
 */
class ProductTagsCategoryController extends AdminController {

    /**
     * 构造函数，处理动态边栏
     * @return void
     */
    public function _initialize(){
        $this->assign('_extra_menu',array(
            '标签管理'=> D('ProductTagsCategory')->getCategory(),
        ));
        parent::_initialize();
    }

    /**
     * 标签管理列表
     */
    public function index(){
        $this->meta_title = '标签分类列表';
        $list       =   D('ProductTagsCategory')->getList();
        $request    =   (array)I('request.');
        $total      =   $list? count($list) : 1 ;
        $listRows   =   C('LIST_ROWS') > 0 ? C('LIST_ROWS') : 10;
        $page       =   new \Think\Page($total, $listRows, $request);
        $voList     =   array_slice($list, $page->firstRow, $page->listRows);
        $p          =   $page->show();
        $this->assign('_list', $voList);
        $this->assign('_page', $p? $p: '');
        $this->display();
    }

    /**
     * 编辑标签
     * @param  integer $id 标签id
     * @return void
     */
    public function edit($id = null){
        $TagsCategory = D('ProductTagsCategory');

        if(IS_POST){ //提交表单
            if(false !== $TagsCategory->update()){
                $this->success('编辑成功！', U('index'));
            } else {
                $error = $TagsCategory->getError();
                $this->error(empty($error) ? '未知错误！' : $error);
            }
        } else {
            /* 获取标签信息 */
            $info = $id ? $TagsCategory->info($id) : '';

            $this->assign('info', $info);
            $this->meta_title = '编辑标签分类';
            $this->display();
        }
    }

    /**
     * 新增标签
     * @return void
     */
    public function add(){
        $TagsCategory = D('ProductTagsCategory');

        if(IS_POST){ //提交表单
            if(false !== $TagsCategory->update()){
                $this->success('新增成功！', U('index'));
            } else {
                $error = $TagsCategory->getError();
                $this->error(empty($error) ? '未知错误！' : $error);
            }
        } else {
            /* 获取标签信息 */
            $this->assign('info', null);
            $this->meta_title = '新增标签分类';
            $this->display('edit');
        }
    }

    /**
     * 删除一个标签
     * @return void
     */
    public function remove(){
        $id = I('id');
        if(empty($id)){
            $this->error('参数错误!');
        }

        //判断该分类下有无标签，有则不允许删除
        $tags =  M('ProductTags')->where('category='.$id)->field('id')->select();
        if(!empty($tags)){
            $this->error('请先删除该分类下的标签');
        }

        //删除该标签信息
        $res = M('ProductTagsCategory')->delete($id);

        if($res !== false){
            //记录标签删除行为
            action_log('delete_product_tags_categpry', 'productTags', $id, UID);      
            $this->success('删除标签分类成功！');
        }else{
            $this->error('删除标签分类失败！');
        }

    }
}
