<?php
// +----------------------------------------------------------------------
// | 基础分类管理
// +----------------------------------------------------------------------
// | date 2014-10-17
// +----------------------------------------------------------------------
// | Author: crohn <lllliuliu@163.com>
// +----------------------------------------------------------------------

/*
创建表模板 请手动创建
CREATE TABLE `onethink_***_category` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '分类ID',
`name` varchar(30) NOT NULL COMMENT '标志',
`title` varchar(50) NOT NULL COMMENT '标题',
`pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上级分类ID',
`rootid` int(10) NOT NULL  COMMENT '根分类ID',
`depth` int(10) NOT NULL COMMENT '层级',
`sort` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序（同级有效）',
`static_path` varchar(255) NOT NULL DEFAULT '' COMMENT '静态文件路径',
`list_row` tinyint(3) unsigned NOT NULL DEFAULT '10' COMMENT '列表每页行数',
`meta_title` varchar(50) NOT NULL DEFAULT '' COMMENT 'SEO的网页标题',
`keywords` varchar(255) NOT NULL DEFAULT '' COMMENT '关键字',
`description` varchar(255) NOT NULL DEFAULT '' COMMENT '描述',
`template_index` varchar(100) NOT NULL COMMENT '频道页模板',
`template_lists` varchar(100) NOT NULL COMMENT '列表页模板',
`template_detail` varchar(100) NOT NULL COMMENT '详情页模板',
`template_edit` varchar(100) NOT NULL COMMENT '编辑页模板',
`model` varchar(100) NOT NULL DEFAULT '' COMMENT '列表绑定模型',
`model_sub` varchar(100) NOT NULL DEFAULT '' COMMENT '子文档绑定模型',
`type` varchar(100) NOT NULL DEFAULT '' COMMENT '允许发布的内容类型',
`link_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '外链',
`allow_publish` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否允许发布内容',
`display` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '可见性',
`reply` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否允许回复',
`check` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '发布的文章是否需要审核',
`reply_model` varchar(100) NOT NULL DEFAULT '',
`extend` text NOT NULL COMMENT '扩展设置',
`create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
`update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
`status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '数据状态',
`icon` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分类图标',
PRIMARY KEY (`id`),
UNIQUE KEY `uk_name` (`name`) USING BTREE,
KEY `pid` (`pid`),
KEY `rootid` (`rootid`),
KEY `depth` (`depth`),
KEY `rootid-depth` (`rootid`,`depth`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='**分类表';
 */

namespace Admin\Controller;

/**
 *  基础分类管理控制器
 */
class BaseCategoryController extends AdminController
{

    //基础分类模板文件夹
    protected $base_view = 'BaseCategory';

    /**
     * 控制器初始化
     */
    protected function _initialize()
    {
        $this->assign('cate_name', $this->cate_name);
        parent::_initialize();
    }

    /**
     * 分页大小
     */
    const PAGE_SIZE = 5;

    /**
     * 分类管理列表
     *
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function index()
    {
        $model = D($this->cate_name);

        //分页
        $page = new \Think\Page($model->where(['pid' => 0])->count(), self::PAGE_SIZE);
        $cur_page = I('get.p', 1) + 0;
        $start = ($cur_page - 1) * self::PAGE_SIZE;
        $list = $model->field('id,name,title,sort,pid,allow_publish,status')->
            limit($start . ',' . self::PAGE_SIZE)->where(['pid' => 0])->select();

        //获取根分类的子树
        foreach ($list as $k => $v) {
            $child_tree = $model->getTreeById($v['id'], 'id,name,title,sort,pid,allow_publish,status');
            if (!empty($child_tree)) {
                $list[$k]['_'] = $child_tree;
            }
        }

        $this->assign('_page', $page->show());
        $this->assign('tree', $list);
        $this->assign('cate_name', $this->cate_name);
        $this->assign('document_name', $this->document_name);
        $this->meta_title = '分类管理';
        $this->display($this->view_select[strtolower(ACTION_NAME)] ? $this->view_select[strtolower(ACTION_NAME)] : $this->base_view . ':' . strtolower(ACTION_NAME));
    }

    /**
     * 显示分类树，仅支持内部调
     *
     * @param  array $tree 分类树
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function tree($tree = null)
    {
        $this->assign('tree', $tree);
        $this->assign('cate_name', $this->cate_name);
        $this->display($this->base_view . ':tree');
    }

    /* 编辑分类 */
    public function edit($id = null, $pid = 0)
    {
        $Category = D($this->cate_name);

        if (IS_POST) {
            //提交表单
            if (false !== $Category->update()) {
                $this->success('编辑成功！', U('index'));
            } else {
                $error = $Category->getError();
                $this->error(empty($error) ? '未知错误！' : $error);
            }
        } else {
            $cate = '';
            if ($pid) {
                /* 获取上级分类信息 */
                $cate = $Category->info($pid, 'id,name,title,status,rootid,depth');
                if (!($cate && 1 == $cate['status'])) {
                    $this->error('指定的上级分类不存在或被禁用！');
                }
            }

            //可选模型列表
            $this->getCurrentModel();

            /* 获取分类信息 */
            $info = $id ? $Category->info($id) : '';

            $this->assign('info', $info);
            $this->assign('category', $cate);
            $this->meta_title = '编辑分类';
            $this->display($this->view_select[strtolower(ACTION_NAME)] ? $this->view_select[strtolower(ACTION_NAME)] : $this->base_view . ':' . strtolower(ACTION_NAME));
        }
    }

