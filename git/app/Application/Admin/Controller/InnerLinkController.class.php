<?php
// +----------------------------------------------------------------------
// | 内链功能
// +----------------------------------------------------------------------
// | Author: Jeffrey Lau <liupan182@163.com>
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------
// | Date: 2016-3-10    Version:1.0.0
// +----------------------------------------------------------------------


namespace Admin\Controller;

use Think\Controller;

class InnerLinkController extends AdminController
{
    private $name = '关键词内链';


    /**
     * 构造函数，处理动态边栏
     * @return void
     */
    public function _initialize()
    {
        parent::_initialize();
    }


    public function index()
    {
        $name = I('get.name');
        $map = array();
        if ($name) {
            $map['words'] = array('LIKE', '%' . $name . '%');
            $count = M('Innerlink')->where($map)->count();
        } else {
            $count = M('Innerlink')->count();
        }
        $Page = new \Think\Page($count, 20);
        $Page->setConfig('prev', '上一页');
        $Page->setConfig('next', '下一页');
        $Page->setConfig('first', '第一页');
        $Page->setConfig('last', '尾页');
        $Page->setConfig('theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
        $show = $Page->show();
        if ($name) {
            $dataList = M('Innerlink')->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->field("*")->select();
        } else {
            $dataList = M('Innerlink')->limit($Page->firstRow . ',' . $Page->listRows)->field("*")->select();
        }

        $this->page = $show;
        $this->dataList = $dataList;
        $this->assign('lists', $dataList);
        $this->assign('name', $this->name);
        $this->display();
    }

    /*
     * 新增
     *
     */
    public function add()
    {
        $this->assign('type', '1');
        $this->assign('name', $this->name);
        $this->display();

    }

    /*
     * 编辑
     *
     */
    public function edit()
    {
        $id = I('get.id');
        $c = M('Innerlink')->where(array("id" => $id))->find();
        $this->assign('c', $c);
        $this->assign('name', $this->name);
        $this->assign('type', '2');
        $this->display('add');

    }

    /*
     * 添加动作
     *
     */
    public function add_action()
    {
        $id = I('id');
        $type = I('type');
        $keywords = I('keywords');
        $url = I('url');
        $pc = I('pc');
        $mobile = I('mobile');
        if (empty($keywords)) {
            $this->error('关键词不能为空');
        }
        if (empty($url)) {
            $this->error('链接地址不能为空');
        }
        if (empty($pc) && empty($mobile)) {
            $this->error('平台不能为空');
        }
        $platform = "";//适合平台
        if ($pc && $mobile) {
            $platform = "1";
        }
        if ($pc && empty($mobile)) {
            $platform = "2";
        }
        if (empty($pc) && $mobile) {
            $platform = "3";
        }
        $c = M('Innerlink');
        $data['words'] = $keywords;
        $data['url'] = $url;
        $data['platform'] = $platform;
        $data['status'] = '1';
        if ($type == "1") {//新增
            $c->add($data);
            $this->cacheLink();
            $this->success('新增成功！', U('index'));
        } else {
            M('Innerlink')->where(array("id" => $id))->save($data);
            $this->cacheLink();
            $this->success('编辑成功！', U('index'));
        }


    }

    private function cacheLink()
    {
        $pcLink = array();
        $mobileLink = array();
        $link = M('Innerlink')->where(array("status" => "1"))->select();
        foreach ($link as $key => $val) {
            if ($val['platform'] == "1" || $val['platform'] == "2") {//PC版本
                $pcLink[$key]['words'] = $val['words'];
                $pcLink[$key]['url'] = $val['url'];
            }
            if ($val['platform'] == "1" || $val['platform'] == "3") {//手机版
                $mobileLink[$key]['words'] = $val['words'];
                $mobileLink[$key]['url'] = $val['url'];
            }
        }
        S('PcInnerLink', $pcLink);//缓存内链数据
        S('MobileInnerLink', $mobileLink);//缓存内链数据
    }

    /*
     * 删除项目
     *
     */
    public function removeItem()
    {
        $id = I('id');
        if (!is_numeric($id)) {
            return;
        }
        $c = M('Innerlink')->where(array("id" => $id))->delete();
        if ($c) {
            $this->cacheLink();
            $this->success('操作成功');
        } else {
            $this->error('操作失败');
        }
    }

    /**
     * 设置状态
     *
     */
    public function setStatus()
    {
        $id = I('id');
        $type = I('type');
        if (!is_numeric($id)) {
            return;
        }
        if ($type == "1") {//启用
            $data['status'] = "1";
        } else {//禁用
            $data['status'] = "0";
        }
        $c = M('Innerlink')->where(array("id" => $id))->save($data);
        if ($c) {
            $this->cacheLink();
            $this->success('操作成功');
        } else {
            $this->error('操作失败');
        }

    }
}
