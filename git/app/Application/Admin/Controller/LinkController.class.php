<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: leha.com
// +----------------------------------------------------------------------

namespace Admin\Controller;

/**
 * 后台友链控制器
 * @author leha.com
 */

class LinkController extends AdminController {
	//文档模型名
	protected $document_name = 'Link';
	protected $main_title='友链';
	
	public function __construct() {
		parent::__construct();
		$this->assign('main_title', $this->main_title);
	}
    /**
     * 频道列表
     * @author leha.com
     */
    public function index(){
        $pid = I('get.pid', 0);
        /* 获取频道列表 */
        $map  = array('status' => array('gt', -1), 'pid'=>$pid);
        $list = M($this->document_name)->where($map)->order('id desc')->select();
        int_to_string($list,array( 'group'=>C('LINK_GROUP') ));

        $this->assign('list', $list);
        $this->assign('pid', $pid);
        $this->meta_title = '友链管理';
        $this->display();
    }

    /**
     * 添加频道
     * @author leha.com
     */
    public function add(){
        $models = M('Model')->getByName($this->document_name);
    	$this->assign('models', $models);
    	//获取表单字段排序
    	$fields = get_attribute($models['id']);
    	$this->assign('fields',$fields);
        if(IS_POST){
            $Channel = D($this->document_name);
            $data = $Channel->create();
            if($data){
                $id = $Channel->add();
                if($id){
                    $this->success('新增成功', U('index'));
                    //记录行为
                    action_log('update_channel', $this->document_name, $id, UID);
                } else {
                    $this->error('新增失败');
                }
            } else {
                $this->error($Channel->getError());
            }
        } else {
            $pid = I('get.pid', 0);
            //获取父友链
            if(!empty($pid)){
                $parent = M($this->document_name)->where(array('id'=>$pid))->field('title')->find();
                $this->assign('parent', $parent);
            }

            $this->assign('pid', $pid);
            $this->assign('info',null);
            $this->meta_title = '新增友链';
            $this->display('edit');
        }
    }

    /**
     * 编辑频道
     * @author leha.com
     */
    public function edit($id = 0){
        $models = M('Model')->getByName($this->document_name);
    	$this->assign('models', $models);
    	//获取表单字段排序
    	$fields = get_attribute($models['id']);
    	$this->assign('fields',$fields);
        if(IS_POST){
            $Channel = D($this->document_name);
            $data = $Channel->create();
            if($data){
                if($Channel->save()){
                    //记录行为
                    action_log('update_channel', $this->document_name, $data['id'], UID);
                    $this->success('编辑成功', U('index'));
                } else {
                    $this->error('编辑失败');
                }

            } else {
                $this->error($Channel->getError());
            }
        } else {
            $info = array();
            /* 获取数据 */
            $info = M($this->document_name)->find($id);

            if(false === $info){
                $this->error('获取配置信息错误');
            }

            $pid = I('get.pid', 0);
            //获取父友链
            if(!empty($pid)){
            	$parent = M($this->document_name)->where(array('id'=>$pid))->field('title')->find();
            	$this->assign('parent', $parent);
            }

            $this->assign('pid', $pid);
            $this->assign('data', $info);
            $this->meta_title = '编辑友链';
            $this->display();
        }
    }

    /**
     * 删除频道
     * @author leha.com
     */
    public function remove(){
        $id = array_unique((array)I('id',0));

        if ( empty($id) ) {
            $this->error('请选择要操作的数据!');
        }

        $map = array('id' => array('in', $id) );
        if(M($this->document_name)->where($map)->delete()){
            //记录行为
            action_log('update_channel', $this->document_name, $id, UID);
            $this->success('删除成功');
        } else {
            $this->error('删除失败！');
        }
    }

    /**
     * 友链排序
     * @author huajie <banhuajie@163.com>
     */
    public function sort(){
        if(IS_GET){
            $ids = I('get.ids');
            $pid = I('get.pid');

            //获取排序的数据
            $map = array('status'=>array('gt',-1));
            if(!empty($ids)){
                $map['id'] = array('in',$ids);
            }else{
                if($pid !== ''){
                    $map['pid'] = $pid;
                }
            }
            $list = M($this->document_name)->where($map)->field('id,title')->order('sort asc,id asc')->select();

            $this->assign('list', $list);
            $this->meta_title = '友链排序';
            $this->display();
        }elseif (IS_POST){
            $ids = I('post.ids');
            $ids = explode(',', $ids);
            foreach ($ids as $key=>$value){
                $res = M($this->document_name)->where(array('id'=>$value))->setField('sort', $key+1);
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