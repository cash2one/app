<?php
// +----------------------------------------------------------------------
// | 标签管理
// +----------------------------------------------------------------------
// | date 2014-9-2
// +----------------------------------------------------------------------
// | Author: crohn <lllliuliu@163.com>
// +----------------------------------------------------------------------

/*
创建onethink_tags表

CREATE TABLE onethink_tags (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '标签ID',
    `category` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分类ID',
    `name` varchar(30) NOT NULL COMMENT '标志',
    `title` varchar(50) NOT NULL COMMENT '标题',
    `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上级标签ID',
    `rootid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '根节点ID',
    `depth` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '层级',
    `sort` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序（同级有效）',
    `list_row` tinyint(3) unsigned NOT NULL DEFAULT '10' COMMENT '列表每页行数',
    `meta_title` varchar(50) NOT NULL DEFAULT '' COMMENT 'SEO的网页标题',
    `keywords` varchar(255) NOT NULL DEFAULT '' COMMENT '关键字',
    `description` varchar(255) NOT NULL DEFAULT '' COMMENT '描述',
    `link_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '外链',
    `display` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '可见性',
    `extend` text NOT NULL COMMENT '扩展设置',
    `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
    `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
    `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '数据状态（-1-删除，0-禁用，1-正常，2-待审核）',
    `icon` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '标签图标',
    `old_id` int(10) unsigned NOT NULL COMMENT '原表ID',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_name` (`name`),
    KEY `pid` (`pid`),
    KEY `rootid` (`rootid`),
    KEY `depth` (`depth`),
    KEY `rootid-depth` (`rootid`,`depth`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='标签表'
       
创建onethink_tags_map表

CREATE TABLE onethink_tags_map (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
    `tid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'tags表ID',
    `did` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '数据表ID',
    `type` varchar(20) NOT NULL DEFAULT '' COMMENT '类型',
    `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
    `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
    PRIMARY KEY (`id`),
    KEY `type` (`type`),
    KEY `did` (`did`),
    KEY `tid` (`tid`)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='标签和数据映射表'

*/

namespace Admin\Controller;

/**
 * 后台标签管理控制器
 */
class TagsController extends AdminController {

    /**
     * 构造函数，处理动态边栏
     * @return void
     */
    public function _initialize(){
        $this->assign('_extra_menu',array(
            '标签管理'=> D('TagsCategory')->getCategory(),
        ));
        parent::_initialize();
    }

    /**
    * 标签管理列表
    * @return void
    */
    public function index(){
        //搜索条件
        $field = I('field');
        $keyword = I('keyword');
        $category = I('category');       
        is_numeric($category) && $map['category'] = $category;

        //分页
        $row = 20;
        $p = intval(I('p'));
        $p<=0 && $p=1;
        //add liujun 2015-07-22
        $column = 'id,id as tagid,name,title,sort,pid,status,category,(select count(*) from onethink_tags_map where tid = tagid and did > 0) as total';
        $order = 'sort DESC';//排序
        if(C('THEME') == 'qbaobei')  $order = 'id ASC';//排序
        if(!empty(I('order_name')) && !empty(I('order_type'))){
        	$order = I('order_name').' '.I('order_type');
        }
        if(!empty($field) && !empty($keyword)){
            $map[$field]  = array('like', '%'.(string)$keyword.'%');
            $count = M('Tags')->field($column)->where($map)->count(); //用于搜索，但是没有保持树形结构
            $p>$count && $p = $count;
            $tree = M('Tags')->field($column)->where($map)->order($order)->page($p,$row)->select();
        }
        else
        	
        {
            $tree = D('Tags')->getTree(0, $column, $map,$order);
            $count = count($tree);
            $p>$count && $p = $count;
            $tree = array_slice($tree, ($p-1)*$row, $row, true);
        }
        //获取标签统计 add liujun 2015-07-20
        foreach($tree as $key=>$value){
        	$mTypes = getTagsMapType();//获取关联类型
        	$countArr = array();
        	foreach($mTypes as $m_key=>$m_value){
        		$mCount = M('TagsMap')->field('count(*) as count')->where(array('type'=>$m_key,'tid'=>$value['id']))->find();
        		$countArr[$m_key]['title'] = $m_value;
        		$countArr[$m_key]['total'] = $mCount['count'];
        	}
        	$tree[$key]['count'] = $countArr;
        }
        
        $this->assign('tree', $tree);
        $page = new \Think\Page($count, $row);
        $p =$page->show();
        $this->assign('_page', $p? $p: '');

        C('_SYS_GET_TAGS_TREE_', true); //标记系统获取标签树模板
        $this->meta_title = '标签管理';
        $this->assign('category', $category);       
        $this->display();
    }



    /**
     * 显示标签树，仅支持内部调
     * @param  array $tree 标签树
     * @return void
     */
    public function tree($tree = null){
        C('_SYS_GET_TAGS_TREE_') || $this->_empty();
        $this->assign('tree', $tree);
        $this->display('tree');
    }

    /**
     * 编辑标签
     * @param  integer $id 标签id
     * @param  integer $pid 标签pid
     * @return void
     */
    public function edit($id = null, $pid = 0){
        $Tags = D('Tags');
        $url = "";
        if(IS_POST){ //提交表单
            if(false !== $Tags->update()){
                $this->success('编辑成功！',U('Tags/index/category/'.I('category')));
            } else {
                $error = $Tags->getError();
                $this->error(empty($error) ? '未知错误！' : $error);
            }
        } else {
            $ptag = '';
            if($pid){
                /* 获取上级标签信息 */
                $ptag = $Tags->info($pid, 'id,name,title,status,rootid,depth,category');
                if(!($ptag && 1 == $ptag['status'])){
                    $this->error('指定的上级标签不存在或被禁用！');
                }
            }

            /* 获取标签信息 */
            $info = $id ? $Tags->info($id) : '';

            $this->assign('info',       $info);
            $this->assign('ptag',   $ptag);
            $this->meta_title = '编辑标签';
            $this->display();
        }
    }

