<?php
/**
 * 下载模块widget
 * gfw主题
 **/

namespace Down\Widget;

use Down\Controller\BaseController;

class GfwWidget extends BaseController{

    /**
     * 首页热门推荐
     */
    public function indexHot(){
        $DownCategory = D('DownCategory');
        $hotcate = $DownCategory->where('position&1')->order('sort DESC,create_time desc')->limit(8)->select();//获取热门推荐分类
        $this->assign('hotcate', $hotcate);
        $this->display('Widget/indexHot');
    }

    /**
     * 推荐精品手游列表页
     *@return void
     */
    public function indexLTree(){
        $tree = D('DownCategory')->getTree();//获取分类树
        $this->assign('tree', $tree);
        //模板选择
        $this->display('Widget/indexLTree');
    }

    /**
     * 首页热门推荐
     */
    public function indexRTree(){
        $DownCategory = D('DownCategory');
        $tree = $DownCategory->getTree();//获取分类树
        $this->assign('tree', $tree);
        $this->display('Widget/indexRTree');
    }

    /**
     * 网络产品列表分类
     */
    public function listLTree(){
        $cate = I('cate');
        $cate_arr = getParentCategory($cate, 'DownCategory');
        $cate_root = array_shift($cate_arr);
        $cate = $cate_root['id'];
        $DownCategory = D('DownCategory');
        $tree = $DownCategory->getTree($cate);//获取分类树
        $this->assign('tree', $tree);
        $this->display('Widget/listLTree');
    }

    /**
     * 分类导航
     */
    public function navList(){
        $cate = I('cate');
        $here = getParentCategory($cate, 'down_category');
        $this->assign('here', $here);
        $this->display('Widget/navList');
    }

    /**
     * 实体产品列表页
     */
    public function listProduct(){
        $id  = I('request.cate');//获取分类id
        $DownCategory = D('DownCategory');
        $Down = D('Down');

        $tree = $DownCategory->getTree();//获取分类树
        $catelist = $DownCategory->getTree($id);//返回指定分类极其子分类

        if($catelist['_']){
            $catedata = array();
            foreach($catelist['_'] as $key=>$value){
                if($id == $value['id']){
                    $catelist['_'][$key]['selected'] = 1;
                }else{
                    $catelist['_'][$key]['selected'] = 0;
                }
            }
        }
        $this->assign('tree', $tree);
        $this->assign('catelist', $catelist);

        $this->display('Widget/listProduct');

    }

    /**
     * 网络产品频道页
     */
    public function listSoft(){
        $id  = I('cate');//当前分类id
        $DownCategory = D('DownCategory');
        //获取指定分类子分类ID
        $field = 'id,name,pid,title';
        $category = $DownCategory->getTree($id, $field);
        foreach ($category['_'] as $key=>$value) {
            $cat_arr[$key]['id'] = $value['id'];
            $cat_arr[$key]['name'] = $value['name'];
            $cat_arr[$key]['pid'] = $value['pid'];
            $cat_arr[$key]['title'] = $value['title'];
            //通过分类id获取相应产品
            $where = array(
                'map' => array('category_id' => $value['id'])
            );
            $cat_arr[$key]['_'] = D('Down')->page(1, 18)->listsWhere($where,true);
        }

        $catelist = $DownCategory->getTree($id);//返回指定分类极其子分类

        if($catelist['_']){
            $catedata = array();
            foreach($catelist['_'] as $key=>$value){
                if($id == $value['id']){
                    $catelist['_'][$key]['selected'] = 1;
                }else{
                    $catelist['_'][$key]['selected'] = 0;
                }
            }
        }
        $this->assign('catelist', $category);
        $this->assign('cat_arr', $cat_arr);
        $this->display('Widget/listSoft');
    }

    /**
     * 网络产品列表页
     */
    public function listSoft2(){

        $DownCategory = D('DownCategory');

        $tree = $DownCategory->getTree();//获取分类树
        $this->assign('tree', $tree);
        $this->display('Widget/listSoft2');
    }

    /**
     * 产品详情导航
     */
    public function navDetail(){
        $id = I('id');
        $info = D('Down')->downAll($id);
        $here = getParentCategory($info['category_id'], 'down_category');
        $this->assign('here', $here);
        $this->display('Widget/navDetail');
    }

    /**
     * 公司相关产品
     */
    public function comPro(){
        $this->display('Widget/comPro');
    }

    /**
     * 实体产品详情页
     */
    public function productDetail(){
        $id  = I('request.id');//数据id
        $info = D('Down')->downAll($id);

        //获取单个产品关联的公司详情
        if(!empty($info['package_id'])){
            $packinfo = D('Package')->detail($info['package_id']);
            $packinfo['province_id'] = M('province')->where('id='.$packinfo['province_id'])->getField('name');//赋值省份名称
            $packinfo['city_id'] = M('city')->where('id='.$packinfo['city_id'])->getField('name');//赋值城市名称
            $area = getArea($packinfo['province_id']);//获取区
            $packinfo['area_id'] = $area['name'];
        }

        $this->assign('info', $info);
        $this->assign('packinfo', $packinfo);//根据产品获取公司详情
        $this->display('Widget/productDetail');
    }

}