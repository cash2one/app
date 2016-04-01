<?php
// +----------------------------------------------------------------------
// |  系统菜单管理类
// +----------------------------------------------------------------------
// |  Author: 朱德胜
// |  Time  : 2015-07-19
// +----------------------------------------------------------------------

namespace IAPI\Controller;

class MenuController extends CommonController {
	
	private $mod;

	function initialize(){
		$this->mod =  new \Admin\Model\MenuModel();
	}

    /**
     * 添加系统菜单
     * @return json or xml
     */
	function insertMenu(){
		$result = array('status' => 0);
		
		// 创建数据，并验证数据
		$data   = $this->mod->create();
		if(!$data){
			$result['error'] = $this->mod->getError();
			$this->ajaxReturn($result, $this->returnType);
		}
		
		if($data['pid']){
			// 检查栏目路径是否存在，并返回选中菜单ID
			$pid = $this->isMenuExists($data['pid']);

			if(!$pid){
				$result['error'] = '系统菜单不存在';
				$this->ajaxReturn($result, $this->returnType);
			}

			$isId = $data['pid'].'-'.$data['title'];
		}else{
			$pid = 0;
			$isId = $data['title'];
		}

		// 检查菜单是否存在
		if($this->isMenuExists($isId)){
			$result['error'] = '菜单已存在';
			$this->ajaxReturn($result, $this->returnType);
		}
		
		$data['pid'] = $pid;
		
		// 创建系统菜单记录
		if($this->mod->add($data)){
			$result = 1;
		}else{
			$result = 0;
		}

		$this->ajaxReturn($result, $this->returnType);
	}

    /**
     * 修改系统菜单
     * @return json or xml
     */
	function updateMenu(){
		$result = array('status' => 0);
		
		// 创建数据，并验证数据
		$data   = $this->mod->create();
		if(!$data){
			$result['error'] = $this->mod->getError();
			$this->ajaxReturn($result, $this->returnType);
		}
		
		// 记录主键ID
		$data['id'] = $this->isMenuExists(I('post.absTitle'));
		
		if(!$data['id']){
			$result['error'] = '菜单不存在';
			$this->ajaxReturn($result, $this->returnType);
		}
		
		if($data['pid']){
			// 检查栏目路径是否存在，并返回选中菜单ID
			$pid = $this->isMenuExists($data['pid']);

			if(!$pid){
				$result['error'] = '菜单不存在';
				$this->ajaxReturn($result, $this->returnType);
			}
		}else{
			$pid = 0;
		}

		$data['pid'] = $pid;
		
		// 修改系统菜单记录
		if($this->mod->save($data)){
			$result = 1;
		}else{
			$result = 0;
		}

		$this->ajaxReturn($result, $this->returnType);
	}

    /**
     * 删除系统菜单
     * @return json or xml
     */
	function deleteMenu(){
		$id = $this->isMenuExists(I('post.absTitle'));
		
		if(!$id){
			$result['error'] = '菜单不存在';
			$this->ajaxReturn($result, $this->returnType);
		}

		// 删除系统菜单记录
		if($this->mod->delete($id)){
			$result = 1;
		}else{
			$result = 0;
		}

		$this->ajaxReturn($result, $this->returnType);
	}

    /**
     * 系统菜单列表
     * @return json or xml
     */
	function listMenuGet(){
        $type       =   C('CONFIG_GROUP_LIST');
        $all_menu   =   M('Menu')->getField('id,title');
        $map['pid'] =   I('get.pid');

        $result = M("Menu")->where($map)->order('sort asc,id asc')->select();

        int_to_string($result, array('hide'=>array(1=>'是',0=>'否'),'is_dev'=>array(1=>'是',0=>'否')));

        if($result) {
            foreach($result as &$key){
                if($key['pid']){
                    $key['up_title'] = $all_menu[$key['pid']];
                }
            }
			
			$this->ajaxReturn($result, $this->returnType);
        }
	}

