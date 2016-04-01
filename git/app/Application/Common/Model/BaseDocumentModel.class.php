<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Common\Model;
use Think\Model;
use Think\Page;

/**
 * 文档基础模型
 */
class BaseDocumentModel extends Model{
    /*根据ID查出的表数据*/
    public $info = array(

    );

    /* 自动验证规则 */
    protected $_validate = array(

    );

    /* 自动完成规则 */
    protected $_auto = array(

    );

    /**
     * 获取详情页数据
     * @param  integer $id 文档ID
     * @return array       详细数据
     */
    public function detail($id){
        $fields = $this->getDbFields();
        if(in_array('status',$fields)) $w['status'] = 1;
        /* 获取基础数据 */
        // 修改BUG，find带参数和where不可混用  crohn 2015-6-16
        $w['id'] = intval($id);       
        $info = $this->field(true)->where($w)->find();
        if ( !$info ) {
            $this->error = '数据不存在';
            return false;
        }elseif(!(is_array($info) || 1 !== $info['status'])){
            $this->error = '数据被禁用或已删除！';
            return false;
        }

        /* 获取模型数据 */
        $logic  = $this->logic($info['model_id']);
        $detail = $logic->detail($id); //获取指定ID的数据
        if(!$detail){
            $this->error = $logic->getError();
            return false;
        }

        return  array_merge($info, $detail);
    }

    /**
     * 获取扩展模型对象
     * @param  integer $model 模型编号
     * @return object         模型对象
     */
    private function logic($model){
        $name = get_model_by('id', $model, 'name');
        $name = parse_name($name['name'], 1);
        $class = is_file(COMMON_PATH . 'Logic/' . $name . 'Logic' . EXT) ? $name : 'Base';
        $class = 'Common\\Logic\\' . $class . 'Logic';
        return new $class(strtolower($name));       
    }




    /**
     * 根据各种条件获取文档列表
     * @param  array   $map    条件参数数组
     * @param  string   $extend    false-不查询扩展模型 true-查询所有扩展模型字段 string-需要查询的扩展模型字段
     * @return array              文档列表
     */
    public function listsWhere($where, $extend = false){
        $map = $where['map'];
        //状态
        $map['status'] = empty($map['status']) ? 1: $map['status'];
        //类型 pid判断
        $map['pid'] = empty($map['pid']) ? 0: $map['pid'];      
        //合并公共条件
        $map = array_merge($map, $this->listMap());   
        //设置字段和排序
        $field = $where['field'] ? $where['field'] : true;
        if ($extend && $field!==true) $field .= ',model_id,id' ;  //如果需要查询扩展模型，强制加入model_id和id字段
        $order = $where['order'] ? $where['order'] : 'id DESC';        
        $this->field($field)->where($map)->order($order);
        //设置数目
        $limit = $where['limit'];        
        is_numeric($limit) && $this->limit($limit);

        $list= $this->select();
        if (!$list) return false;

        if ($extend) {
            //扩展模型分组,如果发现效率问题可以更改为使用数据库进行
            $models = array();
             foreach ($list as $result) {
                $models[$result['model_id']][] = $result['id'];
             }
             //分组查询并合并数据
             $extends = array();             
             foreach ($models as $k => $m) {
                $model_obj = $this->logic($k);
                $extends += $model_obj->lists($m);
             }
             //合并到主表结果，这里也可以不合并分开返回；后台做了检测，除了ID不会有同名字段
             foreach ($list as $key => $value) {
                $list[$key] = array_merge($list[$key], $extends[$value['id']]);
             }
        }

        return $list;
    }

    /**
     * 根据各种条件获取文档列表总数
     * @param  array   $map    条件参数数组
     * @return array              文档列表
     */
    public function listsWhereCount($where){
        $map = $where['map'];
        //状态
        $map['status'] = empty($map['status']) ? 1: $map['status'];
        //类型 pid判断
        $map['pid'] = empty($map['pid']) ? 0: $map['pid'];      
        //合并公共条件
        $map = array_merge($map, $this->listMap());
        //查询总数  
        return $this->where($map)->count('id');
    }


    /**
     * 公共的列表查询条件
     * @param  number  $category 分类ID
     * @param  number  $pos      推荐位
     * @param  integer $status   状态
     * @return array             查询条件
     */
    private function listMap(){
        $map = array();
        $map['create_time'] = array('lt', NOW_TIME);
        //$map['_string']     = 'deadline = 0 OR deadline > ' . NOW_TIME;

        return $map;
    }

/***************以下全是未处理方法**********************/

    /**
     * 计算列表总数
     * @param  number  $category 分类ID
     * @param  integer $status   状态
     * @return integer           总数
     */
    public function listCount($category, $status = 1){
        $map = $this->listMap($category, $status);
        return $this->where($map)->count('id');
    }



