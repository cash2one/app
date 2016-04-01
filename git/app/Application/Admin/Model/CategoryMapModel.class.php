<?php
/**
 * User: xiao
 * Date: 2015/7/6
 */

namespace Admin\Model;
use Think\Model;

class CategoryMapModel extends Model {
    /**
     * 清空回收站的时候删除关联数据
     * @param  array $ids did数组,文档表id
     * @param  string $type 类型
     * @return  boolean
     */
    public function remove($ids, $type = 'document'){
        $map = array(
            'did'=>array( 'IN',trim(implode(',',$ids),',') ),
            'type'=>strtolower($type)
        );
        return $this->where( $map )->delete();

    }

    /**
     * 文档数据插入或修改时操作
     * @param  array $data 数据
     * @param  string $type 类型
	 * @param  string $catename 栏目名称
     * @return  void
     */
    public function update($data, $catename,  $type = 'document'){
        //小写
        $type = strtolower($type);

        //获取id，如果是新增，TP的model类中会把插入ID写入到data；如果是修改，post数据会传递ID
        $id = $data['id'];
		
        //删除此类型文章所有关联
        $this->where(array('did'=>$id,'type'=>$type))->delete();
		
        //插入新的关联数据
        $input = I('Category'); //由于标签字段不存在于模型，在data数据已经被删除，那么使用I方式获取
        if (!empty($input) || is_array($input)) {
			
            foreach ($input as $value) {
                $add = array(
                    'cid'=>$value,
                    'did'=>$id,
					
                    'type'=>$type,
                    'update_time'=>NOW_TIME,
                    'create_time'=>NOW_TIME
                );
                $this->add($add);
            }
        }
    }
} 