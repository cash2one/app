<?php
// +----------------------------------------------------------------------
// | 模板管理
// +----------------------------------------------------------------------
// | date 2014-9-25
// +----------------------------------------------------------------------
// | Author: crohn <lllliuliu@163.com>
// +----------------------------------------------------------------------

/*
创建onethink_template表

CREATE TABLE onethink_template (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `type` varchar(30) NOT NULL COMMENT '类型',
    `name` varchar(30) NOT NULL COMMENT '标志',
    `title` varchar(50) NOT NULL COMMENT '标题',
    `path` varchar(255) NOT NULL COMMENT '模板路径',
    `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
    `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
    `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '数据状态（-1-删除，0-禁用，1-正常，2-待审核）',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_name` (`name`),
    KEY `type` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='模板表'
*/

namespace Admin\Controller;

/**
 * 后台模板管理控制器
 */
class TemplateController extends AdminController {

    private $name = '模板';

    /**
     * 构造函数，处理动态边栏
     * @return void
     */
    public function _initialize(){
        parent::_initialize();
    }

    /**
    * 列表
    * @return void
    */
    public function index(){
        $this->meta_title = $this->name.'列表';
        $map = array();
        $type = I('type');
        $type && $map['type'] = $type;
        $list       =   D('Template')->getList($map);
        $request    =   (array)I('request.');
        $total      =   $list? count($list) : 1 ;
        $listRows   =   C('LIST_ROWS') > 0 ? C('LIST_ROWS') : 10;
        $page       =   new \Think\Page($total, $listRows, $request);
        $voList     =   array_slice($list, $page->firstRow, $page->listRows);
        $p          =   $page->show();
        $this->assign('_list', $voList);
        $this->assign('_page', $p? $p: '');
        $this->assign('name', $this->name);
        $this->assign('type', $type);
        $this->assign('type_list', C('TEMPLATE_TYPE'));
        $this->display();
    }

    /**
     * 编辑
     * @param  integer $id id
     * @return void
     */
    public function edit($id){
        $model = D('Template');

        if(IS_POST){ //提交表单
            if(false !== $model->update()){
                $this->success('编辑成功！', U('index'));
            } else {
                $error = $Tags->getError();
                $this->error(empty($error) ? '未知错误！' : $error);
            }
        } else {
            /* 获取标签信息 */
            $info = $model->info($id);

            $this->assign('info',       $info);
            $this->meta_title =  $this->name.'编辑';
            $this->assign('name', $this->name);
            $this->display();
        }
    }

    /**
     * 新增
     * @return void
     */
    public function add(){
        $model = D('Template');

        if(IS_POST){ //提交表单
            if(false !== $model->update()){
                $this->success('新增成功！', U('index'));
            } else {
                $error = $model->getError();
                $this->error(empty($error) ? '未知错误！' : $error);
            }
        } else {

            /* 获取标签信息 */
            $this->assign('info',  null);
            $this->meta_title =  $this->name.'新增';
            $this->assign('name', $this->name);
            $this->display('edit');
        }
    }

    /**
     * 删除
     * @return void
     */
    public function remove(){
        $id = I('id');
        if(empty($id)){
            $this->error('参数错误!');
        }
        $model = D('Template');

        //删除信息
        $res = $model->delete($id);

        if($res !== false){
            //记录删除行为
            action_log('delete_template', 'template', $id, UID);
            $this->success('删除成功！');
        }else{
            $this->error('删除失败！');
        }

    }
}
