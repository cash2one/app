<?php
// +----------------------------------------------------------------------
// | 预设站点管理
// +----------------------------------------------------------------------
// | date 2014-11-7
// +----------------------------------------------------------------------
// | Author: crohn <lllliuliu@163.com>
// +----------------------------------------------------------------------
/*
创建onethink_preset_site表

CREATE TABLE onethink_preset_site (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `site_name` varchar(50) NOT NULL COMMENT '站点名称',
    `site_url` varchar(100) NOT NULL COMMENT '站点地址',
    `description` varchar(300) NOT NULL COMMENT '描述',
    `download_name` varchar(1000) NOT NULL COMMENT '下载名称',
    `download_url` varchar(1000) NOT NULL COMMENT '下载地址',
    `autofill` varchar(700) NOT NULL COMMENT '自动填充',
    `is_lb` tinyint(4) NOT NULL DEFAULT '1' COMMENT '链接方式：1-显示全部分站，0-不显示，自动负载均衡）',
    `is_default` tinyint(4) NOT NULL DEFAULT '0' COMMENT '设置默认：1-是，0-否）',
    `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
    `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='预设站点表'
*/
namespace Admin\Controller;

/**
 * 预设站点管理控制器
 */
class PresetSiteController extends AdminController {

    private $name = '预设站点';
    private $model_name = 'PresetSite';
    private $log_name = 'preset_site';

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
        if(I('title')) $map['site_name'] = array('LIKE','%'.I('title').'%');
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
     * 描述：预设站点排序（用于前端显示下载地址排序）
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function sort(){
        if(IS_GET){
            $ids        =   I('get.ids');
            //获取排序的数据
            if(!empty($ids)){
                $map['id'] = array('in',$ids);
            }
            $list = M('preset_site')->where($map)->field('id,site_name')->order('level DESC,id DESC')->select();
            $this->assign('list', $list);
            $this->meta_title = '预设站点排序(主要用于前端下载站点显示顺序)';
            $this->display();
        }elseif (IS_POST){
            $ids = I('post.ids');
            $ids = array_reverse(explode(',', $ids));
            foreach ($ids as $key=>$value){
                $res = M('preset_site')->where(array('id'=>$value))->setField('level', $key+1);
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
