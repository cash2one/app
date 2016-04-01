<?php
namespace Admin\Model;
use Think\Model;

/**
 * 模型
 */
class CompanyModel extends Model{

    protected $_validate = array(
        array('name', 'require', '公司名不能为空', self::MUST_VALIDATE , 'regex', self::MODEL_BOTH),
    );

    protected $_auto = array(
        array('create_time', NOW_TIME, self::MODEL_INSERT),
        array('update_time', NOW_TIME, self::MODEL_BOTH),
        array('position', 'getPosition', self::MODEL_BOTH, 'callback'),
    );

    private $log_name = 'company';

    /**
     * 生成推荐位的值
     * @return number 推荐位
     * @author huajie <banhuajie@163.com>
     */
    protected function getPosition(){
        $position = I('post.position');
        if(!is_array($position)){
            return 0;
        }else{
            $pos = 0;
            foreach ($position as $key=>$value){
                $pos += $value;		//将各个推荐位的值相加
            }
            return $pos;
        }
    }
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
    public function getList($where = '',$order="id desc"){
       $lists  = $this->where($where)->field(true)->order($order)->select();
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