    /**
     * 新增标签
     * @param  integer $pid 标签pid
     * @param  integer $category    分类ID
     * @return void
     */
    public function add($category , $pid = 0){
        $Tags = D('Tags');

        if(IS_POST){ //提交表单
            if(false !== $Tags->update()){
                $this->success('新增成功！',U('Tags/index/category/'.I('category')));
            } else {
                $error = $Tags->getError();
                $this->error(empty($error) ? '未知错误！' : $error);
            }
        } else {
            $ptag = array();
            if($pid){
                /* 获取上级标签信息 */
                $ptag = $Tags->info($pid, 'id,name,title,status,rootid,depth,category');
                if(!($ptag && 1 == $ptag['status'])){
                    $this->error('指定的上级标签不存在或被禁用！');
                }
            }

            /* 获取标签信息 */
            $this->assign('info',       null);
            $this->assign('ptag', $ptag);
            $this->assign('category', $category);
            $this->meta_title = '新增标签';
            $this->display('edit');
        }
    }

    /**
     * 删除一个标签
     * @return void
     */
    public function remove(){
        $id = I('id');
        $ids = I('ids');

        if( empty($id) && (!is_array($ids) || empty($ids)) ){
            $this->error('参数错误!');
        }
        empty($ids) && $ids =array();
        $ids = array_filter(array_merge($ids, array($id)));
              
        foreach ($ids as $id) {
            //判断该标签下有没有子标签，有则不允许删除
            $child = M('Tags')->where(array('pid'=>$id))->field('id')->select();
            if(!empty($child)){
                $this->error('请先删除该标签下的子标签');
            }
        }

        //删除该标签信息
        $res = M('Tags')->delete(implode(',', $ids));

        if($res !== false){
            foreach ($ids as $id) {
                //记录标签删除行为
                action_log('delete_tags', 'tags', $id, UID);
            }
            //删除该标签映射信息
            $res_map= M('TagsMap')->where(array('tid'=>array('in',$ids)))->delete();
            if($res_map !== false){
                foreach ($ids as $id) {
                    //记录标签映射删除行为
                    action_log('delete_tags_map', 'tagsMap', $id, UID);              
                }
                $this->success('删除标签成功！');
            }
        }else{
            $this->error('删除标签失败！');
        }

    }

    /**
     * 操作标签初始化
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

        //获取标签
        $map = array('status'=>1, 'id'=>array('neq', $from));
        $list = M('Tags')->where($map)->field('id,pid,title')->select();


        //移动标签时增加移至根标签
        if(strcmp($type, 'move') == 0){
        	//不允许移动至其子孙标签
            $list = tree_to_list(list_to_tree($list), false);

        	$pid = M('Tags')->getFieldById($from, 'pid');
        	$pid && array_unshift($list, array('id'=>0,'title'=>'根标签'));
        }

        $this->assign('type', $type);
        $this->assign('operate', $operate);
        $this->assign('from', $from);
        $this->assign('list', $list);
        $this->meta_title = $operate.'标签';
        $this->display();
    }

    /**
     * 移动文档
     * @author huajie <banhuajie@163.com>
     */
    public function moveTags() {
        if(empty($_POST['ids'])) {
            $this->error('请选择要移动的标签！');
        }

        //获取分类
      //  dump($_POST['ids']);
    //    exit;

        session('moveTags', $_POST['ids']);
        $this->success('请选择要移动到的标签！');
    }
    /**
     * 粘贴文档
     * @author huajie <banhuajie@163.com>
     */
    public function pasteTags() {
        $moveList = session('moveTags');
        if(empty($moveList)) {
            $this->error('没有选择标签！');
        }
        if(!isset($_POST['category'])) {
            $this->error('请选择要粘贴到的标签分类！');
        }
        $cate_id = I('post.category');   //当前分类

        if(!empty($moveList)) {// 移动    TODO:检查name重复
            foreach ($moveList as $key=>$value){
                $data = array();
                $Model              =   M('Tags');
                $map['id']          =   $value;
                $data['category']=   $cate_id;
                $rs = $Model->where($map)->find();
                if($rs)
                {
                    $prs = $Model->where("id='".$rs['pid']."' AND category='".$data['category']."'")->find();
                    if(empty($prs))
                    {
                        $data['pid']        =  0;
                        $data['rootid'] = $value;
                    }
                }
                $res = $Model->where($map)->save($data);
            }
            session('moveTags',null);
            if(false !== $res){
                $this->success('标签移动成功！');
            }else{
                $this->error('标签移动失败！');
            }
        }
    }
    /**
     * 移动标签
     * @return void
     */
    public function move(){
        $to = I('post.to');
        $from = I('post.from');
        if($to == '0') $id = $from;
        else $id = $to;
        $cate_id =  M('Tags')->field('category')->where(array('id'=>$id))->find();
        if(is_numeric($cate_id['category'])){
            $data['pid'] = $to;
            $data['category'] = $cate_id['category'];
            $data['id'] = $from;
            $res = M('Tags')->save($data);
            if($res !== false){
                $this->success('标签移动成功！', U('index',array('category'=>$cate_id['category'])));
            }
        }
        $this->error('标签移动失败！');
    }


}
