<?php
// +----------------------------------------------------------------------
// | 产品标签关联记录管理
// +----------------------------------------------------------------------
// | date 20145-7-13
// +----------------------------------------------------------------------
// | Author: liujun
// +----------------------------------------------------------------------

namespace Admin\Controller;

/**
 * 后台产品标签关联管理控制器
 */
class ProductTagsMapController extends AdminController {

    /**
     * 构造函数，处理动态边栏
     * @return void
     */
    public function _initialize(){
        $this->assign('_extra_menu',array(
            '产品标签管理'=> D('ProductTagsCategory')->getCategory(),
        ));
        parent::_initialize();
    }

    /**
    * 列表
    * @date: 2015-7-13
    * @author: liujun
    */
    public function index(){
    	$tid = intval(I('tid'));//标签Id
    	$type = !empty(trim(I('type')))?trim(I('type')):'down';//类型
    	$row = 30;
        $p = intval(I('p'));
        $p<=0 && $p=1;
    	
    	$info = D('ProductTags')->info($tid);
    	if(!$info){
    		$this->error('产品标签记录不存在！');
    	}
    	$join = 'left join '.C(DB_PREFIX).$type.' as d ON m.did = d.id';
    	$field = 'm.id,m.tid,m.type,m.did,m.sort,m.update_time,d.title';
    	$order = 'm.sort ASC';
    	$where = array('m.tid'=>$tid,'m.type'=>$type);
    	
    	//处理搜索条件
    	if (!empty(I('time_start'))) {
    		$where['m.update_time'][] = array('egt',strtotime(I('time_start')));
    	}
    	if (!empty(I('time_end'))) {
    		$where['m.update_time'][] = array('elt',24*60*60 + strtotime(I('time_end')));
    	}
    	if (!empty(I('title'))){
    		$where['d.title'] = array('like','%'.I('title').'%');
    	}
    	if(!empty(I('order_name')) && !empty(I('order_type'))){
    		$order = I('order_name').' '.I('order_type');
    	}
    	//获取关联记录
    	$counts = M("ProductTagsMap")->alias('m')->join($join)->where($where)->count();
    	$mapList = M('ProductTagsMap')->alias('m')->join($join)->field($field)->where($where)->page($p,$row)->order($order)->select();
    	
    	$page = new \Think\Page($counts, $row);
    	$showPage =$page->show();
    	$this->assign('_page', $showPage?$showPage:'');
    	
    	$this->assign('mapTypes',getTagsMapType());//获取关联表中的类型
    	$this->assign('type',$type);//默认类型
    	$this->assign('p',$p);//当前第几页
    	
    	$this->assign('info', $info);
    	$this->assign('_total',$counts);
    	$this->assign('mapList', $mapList);
    	
    	// 记录当前列表页的cookie
    	Cookie('__forward__',$_SERVER['REQUEST_URI']);
    	
    	$this->display();
    }
    
    /**
     * 新增
     * @date: 2015-7-13
     * @author: liujun
     */
    public function add(){
    	$tid = intval(I('tid'));
    	$type = !empty(trim(I('type')))?trim(I('type')):'down';//类型
    	//获取标签信息
    	$info = D('ProductTags')->info($tid);
    	if(!$info){
    		$this->error('产品标签记录不存在！');
    	}
    	if(IS_POST){ //提交表单
    		$mapData = array(
    			'tid' => $tid,
    			'did' => intval(I('did')),
    			'type' => I('type'),
    			'sort' => I('sort'),
    			'create_time'=>NOW_TIME,
    			'update_time'=>NOW_TIME
    		);
    		$rsInfo = M($mapData['type'])->field('id,title')->where(array('id'=>$mapData['did']))->find();
    		if(!$rsInfo){
    			$this->error('输入的'.getTagsMapType($mapData['type']).'ID不正确！');
    		}
    		$flag = M('ProductTagsMap')->add($mapData);
    		$msg = getTagsMapType($mapData['type']).$mapData['did'].'关联标签'.$info['title'].$mapData['tid'];
    		if($flag){
    			$msg = $msg.'成功！';
    		}else{
    			$msg = $msg.'失败！';
    		}
    		$this->success($msg,U('ProductTagsMap/index/type/'.$mapData['type'].'/tid/'.$mapData['tid']));
    	}
    	
    	$this->assign('type',$type);//默认类型
    	$this->assign('mapTypes',getTagsMapType());//获取关联表中的类型
    	$this->assign('info', $info);
    	$this->display();
    }
    
    /**
    * 编辑
    * @date: 2015-7-13
    * @author: liujun
    */
    public function edit(){
    	$id = intval(I('id'));
    	$type = intval(I('type'));
    	$p = intval(I('p'))?intval(I('p')):1;
    	$info = M('ProductTagsMap')->alias('m')->join('left join '.C(DB_PREFIX).'tags as t on m.tid=t.id')->field('m.*,t.title')->where(array('m.id'=>$id,'m.type'=>$type))->find();
    	if(!$info){
    		$this->error('产品标签关联记录不存在！');
    	}
    	if(IS_POST){ //提交表单
    		$mapData = array(
    			'id' => $id,
    			'sort' => I('sort'),
    			'update_time'=>NOW_TIME
    		);
    		$flag = M('ProductTagsMap')->save($mapData);
    		if($flag){
				$this->success('编辑成功！',U('ProductTagsMap/index/type/'.$info['type'].'/tid/'.$info['tid'].'/p/'.$p));
    		} else {
    			$error = M('ProductTagsMap')->getError();
    			$this->error(empty($error) ? '未知错误！' : $error);
    		}
    	}else{
    		$relationInfo = M($info['type'])->field('title')->where(array('id'=>$info['did']))->find();
    		if($relationInfo){
    			$info['relationTitle'] = $relationInfo['title'];
    		}
    		
    		$this->assign('mapTypes',getTagsMapType());//获取关联表中的类型
    		$this->assign('p',$p);//当前第几页
    		$this->assign('info',$info);
    		$this->display();
    	}
    }
    
    /**
    * 取消产品标签关联记录
    * @date: 2015-7-13
    * @author: liujun
    */
    public function delete(){
    	$id = intval(I('id'));//关联Id
    	$type = trim(I('type'));//类型
    	$info = M('ProductTagsMap')->where(array('id'=>$id,'type'=>$type))->find();
    	if($info){
	    	//删除产品标签信息
	    	$flag = M('ProductTagsMap')->delete($id);
	    	if($flag !== false){
	    		$this->success('取消产品标签关联记录成功！');
	    	}else{
	    		$this->error('取消产品标签关联记录失败！');
	    	}
    	}else{
    		$this->error('找不到产品标签关联记录！');
    	}
    }
}