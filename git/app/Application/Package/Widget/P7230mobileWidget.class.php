<?php

namespace Package\Widget;
use Package\Controller\BaseController;

class P7230mobileWidget extends BaseController{

    public function relateDown($id){
        if(empty($id)) return false;

        $info = M('ProductTagsMap')->alias('a')->field('b.id,b.title')->join('__DOWN__ b ON a.did = b.id')->join('__DOWN_DMAIN__ c ON b.id = c.id')
            ->where("a.tid = $id AND a.type='down' AND b.status = 1 AND b.pid = 0 AND c.channel = 1")->order('b.id DESC')->find();

        if(empty($info)){
            $info = M('ProductTagsMap')->alias('a')->field('b.id,b.title')->join('__DOWN__ b ON a.did = b.id')
                ->where("a.tid = $id AND a.type='down' AND b.status = 1 AND b.pid = 0")->order('b.id DESC')->find();
        }

        $this->assign('info',$info);
        $this->display('Widget/relateDown');
    }

    /**
     * 作者:肖书成
     * 描述:礼包列表页，此处是个bug
     * 时间:2015/11/11
     */
    public function packageList(){
        //根据传入static_page表ID查找数据
        $page_id    = I('page_id');
        $page_info  = get_staticpage($page_id);
        $cate       = I('cate');
        $where      = "status = 1 AND ";
        $libao      = false;
        if($libao = in_array($cate,array('1','2','4'))){
            $where  .= "category_id = $cate";
        }elseif($cate == 'true'){
            $where  .= "category_id IN(1,2,4)";
        }else{
            return false;
        };

        //分页获取数据
        if($libao){
            $info   = M('PackageCategory')->field('id,title,list_row,meta_title,keywords,description')->where("id = $cate AND status = 1")->find();
        }else{
            $info['title'] = I('title');
        }
        $count  = M('Package')->where($where)->count('id');// 查询满足要求的总记录数

        //是否返回总页数
        $row = empty($info['list_row'])?10:(int)$info['list_row'];
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

        $lists = M('Package')->alias('a')->field('a.id,a.title,a.cover_id,b.end_time')->join('__PACKAGE_PMAIN__ b ON a.id = b.id')->where("a.status = 1 AND a.category_id = $cate")->order('a.update_time DESC')->page($p, $row)->select();
        $this->assign('lists',$lists);// 赋值数据集

        $path = $page_info['path'] . getStaticExt();

        $Page = new \Think\Page($count, $row,'', false, $path);// 实例化分页类 指定路径规则
        $Page->setConfig('prev','上一页');
        $Page->setConfig('next','下一页');
        $Page->setConfig('theme','%UP_PAGE% %DOWN_PAGE%');
        $show       = $Page->show();// 分页显示输出
        $this->assign('page',$show);// 赋值分页输出

        //SEO
        $SEO['title']       = $info['meta_title'];
        $SEO['keywords']    = $info['keywords'];
        $SEO['description'] = $info['description'];

        if(!empty($page_info)){
            $SEO['title']       = $page_info['title']?$page_info['title']:$SEO['title'];
            $SEO['keywords']    = $page_info['keywords']?$page_info['keywords']:$SEO['keywords'];
            $SEO['description'] = $page_info['description']?$page_info['description']:$SEO['description'];
        }

        $this->assign("info",$info);
        $this->assign("SEO",$SEO);
        $this->display('Category/index');
    }
}