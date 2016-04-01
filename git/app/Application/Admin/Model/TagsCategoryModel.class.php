<?php
namespace Admin\Model;
use Think\Model;

/**
 * 标签分类模型
 */
class TagsCategoryModel extends Model{

    protected $_validate = array(
        array('name', 'require', '标识不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('name', '', '标识已经存在', self::VALUE_VALIDATE, 'unique', self::MODEL_BOTH),
        array('title', 'require', '名称不能为空', self::MUST_VALIDATE , 'regex', self::MODEL_BOTH)
    );

    protected $_auto = array(
        array('create_time', NOW_TIME, self::MODEL_INSERT),
        array('update_time', NOW_TIME, self::MODEL_BOTH),
        array('status', '1', self::MODEL_BOTH),
    );


    /**
     * 获取标签分类详细信息
     * @param  milit   $id 标签ID或标识
     * @param  boolean $field 查询字段
     * @return array     标签信息
     */
    public function info($id, $field = true){
        /* 获取标签信息 */
        $map = array();
        if(is_numeric($id)){ //通过ID查询
            $map['id'] = $id;
        } else { //通过标识查询
            $map['name'] = $id;
        }
        return $this->field($field)->where($map)->find();
    }

     /**
      * 获取插件列表
      * @param  array $where 条件
      * @return array
      */
     public function getList($where = ''){
        $lists  = $this->where($where)->field(true)->select();
        int_to_string($lists);
        return $lists;
     }

    /**
     * 更新标签信息
     * @return boolean 更新状态
     */
    public function update(){
        $data = $this->create();
        if(!$data){ //数据对象创建错误
            return false;
        }

        /* 添加或更新数据 */
        if(empty($data['id'])){
            $res = $this->add();
        }else{
            $res = $this->save();
        }

        //记录行为
        action_log('update_tags_category', 'tagsCategory', $data['id'] ? $data['id'] : $res, UID);

        return $res;
    }



    /**
     * 获取标签分类
     * @return  void
     */
    public function getCategory(){
        $category = array();
        $lists = $this->where("status=1")->field('id,title,name')->select();
        if($lists){
            foreach ($lists as $value) {
                $category[] = array('title'=>$value['title'],'url'=>"Tags/index?category={$value['id']}");
            }
        }
        return $category;
    }


 

}
