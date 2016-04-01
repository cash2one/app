<?php
// +----------------------------------------------------------------------
// |  数据库表管理类
// +----------------------------------------------------------------------
// |  Author: 朱德胜
// |  Time  : 2015-06-29
// +----------------------------------------------------------------------

namespace IAPI\Controller;

class TableController extends CommonController {

    /**
     * 数据库列表
     * @return json or xml
     */
	function listTable(){
		$result = M()->query("SHOW TABLE STATUS FROM `".C('DB_NAME')."`");

		if($result){
			$this->ajaxReturn($result, $this->returnType);
		}
	}

    /**
     * 数据表字段列表
     * @return json or xml
     */
	function listField(){
		$result = M()->query("SHOW COLUMNS FROM `".I('get.table')."`");

		if($result){
			$this->ajaxReturn($result, $this->returnType);
		}
	}

    /**
     * 新增数据表字段
     * @return json or xml
     */
	function insert(){
		$result	= array('status' => '0', 'error' => '');

		if(I('post.table')){
			$table = I('post.table');
			unset($_POST['table']);
		}
		
		// 检查数据表是否存在
		if(!$this->isTable($table)){
			$result['error'] = '数据表不存在';
			$this->ajaxReturn($result, $this->returnType);
		}
		
		$mod = new \IAPI\Logic\TableLogic($table);
		// 检查字段名称是否存在
		$exists = $mod->execute('desc '.$table.' `'.I('post.name').'`');
		if($exists){
			$result['error'] = '字段已存在';
			$this->ajaxReturn($result, $this->returnType);
		}

		// 新增字段
		if($mod->create()){
			if($mod->addField()){
				$result = 1;
			}else{
				$result = 0;
			}
		}else{
			$result['status'] = '-1';
			$result['error']  = $mod->getError();
		}

		$this->ajaxReturn($result, $this->returnType);
	}

    /**
     * SQL语句建表
     * @return json or xml
     */
	function addSqlTable(){
		$result	= array('status' => '0', 'error' => '');

		$sql = I('post.sql');
		
		//if(!$sql || @eregi("(drop|insert|select|update|delete)[^0-9a-z@\.-]", $sql) || !eregi('CREATE TABLE', $sql)){
		// 暂时先只用添加表开头 crohn 2015-7-14
		if(!$sql || !preg_match('/^(CREATE TABLE|alter table|update)/', $sql)){
			$result['error'] = '无效SQL语句';
			$this->ajaxReturn($result, $this->returnType);
		}
		
		$sql = str_replace(';', '@@', rtrim($sql, ';'));
		
		M()->execute($sql);

		$this->ajaxReturn(1, $this->returnType);
	}

    /**
     * 检查数据表是否存在
     * @param $table 数据表名称
     * @return boolean
     */
	private function isTable($table){
		if($table){
			$tableInfo = M()->query("SHOW TABLE STATUS FROM `".C('DB_NAME')."`");

			foreach($tableInfo as $args){
				if($args['name'] == $table){
					return true;
				}
			}
		}

		return false;
	}
}
?>