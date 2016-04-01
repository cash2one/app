<?php

namespace Home\Controller;
use OT\DataDictionary;

/**
 * 前台厂商控制器
 * 厂商页和厂商详情
 */
class CompanyController extends BaseController {

    /**
     * 厂商页
     */
    public function index(){

        //根据传入static_page表ID查找数据
        $page_id = I('page_id');
        $page_info = get_staticpage($page_id);

        //分页获取数据
        $theme = C('THEME');
        if($theme === 'afs'){
            $row = 20;
        }else{
            $row = 12;
        }

        //判断是否是手机版
        if(substr(I('t'),-6) === 'mobile'){
            $url        =   C('MOBILE_STATIC_URL');
            $isMobile = true;
        }else{
            $url        =   C('STATIC_URL');
            $isMobile = false;
        }
        $row        = I('row')?I('row'):$row;

        $count  = M('Company')->where('status = 1')->count();// 查询满足要求的总记录数

        //是否返回总页数
        if(I('gettotal')){
            if(empty($count)){
                echo 1;     
            }else{
                echo ceil($count/$row);               
            }
            exit();
        }

        $p = intval(I('p'));
        if (!is_numeric($p) || $p<=0 ) $p = 1;
        if ($p > $count ) $p = $count; //容错处理



        $lists = M('Company')->where('status = 1')->order('create_time desc')->page($p, $row)->select();
        foreach($lists as $k=>&$v){
            $v['url']   =   $url.'/'.(substr($v['path'],0,1) == '/'?substr($v['path'],1):$v['path']);
            $v['url']   =   substr($url,-1) == '/'?$v['url']:$v['url'].'/';
        }unset($v);

        $this->assign('lists',$lists);// 赋值数据集

        $path = $page_info['path'] .getStaticExt();

        $Page = new \Think\Page($count, $row, '', false, $path);// 实例化分页类 指定路径规则
        $Page->setConfig('prev','上一页');
        $Page->setConfig('next','下一页');
        $Page->setConfig('first','首页');
        $Page->setConfig('last','尾页');

        if($isMobile){//Author: xiao 为了兼容手机版列表
            $Page->isMobile  = true;
            $Page->setConfig('theme','%UP_PAGE% %DOWN_PAGE%');
        }else{
            $Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %END% %DOWN_PAGE%');
        }

        $show       = $Page->show();// 分页显示输出
        $this->assign('page',$show);// 赋值分页输出

        //SEO
        $seo['title'] = $page_info['title'];
        $seo['keywords'] =  $page_info['keywords'];
        $seo['description'] = $page_info['description'];

        $this->assign('SEO',$seo);

        //模板选择
        $this->display('Widget/company');
    }


    /**
     * 厂商详情页
     */
    public function detail(){
        //厂商ID
        $id = I('id');
        if(!is_numeric($id) || $id<1){
            return false;
        }

        //厂商数据
        $info = M('company')->where('id = '.$id)->find();
        $this->assign('info',$info);

        //分页获取数据
        $where      =1;
        $theme = C('THEME');
        if($theme === 'afs' || $theme === 'afsmobile'){
            $row    = 10000;
            $order  = 'a.view DESC';
            $where  = 'b.data_type = 1';
        }else{
            $row    = 24;
            $order  = 'a.id DESC';
        }

        //判断是否是手机版
        substr(I('t'),-6) === 'mobile'?$isMobile = true:$isMobile = false;

        $row        = I('row')?I('row'):$row;

        $count  = M('Down')->alias('a')->join('__DOWN_DMAIN__ b ON a.id = b.id')->where("a.status = 1 AND a.pid = 0 AND b.company_id = $id AND $where")->count();// 查询满足要求的总记录数

        //是否返回总页数
        if(I('gettotal')){
            if(empty($count)){
                echo 1;     
            }else{
                echo ceil($count/$row);               
            }
            exit();
        }

        $p = intval(I('p'));
        if (!is_numeric($p) || $p<=0 ) $p = 1;
        if ($p > $count ) $p = $count; //容错处理

        $lists = M('Down')->alias('a')->field('a.id,a.view hits,a.smallimg,a.title,a.abet,a.description,a.update_time,b.version,b.size,c.title cate')->join('__DOWN_DMAIN__ b ON a.id = b.id')->join('__DOWN_CATEGORY__ c ON a.category_id = c.id')->where("a.status = 1 AND a.pid = 0 AND b.company_id = $id AND $where")->order($order)->page($p, $row)->select();

        $this->assign(array(
            'count'=>$count,
            'lists'=>$lists,
            'empty'=>'<span style="display: block; color: #666; margin-top: 30px; text-align: center; line-height: 30px; height: 30px; width: 100%">暂时没有数据</span>',
        ));// 赋值数据集

        if((int)$count>(int)$row){
            $path = (substr($info['path'],-1) == '/'?substr($info['path'],0,-1):$info['path']) .'/page_{page}'. getStaticExt();

            $Page = new \Think\Page($count, $row, '', false, $path);// 实例化分页类 指定路径规则
            $Page->setConfig('prev','上一页');
            $Page->setConfig('next','下一页');
            $Page->setConfig('first','首页');
            $Page->setConfig('last','尾页');

            if($isMobile){//Author: xiao 为了兼容手机版列表
                $Page->isMobile  = true;
                $Page->setConfig('theme','%UP_PAGE% %DOWN_PAGE%');
            }else{
                $Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %END% %DOWN_PAGE%');
            }

            $show       = $Page->show();// 分页显示输出
            $this->assign('page',$show);// 赋值分页输出
        }
        $this->assign('count',$count);

        //SEO
        $seo['title'] = $info['title'];
        $seo['keywords'] =  $info['keywords'];
        $seo['description'] = $info['description'];

        $this->assign('SEO',$seo);
        //模板选择
        $this->display('Widget/csyx');
    }

    /**
     * 描述：厂商手机内容页
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function mobileDetail()
    {
        $id = I('id');
        if(is_numeric($id))
        {
            $this->assign('id',$id);
            $this->display('Widget/csyxmobile');
        }

    }


}