    /* 新增分类 */
    public function add($pid = 0)
    {
        $Category = D($this->cate_name);

        if (IS_POST) {
            //提交表单
            if (false !== $Category->update()) {
                $this->success('新增成功！', U('index'));
            } else {
                $error = $Category->getError();
                $this->error(empty($error) ? '未知错误！' : $error);
            }
        } else {
            $cate = array();
            if ($pid) {
                /* 获取上级分类信息 */
                $cate = $Category->info($pid, 'id,name,title,status,rootid,depth');
                if (!($cate && 1 == $cate['status'])) {
                    $this->error('指定的上级分类不存在或被禁用！');
                }
            }

            //可选模型列表
            $this->getCurrentModel();

            /* 获取分类信息 */
            $this->assign('info', null);
            $this->assign('category', $cate);
            $this->meta_title = '新增分类';
            $this->display($this->view_select[strtolower(ACTION_NAME)] ? $this->view_select[strtolower(ACTION_NAME)] : $this->base_view . ':' . strtolower(ACTION_NAME));
        }
    }

    /**
     * 获取当前可选模型
     *
     * @author crohn <lllliuliu@163.com>
     */
    public function getCurrentModel()
    {
        //获取主模型ID
        $mid = get_model_by('name', strtolower($this->document_name), 'id');
        //获取继承当前主模型列表模型
        $model_list = get_document_model_by_extend(null, null, $mid['id']);
        $this->assign('model_list', $model_list);
    }

    /**
     * 删除一个分类
     *
     * @author huajie <banhuajie@163.com>
     */
    public function remove()
    {
        $cate_id = I('id');
        if (empty($cate_id)) {
            $this->error('参数错误!');
        }

        //判断该分类下有没有子分类，有则不允许删除
        $child = M($this->cate_name)->where(array('pid' => $cate_id))->field('id')->select();
        if (!empty($child)) {
            $this->error('请先删除该分类下的子分类');
        }

        //判断该分类下有没有内容
        $document_list = M($this->document_name)->where(array('category_id' => $cate_id))->field('id')->select();
        if (!empty($document_list)) {
            $this->error('请先删除该分类下的数据（包含回收站）');
        }

        //删除该分类信息
        $res = M($this->cate_name)->delete($cate_id);
        if ($res !== false) {
            //记录行为
            action_log('update_' . $this->cate_name, $this->document_name, $cate_id, UID);
            $this->success('删除分类成功！');
        } else {
            $this->error('删除分类失败！');
        }
    }

    /**
     * 操作分类初始化
     *
     * @param string $type
     * @author huajie <banhuajie@163.com>
     */
    public function operate($type = 'move')
    {
        //检查操作参数
        if (strcmp($type, 'move') == 0) {
            $operate = '移动';
        } elseif (strcmp($type, 'merge') == 0) {
            $operate = '合并';
        } else {
            $this->error('参数错误！');
        }
        $from = intval(I('get.from'));
        empty($from) && $this->error('参数错误！');

        //获取分类
        $map = array('status' => 1, 'id' => array('neq', $from));
        $list = M($this->cate_name)->where($map)->field('id,pid,title')->select();

        //移动分类时增加移至根分类
        if (strcmp($type, 'move') == 0) {
            //不允许移动至其子孙分类
            $list = tree_to_list(list_to_tree($list));

            $pid = M($this->cate_name)->getFieldById($from, 'pid');
            $pid && array_unshift($list, array('id' => 0, 'title' => '根分类'));
        }

        $this->assign('type', $type);
        $this->assign('operate', $operate);
        $this->assign('from', $from);
        $this->assign('list', $list);
        $this->meta_title = $operate . '分类';
        $this->display($this->view_select[strtolower(ACTION_NAME)] ? $this->view_select[strtolower(ACTION_NAME)] : $this->base_view . ':' . strtolower(ACTION_NAME));
    }