    /**
     * 返回前一篇文档信息
     * @param  array $info 当前文档信息
     * @return array
     */
    public function prev($info){
        $map = array(
            'id'          => array('lt', $info['id']),
            'pid'		  => 0,
            'category_id' => $info['category_id'],
            'status'      => 1,
            'create_time' => array('lt', NOW_TIME),
            '_string'     => 'deadline = 0 OR deadline > ' . NOW_TIME,  			
        );

        /* 返回前一条数据 */
        return $this->field(true)->where($map)->order('id DESC')->find();
    }

    /**
     * 获取下一篇文档基本信息
     * @param  array    $info 当前文档信息
     * @return array
     */
    public function next($info){
        $map = array(
            'id'          => array('gt', $info['id']),
            'pid'		  => 0,
            'category_id' => $info['category_id'],
            'status'      => 1,
            'create_time' => array('lt', NOW_TIME),
            '_string'     => 'deadline = 0 OR deadline > ' . NOW_TIME,  			
        );

        /* 返回下一条数据 */
        return $this->field(true)->where($map)->order('id')->find();
    }

    public function update(){
        /* 检查文档类型是否符合要求 */
        $Model = new \Admin\Model\DocumentModel();
        $res = $Model->checkDocumentType( I('type'), I('pid') );
        if(!$res['status']){
            $this->error = $res['info'];
            return false;
        }

        /* 获取数据对象 */
        $data = $this->field('pos,display', true)->create();
        if(empty($data)){
            return false;
        }

        /* 添加或新增基础内容 */
        if(empty($data['id'])){ //新增数据
            $id = $this->add(); //添加基础内容

            if(!$id){
                $this->error = '添加基础内容出错！';
                return false;
            }
            $data['id'] = $id;
        } else { //更新数据
            $status = $this->save(); //更新基础内容
            if(false === $status){
                $this->error = '更新基础内容出错！';
                return false;
            }
        }

        /* 添加或新增扩展内容 */
        $logic = $this->logic($data['model_id']);
        if(!$logic->update($data['id'])){
            if(isset($id)){ //新增失败，删除基础数据
                $this->delete($data['id']);
            }
            $this->error = $logic->getError();
            return false;
        }

        //内容添加或更新完成
        return $data;

    }

    /**
     * 获取段落列表
     * @param  integer $id    文档ID
     * @param  integer $page  显示页码
     * @param  boolean $field 查询字段
     * @param  boolean $logic 是否查询模型数据
     * @return array
     */
    public function part($id, $page = 1, $field = true, $logic = true){
        $map  = array('status' => 1, 'pid' => $id, 'type' => 3);
        $info = $this->field($field)->where($map)->page($page, 10)->order('id')->select();
        if(!$info) {
            $this->error = '该文档没有段落！';
            return false;
        }

        /* 不获取内容详情 */
        if(!$logic){
            return $info;
        }

        /* 获取内容详情 */
        $model = $logic = array();
        foreach ($info as $value) {
            $model[$value['model_id']][] = $value['id'];
        }
        foreach ($model as $model_id => $ids) {
            $data   = $this->logic($model_id)->lists($ids);
            $logic += $data;
        }

        /* 合并数据 */
        foreach ($info as &$value) {
            $value = array_merge($value, $logic[$value['id']]);
        }

        return $info;
    }

    /**
     * 获取指定文档的段落总数
     * @param  number $id 段落ID
     * @return number     总数
     */
    public function partCount($id){
        $map = array('status' => 1, 'pid' => $id, 'type' => 3);
        return $this->where($map)->count('id');
    }

    /**
     * 获取推荐位数据列表
     * @param  number  $pos      推荐位 1-列表推荐，2-频道页推荐，4-首页推荐
     * @param  number  $category 分类ID
     * @param  number  $limit    列表行数
     * @param  boolean $filed    查询字段
     * @return array             数据列表
     */
    public function position($pos, $category = null, $limit = null, $field = true){
        $map = $this->listMap($category, 1, $pos);

        /* 设置列表数量 */
        is_numeric($limit) && $this->limit($limit);

        /* 读取数据 */
        return $this->field($field)->where($map)->select();
    }

    /**
     * 获取数据状态
     * @return integer 数据状态
     * @author huajie <banhuajie@163.com>
     */
    protected function getStatus(){
        $cate = I('post.category_id');
        $check = M('Category')->getFieldById($cate, 'check');
        if($check){
            $status = 2;
        }else{
            $status = 1;
        }
        return $status;
    }

    /**
     * 获取根节点id
     * @return integer 数据id
     * @author huajie <banhuajie@163.com>
     */
    protected function getRoot(){
        $pid = I('post.pid');
        if($pid == 0){
            return 0;
        }
        $p_root = $this->getFieldById($pid, 'root');
        return $p_root == 0 ? $pid : $p_root;
    }


}
