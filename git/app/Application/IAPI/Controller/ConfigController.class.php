<?php
// +----------------------------------------------------------------------
// |  系统配置管理类
// +----------------------------------------------------------------------
// |  Author: 朱德胜
// |  Time  : 2015-06-26
// +----------------------------------------------------------------------

namespace IAPI\Controller;

class ConfigController extends CommonController
{

    /**
     * 全部配置详情信息
     *
     * @return json or xml
     */
    public function select()
    {
        $logic = array('status' => 1);

        if (isset($_GET['group'])) {
            $logic['group'] = I('get.group');
        }

        $config = M("Config")->where($logic)->order('sort')->select();

        if ($config) {
            $this->ajaxReturn($config, $this->returnType);
        }
    }

    /**
     * 配置类型
     *
     * @return json or xml
     */
    public function type()
    {
        $config = S('DB_CONFIG_DATA');

        if (isset($config['CONFIG_GROUP_LIST'])) {
            $this->ajaxReturn($config['CONFIG_GROUP_LIST'], $this->returnType);
        }
    }

    /**
     * 数据类型
     *
     * @return json or xml
     */
    public function dataType()
    {
        $config = S('DB_CONFIG_DATA');

        if (isset($config['CONFIG_TYPE_LIST'])) {
            $this->ajaxReturn($config['CONFIG_TYPE_LIST'], $this->returnType);
        }
    }

    /**
     * 配置参数列表
     *
     * @return json or xml
     */
    public function lists()
    {
        // 数据显示数量
        if ($_GET['limit']) {
            $limit = I('get.limit');
        }

        // 排序方式
        if ($_GET['order']) {
            $order = I('get.order');
        }

        $logic = array('status' => 1);

        // 配置群组
        if ($_GET['group']) {
            $logic['group'] = I('get.group');
        }

        $result = M('config')->where($logic)->order($order)->limit($limit)->select();

        if ($result) {
            $this->ajaxReturn($result, $this->returnType);
        }
    }

    /**
     * 新增配置
     *
     * @return json or xml
     */
    public function insert()
    {
        $mod = new \Admin\Model\ConfigModel();
        $result = array('status' => '0', 'error' => '');

        // 获取配置分组ID
        if (isset($_POST['groupname'])) {
            $groupname = I('post.groupname');

            if ($groupname) {
                $config = S('DB_CONFIG_DATA');
                $type = array_flip($config['CONFIG_GROUP_LIST']);

                if (isset($type[$groupname])) {
                    $_POST['group'] = $type[$groupname];
                } else {
                    $result['error'] = '配置分组不存在';
                    $this->ajaxReturn($result, $this->returnType);
                }
            }

            unset($_POST['groupname']);
        }

        // 检查标识是否存在
        $name = I('name');
        if ($name) {
            if ($mod->where(array('name' => strtoupper($name)))->find()) {
                $result['error'] = '标识已经存在';
                $this->ajaxReturn($result, $this->returnType);
            }
        }

        // 数据写入库
        if ($mod->create()) {
            if ($mod->add()) {
                S('DB_CONFIG_DATA', null);
                S('DB_CONFIG_DATA', api('Config/lists'));
                $result = 1;
            } else {
                $result = 0;
            }
        } else {
            $result['status'] = '-1';
            $result['error'] = $mod->getError();
        }

        $this->ajaxReturn($result, $this->returnType);
    }

