<?php
// +----------------------------------------------------------------------
// | 基础分类管理模型
// +----------------------------------------------------------------------
// | date 2014-10-17
// +----------------------------------------------------------------------
// | Author: crohn <lllliuliu@163.com>
// +----------------------------------------------------------------------

namespace Admin\Model;

use Think\Model;

/**
 * 基础分类管理模型
 */
class BaseCategoryModel extends Model
{

    protected $_validate = array(
        array('name', 'require', '标识不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('name', '', '标识已经存在', self::VALUE_VALIDATE, 'unique', self::MODEL_BOTH),
        array('title', 'require', '名称不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('meta_title', '1,50', '网页标题不能超过50个字符', self::VALUE_VALIDATE, 'length', self::MODEL_BOTH),
        array('recommend_view_name', '1,20', '推荐名称不能超过20个字符', self::VALUE_VALIDATE, 'length', self::MODEL_BOTH),
        array('keywords', '1,255', '网页关键字不能超过255个字符', self::VALUE_VALIDATE, 'length', self::MODEL_BOTH),
        array('meta_title', '1,255', '网页描述不能超过255个字符', self::VALUE_VALIDATE, 'length', self::MODEL_BOTH),
    );

    protected $_auto = array(
        array('model', 'arr2str', self::MODEL_BOTH, 'function'),
        array('model', null, self::MODEL_BOTH, 'ignore'),
        array('model_sub', 'arr2str', self::MODEL_BOTH, 'function'),
        array('model_sub', null, self::MODEL_BOTH, 'ignore'),
        array('type', 'arr2str', self::MODEL_BOTH, 'function'),
        array('type', null, self::MODEL_BOTH, 'ignore'),
        array('reply_model', 'arr2str', self::MODEL_BOTH, 'function'),
        array('reply_model', null, self::MODEL_BOTH, 'ignore'),
        array('extend', 'json_encode', self::MODEL_BOTH, 'function'),
        array('extend', null, self::MODEL_BOTH, 'ignore'),
        array('position', 'getPosition', self::MODEL_BOTH, 'callback'),
        array('create_time', NOW_TIME, self::MODEL_INSERT),
        array('update_time', NOW_TIME, self::MODEL_BOTH),
        array('status', '1', self::MODEL_BOTH),
    );

    /**
     * 获取分类详细信息
     *
     * @param  milit   $id    分类ID或标识
     * @param  boolean $field 查询字段
     * @return array     分类信息
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function info($id, $field = true)
    {
        /* 获取分类信息 */
        $map = array();
        if (is_numeric($id)) {
            //通过ID查询
            $map['id'] = $id;
        } else {
            //通过标识查询
            $map['name'] = $id;
        }

        return $this->field($field)->where($map)->find();
    }

    /**
     * 获取分类树，指定分类则返回指定分类极其子分类，不指定则返回所有分类树
     *
     * @param  integer $id     分类ID
     * @param  boolean $field  查询字段
     * @param          $typeid 分类区分后台展示，生成列表页面 0，生成列表。1，展示
     * @return array          分类树
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function getTree($id = 0, $field = true, $typeid = 0)
    {
        /* 获取当前分类信息 */
        if ($id) {
            $info = $this->info($id);
            $id = $info['id'];
        }

        /* 获取所有分类 */
        if ($typeid == 1) {
            $map = array('status' => array('gt', -1));
        } else {
            $map = array('status' => 1);
        }

        $list = $this->field($field)->where($map)->order('sort')->select();
        $list = list_to_tree($list, $pk = 'id', $pid = 'pid', $child = '_', $root = $id);

        /* 获取返回数据 */
        if (isset($info)) {
            //指定分类则返回当前分类极其子分类
            $info['_'] = $list;
        } else {
            //否则返回所有分类
            $info = $list;
        }

        return $info;
    }

    /**
     * 获取指定分类的分类树
     *
     * @param  integer $id    分类ID
     * @param  mixed   $field 查询字段
     * @param  boolean   $include_self 节点树是否包含当前节点自身
     * @return array         分类树
     * @author  sunjianhua
     */
    public function getTreeById($id, $field = true, $include_self = false)
    {
        $map = array('status' => array('gt', -1));
        $list = $this->field($field)->where($map)->select();
        $tree = list_to_tree($list, $pk = 'id', $pid = 'pid', $child = '_', $root = $id);

        if ($include_self) {
            $index = -1;
            foreach ($list as $k => $v) {
                if ($v['id'] == $id) {
                    $list[$k]['_'] = $tree;
                    $index = $k;
                    break;
                }
            }
            return ($index === -1) ? [] : $list[$index];
        }

        return $tree;
    }

    /**
     * 获取指定分类子分类ID
     *
     * @param  string $cate 分类ID
     * @return string       id列表
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function getChildrenId($cate)
    {
        $field = 'id,name,pid,title,link_id';
        $category = $this->getTree($cate, $field);
        $ids[] = $cate;
        foreach ($category['_'] as $key => $value) {
            $ids[] = $value['id'];
        }

        return implode(',', $ids);
    }

    /**
     * 描述：获取所有子分类(修改获取子类方式： 谭坚  2015-6-25)
     *
     * @param $cate
     * @return string
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     *
     */
    public function getAllChildrenId($cate, &$ids = array())
    {
        $field = 'id,name,pid,title,link_id';
        $category = $this->getTree($cate, $field);
        $ids[] = $cate;
        foreach ($category['_'] as $key => $value) {
            $ids[] = $value['id'];
            $this->getAllChildrenId($value['id'], $ids);
        }
        $ids = array_unique($ids);

        return implode(',', $ids);
    }

