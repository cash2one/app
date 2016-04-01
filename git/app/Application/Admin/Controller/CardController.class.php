<?php
// +----------------------------------------------------------------------
// | 卡号管理
// +----------------------------------------------------------------------
// | date 2014-10-8
// +----------------------------------------------------------------------
// | Author: crohn <lllliuliu@163.com>
// +----------------------------------------------------------------------

/*
创建onethink_card表

CREATE TABLE onethink_card (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `center_cid` int(10) unsigned NOT NULL  COMMENT '礼包中心的卡号表数据ID',
    `center_did` int(10) unsigned NOT NULL  COMMENT '礼包中心此卡号所属文章数据ID',
    `did` int(10) unsigned NOT NULL  COMMENT '文章ID',
    `number` varchar(255) NOT NULL COMMENT '号码',
    `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
    `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
    `get_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '获取时间',
    `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '数据状态（-1-删除，0-禁用，1-正常，2-待审核）',
    `draw_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '领取状态（0-待领取，1-已领取）',
    `ip` varchar(30) NOT NULL COMMENT '领取卡号的IP',
    `user_id`  int(10) unsigned NOT NULL  COMMENT '领取卡号的用户ID',
    `draw_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '领取时间',
    PRIMARY KEY (`id`),
    KEY `draw_status` (`draw_status`),
    KEY `ip` (`ip`),
    KEY `user_id` (`user_id`),
    KEY `did` (`did`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='卡号表'
*/

namespace Admin\Controller;

/**
 * 后台卡号管理控制器
 */
class CardController extends AdminController {

    private $name = '卡号';

    /**
     * 构造函数，处理动态边栏
     * @return void
     */
    public function _initialize(){
        parent::_initialize();
    }

    /**
    * 首页
    * @return void
    */
    public function index(){
        $this->meta_title = $this->name.'列表';
        /*条件*/
        $map = array();    
        $did = I('did'); //文章id
        if (!empty($did)){
            //根据did查询文章数据
            $document = D('Package')->detail($did);
            $document && $map['did'] = $did;
        } 
        //查询
        $list       =   D('Card')->getList($map);
        //分页
        $request    =   (array)I('request.');
        $total      =   $list? count($list) : 1 ;
        $listRows   =   C('LIST_ROWS') > 0 ? C('LIST_ROWS') : 10;
        $page       =   new \Think\Page($total, $listRows, $request);
        $voList     =   array_slice($list, $page->firstRow, $page->listRows);
        $p          =   $page->show();
        //参数
        $this->assign('_list', $voList);
        $this->assign('_page', $p? $p: '');
        $this->assign('name', $this->name);
        $this->assign('type', $type);
        $this->assign('document', $document);
        $this->assign('type_list', C('CARD_TYPE'));
        $this->display();
    }

    /**
     * 编辑
     * @param  integer $id id
     * @return void
     */
    public function edit($id){
        $model = D('Card');

        if(IS_POST){ //提交表单
            if(false !== $model->update()){
                $this->success('编辑成功！');
            } else {
                $error = $Tags->getError();
                $this->error(empty($error) ? '未知错误！' : $error);
            }
        } else {
            /* 获取标签信息 */
            $info = $model->info($id);

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
        $model = D('Card');

        if(IS_POST){ //提交表单
            if(false !== $model->update()){
                $this->success('新增成功！');
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
     * 删除
     * @return void
     */
    public function remove(){
        $id = I('id');
        if(empty($id)){
            $this->error('参数错误!');
        }
        $model = D('Card');

        //获取信息
        $info = $model->info($id, 'did');

        //删除信息
        $res = $model->delete($id);

        if($res !== false){
            //记录删除行为
            action_log('delete_card', 'card', $id, UID);
            //刷新APC缓存
            flushCard($info['did']);
            $this->success('删除成功！');
        }else{
            $this->error('删除失败！');
        }

    }
}
