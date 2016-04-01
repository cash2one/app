<?php

namespace Down\Controller;

use Think\Exception;

class PhDetailController extends BaseController
{
    public function index()
    {
        $id = I('id');
        if (!is_numeric($id)) {
            $this->error('页面不存在！');
        }
        //数据查询
        $info = D('Down')->downAll($id);
        if (!$info) {
            $this->error('页面不存在！');
        }

        $_LISTS_ = array();
        $softID = M('DownPaihang')->where("id='$id'")->getField('soft_id');
        $ids = split(",", $softID);
        foreach ($ids as $id) {
            $_LISTS_[] = M('Down')->where("status=1 AND `id`=$id")->select();
        }
        $arrT = array();
        foreach ($_LISTS_ as $value) {
            foreach ($value as $value) {
                $arrT[] = $value;
            }
        }
        //面包屑导航
        /*$crumb = getCrumb($id,'down');
        $this->assign('crumb',$crumb);*/

        //SEO
        $this->assign("SEO", WidgetSEO(array('detail', 'Down', $id)));

        $this->assign('dataList', $arrT);
        $this->assign('info', $info);
        $this->display();
    }

    /*public function phTop()
    {
        set_time_limit(200);
        //控制层页面静态化
        $path = './Static/phTop.html';
        $file_time = fileatime($path);
        $time_now = time();
        if ((intval($file_time) + 3600) > $time_now) {
            include($path);
            //$this->show('Static/phTop.html');
        } else {
            unlink($path);
            ob_start();
            $this->display('Detail/phtop');
            $result = ob_get_contents();
            ob_flush();
            try {
                if (!file_put_contents($path, $result)) {
                    throw  Exception('文件写入失败');
                }
            } catch (Exception $e) {
                echo $e->getMessage();
                $this->redirect('index');
            }
            //$this->display('Detail/phtop');
        }
    }*/
}
