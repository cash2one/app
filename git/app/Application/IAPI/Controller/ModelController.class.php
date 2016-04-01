<?php
// +----------------------------------------------------------------------
// |  系统模型管理类
// +----------------------------------------------------------------------
// |  Author: 朱德胜
// |  Time  : 2015-06-29
// +----------------------------------------------------------------------

namespace IAPI\Controller;

class ModelController extends CommonController {

    /**
     * 模型列表
     * @return json or xml
     */
	function lists(){
		// 数据显示数量
		if($_GET['limit']){
			$limit = I('get.limit');
		}

		// 排序方式
		if($_GET['order']){
			$order = I('get.order');
		}
		
		$logic = array('status' => array('gt', -1));
		
		$result = M('model')->where($logic)->order($order)->limit($limit)->select();
		
		if($result){
			int_to_string($result);
			$this->ajaxReturn($result, $this->returnType);
		}
	}

    /**
     * 修改模型
     * @return json or xml
     */
	function update(){
		$result = array('status' => 0);
		$mod	= M('Model');
		$details= $mod->getByName(I('post.model'));

		if(!$details){
			$result['error'] = '模型不存在';
			$this->ajaxReturn($result, $this->returnType);
		}
		
		$value = str_replace('&amp;', '&',I('post.list_grid'));
	
		$save  = $mod->where(array('id' => $details['id']))->save(array('list_grid' => $value));

		if($save){
			// 清除模型缓存数据
			S('DOCUMENT_MODEL_LIST_'.$details['extend'], null);

			$result = 1;
		}else{
			$result = 0;
		}
		
		$this->ajaxReturn($result, $this->returnType);
	}

    /**
     * 模型详情信息
     * @return json or xml
     */
	function details(){
		$result = array('status' => 0);

		$model	= M('Model')->getByName(I('get.model'));

		if(!$model){
			$result['error'] = '模型不存在';
			$this->ajaxReturn($result, $this->returnType);
		}

		$this->ajaxReturn($model, $this->returnType);
	}

    /**
     * 模型字段详情列表
     * @return json or xml
     */
	function fieldList(){
		$result = array('status' => 0);

		$model	= M('Model')->getByName(I('get.model'));
	
		if(!$model){
			$result['error'] = '模型不存在';
			$this->ajaxReturn($result, $this->returnType);
		}
		
		$result = M('attribute')->where(array('model_id' => $model['id']))->select();

		if($result){
			int_to_string($result);
			$this->ajaxReturn($result, $this->returnType);
		}
	}

    /**
     * 新增模型字段
     * @return json or xml
     */
	function insertField(){
		$mod = new \Admin\Model\AttributeModel();
		$result	= array('status' => '0', 'error' => '');
		
		$model	= M('Model')->getByName(I('post.model'));
	
		if(!$model){
			$result['error'] = '模型不存在';
			$this->ajaxReturn($result, $this->returnType);
		}
		
		unset($_POST['model']);
		$_POST['model_id'] = $model['id'];
		
		if($mod->where(array('name' => I('post.name')))->find()){
			$result['error'] = '字段已存在';
			$this->ajaxReturn($result, $this->returnType);
		}

		if($mod->update()){
			$result = 1;
		}else{
			$error = $mod->getError();

			if($error){
				$result['status'] = '-1';
				$result['error']  = $error;
			}else{
				$result = 0;
			}
		}

		$this->ajaxReturn($result, $this->returnType);
	}

    /**
     * 修改模型字段
     * @return json or xml
     */
	function updateField(){
		$mod = new \Admin\Model\AttributeModel();
		$result	= array('status' => '0', 'error' => '');
		
		$model	= M('Model')->getByName(I('post.model'));
	
		if(!$model){
			$result['error'] = '模型不存在';
			$this->ajaxReturn($result, $this->returnType);
		}

		$_POST['model_id'] = $model['id'];
		$before = I('post.before');
		unset($_POST['model'], $_POST['before']);
		
		if($before != I('post.name')){
			if($mod->where(array('name' => I('post.name')))->find()){
				$result['error'] = '字段名已存在';
				$this->ajaxReturn($result, $this->returnType);
			}
		}
		
		$detail = $mod->where(array('name' => $before))->find();
		
		if(!$detail){
			$result['error'] = '记录不存在';
			$this->ajaxReturn($result, $this->returnType);
		}
		
		$_POST['id'] = $detail['id'];

		if($mod->update()){
			$result = 1;
		}else{
			$error = $mod->getError();

			if($error){
				$result['status'] = '-1';
				$result['error']  = $error;
			}else{
				$result = 0;
			}
		}

		$this->ajaxReturn($result, $this->returnType);
	}

    /**
     * 删除模型字段
     * @return json or xml
     */
	function deleteField(){
		$mod = new \Admin\Model\AttributeModel();
		$result	= array('status' => '0', 'error' => '');
		
		$model	= M('Model')->getByName(I('post.model'));
		
		if(!$model){
			$result['error'] = '模型不存在';
			$this->ajaxReturn($result, $this->returnType);
		}
		
		$detail = $mod->where(array('name' => I('post.field')))->find();
		
		if(!$detail){
			$result['error'] = '字段不存在';
			$this->ajaxReturn($result, $this->returnType);
		}
		
		$isField = M()->execute('desc '.C('DB_PREFIX').$model['name'].' `'.$detail['name'].'`');
	
		if($isField){
			$mod->deleteField($detail);
		}
		
		if($mod->where("id = $detail[id]")->delete()){
			$result = 1;
		}else{
			$result = 0;
		}

		$this->ajaxReturn($result, $this->returnType);
	}

    /**
     * 模型字段详情信息
     * @return json or xml
     */
	function detailField(){
		$mod = new \Admin\Model\AttributeModel();
		$result	= array('status' => '0', 'error' => '');
		
		$model	= M('Model')->getByName(I('get.model'));
		
		if(!$model){
			$result['error'] = '模型不存在';
			$this->ajaxReturn($result, $this->returnType);
		}
		
		$detail = $mod->where(array('name' => I('get.field')))->find();
		
		if($detail){
			$this->ajaxReturn($detail, $this->returnType);
		}else{
			$result['error'] = '字段不存在';
			$this->ajaxReturn($result, $this->returnType);
		}
	}
}
?>