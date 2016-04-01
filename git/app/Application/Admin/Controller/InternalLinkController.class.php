<?php
// +----------------------------------------------------------------------
// | 内链管理
// +----------------------------------------------------------------------
// | date 2014-9-26
// +----------------------------------------------------------------------
// | Author: crohn <lllliuliu@163.com>
// +----------------------------------------------------------------------

/*
创建onethink_internal_link表

CREATE TABLE onethink_internal_link (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `title` varchar(50) NOT NULL COMMENT '标题',
    `content` text NOT NULL COMMENT '内容',
    `template_id` int(10) unsigned NOT NULL COMMENT '模板ID',
    `description` text NOT NULL COMMENT '描述',
    `icon` int(10) unsigned NOT NULL COMMENT '图片ID，用于预览',
    `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
    `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
    `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '数据状态（-1-删除，0-禁用，1-正常，2-待审核）',
    `old_id` int(10) unsigned NOT NULL COMMENT '老版本ID',
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='内链表'
*/

namespace Admin\Controller;

/**
 * 后台模板管理控制器
 */
class InternalLinkController extends AdminController {

    private $name = '内链';
    private $model_name = 'InternalLink';
    private $log_name = 'internal_link';

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
        if(I('title')) $map['title'] = array('LIKE','%'.I('title').'%');
        $list       =   D($this->model_name)->getList($map);
        $request    =   (array)I('request.');
        $total      =   $list? count($list) : 1 ;
        $listRows   =   C('LIST_ROWS') > 0 ? C('LIST_ROWS') : 10;
        $page       =   new \Think\Page($total, $listRows, $request);
        $page->parameter['title']   =   I('title');  //用于分页带参数
        $voList     =   array_slice($list, $page->firstRow, $page->listRows);
        $p          =   $page->show();
        $this->assign('_list', $voList);
        $this->assign('_page', $p? $p: '');
        $this->assign('name', $this->name);
        $this->display();
    }

    /**
     * 编辑
     * @param  integer $id id
     * @return void
     */
    public function edit($id){
        $model = D($this->model_name);

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
            $this->assign('template', $this->getTemplate());
            $this->display();
        }
    }

    /**
     * 新增
     * @return void
     */
    public function add(){
        $model = D($this->model_name);

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
            $this->assign('template', $this->getTemplate());
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
        $model = D($this->model_name);

        //删除信息
        $res = $model->delete($id);

        if($res !== false){
            //记录删除行为
            action_log('delete_'.$this->log_name, $this->log_name, $id, UID);
            $this->success('删除成功！');
        }else{
            $this->error('删除失败！');
        }

    }

    /**
     * 获取内链模板
     * @return void
     */
    public function getTemplate(){

        $rs = D('Template')->getList(array('type'=>C('TEMPLATE_INTERNAL_LINK')));
        return $rs;

    }


    /**
     * 描述：内链排序
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function sort(){
        if(IS_GET){
            $ids        =   I('get.ids');
            //获取排序的数据
            $map['status'] = array('gt',-1);
            if(!empty($ids)){
                $map['id'] = array('in',$ids);
            }
            $list = M('internal_link')->where($map)->field('id,title')->order('level DESC,id DESC')->select();

            $this->assign('list', $list);
            $this->meta_title = '内链排序';
            $this->display();
        }elseif (IS_POST){
            $ids = I('post.ids');
            $ids = array_reverse(explode(',', $ids));
            foreach ($ids as $key=>$value){
                $res = M('internal_link')->where(array('id'=>$value))->setField('level', $key+1);
            }
            if($res !== false){
                $this->success('排序成功！');
            }else{
                $this->error('排序失败！');
            }
        }else{
            $this->error('非法请求！');
        }
    }


}