    /**
     * 获取指定分类的同级分类
     *
     * @param  integer $id    分类ID
     * @param  boolean $field 查询字段
     * @return array
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function getSameLevel($id, $field = true)
    {
        $info = $this->info($id, 'pid');
        $map = array('pid' => $info['pid'], 'status' => 1);

        return $this->field($field)->where($map)->order('sort')->select();
    }

    /**
     * 更新分类信息
     *
     * @return boolean 更新状态
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function update()
    {
        $data = $this->create();
        if (!$data) {
            //数据对象创建错误
            return false;
        }
        //保留自动完成，改进分类状态使之可人工操作
        $data['status'] = I('post.status');
        /* 添加或更新数据 */
        if (empty($data['id'])) {
            /* 新增数据设置rootid和depth */
            if ($data['pid'] == 0) {
                //根分类depth
                $data['depth'] = 1;
            } else {
                //非根分类的depth和rootid都来自父分类
                $pcate = $this->info($data['pid']);
                if (!$pcate) {
                    return false;
                }
                $data['depth'] = $pcate['depth'] + 1;
                $data['rootid'] = $pcate['rootid'];
            }

            $res = $this->add($data);

            //如果是根分类，rootid为自己
            if ($data['pid'] == 0 && $res) {
                $this->where('id=' . $res)->setField('rootid', $res);
            }

        } else {
            $res = 0;
            // 新增子栏目继承逻辑 朱德胜 2015-08-13
            $inherit = I('post.inherit');
            $childd = array();

            // 子栏目继承域名
            if ($inherit['domain']) {
                $childd['domain'] = $data['domain'];
                $childd['reverse_rule'] = $data['reverse_rule'];

                if (C('MOBILE_START')) {
                    $childd['mobile_domain'] = $data['mobile_domain'];
                    $childd['mobile_reverse_rule'] = $data['mobile_reverse_rule'];
                }
            }

            $field = array('template_index', 'path_index', 'template_lists', 'path_lists', 'path_lists_index', 'template_detail', 'path_detail', 'template_edit');

            // 子栏目继承PC模板
            if ($inherit['template']) {
                foreach ($field as $name) {
                    $childd[$name] = $data[$name];
                }
            }

            // 子栏目继承手机模板
            if ($inherit['mobile_template'] && C('MOBILE_START')) {
                foreach ($field as $name) {
                    $childd['mobile_' . $name] = $data['mobile_' . $name];
                }
            }

            // 继承记录修改
            if ($childd) {
                $details = $this->where(array('id' => $data['id']))->find();

                $category = $this->field('id,title,pid')->where("rootid = {$details['rootid']}")->order('id ASC')->select();

                $category = parseRelation($category, $details['id']);

                if ($category) {
                    foreach ($category as $value) {
                        $childd['id'] = $value['id'];

                        if ($this->save($childd)) {
                            $res++;
                        }
                    }
                }
            }

            // 修改栏目记录
            if ($this->save($data)) {
                $res++;
            }
        }
        if ($res == '0') {
            $res = false;
        }
        //修改没有更新的情况下，提示分类修改成功BUG  modify by tanjian 2016-1-29日
        //更新分类缓存
        S('sys_category_list_' . $this->cate_name, null);

        //记录行为
        action_log('update_' . $this->cate_name, $this->document_name, $data['id'] ? $data['id'] : $res, UID);

        return $res;
    }

    /**
     * 生成推荐位的值
     *
     * @return number 推荐位
     * @author huajie <banhuajie@163.com>
     */
    protected function getPosition()
    {
        $position = I('post.position');
        if (!is_array($position)) {
            return 0;
        } else {
            $pos = 0;
            foreach ($position as $key => $value) {
                $pos += $value; //将各个推荐位的值相加
            }

            return $pos;
        }
    }

    /**
     * 查询后解析扩展信息
     *
     * @param  array $data 分类数据
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    protected function _after_find(&$data, $options)
    {
        /* 分割模型 */
        if (!empty($data['model'])) {
            $data['model'] = explode(',', $data['model']);
        }

        if (!empty($data['model_sub'])) {
            $data['model_sub'] = explode(',', $data['model_sub']);
        }

        /* 分割文档类型 */
        if (!empty($data['type'])) {
            $data['type'] = explode(',', $data['type']);
        }

        /* 分割模型 */
        if (!empty($data['reply_model'])) {
            $data['reply_model'] = explode(',', $data['reply_model']);
        }

        /* 分割文档类型 */
        if (!empty($data['reply_type'])) {
            $data['reply_type'] = explode(',', $data['reply_type']);
        }

        /* 还原扩展数据 */
        if (!empty($data['extend'])) {
            $data['extend'] = json_decode($data['extend'], true);
        }
    }
}
