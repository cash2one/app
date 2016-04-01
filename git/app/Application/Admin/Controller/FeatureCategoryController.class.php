<?php
// +----------------------------------------------------------------------
// | 分类管理
// +----------------------------------------------------------------------
// | date 2014-8-6
// +----------------------------------------------------------------------
// | Author: leha.com
// +----------------------------------------------------------------------

/*
创建category_tags表
*/
namespace Admin\Controller;

/**
 * 后台分类管理控制器
 */
class FeatureCategoryController extends AdminController {
	protected $document_name = 'FeatureCategory';

    /**
     * 分类管理列表
     */
    public function index($pid=0){
    	$results=M($this->document_name)->where('pid=0')->select();
    	$this->assign('results', $results);
    	$this->display();		   	
    }
	
     /**
     * 返回json结构的分类树，用于easyui的combotree插件使用
     * @param  integer $did    文档ID，如果传入参数则返回此文档选取分类的ID，否则返回空
     * @param  integer $id    分类ID
     * @param  array $field 需要查询的字段
     * @return string json结构数据 json之前的数组结构为array('json'=>json_arr, 'select_tags'=>selectid_arr)
     */
    public function getJson($did = 0, $id = 0, $field = array('id','title'=>'text','pid')){
        //获取数据
        $model = D($this->document_name);
        $list = $model->field($field)->where('status=1')->order('sort')->select();

        //combotree数据源格式化
        $json = array();
        if(is_array($list)) {
            //创建基于主键的数组引用
            $refer = array();
            foreach ($list as $key => $data) {
                $refer[$data['id']] =& $list[$key];
            }
            //格式化数据
            foreach ($list as $key => $data) {
                // 判断是否存在parent
                $parentId =  $data['pid'];
                if (0 == $parentId) {
                    $json[] =& $list[$key];
                }else{
                    if (isset($refer[$parentId])) {
                        $parent =& $refer[$parentId];
                        $parent['children'][] =& $list[$key];
                    }
                }
            }
        }

        //获取当前did已经选取的tags
        $select_tags = array();
        if ($did) {
            $rs = M('tagsMap')->field('tid, type')->where('did='.$did)->select();

            foreach ($rs as $v) {
                switch ($v['type']) {
                    case 1:
                        $select_tags['main'][] = $v['tid'];
                        break;
                    case 2:
                        $select_tags['sub'][] = $v['tid'];
                        break;
                }
            }
        }

        $last = array('json'=>$json,'select_tags'=>$select_tags);
        echo json_encode($last);
    }   

    /**
     * 显示分类树，仅支持内部调
     * @param  array $tree 分类树
     * @return void
     */
    public function tree($tree = null){
        C('_SYS_GET_TAGS_TREE_') || $this->_empty();
        $this->assign('tree', $tree);
        $this->display('tree');
    }

    /* 编辑分类 */
    public function edit($id = null, $pid = 0){
        $Tags = D($this->document_name);

        if(IS_POST){ //提交表单
        	$data = $Tags->create();
            if(false !== $Tags->save()){
                $this->success('编辑成功！',U('index'));
            } else {
                $error = $Tags->getError();
                $this->error(empty($error) ? '未知错误！' : $error);
            }
        } else {
            $cate = '';
            if($pid){
                /* 获取上级分类信息 */
                $cate = $Tags->where('id='.$pid)->find();
                if(!($cate)){
                    $this->error('指定的上级分类不存在或被禁用！');
                }
            }

            /* 获取分类信息 */
            $info = $id ? $Tags->where('id='.$id)->find(): '';

            $this->assign('info',       $info);
            $this->assign('tags',   $cate);
            $this->meta_title = '编辑分类';
            $this->display();
        }
    }

