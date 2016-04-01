<?php
// +----------------------------------------------------------------------
// | 厂商管理
// +----------------------------------------------------------------------
// | date 2014-11-14
// +----------------------------------------------------------------------
// | Author: crohn <lllliuliu@163.com>
// +----------------------------------------------------------------------
/*
创建onethink_company表

CREATE TABLE onethink_company (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `uid` int(10) unsigned NOT NULL COMMENT '用户id',
    `name` varchar(500) NOT NULL COMMENT '公司名',
    `name_e` varchar(500) NOT NULL COMMENT '英文名',
    `path` varchar(500) NOT NULL COMMENT '生成路径',
    `pinyin` varchar(20) NOT NULL COMMENT '拼音首字母',
    `keywords` varchar(500) NOT NULL COMMENT '关键词',
    `title` varchar(500) NOT NULL COMMENT 'titile',
    `description` text NOT NULL COMMENT '厂商详细说明 描述',
    `homepage` varchar(500) NOT NULL COMMENT '主页',
    `img` int(10) unsigned NOT NULL COMMENT '图片ID',
    `position_img` int(10) unsigned NOT NULL COMMENT '推荐图片ID',
    `scontent` text NOT NULL COMMENT '厂商简单说明',
    `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
    `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='厂商表'
*/
namespace Admin\Controller;

class CompanyController extends AdminController {

    private $name = '厂商';
    private $model_name = 'Company';
    private $log_name = 'company';

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
        if(I('title')) $map['name'] = array('LIKE','%'.I('title').'%');
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
	 * 描述：获取厂商标签数据
	 * @param string $keywords
	 * @param array $field
	 * @return string
	 * Author:谭坚
	 * Version:1.0.0
	 * Modify Time:
	 * Modify Author:
	 */
	public function getTagsSearch( $keywords = '', $field = array('id','name'))
	{
		//必须要有关键词
		if(empty($keywords)) return '';
		$keywords = urldecode($keywords);
		$map = array();
		$map['name'] = array('like', "%$keywords%");
		//获取数据
		$model = M('company');
		//组合条件
		//$map['status'] = 1;  //取消禁用厂商不能搜索
		$list = $model->field($field)->where($map)->select();
		echo json_encode($list);
	}

    /**
     * 描述：查看
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function view()
    {
        if(I('id'))
        {
            $id = I('id');
            //实例化静态生成类
            $obj['model'] = ''; //不需要指定数据模型
            $obj['module'] = 'Home';
            $obj['category'] = ''; //不需要指定分类模型
            $obj['static_url'] = C('STATIC_CREATE_URL');
            $class = 'Common\\Library\\TempCreateLibrary';
            $staticInstance = new $class($obj);
            $params['id'] = $id;
            $url = $staticInstance->getViewUrl('Company', 'detail',$params);
            redirect($url);
        }
    }

    /**
     * 描述：厂商生成
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function create()
    {
        $ids = I('ids');
        $id = I('id');
        if(empty($ids) && empty($id))  $this->error('请选择要操作的数据');
        R('StaticCreate/companyDetail');
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
            $fields = $model->getDbFields();
            if(in_array('position',$fields)) $position=1;
            $this->assign('position',$position);
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
            $_POST['uid'] = UID;//赋值
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
     * 设置一条或者多条数据的状态
     *
     */
    public function setStatus(){
        //单个禁用
        if(I('id'))
        {
            $data = array(); //初始化
            $data['id'] = I('id');
            $data['status'] = I('status');
            if($data['status']) $success = '启用成功！';
            else $success = '禁用成功！';
            M('Company')->save($data);
            return  $this->success($success,'',IS_AJAX);
        }

        return parent::setStatus('Company');
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
     * 描述：厂商批量删除
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function removeAll()
    {
        $ids    =   I('request.ids');
        if(empty($ids)){
            $this->error('请选择要操作的数据');
        }
        $data = array();
        $data['id'] = array('in', $ids);
        $model = D($this->model_name);

        //删除信息
        $res = $model->where($data)->delete();
        if($res !== false){
            //记录删除行为
            action_log('delete_'.$this->log_name, $this->log_name, '', UID);
            $this->success('批量删除成功！','',IS_AJAX);
        }else{
            $this->error('批量删除失败！','',IS_AJAX);
        }
    }
}
