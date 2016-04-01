<?php
namespace Admin\Model;
use Think\Model;

/**
 * 标签模型
 */
class TagsModel extends Model{

    protected $_validate = array(
        array('name', 'require', '标识不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('name', '', '标识已经存在', self::VALUE_VALIDATE, 'unique', self::MODEL_BOTH),
        array('title', 'require', '名称不能为空', self::MUST_VALIDATE , 'regex', self::MODEL_BOTH),
        array('meta_title', '1,50', '网页标题不能超过50个字符', self::VALUE_VALIDATE , 'length', self::MODEL_BOTH),
        array('keywords', '1,255', '网页关键字不能超过255个字符', self::VALUE_VALIDATE , 'length', self::MODEL_BOTH),
        array('meta_title', '1,255', '网页描述不能超过255个字符', self::VALUE_VALIDATE , 'length', self::MODEL_BOTH),
    );

    protected $_auto = array(
        array('extend', 'json_encode', self::MODEL_BOTH, 'function'),
        array('extend', null, self::MODEL_BOTH, 'ignore'),
        array('create_time', NOW_TIME, self::MODEL_INSERT),
        array('update_time', NOW_TIME, self::MODEL_BOTH),
        array('status', '1', self::MODEL_BOTH),
        array('position', 'getPosition', self::MODEL_BOTH, 'callback'), //推荐数据自动处理，回调函数
    );

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
     * 获取标签详细信息
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
     * 获取标签树，指定标签则返回指定标签极其子标签，不指定则返回所有标签树
     * @param  integer $id    标签ID
     * @param  boolean $field 查询字段
     * @param  array $map 查询条件
     * @param  string $order 排序
     * @return array          标签树
     */
    public function getTree($id = 0, $field = true, $map = array(),$order = ''){
    	//排序  update liujun 2015-07-22
    	if(empty($order)){
    		$order = 'update_time desc,sort desc';
    	}
        /* 获取当前标签信息 */
        if($id){
            $info = $this->info($id);
            $id   = $info['id'];
        }

        /* 获取所有标签 */
        $map['status']  = array('gt', -1);
        $list = $this->field($field)->where($map)->order($order)->select();
        $list = list_to_tree($list, $pk = 'id', $pid = 'pid', $child = '_', $root = $id);

        /* 获取返回数据 */
        if(isset($info)){ //指定标签则返回当前标签极其子标签
            $info['_'] = $list;
        } else { //否则返回所有标签
            $info = $list;
        }

        return $info;
    }

    /**
     * 获取指定标签的同级标签
     * @param  integer $id    标签ID
     * @param  boolean $field 查询字段
     * @return array
     */
    public function getSameLevel($id, $field = true){
        $info = $this->info($id, 'pid');
        $map = array('pid' => $info['pid'], 'status' => 1);
        return $this->field($field)->where($map)->order('sort')->select();
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
            /* 新增数据设置rootid和depth */            
            if ($data['pid']==0){
                //根分类depth
                $data['depth'] = 1;
            }else{
                //非根分类的depth和rootid都来自父分类
                $ptag = $this->info($data['pid']);
                if (!$ptag) return false;
                $data['depth'] = $ptag['depth']+1;
                $data['rootid'] = $ptag['rootid'];                         
            }

            $res = $this->add($data);

            //如果是根分类，rootid为自己
            if ($data['pid']==0 && $res) $this->where('id='.$res)->setField('rootid', $res);
        }else{
            $res = $this->save();
        }

        //更新标签缓存
        S('sys_tags_list', null);

        //记录行为
        action_log('update_tags', 'tags', $data['id'] ? $data['id'] : $res, UID);

        return $res;
    }

    /**
     * 查询后解析扩展信息
     * @param  array $data 标签数据
     */
    protected function _after_find(&$data, $options){
        /* 还原扩展数据 */
        if(!empty($data['extend'])){
            $data['extend'] = json_decode($data['extend'], true);
        }
    }

}
