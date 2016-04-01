<?php
// +----------------------------------------------------------------------
// | 描述:亲宝贝微信二维逻辑类
// +----------------------------------------------------------------------
// | Author: 谭坚 <tanjian691406429@163.com>
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------
// | Date: 16-3-7 下午5:37    Version:1.0.0 
// +----------------------------------------------------------------------

namespace Admin\Model;

use Think\Model;

class QbaobeiWeixinModel extends Model
{
    protected $tableName = 'weixin';
    /* 自动验证规则 */
    protected $_validate = array(
        array('title', 'require', '模块名字不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('title', '1,30', '模块名字长度不能超过30个字符', self::MUST_VALIDATE, 'length', self::MODEL_BOTH),
        array('content', 'require', '模块内容不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
    );
    /* 自动完成规则 */
    protected $_auto = array(
        array('uid', 'is_login', self::MODEL_INSERT, 'function'),
        array('title', 'htmlspecialchars', self::MODEL_BOTH, 'function'),
        array('create_time', NOW_TIME, self::MODEL_BOTH, 'callback'),
        array('update_time', NOW_TIME, self::MODEL_BOTH),
    );

    /**
     * 描述：更新微信信息
     *
     * @param array $data_sub
     * @return bool|mixed
     * @author  谭坚
     * @version 1.0.0
     * @modify  time:
     * @modify  author:
     */
    public function addWeixin($data_sub = array())
    {
        $weixin = D('weixin');
        $weixin->setProperty("_validate", $this->_validate);
        $weixin->setProperty("_auto", $this->_auto);
        $data = $weixin->create();
        if (empty($data)) {
            return false;
        }
        $data['category_str'] = $this->getCategoryIdStr();
        $rs = $weixin->add($data);
        if (!empty($data_sub) && is_numeric($rs)) {
            foreach ($data_sub as &$val) {
                $val['type_id'] = $rs;
            }
            D('weixin_category')->addAll($data_sub);
        }
        return $rs;
    }

    /**
     * 描述：更新微信二维码信息
     *
     * @param array $data_sub
     * @return bool
     * @author  谭坚
     * @version 1.0.0
     * @modify  time:
     * @modify  author:
     */
    public function updateWeixin($data_sub = array())
    {
        $weixin = D('weixin');
        $weixin->setProperty("_validate", $this->_validate);
        $weixin->setProperty("_auto", $this->_auto);
        $data = $weixin->create();
        if (empty($data)) {
            return false;
        }
        $data['category_str'] = $this->getCategoryIdStr();
        $rs = $weixin->save($data);
        $id = $data['id'];
        if (!is_numeric($id) || empty($id)) {
            return false;
        }
        $where = array();
        $where['type_id'] = $id;
        D('weixin_category')->where($where)->delete();
        unset($where);
        if (!empty($data_sub)) {
            D('weixin_category')->addAll($data_sub);
        }
        return $rs;
    }

    /**
     * 描述：删除对应微信二维码相关内容
     *
     * @param string $id
     * @return bool|mixed
     * @author  谭坚
     * @version 1.0.0
     * @modify  time:
     * @modify  author:
     */
    public function deleteWeixin($id = '')
    {
        if (!is_numeric($id) || empty($id)) {
            return false;
        }
        $where = array();
        $where['type_id'] = $id;
        $rs_sub = D('weixin_category')->where($where)->delete();
        unset($where);
        $where = array();
        $where['id'] = $id;
        $rs = D('weixin')->where($where)->delete();
        unset($where);
        return ($rs || $rs_sub);
    }

    /**
     * 描述：根据微信二维码id和类型获取栏目id
     *
     * @param string $id
     * @param int    $type
     * @return bool|mixed
     * @author  谭坚
     * @version 1.0.0
     * @modify  time:
     * @modify  author:
     */
    public function getCategoryIds($id = '', $type = 0)
    {
        if (!is_numeric($id) || empty($id)) {
            return false;
        }
        $where = array();
        $where['type'] = $type;
        $where['type_id'] = $id;
        $category_ids = D('weixin_category')->where($where)->getField('category_id', true);
        unset($where);
        return $category_ids;
    }

    /**
     * 描述：根据微信二维码id获取相关信息
     *
     * @param string $id
     * @return bool|mixed
     * @author  谭坚
     * @version 1.0.0
     * @modify  time:
     * @modify  author:
     */
    public function getWeixinById($id = '')
    {
        if (!is_numeric($id) || empty($id)) {
            return false;
        }
        $where = array();
        $where['id'] = $id;
        $rs = D('weixin')->where($where)->find();
        unset($where);
        return $rs;
    }

    /**
     * 描述：获取微信列表信息
     *
     * @param int $type
     * @param int $p
     * @param int $row
     * @return mixed
     * @author  谭坚
     * @version 1.0.0
     * @modify  time:
     * @modify  author:
     */
    public function listWeixin($type = 0, $p = 1, $row = 20)
    {
        $where = array();
        $where['status'] = 1;
        if ((string)$type != 'all') {
            $where['type'] = $type;
        }
        $rs = D('weixin')->where($where)->page($p, $row)->select();
        return $rs;
    }

    /**
     * 描述：获取微信总数
     *
     * @param int $type
     * @return mixed
     * @author  谭坚
     * @version 1.0.0
     * @modify  time:
     * @modify  author:
     */
    public function getCountWeixin($type = 0)
    {
        $where = array();
        $where['status'] = 1;
        if ((string)$type != 'all') {
            $where['type'] = $type;
        }
        $count = D('weixin')->where($where)->count();
        return $count;
    }

    /**
     * 描述：创建时间不写则取当前时间
     *
     * @return int
     * @author  谭坚
     * @version 1.0.0
     * @modify  time:
     * @modify  author:
     */
    protected static function getCreateTime()
    {
        $create_time = I('create_time');
        return $create_time ? strtotime($create_time) : NOW_TIME;
    }

    /**
     * 描述：获取微信二维码对已的栏目字符串，栏目用逗号分隔（例子：123,344,34343）
     *
     * @return string
     * @author  谭坚
     * @version 1.0.0
     * @modify  time:
     * @modify  author:
     */
    protected static function getCategoryIdStr()
    {
        $category_ids = I('category_id');
        if (!is_array($category_ids)) {
            return '';
        } else {
            return implode(',', $category_ids);
        }
    }
}