    /**
     * 移动分类
     *
     * @author huajie <banhuajie@163.com>
     */
    public function move()
    {
        $to = I('post.to');
        $from = I('post.from');
        $res = M($this->cate_name)->where(array('id' => $from))->setField('pid', $to);
        if ($res !== false) {
            $this->success('分类移动成功！', U('index'));
        } else {
            $this->error('分类移动失败！');
        }
    }

    /**
     * 合并分类
     *
     * @author huajie <banhuajie@163.com>
     */
    public function merge()
    {
        $to = I('post.to');
        $from = I('post.from');
        $Model = M($this->cate_name);

        //检查分类绑定的模型
        $from_models = explode(',', $Model->getFieldById($from, 'model'));
        $to_models = explode(',', $Model->getFieldById($to, 'model'));
        foreach ($from_models as $value) {
            if (!in_array($value, $to_models)) {
                $this->error('模型不一致无法合并');
            }
        }

        //检查分类选择的文档类型
        $from_types = explode(',', $Model->getFieldById($from, 'type'));
        $to_types = explode(',', $Model->getFieldById($to, 'type'));
        foreach ($from_types as $value) {
            if (!in_array($value, $to_types)) {
                $this->error('文档类型不一致无法合并');
            }
        }

        //合并文档
        $res = M($this->document_name)->where(array('category_id' => $from))->setField('category_id', $to);

        if ($res !== false) {
            //删除被合并的分类
            $Model->delete($from);
            $this->success('合并分类成功！', U('index'));
        } else {
            $this->error('合并分类失败！');
        }

    }

    /**
     * 分类搜索
     *
     * @author sunjianhua
     * @return
     */
    public function search()
    {
        $kw = trim(I('post.kw'));
        if (empty($kw)) {
            $this->error('搜索关键词不能为空');
        }

        $model = D($this->cate_name);
        $list = $model->field('id,name,title,sort,pid,rootid,allow_publish,status')->
            where("status > -1 and title like '%s'", '%' . $kw . '%')->select();
        if (empty($list)) {
            $this->error('没有相关搜索结果。换个关键词试试？');
        }

        //获取非根节点的整个节点树
        $rootids = [];//保存已获取的节点树，避免重复获取
        foreach ($list as $k => $v) {
            if ($v['pid'] > 0) {
                //获取当前节点的自根节点起的子树
                if (!isset($rootids[$v['rootid']])) {
                    $list[$k] = $model->getTreeById($v['rootid'], 'id,name,title,sort,pid,rootid,allow_publish,status', true);
                    $rootids[$v['rootid']] = $list[$k];
                } else {
                    if (!in_array($rootids[$v['rootid']], $list)) {
                        $list[$k] = $rootids[$v['rootid']];
                    } else {
                        //剔除同一根节点下的搜索结果
                        unset($list[$k]);
                    }
                }
            } else {
                $list[$k]['_'] = $model->getTreeById($v['id'], 'id,name,title,sort,pid,rootid,allow_publish,status');
                $rootids[$v['rootid']] = $list[$k];
            }
        }

        $this->assign('tree', $list);
        $this->assign('cate_name', $this->cate_name);
        $this->assign('document_name', $this->document_name);
        $this->meta_title = '分类搜索结果';
        $this->display($this->view_select[strtolower(ACTION_NAME)] ? $this->view_select[strtolower(ACTION_NAME)] :
            $this->base_view . ':index');
    }

//     /**
    //      * 建表
    //      * @return void
    //      */
    //     public function createTable(){
    //         //检查表是否存在
    //         $sql = <<<sql
    //               SHOW TABLES LIKE '{$this->table_name}';
    // sql;

//         $res = M()->query($sql);

//         if(count($res)){
    //             $sql = <<<sql
    //                 CREATE TABLE IF NOT EXISTS `{$this->table_name}` (
    //                 `{$field['name']}`  {$field['field']} {$default} COMMENT '{$field['title']}'
    //                 )
    //                 ENGINE={$model_info['engine_type']}
    //                 DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
    //                 CHECKSUM=0
    //                 ROW_FORMAT=DYNAMIC
    //                 DELAY_KEY_WRITE=0
    //                 ;
    // sql;

//         }
    //         $res = M()->execute($sql);
    //         return $res !== false;
    //     }
}
