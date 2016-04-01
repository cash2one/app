<?php
namespace Admin\Model;
use Think\Model;

/**
 * 模型
 */
class CardModel extends Model{

    protected $_validate = array(
        array('number', 'require', '卡号不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
    );

    protected $_auto = array(
        array('create_time', NOW_TIME, self::MODEL_INSERT),
        array('update_time', NOW_TIME, self::MODEL_BOTH),
        array('status', '1', self::MODEL_BOTH),
    );


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
         $rs = $this->field($field)->where($map)->find();
         return $rs;
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
        action_log('update_card', 'card', $data['id'] ? $data['id'] : $res, UID);

        return $res;
    }

    /**
     * 批量添加
     * @param array $data 表单数据     
     * @return boolean 添加状态
     */
    public function addList($data){
        if (!$data['id']) return false;
        $card_list = I('card_list'); //data已经不存在此字段，使用I方法获取
        if (!$card_list) return false;
        //数据插入
        $insert = $rs = array();
        $insert['did'] = $data['id'];
        $insert['status'] = 1;
        $insert['create_time'] = $insert['update_time'] = time();
        $arr = preg_split('/[,;\r\n]+/', trim($card_list,',;\r\n'));
        foreach ($arr as $value) {
            $insert['number'] = $value;
            $rs['number'] = $this->add($insert);
        }
        return $rs;
    }

    /**
     * 获取列表
     * @param  array $where 条件
     * @return array
     */
    public function getList($where = ''){
       $lists  = $this->where($where)->field(true)->select();
       int_to_string($lists);
       int_to_string($lists, array('draw_status'=>array(0=>'未领取',1=>'已领取')));
       return $lists;

    }

    /**
     * 清空回收站的时候删除关联数据
     * @param  array $ids did数组,文档表id
     * @return  boolean
     * @author crohn <lllliuliu@163.com>
     */
    public function remove($ids){

        return $this->where( array( 'did'=>array( 'IN',trim(implode(',',$ids),',') ) ) )->delete();
              
    }

    /**
     * 插入成功后操作
     * @param  array $data 数据
     * @return  void
     * @author crohn <lllliuliu@163.com>
     */
    // protected function _after_insert(&$data, $options){
    //     //刷新APC缓存
    //     $info = $this->info(intval($data['id']), 'did');
    //     flushCard($info['did']);
    // }

    /**
     * 更新成功后操作
     * @param  array $data 数据
     * @return  void
     * @author crohn <lllliuliu@163.com>
     */
    protected function _after_update(&$data, $options){
        //刷新APC缓存
        $info = $this->info(intval($data['id']), 'did');
        flushCard($info['did']);
    }
}