    /* 新增分类 */
    public function add($pid = 0){
        $Tags = D($this->document_name);

        if(IS_POST){ //提交表单
        	$data = $Tags->create();
            if(false !== ($res=$Tags->add())){
            	if($pid) $Tags->where('id='.$pid)->save(array('is_parent'=>'1'));
                //保存根栏目id
                if($res && in_array('rootid',$Tags->getDbFields()))
                {
                    if($pid==0)
                    {
                        $Tags->where('id='.$res)->setField('rootid', $res);
                    }
                    else
                    {
                        $pcate = $Tags->where('id='.$pid)->field('rootid')->find();
                        if(is_numeric($pcate['rootid']))
                        $Tags->where('id='.$res)->setField('rootid', $pcate['rootid']);
                    }
                }
                $this->success('新增成功！',U('index'));
            } else {
                $error = $Tags->getError();
                $this->error(empty($error) ? '未知错误！' : $error);
            }
        } else {
            $cate = array();
            
            if($pid){
                /* 获取上级分类信息 */
                $cate = $Tags->where('id='.$pid)->find();
                //die(var_dump($cate));
                if(!($cate)){
                    $this->error('指定的上级分类不存在或被禁用！');
                }
            }

            /* 获取分类信息 */
            $this->assign('info',       null);
            $this->assign('tags', $cate);
            $this->meta_title = '新增分类';
            $this->display('edit');
        }
    }

    /**
     * 删除一个分类
     * @return void
     */
    public function remove(){
        $cate_id = I('id');
        if(empty($cate_id)){
            $this->error('参数错误!');
        }

        //判断该分类下有没有子分类，有则不允许删除
        $child = M($this->document_name)->where(array('pid'=>$cate_id))->field('id')->select();
        if(!empty($child)){
            $this->error('请先删除该分类下的子分类');
        }

        //删除该分类信息
        $res = M($this->document_name)->delete($cate_id);

        if($res !== false){
            //记录分类删除行为
            action_log('delete_tags', 'tags', $cate_id, UID);

            //删除该分类映射信息
            $res_map= M('TagsMap')->where('tid='.$cate_id)->delete();
             if($res_map !== false){
                //记录分类映射删除行为
                action_log('delete_tags_map_attach', 'tagsMap', $cate_id, UID);              
                $this->success('删除分类成功！');
             }
        }else{
            $this->error('删除分类失败！');
        }

    }

    /**
     * 操作分类初始化
     * @param string $type
     * @return void
     */
    public function operate($type = 'move'){
        //检查操作参数
        if(strcmp($type, 'move') == 0){
            $operate = '移动';
        }elseif(strcmp($type, 'merge') == 0){
            $operate = '合并';
        }else{
            $this->error('参数错误！');
        }
        $from = intval(I('get.from'));
        empty($from) && $this->error('参数错误！');

        //获取分类
        $map = array('status'=>1, 'id'=>array('neq', $from));//,'is_parent'=>1);
        $list= M($this->document_name)->where($map)->field('id,pid,title,is_parent')->select();
        //die(var_dump($list[0]));

        //移动分类时增加移至根分类
        if(strcmp($type, 'move') == 0){
        	//不允许移动至其子孙分类
        	$list = tree_to_list(list_to_tree($list));

        	$pid = M($this->document_name)->getFieldById($from, 'pid');
        	$pid && array_unshift($list, array('id'=>0,'title'=>'根分类'));
        }

        $this->assign('type', $type);
        $this->assign('operate', $operate);
        $this->assign('from', $from);
        $this->assign('list', $list);
        $this->meta_title = $operate.'分类';
        $this->display();
    }

    /**
     * 移动分类
     * @return void
     */
    public function move(){
        $to = I('post.to');
        $from = I('post.from');
        $res = M($this->document_name)->where(array('id'=>$from))->setField('pid', $to);
        if($res !== false){
            $this->success('分类移动成功！', U('index'));
        }else{
            $this->error('分类移动失败！');
        }
    }
    
    /**
     * 子分类
     */    
    public function child($pid){
    	if($v['pid']>0) $split='────';
    	else $split='─';
    	//$temp=$this->tree($v['id']);
    	$child=$temp?'├':'└';
    	
    	$results=M($this->document_name)->where('pid='.$pid)->select();
    	$this->assign('split',$split);
    	$this->assign('child',$child);
    	$this->assign('childs',$results);
    	$this->display();
    	//var_dump($results);
    }

}
