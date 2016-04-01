<?php
namespace Admin\Model;
use Think\Model;

/**
 * 静态页面管理模型
 */
class StaticPageModel extends Model{

    protected $_validate = array(
        array('name', 'require', '页面名字', self::MUST_VALIDATE , 'regex', self::MODEL_BOTH),
        array('module_name', 'require', '生成访问模块名', self::MUST_VALIDATE , 'regex', self::MODEL_BOTH),
        array('controller_name', 'require', '生成访问控制层名', self::MUST_VALIDATE , 'regex', self::MODEL_BOTH),
        array('method_name', 'require', '生成访问方法名', self::MUST_VALIDATE , 'regex', self::MODEL_BOTH),
        array('path', 'require', '生成路径', self::MUST_VALIDATE , 'regex', self::MODEL_BOTH),
    );

    protected $_auto = array(
        array('create_time', NOW_TIME, self::MODEL_INSERT),
        array('update_time', NOW_TIME, self::MODEL_BOTH),
    );

    private $log_name = 'static_page';


    /**
     * 获取详细信息
     * @param  milit   $id 标签ID或标识
     * @param  boolean $field 查询字段
     * @return array     标签信息
     */
    public function info($id, $field = true){
        /* 获取标签信息 */
        $map = array();
        if(is_numeric($id)){ //通过ID查询
            $map['id'] = $id;
        } 
        return $this->field($field)->where($map)->find();
    }

    /**
     * 更新
     * @return boolean 更新状态
     */
    public function update(){
        $data = $this->create();
        if(!$data){ //数据对象创建错误
            return false;
        }

        /* 添加或更新数据 */
        if(empty($data['id'])){
            $res = $this->add($data);
        }else{
            $res = $this->save();
        }

        //记录行为
        action_log('update_'. $this->log_name, $this->log_name, $data['id'] ? $data['id'] : $res, UID);

        return $res;
    }

    /**
     * 获取列表
     * @param  array $where 条件
     * @return array
     */
    public function getList($where = ''){
       $lists  = $this->where($where)->field(true)->select();
       int_to_string($lists);
       return $lists;
    }

    /**
     * 插入成功后操作
     * @param  array $data 数据
     * @return  void
     * @author crohn <lllliuliu@163.com>
     */
    protected function _after_insert(&$data, $options){
        //删除api接口的缓存
    }

    /**
     * 更新成功后操作
     * @param  array $data 数据
     * @return  void
     * @author crohn <lllliuliu@163.com>
     */
    protected function _after_update(&$data, $options){
        //删除api接口的缓存
    }

}
