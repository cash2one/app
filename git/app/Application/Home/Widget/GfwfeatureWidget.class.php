<?php
/**
 * Author: 肖书成
 * Date: 2015/5/11
 * Common:专题
 */

namespace Home\Widget;

use Common\Controller\WidgetController;

class GfwfeatureWidget extends WidgetController{

    //专题1所调用的方法
    public function zt1($id,$row = 11,$isMobile = false){
        //分页数据获取
        $p = intval(I('p'));
        if (!is_numeric($p) || $p<=0 ) $p = 1;

        //专题的基础数据
        $feature            = M('Feature')->field('id,category_rootid,title,seo_title,keywords,description,icon')->where('id = '.$id)->find();

        //SEO
        $SEO['title']       = $feature['seo_title'];
        $SEO['keywords']    = $feature['keywords'];
        $SEO['description'] = $feature['description'];

        //获取本专题的 标签
        $featureTagId       = M('Tags')->where('title = "'.$feature['title'].'"')->getField('id');

        if($featureTagId){
            //统计记录数
            $count          = M('TagsMap')->alias('a')->join('__PACKAGE__ b ON a.did = b.id')->where('a.tid = '.$featureTagId.' AND a.type = "package" AND b.status = 1')->count();

            //分页
            $this->pages($count,$row,$id,$isMobile);

            //查询符合条件的数据
            $lists          = M('TagsMap')->alias('a')->field('b.id,b.title,b.description,b.cover_id')->join('__PACKAGE__ b ON a.did = b.id')->where('a.tid = '.$featureTagId.' AND a.type = "package" AND b.status = 1')->page($p,$row)->select();
        }

        //获取本专题根分类
        $cate           = M('feature_category')->where('id = '.$feature['category_rootid'])->getField('title');

        //页面赋值
        $this->assign(array(
            'base'  =>  $feature,
            'SEO'   =>  $SEO,
            'lists' =>  $lists,
            'cate'  =>  $cate,
        ));
    }

    public function sj1($id){
        $this->zt1($id,8,true);
    }

    //分页
    public function pages($count,$row,$id,$isMobile = false){
        $rows=M('Feature')->field('url_token')->find($id);
        $Page = new \Think\Page($count,$row,'',false,C('FEATURE_ZT_DIR').'/'.$rows['url_token'].C('FEATURE_PAGE'));// 实例化分页类 指定路径规则
        $Page->setConfig('prev','上一页');
        $Page->setConfig('next','下一页');
        $Page->setConfig('first','首页');
        $Page->setConfig('last','尾页');
        if($isMobile){
            $Page->setConfig('theme','%UP_PAGE% %DOWN_PAGE%');
        }

        $show       = $Page->show();// 分页显示输出
        //var_dump(array($id=>ceil($count/$row)),'----');
        $show.='<br totalPageNumber="'.ceil($count/$row).'" style="display:none">';
        $this->assign('page',$show);// 赋值分页输出
        S('Batch'.'PageNumber',array($id=>ceil($count/$row)),86400);
    }
}