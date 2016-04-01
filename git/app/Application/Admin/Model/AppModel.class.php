<?php
/**
 * User: 肖书成
 * Date: 2015/7/24
 * 描述: App类模型管理
 */

namespace Admin\Model;

use Think\Model;

class AppModel extends Model{
    protected $_validate    =   array(
        array('code','require','版本号不能为空'),
        array('title','require','版本名称不能为空'),
        array('code','number','版本号必须是数字'),
    );

    /**
     * 作者:肖书成
     * 描述:新增或修改App表的一条数据
     * @return bool/array  false失败，array成功
     */
    public function update(){
        $data = $this->create();
        if(empty($data)){
            return false;
        }

        /* 字段设置中存储处理 crohn 2014-10-23*/
        $model_id   =   I('model_id',0);
        $data = model_save_type($model_id, $data);
        if(!is_array($data)){
            $this->error = $data;
            return false;
        }

        /* 添加或新增基础内容 */
        if(empty($data['id'])){ //新增数据
            $data['create_time']    =   time();
            $data['update_time']    =   time();
            $id = $this->add($data); //添加基础内容
            if(!$id){
                $this->error = '新增基础内容出错！';
                return false;
            }
        } else { //更新数据
            //$data['edit_id'] = UID;//增加编辑者id
            $data['update_time']    =   time();

            $status = $this->save($data); //更新基础内容
            if(false === $status){
                $this->error = '更新基础内容出错！';
                return false;
            }
        }
        //copy thanks authorship
        return $data;
    }

} 