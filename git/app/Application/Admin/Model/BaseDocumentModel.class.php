<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: huajie <banhuajie@163.com>
// +----------------------------------------------------------------------

namespace Admin\Model;

use Admin\Model\AuthGroupModel;
use Think\Model;

/**
 * 文档基础模型
 */
class BaseDocumentModel extends Model
{

    //PC版静态生成类实例
    protected $staticInstance = null;

    //手机版静态生成类实例
    protected $staticInstanceM = null;

    /* 自动验证规则 */
    protected $_validate = array(
        array('name', '/^[a-zA-Z]\w{0,39}$/', '文档标识不合法', self::VALUE_VALIDATE, 'regex', self::MODEL_BOTH),
        array('name', 'checkName', '标识已经存在', self::VALUE_VALIDATE, 'callback', self::MODEL_BOTH),
        array('title', 'require', '标题不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('title', '1,80', '标题长度不能超过80个字符', self::MUST_VALIDATE, 'length', self::MODEL_BOTH),
        array('level', '/^[\d]+$/', '优先级只能填正整数', self::VALUE_VALIDATE, 'regex', self::MODEL_BOTH),
        array('category_id', 'require', '分类不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_INSERT),
        array('category_id', 'require', '分类不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_UPDATE),
        array('category_id', 'check_category', '该分类不允许发布内容', self::EXISTS_VALIDATE, 'callback', self::MODEL_UPDATE),
        array('category_id,type', 'check_category', '内容类型不正确', self::MUST_VALIDATE, 'callback', self::MODEL_INSERT),
        array('model_id,pid,category_id', 'check_category_model', '该分类没有绑定当前模型', self::MUST_VALIDATE, 'callback', self::MODEL_INSERT),
    );

    /* 自动完成规则 */
    protected $_auto = array(
        array('uid', 'is_login', self::MODEL_INSERT, 'function'),
        array('title', 'htmlspecialchars', self::MODEL_BOTH, 'function'),
        array('description', 'htmlspecialchars', self::MODEL_BOTH, 'function'),
        array('root', 'getRoot', self::MODEL_BOTH, 'callback'),
        //array('link_id', 'getLink', self::MODEL_BOTH, 'callback'),
        //array('attach', 0, self::MODEL_INSERT),
        //array('view', 0, self::MODEL_INSERT),
        //array('comment', 0, self::MODEL_INSERT),
        //array('extend', 0, self::MODEL_INSERT),
        array('create_time', 'getCreateTime', self::MODEL_BOTH, 'callback'),
        array('update_time', NOW_TIME, self::MODEL_BOTH),
        array('status', 'getStatus', self::MODEL_BOTH, 'callback'),
        array('position', 'getPosition', self::MODEL_BOTH, 'callback'),
        array('home_position', 'getHomePosition', self::MODEL_BOTH, 'callback'),
        array('home_yuer_position', 'getYuerPosition', self::MODEL_BOTH, 'callback'),
        array('home_huaiyun_position', 'getHuaiyunPosition', self::MODEL_BOTH, 'callback'),
        array('content_position', 'getContentPosition', self::MODEL_BOTH, 'callback'),
        array('home_edu_position', 'getEduPosition', self::MODEL_BOTH, 'callback'),
        array('home_food_position', 'getFoodPosition', self::MODEL_BOTH, 'callback'),
        array('pc_position', 'getPcPosition', self::MODEL_BOTH, 'callback'),
        array('app_position', 'getAppPosition', self::MODEL_BOTH, 'callback'),
        array('deadline', 'strtotime', self::MODEL_BOTH, 'function'),
    );

    /**
     * 生成推荐位的值
     * @return number 推荐位
     * @author huajie <banhuajie@163.com>
     */
    protected function getEduPosition()
    {
        $position = I('post.home_edu_position');
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
     * 生成推荐位的值
     * @return number 推荐位
     * @author huajie <banhuajie@163.com>
     */
    protected function getFoodPosition()
    {
        $position = I('post.home_food_position');
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
     * 生成推荐位的值
     * @return number 推荐位
     * @author huajie <banhuajie@163.com>
     */
    protected function getPcPosition()
    {
        $position = I('post.pc_position');
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
     * 生成推荐位的值
     * @return number 推荐位
     * @author huajie <banhuajie@163.com>
     */
    protected function getYuerPosition()
    {
        $position = I('post.home_yuer_position');
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
     * 生成推荐位的值
     * @return number 推荐位
     * @author huajie <banhuajie@163.com>
     */
    protected function getHuaiyunPosition()
    {
        $position = I('post.home_huaiyun_position');
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
     * 生成推荐位的值
     * @return number 推荐位
     * @author huajie <banhuajie@163.com>
     */
    protected function getContentPosition()
    {
        $position = I('post.content_position');
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
     * 验证分类是否允许发布内容
     * @param  integer $id 分类ID
     * @return boolean     true-允许发布内容，false-不允许发布内容
     */
    protected function check_category($id)
    {
        return check_category($id, $this->cate_name);
    }

    /**
     * 检测分类是否绑定了指定模型
     * @param  array $info 模型ID和分类ID数组
     * @return boolean     true-绑定了模型，false-未绑定模型
     */
    protected function check_category_model($info)
    {
        return check_category_model($id, $this->cate_name);
    }

    /**
     * 获取详情页数据
     * @param  integer $id 文档ID
     * @return array       详细数据
     */
    public function detail($id)
    {
        /* 获取基础数据 */
        $info = $this->field(true)->find($id);
        if (!(is_array($info) || 1 !== $info['status'])) {
            $this->error = '文档被禁用或已删除！';
            return false;
        }

        /* 获取模型数据 */
        $logic = $this->logic($info['model_id']);
        $detail = $logic->detail($id); //获取指定ID的数据
        if (!$detail) {
            $this->error = $logic->getError();
            return false;
        }
        $info = array_merge($info, $detail);

        return $info;
    }

    /**
     * 检测属性的自动验证和自动完成属性
     * @param integer $model_id 模型ID
     * @param array $_validate 原有自动验证
     * @param array $_auto 原有自动完成
     * @return boolean
     */
    public function getValidateAndAuto($model_id, $_validate, $_auto)
    {
        $fields = get_model_attribute($model_id, false);
        $validate = $auto = array();
        foreach ($fields as $key => $attr) {
            if ($attr['is_must']) {
                // 必填字段
                $validate[] = array($attr['name'], 'require', $attr['title'] . '必须!', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH);
            }

            // 自动验证规则 已经存在不验证
            $y = false; //存在标识
            foreach ($_validate as $v) {
                if ($v[0] == $attr['name']) {
                    $y = true;
                }

            }
            if (!empty($attr['validate_rule']) && !$y) {
                $validate[] = array($attr['name'], $attr['validate_rule'], $attr['error_info'] ? $attr['error_info'] : $attr['title'] . '验证错误', 0, $attr['validate_type'], $attr['validate_time']);
            }

            // 自动完成规则
            $y = false; //存在标识
            foreach ($_auto as $a) {
                if ($a[0] == $attr['name']) {
                    $y = true;
                }

            }
            if (!$y) {
                if (!empty($attr['auto_rule'])) {
                    $auto[] = array($attr['name'], $attr['auto_rule'], $attr['auto_time'], $attr['auto_type']);
                } elseif ('checkbox' == $attr['type']) {
                    // 多选型 使用多值相加方式存储
                    $auto[] = array($attr['name'], 'checkboxSave', 3, 'function');
                } elseif ('datetime' == $attr['type'] || 'date' == $attr['type']) {
                    // 日期型和时间型
                    $auto[] = array($attr['name'], 'strtotime', 3, 'function');
                }
            }
        }
        $validate = array_merge($validate, (array)$_validate);
        $auto = array_merge($auto, (array)$_auto);
        return array($validate, $auto);
    }

    /**
     * 新增或更新一个文档
     * @param array $data 手动传入的数据
     * @return boolean fasle 失败 ， int  成功 返回完整的数据
     * @author huajie <banhuajie@163.com>
     */
    public function update($data = null)
    {
        /* 检查文档类型是否符合要求 */
        $res = $this->checkDocumentType(I('type', 2), I('pid'));
        if (!$res['status']) {
            $this->error = $res['info'];
            return false;
        }

        /* 获取数据对象 */
        //合并自动验证规则
        if (I('model_id')) {
            list($validate, $auto) = $this->getValidateAndAuto(I('model_id'), $this->_validate, $this->_auto);
            $this->validate($validate)->auto($auto);
        }

        $data = $this->create();
        if (empty($data)) {
            return false;
        }

        /* 字段设置中存储处理 crohn 2014-10-23*/
        $model_id = I('model_id', 0);
        $data = model_save_type($model_id, $data);
        if (!is_array($data)) {
            $this->error = $data;
            return false;
        }

        /* 添加或新增基础内容 */
        if (empty($data['id'])) {
            //新增数据
            $id = $this->add($data); //添加基础内容
            if (!$id) {
                $this->error = '新增基础内容出错！';
                return false;
            }
        } else {
            //更新数据
            $data['edit_id'] = UID; //增加编辑者id
            $status = $this->save($data); //更新基础内容
            if (false === $status) {
                $this->error = '更新基础内容出错！';
                return false;
            }
        }

        /* 添加或新增扩展内容 */
        $logic = $this->logic($data['model_id']);
        //$logic->checkModelAttr($data['model_id']); 封装为一个函数使用
        list($validate, $auto) = $this->getValidateAndAuto($data['model_id'], $logic->_validate, $logic->_auto);
        $logic->validate($validate)->auto($auto);
        if (!$logic->update($id, $data)) {
            if (isset($id)) {
                //新增失败，删除基础数据
                $this->delete($id);
            }
            $this->error = $logic->getError();
            return false;
        }

        /* 行为记录 */
        if ($id) {
            action_log('add_' . $this->document_name, $this->document_name, $id, UID);
        } else {
            action_log('edit_' . $this->document_name, $this->document_name, $id, UID);
        }

        // 整合为完整数据源，标示操作方式
        // TODO:整体改造为数据对象
        $op = 'update';
        if (empty($data['id'])) {
            $data['id'] = $id;
            $op = 'add';
        }

        //HOOK
        $this->hook(__FUNCTION__, $data['id']);

        // 内容添加或更新完成,明示操作方式
        return ['data' => $data, 'op' => $op];

    }

    /**
     * 新增或更新一个文档
     * @param array $data 传入的数据
     * @param array $op 操作类型,add/update
     * @return boolean fasle 失败 ， int  成功 返回完整的数据
     */
    public function my_update_after($data, $op)
    {
        $id = $data['id'];
        if ($id <= 0) {
            return false;
        }

        /* 生成静态文件 */
        // 状态为1才生成 crohn 2015-6-16
        if ($data['status'] == 1) {
            $category_id = $data['category_id'];
            $p = array('id' => $id, 'category_id' => $category_id);
            $this->updateCreateStatic($p, true);
        }

        //hook('documentSaveComplete', array('model_id'=>$data['model_id']));

        /* 发送百度站点地图推送 */
        if (C('BAIDU_SITEMAP_POST_SWITCH') && $op == 'add') {
            $sitemap_data['time'] = date("Y-m-d", $data['update_time']);
            $siteMapI = new \Common\Library\SiteMapLibrary();
            //PC版本
            $sitemap_data['url'] = staticUrl('detail', $id, $this->document_name);
            $result = $siteMapI->post('p', array($sitemap_data));
            $maplog['url'] = $sitemap_data['url'];
            $maplog['update_time'] = time();
            $maplog['status'] = $result['success'] ? '200' : ($result['error'] ? $result['error'] : '404');
            $sql = "SHOW TABLES LIKE '" . C('DB_PREFIX') . "api_sitemap_log'";
            $res = $this->query($sql);
            if (count($res) > 0) {
                M('api_sitemap_log')->add($maplog); //记录时时推送日志
            }
            // M('api_sitemap_log')->add($maplog);//记录时时推送日志
            //手机版
            $sitemap_data['url'] = staticUrlMobile('detail', $id, $this->document_name);
            $result = $siteMapI->post('m', array($sitemap_data));
            $maplog['url'] = $sitemap_data['url'];
            $maplog['status'] = $result['success'] ? '200' : ($result['error'] ? $result['error'] : '404');
            if (count($res) > 0) {
                M('api_sitemap_log')->add($maplog); //记录时时推送日志
            } //记录时时推送日志
        }

        /* 数据中心推送 */
        $data['data_center'] && $this->uploadDataCenter($data);
    }

    /**
     * 描述：上传数据中心并更新数据
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function uploadDataCenter($res)
    {
        $url = 'http://dc.20hn.cn/v1/data/insert.json'; //数据中心地址
        $data = array();
        $data['title'] = $res['title'];
        $data['content'] = $res['content'];
        $data['url'] = I('collector_true_url');
        $data['siteid'] = I('siteid');
        $data['module'] = strtolower($this->document_name);
        $data['classid'] = $res['category_id'];
        $data['uid'] = UID;
        $data['origid'] = $res['id'];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_exec($ch);
        curl_close($ch);
    }

    /**
     * 获取数据状态
     * @return integer 数据状态
     */
    protected function getStatus()
    {
        $id = I('post.id');
        $status = I('post.status');
        $cate = I('post.category_id');
        if (empty($id)) {
            //新增
            if (!isset($status)) {
                $status = 1;
            }
        } else {
            //更新
            //$status = $this->getFieldById($id, 'status');
            $status = isset($status) ? $status : $this->getFieldById($id, 'status');
            //编辑草稿改变状态
            if ($status == 3) {
                $status = 1;
            }
        }
        return $status;
    }

    /**
     * 获取根节点id
     * @return integer 数据id
     * @author huajie <banhuajie@163.com>
     */
    protected function getRoot()
    {
        $pid = I('post.pid');
        if ($pid == 0) {
            return 0;
        }
        $p_root = $this->getFieldById($pid, 'root');
        return $p_root == 0 ? $pid : $p_root;
    }

    /**
     * 创建时间不写则取当前时间
     * @return int 时间戳
     * @author huajie <banhuajie@163.com>
     */
    protected function getCreateTime()
    {
        $create_time = I('post.create_time');
        return $create_time ? strtotime($create_time) : NOW_TIME;
    }

    /**
     * 获取扩展模型对象
     * @param  integer $model 模型编号
     * @return object         模型对象
     */
    protected function logic($model)
    {
        $name = get_model_by('id', $model, 'name');
        $name = parse_name($name['name'], 1);
        $class = is_file(MODULE_PATH . 'Logic/' . $name . 'Logic' . EXT) ? $name : 'Base';
        $class = MODULE_NAME . '\\Logic\\' . $class . 'Logic';
        return new $class(strtolower($name));
    }

    /**
     * 检查标识是否已存在(只需在同一根节点下不重复)
     * @param string $name
     * @return true无重复，false已存在
     * @author huajie <banhuajie@163.com>
     */
    protected function checkName()
    {
        $name = I('post.name');
        $category_id = I('post.category_id', 0);
        $id = I('post.id', 0);

        $map = array('name' => $name, 'id' => array('neq', $id), 'status' => array('neq', -1));

        $category = get_category_by_model($category_id, null, $this->cate_name);
        if ($category['pid'] == 0) {
            $map['category_id'] = $category_id;
        } else {
            $parent = get_parent_category_by_model($category['id'], $this->cate_name);
            $root = array_shift($parent);
            $map['category_id'] = array('in', D($this->cate_name)->getChildrenId($root['id']));
        }

        $res = $this->where($map)->getField('id');
        if ($res) {
            return false;
        }
        return true;
    }

    /**
     * 生成不重复的name标识
     * @author huajie <banhuajie@163.com>
     */
    protected function generateName()
    {
        $str = 'abcdefghijklmnopqrstuvwxyz0123456789'; //源字符串
        $min = 10;
        $max = 39;
        $name = false;
        while (true) {
            $length = rand($min, $max); //生成的标识长度
            $name = substr(str_shuffle(substr($str, 0, 26)), 0, 1); //第一个字母
            $name .= substr(str_shuffle($str), 0, $length);
            //检查是否已存在
            $res = $this->getFieldByName($name, 'id');
            if (!$res) {
                break;
            }
        }
        return $name;
    }

    /**
     * 生成推荐位的值
     * @return number 推荐位
     * @author huajie <banhuajie@163.com>
     */
    protected function getHomePosition()
    {
        $position = I('post.home_position');
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
     * 生成App推荐位的值
     * @return number 推荐位
     * @author huajie <banhuajie@163.com>
     */
    protected function getAppPosition()
    {
        $position = I('post.app_position');
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
     * 生成推荐位的值
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
     * 删除状态为-1的数据（包含扩展模型）
     * @return true 删除成功， false 删除失败
     * @author huajie <banhuajie@163.com>
     */
    public function remove()
    {
        //查询假删除的基础数据
        if (is_administrator()) {
            $map = array('status' => -1);
        } else {
            $cate_ids = AuthGroupModel::getAuthCategories(UID);
            $map = array('status' => -1, 'category_id' => array('IN', trim(implode(',', $cate_ids), ',')));
        }
        $base_list = $this->where($map)->field('id,model_id')->select();
        //删除扩展模型数据
        $base_ids = array_column($base_list, 'id');
        //孤儿数据
        $orphan = get_stemma($base_ids, $this, 'id,model_id');

        $all_list = array_merge($base_list, $orphan);
        foreach ($all_list as $key => $value) {
            $logic = $this->logic($value['model_id']);
            $logic->delete($value['id']);
        }

        //删除基础数据
        $ids = array_merge($base_ids, (array)array_column($orphan, 'id'));

        if (!empty($ids)) {
            // 删除静态文件 crohn 2015-3-18
            foreach ($ids as $id) {
                // PC版本路径
                if (is_null($this->staticInstance)) {
                    $this->createStaticInstance();
                }

                $file = $this->staticInstance->pathDetail($id);
                if (!empty($file)) {
                    $file = $this->staticInstance->static_root_dir . '/' . $file;
                    @unlink($file);
                }
                // 手机版本路径
                if (is_null($this->staticInstanceM)) {
                    $this->createStaticInstanceM();
                }

                $m_file = $this->staticInstanceM->pathDetail($id);
                if (!empty($m_file)) {
                    $m_file = $this->staticInstanceM->static_root_dir . '/' . $m_file;
                    @unlink($m_file);
                }
            }

            // 删除标签关联数据 crohn 2014-8-7
            D('TagsMap')->remove($ids, $this->document_name);

            // 删除产品标签关联数据 crohn 2014-10-9
            D('ProductTagsMap')->remove($ids, $this->document_name);

            // 删除数据
            $res = $this->where(array('id' => array('IN', trim(implode(',', $ids), ','))))->delete();
        }
        return $res;
    }

    /**
     * 获取链接id
     * @return int 链接对应的id
     * @author huajie <banhuajie@163.com>
     */
    // protected function getLink(){
    //     $link = I('post.link_id');
    //     if(empty($link)){
    //         return 0;
    //     } else if(is_numeric($link)){
    //         return $link;
    //     }
    //     $res = D('Url')->update(array('url'=>$link));
    //     return $res['id'];
    // }

    /**
     * 保存为草稿
     * @return array 完整的数据， false 保存出错
     * @author huajie <banhuajie@163.com>
     */
    public function autoSave()
    {
        $post = I('post.');

        /* 检查文档类型是否符合要求 */
        $res = $this->checkDocumentType(I('type', 2), I('pid'));
        if (!$res['status']) {
            $this->error = $res['info'];
            return false;
        }

        //触发自动保存的字段
        $save_list = array('name', 'title', 'description', 'position', 'link_id', 'cover_id', 'deadline', 'create_time', 'content');
        foreach ($save_list as $value) {
            if (!empty($post[$value])) {
                $if_save = true;
                break;
            }
        }

        if (!$if_save) {
            $this->error = '您未填写任何内容';
            return false;
        }

        //重置自动验证
        $this->_validate = array(
            array('name', '/^[a-zA-Z]\w{0,39}$/', '文档标识不合法', self::VALUE_VALIDATE, 'regex', self::MODEL_BOTH),
            array('name', '', '标识已经存在', self::VALUE_VALIDATE, 'unique', self::MODEL_BOTH),
            array('title', '1,80', '标题长度不能超过80个字符', self::VALUE_VALIDATE, 'length', self::MODEL_BOTH),
            array('description', '1,140', '简介长度不能超过140个字符', self::VALUE_VALIDATE, 'length', self::MODEL_BOTH),
            array('category_id', 'require', '分类不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
            array('category_id', 'check_category', '该分类不允许发布内容', self::EXISTS_VALIDATE, 'function', self::MODEL_UPDATE),
            array('category_id,type', 'check_category', '内容类型不正确', self::MUST_VALIDATE, 'function', self::MODEL_INSERT),
            array('model_id,pid,category_id', 'check_catgory_model', '该分类没有绑定当前模型', self::MUST_VALIDATE, 'function', self::MODEL_INSERT),
            array('deadline', '/^\d{4,4}-\d{1,2}-\d{1,2}(\s\d{1,2}:\d{1,2}(:\d{1,2})?)?$/', '日期格式不合法,请使用"年-月-日 时:分"格式,全部为数字', self::VALUE_VALIDATE, 'regex', self::MODEL_BOTH),
            array('create_time', '/^\d{4,4}-\d{1,2}-\d{1,2}(\s\d{1,2}:\d{1,2}(:\d{1,2})?)?$/', '日期格式不合法,请使用"年-月-日 时:分"格式,全部为数字', self::VALUE_VALIDATE, 'regex', self::MODEL_BOTH),
        );
        $this->_auto[] = array('status', '3', self::MODEL_BOTH);

        if (!($data = $this->create())) {
            return false;
        }

        /* 添加或新增基础内容 */
        if (empty($data['id'])) {
            //新增数据
            $id = $this->add(); //添加基础内容
            if (!$id) {
                $this->error = '新增基础内容出错！';
                return false;
            }
            $data['id'] = $id;
        } else {
            //更新数据
            $status = $this->save(); //更新基础内容
            if (false === $status) {
                $this->error = '更新基础内容出错！';
                return false;
            }
        }

        /* 添加或新增扩展内容 */
        $logic = $this->logic($data['model_id']);
        if (!$logic->autoSave($id)) {
            if (isset($id)) {
                //新增失败，删除基础数据
                $this->delete($id);
            }
            $this->error = $logic->getError();
            return false;
        }

        //内容添加或更新完成
        return $data;
    }

    /**
     * 检查指定文档下面子文档的类型
     * @param intger $type 子文档类型
     * @param intger $pid 父文档类型
     * @return array 键值：status=>是否允许（0,1），'info'=>提示信息
     * @author huajie <banhuajie@163.com>
     */
    public function checkDocumentType($type = null, $pid = null)
    {
        $res = array('status' => 1, 'info' => '');
        if (empty($type)) {
            return array('status' => 0, 'info' => '文档类型不能为空');
        }
        if (empty($pid)) {
            return $res;
        }
        //查询父文档的类型
        $ptype = is_numeric($pid) ? $this->getFieldById($pid, 'type') : $this->getFieldByName($pid, 'type');
        //父文档为目录时
        switch ($ptype) {
            case 1: // 目录
            case 2: // 主题
                break;
            case 3: // 段落
                return array('status' => 0, 'info' => '段落下面不允许再添加子内容');
            default:
                return array('status' => 0, 'info' => '父文档类型不正确');
        }
        return $res;
    }

    /**
     * 插入成功后操作
     * @param  array $data 数据
     * @return  void
     * @author crohn <lllliuliu@163.com>
     */
    protected function _after_insert(&$data, $options)
    {
        //标签数据处理
        D('TagsMap')->update($data, $this->document_name);
        //产品标签数据处理
        D('ProductTagsMap')->update($data, $this->document_name);
        //是否启用副分类
        if (C('SUB_CATEGORY') == '1') {
            //副分类数据处理
            D('CategoryMap')->update($data, $this->document_name);
        }
        //hook
        $this->hook(__FUNCTION__, $data);
    }

    /**
     * 更新成功后操作
     * @param  array $data 数据
     * @return  void
     * @author crohn <lllliuliu@163.com>
     */
    protected function _after_update(&$data, $options)
    {
        //标签数据处理
        D('TagsMap')->update($data, $this->document_name);
        //产品标签数据处理
        D('ProductTagsMap')->update($data, $this->document_name);

        //是否启用副分类
        if (C('SUB_CATEGORY') == '1') {
            //副分类数据处理

            D('CategoryMap')->update($data, $this->cate_name, $this->document_name);
        }

        //hook
        $this->hook(__FUNCTION__, $data);
    }

    /**
     * HOOK方法，加载不同的特殊逻辑，子类覆盖
     * @param string $method 方法名
     * @param array $params 参数
     * @return void
     * @author crohn <lllliuliu@163.com>
     */
    protected function hook($method, $params = array())
    {

    }

//***************************静态生成方法 START**************************//
    /**
     * update之后生成静态文件,子类可以覆盖并实现自己的生成方式
     * @param  array $p 参数 id-数据id  category_id-分类id
     * @param  boolean $clist 是否同时生成列表页 默认true
     * @return  void
     * @author crohn <lllliuliu@163.com>
     */
    protected function updateCreateStatic($p, $clist = false)
    {
        //生成内容页
        $id = intval($p['id']);

        $this->createStaticDetail($id);
        $this->createStaticDetailM($id);
        //生成列表页
        if ($clist) {
            $category_id = intval($p['category_id']);
            $this->createStaticLists($category_id, 1);
        }
    }

    /**
     * 类工厂
     * @param  array $params 构造函数参数
     * @return  void
     * @author crohn <lllliuliu@163.com>
     */
    protected function InstanceFactory($params)
    {
        $class = 'Common\\Library\\TempCreateLibrary';
        return new $class($params);
    }

    /*----------------PC版-------------------*/
    /**
     * 生成首页
     * @return  void
     * @author crohn <lllliuliu@163.com>
     */
    public function createStaticIndex()
    {
        is_null($this->staticInstance) && $this->createStaticInstance();
        return $this->staticInstance->moduleIndex('Index', 'index');
    }

    /**
     * 生成内容页
     * @param  integer $id 数据id
     * @return  void
     * @author crohn <lllliuliu@163.com>
     */
    public function createStaticDetail($id)
    {
        //为亲宝贝（一期）生成做限制，以后去掉
        if (C('THEME') == "qbaobei") {
            $cate_where['template_lists'] = array(
                array('eq', 'jiaoyu'),
                array('eq', 'jiankang'),
                array('eq', 'tuku'),
                array('eq', 'yingyang'),
                array('eq', 'food'),
                array('eq', 'baike'),
                array('eq', 'video'),
                array('eq', 'chengzhang'),
                array('eq', 'ganmao'),
                array('eq', 'zuowen'),
                'or');

            $cate_rs = D($this->cate_name)->where($cate_where)->getField('id', true);
            $cate_id = D($this->document_name)->where('id=' . $id)->field('category_id')->find();
            if (!in_array($cate_id['category_id'], $cate_rs)) {
                return false;
            }

        }

        is_null($this->staticInstance) && $this->createStaticInstance();
        return $this->staticInstance->detail($id, 'Detail', 'index');
    }

    /**
     * 生成列表页
     * @param  integer $category_id 分类id
     * @param  integer $max_page 最大生成页数
     * @return  void
     * @author crohn <lllliuliu@163.com>
     */
    public function createStaticLists($category_id, $max_page = 30)
    {
        //为亲宝贝（一期）生成做限制，以后去掉
        if (C('THEME') == "qbaobei") {
            $cate_where['template_lists'] = array(
                array('eq', 'jiaoyu'),
                array('eq', 'jiankang'),
                array('eq', 'tuku'),
                array('eq', 'yingyang'),
                array('eq', 'baike'),
                array('eq', 'food'),
                array('eq', 'video'),
                array('eq', 'chengzhang'),
                array('eq', 'ganmao'),
                array('eq', 'zuowen'),
                'or');
            $cate_rs = D($this->cate_name)->where($cate_where)->getField('id', true);
            if (!in_array($category_id, $cate_rs)) {
                return false;
            }

        }

        is_null($this->staticInstance) && $this->createStaticInstance();
        //设置最大生成页数
        if (is_numeric($max_page)) {
            $this->staticInstance->setProperty('lists_maxpage', $max_page);
        }

        return $this->staticInstance->lists($category_id, 'Category', 'index');
    }

    /**
     * 实例化PC版生成静态的类
     * @return  void
     * @author crohn <lllliuliu@163.com>
     */
    protected function createStaticInstance()
    {
        $params['model'] = $this->document_name;
        $params['module'] = $this->module; //这里是自由设置的
        $params['category'] = $this->cate_name;
        $params['static_url'] = C('STATIC_CREATE_URL');
        $this->staticInstance = $this->InstanceFactory($params);
    }

    /*----------------手机版-------------------*/
    /**
     * 生成首页
     * @return  void
     * @author crohn <lllliuliu@163.com>
     */
    public function createStaticIndexM()
    {
        if (is_null($this->staticInstanceM)) {
            $config = $this->createStaticInstanceM();
        }

        // if(empty($config['index']) && empty($this->staticInstanceM->the_moduleindex_path)) return 'noconfig';
        return $this->staticInstanceM->moduleIndex('Index', 'index');
    }

    /**
     * 生成手机版内容页
     * @param  integer $id 数据id
     * @return  void
     * @author crohn <lllliuliu@163.com>
     */
    public function createStaticDetailM($id)
    {
        if (is_null($this->staticInstanceM)) {
            $config = $this->createStaticInstanceM();
        }

        // if(empty($config['detail']) && empty($this->staticInstanceM->the_detail_path)) return 'noconfig';
        return $this->staticInstanceM->detail($id, 'Detail', 'index');
    }

    /**
     * 生成手机版列表页
     * @param  integer $category_id 分类id
     * @return  void
     * @author crohn <lllliuliu@163.com>
     */
    public function createStaticListsM($category_id, $max_page = 30)
    {
        if (is_null($this->staticInstanceM)) {
            $config = $this->createStaticInstanceM();
        }

        //if(empty($config['lists']) && empty($this->staticInstanceM->the_lists_path)) return 'noconfig';
        //设置最大生成页数
        if (is_numeric($max_page)) {
            $this->staticInstanceM->setProperty('lists_maxpage', $max_page);
        }

        return $this->staticInstanceM->lists($category_id, 'Category', 'index');
    }

    /**
     * 实例化手机版生成静态的类
     * @return  void
     * @author crohn <lllliuliu@163.com>
     */
    protected function createStaticInstanceM()
    {
        //获取手机版参数
        $config = C('MOBILE_' . strtoupper($this->document_name));
        $property = array(
            'the_lists_path' => $config['lists'],
            'the_detail_path' => $config['detail'],
            'the_moduleindex_path' => $config['index'],
            'theme' => C('MOBILE_THEME'),
        );
        //传入参数设置
        $params['model'] = $this->document_name;
        $params['module'] = $this->module;
        $params['category'] = $this->cate_name;
        $params['static_url'] = C('STATIC_CREATE_URL');
        $params['static_root_dir'] = C('MOBILE_STATIC_ROOT');
        //实例化对象
        $this->staticInstanceM = $this->InstanceFactory($params);
        //设置手机版固定属性
        $this->staticInstanceM->setProperty($property);

        return $config;
    }
//***************************静态生成方法 END**************************//
}