    /**
     * 系统菜单关系名称
     * @return json or xml
     */
	function nexusMenuGet(){
		$result = array('status' => 0);

		$id = I('get.id');
		
		if($id === '0'){
			return;
		}
		
		// 菜单详情信息
		$details = $this->mod->where(array('status' => 1, 'id' => $id))->find();

		if(!$details){
			$result['erorr'] = '菜单记录不存在！';
			$this->ajaxReturn($result, $this->returnType);
		}
		
		// 获取上级菜单
		$title = $this->menuRootGet($details['pid']);
		
		$details['absTitle'] = '';
		if($title){
			krsort($title);

			foreach($title as $value){
				$details['absTitle'] .= $value.'-';
			}
		}
		
		$details['absTitle'] .= $details['title'];

		// 栏目返回结果
		$this->ajaxReturn($details, $this->returnType);
	}

    /**
     * 系统菜单详情
	 * @param $id int 菜单ID
     * @return array
     */
	function detailsGet(){
		$result = array('status' => 0);

		$id = I('get.id');
		
		// 菜单详情信息
		$details = $this->mod->where(array('status' => 1, 'id' => $id))->find();

		if(!$details){
			$result['erorr'] = '菜单记录不存在！';
			$this->ajaxReturn($result, $this->returnType);
		}
		
		// 栏目返回结果
		$this->ajaxReturn($details, $this->returnType);
	}

    /**
     * 全部菜单信息
     * @return array
     */
	function listTreeMenu(){
		$menus = $this->mod->select();
		$tree  = new \Common\Model\TreeModel();
        $menus = $tree->toFormatTree($menus);
        $result= array_merge(array(0=>array('id'=>0,'title_show'=>'顶级菜单','absTitle'=>0)), $menus);
		
		$menu = array();
		foreach($menus as $value){
			$menu[$value['id']] = array('title' => $value['title'], 'pid' => $value['pid']);
		}
		
		// 菜单路径
		foreach($result as $k => $value){
			$absTitle = $this->menuAppendTitle($value['id'], $menu);
			
			if($absTitle){
				krsort($absTitle);

				$result[$k]['absTitle'] = implode('-', $absTitle);
			}
		}

		//菜单信息返回
		$this->ajaxReturn($result, $this->returnType);
	}

    /**
     * 检查菜单路径
	 * @param $menu array 系统菜单
     * @return int
     */
	private function isMenuExists($menu){
		$menu = explode('-', $menu);
		$root = array_shift($menu);

		$root = $this->mod->where(array('pid' => 0, 'title' => $root))->find();
		
		if(!$root){
			return false;
		}

		if($menu){
			$higher = $root;
			foreach($menu as $name){
				$higher = $this->mod->where(array('pid' => $higher['id'], 'title' => $name))->find();
				
				if(!$higher){
					return false;
				}
			}
		}else{
			return $root['id'];
		}

		return $higher['id'];
	}

    /**
     * 查询菜单路径
	 * @param $id int 菜单ID
	 * @param $menu array 系统菜单
     * @return array
     */
	private function menuAppendTitle($id, $menu){
		$list = array();
		
		if(isset($menu[$id])){
			$list[] = $menu[$id]['title'];

			if($menu[$id]['pid']){
				$tmp = $this->menuAppendTitle($menu[$id]['pid'], $menu);

				if($tmp){
					$list = array_merge($list, $tmp);
				}
			}
		}

		return $list;
	}

    /**
     * 系统主菜单
	 * @param $id int 菜单ID
     * @return array
     */
	private function menuRootGet($id){
		$list = array();

		$details = $this->mod->where(array('status' => 1, 'id' => $id))->find();
		
		if($details){
			$list[] = $details['title'];
			
			if($details['pid']){
				$menu = $this->menuRootGet($details['pid']);
				if($menu){
					$list = array_merge($list, $menu);
				}
			}
		}

		return $list;
	}
}
?>