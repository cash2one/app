<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: huajie <banhuajie@163.com>
// +----------------------------------------------------------------------

namespace Admin\Controller;

/**
 * 行为控制器
 * @author huajie <banhuajie@163.com>
 */
class ActionController extends AdminController {

    /**
     * 行为日志列表
     * @author huajie <banhuajie@163.com>
     */
    public function actionLog(){
        //获取列表数据
        $map['status']    =   array('gt', -1);
        $list   =   $this->lists('ActionLog', $map);
        int_to_string($list);
        foreach ($list as $key=>$value){
            $model_id                  =   get_document_field($value['model'],"name","id");
            $list[$key]['model_id']    =   $model_id ? $model_id : 0;
        }
        $this->assign('_list', $list);
        $this->meta_title = '行为日志';
        $this->display();
    }

    /**
     * 查看行为日志
     * @author huajie <banhuajie@163.com>
     */
    public function edit($id = 0){
        empty($id) && $this->error('参数错误！');

        $info = M('ActionLog')->field(true)->find($id);

        $this->assign('info', $info);
        $this->meta_title = '查看行为日志';
        $this->display();
    }

    /**
     * 删除日志
     * @param mixed $ids
     * @author huajie <banhuajie@163.com>
     */
    public function remove($ids = 0){
        empty($ids) && $this->error('参数错误！');
        if(is_array($ids)){
            $map['id'] = array('in', $ids);
        }elseif (is_numeric($ids)){
            $map['id'] = $ids;
        }
        $res = M('ActionLog')->where($map)->delete();
        if($res !== false){
            $this->success('删除成功！');
        }else {
            $this->error('删除失败！');
        }
    }

    /**
     * 清空日志
     */
    public function clear(){
        $res = M('ActionLog')->where('1=1')->delete();
        if($res !== false){
            $this->success('日志清空成功！');
        }else {
            $this->error('日志清空失败！');
        }
    }



    /**
     * 描述：获取导入数据（采用了数组分页，因为每次更新数据都存入了缓存中，每次更新时替换上一次数据或者缓存过期数据清空）
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function import()
    {
        $map['a.type'] = 2;
        if(I('title'))
        $map['b.title'] = array('LIKE','%'.I('title').'%');
        $map['a.update_time'] = array(array('egt',strtotime(I('time_start')) ? strtotime(I('time_start')) : strtotime('1970-1-2')),array('elt',strtotime(I('time_end')) ? strtotime(I('time_end').' 23:59:59') : strtotime('2035-12-12')));
        if(isset($_GET['status'])){
            $map['b.status'] = I('status');
            $this->assign('status', I('status'));
        }
        $p=$_GET['p']?$_GET['p']:'1';
        $page=10;
       // echo  M('api_import_log')->alias('a')->join('__DOWN__ b ON a.did = b.id')->where($map)->field('a.id,a.did,b.category_id,a.content,b.title,a.update_time')->order('a.id desc')->page($p.','.$page)->fetchsql(true)->select();
        $list = M('api_import_log')->alias('a')->join('__DOWN__ b ON a.did = b.id')->where($map)->field('a.id,a.did,b.category_id,a.content,b.title,a.update_time')->order('a.id desc')->page($p.','.$page)->select();
        $count=M('api_import_log')->alias('a')->join('__DOWN__ b ON a.did = b.id')->where($map)->count();// 查询满足要求的总记录数
        $Page = new \Think\Page($count,$page);// 实例化分页类 传入总记录数和每页显示的记录数
        $Page->parameter['title']   =   I('title');
        $Page->parameter['status']   =   I('status');
        $Page->parameter['time_start']   =   urlencode(I('time_start'));
        $Page->parameter['time_end']   =   urlencode(I('time_end'));
        $pagination = $Page->show();// 分页显示输出
        $this->assign('pagination',$pagination);
        $this->assign('list', $list);
        $this->meta_title = '更新数据';
        $this->display();

    }

    /**
     * 描述：二维数组去重复（按理要写到public里面去）
     * @param $array2D
     * @param bool $stkeep
     * @param bool $ndformat
     * @return mixed
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    function unique_arr($array2D,$stkeep=false,$ndformat=true)
    {
        // 判断是否保留一级数组键 (一级数组键可以为非数字)
        if($stkeep) $stArr = array_keys($array2D);

        // 判断是否保留二级数组键 (所有二级数组键必须相同)
        if($ndformat) $ndArr = array_keys(end($array2D));

        //降维,也可以用implode,将一维数组转换为用逗号连接的字符串
        foreach ($array2D as $v){
            $v = join(",",$v);
            $temp[] = $v;
        }

        //去掉重复的字符串,也就是重复的一维数组
        $temp = array_unique($temp);

        //再将拆开的数组重新组装
        foreach ($temp as $k => $v)
        {
            if($stkeep) $k = $stArr[$k];
            if($ndformat)
            {
                $tempArr = explode(",",$v);
                foreach($tempArr as $ndkey => $ndval) $output[$k][$ndArr[$ndkey]] = $ndval;
            }
            else $output[$k] = explode(",",$v);
        }

        return $output;
    }

    /**
     * 描述：获取百度时时推送url日志
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function sitemapInfo()
    {
        $map['update_time'] = array(array('egt',strtotime(I('time_start')) ? strtotime(I('time_start')) : strtotime('1970-1-2')),array('elt',strtotime(I('time_end')) ? strtotime(I('time_end')) : strtotime('2035-12-12')));
        $p=$_GET['p']?$_GET['p']:'1';
        $page=10;
        // echo  M('api_import_log')->alias('a')->join('__DOWN__ b ON a.did = b.id')->where($map)->field('a.id,a.did,b.category_id,a.content,b.title,a.update_time')->order('a.id desc')->page($p.','.$page)->fetchsql(true)->select();
        $list = M('api_sitemap_log')->where($map)->field('id,url,update_time,status')->order('id desc')->page($p.','.$page)->select();
        $count=M('api_sitemap_log')->where($map)->count();// 查询满足要求的总记录数
        $Page = new \Think\Page($count,$page);// 实例化分页类 传入总记录数和每页显示的记录数
        $Page->parameter['time_start']   =   urlencode(I('time_start'));
        $Page->parameter['time_end']   =   urlencode(I('time_end'));
        $pagination = $Page->show();// 分页显示输出
        $this->assign('pagination',$pagination);
        $this->assign('list', $list);
        $this->meta_title = '百度时时推送地址记录';
        $this->display();
    }
}