    /**
     * 修改配置
     *
     * @return json or xml
     */
    public function update()
    {
        $mod = new \Admin\Model\ConfigModel();
        $result = array('status' => '0', 'error' => '');
        $name = I('post.name');

        // 获取配置分组ID
        if (isset($_POST['groupname'])) {
            $groupname = I('post.groupname');

            if ($groupname) {
                $config = S('DB_CONFIG_DATA');
            }

            $type = array_flip($config['CONFIG_GROUP_LIST']);

            if (isset($type[$groupname])) {
                $_POST['group'] = $type[$groupname];
            } else {
                $result['error'] = '配置分组不存在';
                $this->ajaxReturn($result, $this->returnType);
            }

            unset($_POST['groupname']);
        }

        $before = I('post.before');

        // 检查记录是否存在
        $detail = $mod->where(array('name' => $before))->find();

        if (!$detail) {
            $result['error'] = '配置不存在';
            $this->ajaxReturn($result, $this->returnType);
        }

        // 检查配置标示是否已改动
        if (isset($_POST['before'])) {
            if ($before != $name) {
                if ($empty) {
                    $result['error'] = '配置标识已存在';
                    $this->ajaxReturn($result, $this->returnType);
                }
            }

            unset($_POST['before']);
        }

        $_POST['id'] = $detail['id'];

        // 数据写入库
        if ($mod->create()) {
            if ($mod->save()) {
                $result = 1;
                S('DB_CONFIG_DATA', null);
                S('DB_CONFIG_DATA', api('Config/lists'));
            } else {
                $result = 0;
            }
        } else {
            $result['status'] = '-1';
            $result['error'] = $mod->getError();
        }

        $this->ajaxReturn($result, $this->returnType);
    }

    /**
     * 删除配置
     *
     * @return json or xml
     */
    public function delete()
    {
        $i = 0;

        $mod = M('config');
        $name = strtoupper(I('post.name'));

        if (!$mod->where(array('name' => $name))->find()) {
            $result['status'] = 0;
            $result['error'] = '配置记录不存在';
            $this->ajaxReturn($result, $this->returnType);
        }

        if ($mod->where(array('name' => $name))->delete()) {
            S('DB_CONFIG_DATA', null);
            S('DB_CONFIG_DATA', api('Config/lists'));
            $this->ajaxReturn(1, $this->returnType);
        } else {
            $this->ajaxReturn(0, $this->returnType);
        }
    }

    /**
     * 修改多个配置值
     *
     * @return json or xml
     */
    public function updateValue()
    {

        $i = 0;
        $config = M('config');

        foreach (I('post.') as $name => $value) {
            $result = $config->where(array('name' => $name))->setField('value', $value);
            if ($result) {
                $i++;
            }
        }

        if ($i >= 1) {
            S('DB_CONFIG_DATA', null);
            S('DB_CONFIG_DATA', api('Config/lists'));
            $result = 1;
        } else {
            $result = 0;
        }

        $this->ajaxReturn($result, $this->returnType);
    }

    /**
     * 修改单个配置值
     *
     * @return json or xml
     */
    public function updateOneValue()
    {
        $result = array('status' => '0', 'error' => '');

        $config = M('config');
        $name = key($_POST);
        $value = I('post.' . $name);

        if (!$config->where(array('name' => $name))->find()) {
            $result['error'] = '配置记录不存在';
            $this->ajaxReturn($result, $this->returnType);
        }

        if ($config->where(array('name' => $name))->setField('value', $value)) {
            S('DB_CONFIG_DATA', null);
            S('DB_CONFIG_DATA', api('Config/lists'));
            $result = 1;
        } else {
            $result = 0;
        }

        $this->ajaxReturn($result, $this->returnType);
    }

    /**
     * 配置详情
     *
     * @return json or xml
     */
    public function detail()
    {
        $mod = M('config');
        $name = strtoupper(I('get.name'));

        if (!$mod->where(array('name' => $name))->find()) {
            $result['status'] = 0;
            $result['error'] = '配置记录不存在';
            $this->ajaxReturn($result, $this->returnType);
        }

        $result = $mod->where(array('name' => $name))->find();

        $this->ajaxReturn($result, $this->returnType);
    }

    /**
     * 配置值详情
     *
     * @return json or xml
     */
    public function varDetail()
    {
        $mod = M('config');
        $name = strtoupper(I('get.name'));

        if (!$mod->where(array('name' => $name))->find()) {
            $result['status'] = 0;
            $result['error'] = '记录不存在';
            $this->ajaxReturn($result, $this->returnType);
        }

        $result = $mod->where(array('name' => $name))->find();

        $this->ajaxReturn($result, $this->returnType);
    }
}
