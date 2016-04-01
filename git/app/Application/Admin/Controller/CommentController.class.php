<?php
// +----------------------------------------------------------------------
// leha modified
// +----------------------------------------------------------------------

namespace Admin\Controller;

/**
 * 后台控制器
 * @author leha.com
 */

class CommentController extends AdminController {

    /**
     * 列表
     * @author leha.com
     */
    public function index() {
		/*--数据字段内容替换处理--*/
		$type_text = array(
			'type' =>
				array(
					'document' => '文章',
					'package'=>'礼包',
					'down'=>'下载',
					'comment' => '评论',
				)
		);

		$pid           = I('get.pid', 0);
		$curr_page_num = I('p') ? I('p') : 1 ;
		$map           = array('enabled' => array('gt', -1), 'pid'=>$pid);//获取列表
		$page_size     = 10;

		/*--获取数据--*/
        $list = M('comment')->where($map)->order('id desc')->page($curr_page_num.','.$page_size)->select();
        int_to_string($list, $type_text);//字段内容替换操作


		/*--分页处理--*/
        $count      = M('comment')->where($map)->count();// 查询满足要求的总记录数
        $Page       = new \Think\Page($count, $page_size);// 实例化分页类 传入总记录数和每页显示的记录数
        $pagination = $Page->show();// 分页显示输出


		$this->meta_title = '评论管理';
        $this->assign('list', $list);
		$this->assign('pagination',$pagination);

        $this->display();
    }

    /**
     * 添加
     * @author leha.com
     */
    public function add(){
    	$models=get_model_by('id',11);
    	$this->assign('models', $models);
    	//获取表单字段排序
    	$fields = get_model_attribute($models['id']);
    	$this->assign('fields',$fields);
        if(IS_POST){
            $comment = D('comment');
            $data = $comment->create();
            if($data){
                $id = $comment->add();
                if($id){
                    $this->success('新增成功', U('index'));
                    //记录行为
                    action_log('update_comment', 'comment', $id, UID);
                } else {
                    $this->error('新增失败');
                }
            } else {
                $this->error($comment->getError());
            }
        } else {
            $pid = I('get.pid', 0);
            //获取父节点
            if(!empty($pid)){
                $parent = M('comment')->where(array('id'=>$pid))->field('title')->find();
                $this->assign('parent', $parent);
            }

            $this->assign('pid', $pid);
            $this->assign('info',null);
            $this->meta_title = '新增';
            $this->display('edit');
        }
    }

    /**
     * 编辑
     * @author leha.com
     */
    public function edit($id = 0){
//    	parse_str('box%5B%5D=3&box%5B%5D=5',$a);
    	$models=get_model_by('id',11);
    	$this->assign('models', $models);
    	//获取表单字段排序
    	$fields = get_model_attribute($models['id']);
    	$this->assign('fields',$fields);
    	//var_dump($models);
//echo json_encode(array('test'=>'{:comment("box%5B%5D=1")}'));
        if(IS_POST){
            $comment = D('comment');
            $regular='/<div\s+class="dragable"\s+widget="([^"]+)"[^>]*?>(.*?)<\/div>/is';
            if(preg_match_all($regular,$_POST['content'],$results)){
            	//die(var_dump($results));
            	foreach($results[1] as $k=>$v){
            		$widgets[$v]=$results[2][$k];
            	}
            	$_POST['widget']=json_encode($widgets);
            }
            $data = $comment->create();

            //$regular='/\s+<div[^>]*>([^<]*?)<\/div>/is';

            if($data){
                if($comment->save()){
                    //记录行为
                    action_log('update_comment', 'comment', $data['id'], UID);
                    $this->success('编辑成功', U('index'));
                } else {
                    $this->error('编辑失败');
                }

            } else {
                $this->error($comment->getError());
            }
        } else {
            $data = array();
            /* 获取数据 */
            $data = M('comment')->find($id);
            
            if(false === $data){
                $this->error('获取配置信息错误');
            }

            $pid = I('get.pid', 0);
            //获取父节点
            if(!empty($pid)){
            	$parent = M('comment')->where(array('id'=>$pid))->field('title')->find();
            	$this->assign('parent', $parent);
            }

            $this->assign('pid', $pid);
            $this->assign('data', $data);
            $this->meta_title = '编辑';
            $this->display();
        }
    }

    /**
     * 删除
     * @author leha.com
     */
    public function remove(){
        $id = array_unique((array)I('id',0));

        if ( empty($id) ) {
            $this->error('请选择要操作的数据!');
        }

        $map = array('id' => array('in', $id) );
        if(M('comment')->where($map)->delete()){
            //记录行为
            action_log('update_comment', 'comment', $id, UID);
            $this->success('删除成功');
        } else {
            $this->error('删除失败！');
        }
    }

	/**
	 * 批量操作(启用|禁用|删除)
	 */
	public function batch_operator()
	{
		$operator_type = array('enable' => 1, 'disable' => 0, 'delete' => -1);//操作及标识定义
		$operator      = I('get.op_type', '');
		$comment_ids   = array_unique(I('comment_ids'));


		if (empty ($comment_ids)  || !is_array($comment_ids)||  1 > count($comment_ids))
			$this->error('请选择需要操作的评论！');


		if (!array_key_exists(strtolower($operator), $operator_type) || !isset($operator_type[$operator]))
			$this->error('非法操作!');


		//过滤参数
		foreach ($comment_ids as $k => $v) {
			$v = (int)$v;
			if ( empty ($v)  || !is_numeric($v) )
				unset ($comment_ids[$k]);

			$comment_ids[$k] = $v;
		}


		$data['enabled'] = $operator_type[$operator];
		$where = array('id' => array('in', $comment_ids));


		//comment表enabled字段已经设置为:unsigned,所以不用状态值表示了，直接物理删除.不明白当初是怎么考虑的？
		if ($operator == 'delete') {
			M('comment')->where($where)->delete();
			$this->success('操作成功');
		}


		$msg = array('success' => '操作成功','error' => '操作失败', 'url' => '','ajax' => true);
		$this->editRow('comment', $data, $where, $msg);
	}
    

    /***覆盖状态改变的3个方法***/

	/**
	 * 删除
	 *
	 * @param string $model
	 * @param array  $where
	 * @param array  $msg
	 */
    protected function delete ( $model , $where = array() , $msg = array( 'success'=>'删除成功！', 'error'=>'删除失败！')) {
	    $data['enabled']=-1;
	    $this->editRow(   $model , $data, $where, $msg);
    }

	/**
	 * 启用
	 *
	 * @param string $model
	 * @param array  $where
	 * @param array  $msg
	 */
    protected function resume (  $model , $where = array() , $msg = array( 'success'=>'状态恢复成功！', 'error'=>'状态恢复失败！')){
    	$data    =  array('enabled' => 1);
    	$this->editRow(   $model , $data, $where, $msg);
    }

	/**
	 * 禁用
	 *
	 * @param string $model
	 * @param array  $where
	 * @param array  $msg
	 */
    protected function forbid ( $model , $where = array() , $msg = array( 'success'=>'状态禁用成功！', 'error'=>'状态禁用失败！')){
    	$data    =  array('enabled' => 0);
    	$this->editRow( $model , $data, $where, $msg);
    }
}