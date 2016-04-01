<?php
/**
 * 作者: 肖书成
 * 时间: 2015/7/15
 * 描述: App管理类
 */

namespace Admin\Controller;


class AppController extends AdminController{
    protected $document_name = 'app';


    /**
     * 作者:肖书成
     * 描述:App应用列表
     */
    public function index(){
        $this->meta_title = 'App列表';
        $field      =   'id,code,title,content,url,type,update_time,status';
        $sql        =   M('App')->field($field)->buildSql();
        $count      =   M()->query("SELECT count('id') count FROM $sql str");
        $count      =   $count[0]['count'];
        $p          =   I('p')?I('p'):1;
        $row        =   10;
        $str        =   ((int)$p - 1)*$row;
        $lists       =   M()->query("$sql limit $str,$row");

        $page       =   new \Think\Page($count, $row);
        $show       =   $page->show();
        $this->assign('lists', $lists);
        $this->assign('page', $show);

        //模型ID
        $model = M('Model')->getByName($this->document_name);
        $this->assign('model',$model);

        // 记录当前列表页的cookie
        Cookie('__forward__',$_SERVER['REQUEST_URI']);


        $this->display();
    }


    /**
     * 作者:肖书成
     * 描述:编辑一条App数据
     */
    public function edit(){
        $id     =   I('get.id','');
        if(empty($id)){
            $this->error('参数不能为空！');
        }

        $model_id=  I('model_id');
        if(empty($model_id)){
            $this->error('此数据未绑定模型！');
        }


        //获取子页面导航
        $model    =    get_model_by('id', $model_id);
        $this->assign('model',$model);
        $this->assign('model_id', $model['id']);


        //获取当前页面信息
        $data = M('App')->where('id ='.$id)->find();
        $this->assign('data',$data);


        //获取表单字段排序
        $fields = get_model_attribute($model['id']);
        $this->assign('fields',$fields);


        $info['model_id']   =   $model['id'];
        $info['id']         =   $data['id'];
        $this->assign('info',$info);

        $this->assign('model_id', $model['id']);

        $this->display();
    }


    /**
     * 作者:肖书成
     * 描述:新增App数据页面
     */
    public function add(){
        //接收参数
        $model_id   =   I('get.model_id',0);
        empty($model_id) && $this->error('此数据未绑定模型！');

        //字获取子页面导航
        $model    =    get_model_by('id', $model_id);
        $this->assign('model',$model);
        $this->assign('model_id', $model_id);


        //获取表单字段排序
        $fields = get_model_attribute($model['id']);
        $this->assign('fields',$fields);

        $info['model_id']   =   $model['id'];
        $this->assign('info',$info);

        $this->assign('model_id', $model['id']);

        $this->display();
    }


    /**
     * 作者:肖书成
     * 描述：更新或者修改一条APP数据
     * copy thanks authorship
     */
    public function update(){
        $document   =   D('App');
        $res = $document->update();

        if(!$res){
            $this->error($document->getError());
        }else{
            $this->success($res['id']?'更新成功':'新增成功',Cookie('__forward__'));
        }
    }


    /**
     * 作者:肖书成
     * 描述:删除一条数数据
     */
    public function remove(){
        $id = I('id');
        if(!is_numeric($id)||$id<1){
            $this->error('参数错误');
        }

        $rel = M('App')->where('id = '.$id)->delete();
        if($rel == 1){
            $this->success('删除成功！');
        }elseif((int)$rel === 0){
            $this->error('删除失败，数据不存在！');
        }else{
            $this->error('错误！');
        }
    }

    /**
     * 作者:肖书成
     * 描述:启用和禁用状态
     */
    public function setStatus(){
        //接收参数
        $id     = I('id');
        $status = I('status');

        //验证参数
        if(!is_numeric($id)||$id<1){
            $this->error('参数错误');
        }
        if(!in_array($status,array('0','1'))){
            $this->error('参数错误');
        }

        //数据映射
        $data['id'] = $id;
        $data['status'] = $status;

        //执行SQL操作
        $rel = M('App')->save($data);

        //结果处理
        if($rel == 1){
            $this->success('成功！');
        }elseif((int)$rel === 0){
            $this->error('失败');
        }else{
            $this->error('错误！');
        }
    }


    /**
     * 作者:肖书成
     * 描述:评论列表
     */
    public function commentList(){
        $this->meta_title = '助手反馈';
        $field      =   'id,uname,message,add_time,enabled';
        $sql        =   M('Comment')->field($field)->where('type = "app"')->buildSql();
        $count      =   M()->query("SELECT count('id') count FROM $sql str");
        $count      =   $count[0]['count'];
        $p          =   I('p')?I('p'):1;
        $row        =   10;
        $str        =   ((int)$p - 1)*$row;
        $lists       =   M()->query("$sql limit $str,$row");

        $page       =   new \Think\Page($count, $row);
        $show       =   $page->show();
        $this->assign('lists', $lists);
        $this->assign('page', $show);

        $this->display();
    }

}