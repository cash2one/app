<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Document\Widget;
use Think\Controller;

/**
 * 页面widget
 * 
 */

class P7230Widget extends Controller{


    /**
     * 方法不存在时调用
     *@return void
     */
    public function __call($method,$args) {
        //斜杠会被解析，所以用下划线代替
        $method = str_replace('_', '/', $method);
        $this->display(T($method));
    }

    public function top(){
        $this->display('Widget/TopList');
    }
    public function latest(){
        $this->display('Widget/Latest');
    }
    public function hotZone(){
        $this->display('Widget/HotZone');
    }
    public function everyoneLike(){
        $this->display('Widget/everyoneLike');
    }

    /**
     * 作者:肖书成
     * 描述:文章详情页的相关下载
     */
    public function relateDown($productID){
        $data = array();

        //判断是否存在产品标签
        if(!empty($productID)) {
            $productID = $productID[0]['id'];

            //获取下载数据
            $down       = M('ProductTagsMap')->alias('a')->field('b.id,b.title,b.category_id,b.smallimg,c.system')->join('__DOWN__ b ON a.did = b.id')->join('__DOWN_DMAIN__ c ON a.did = c.id')
                ->where("a.tid = $productID AND a.type = 'down' AND b.status = 1")->order('b.id DESC')->group('c.system')->select();

            if($down){
                foreach($down as $k=>$v){
                    ($v['system'] == 1) && $data['adr'][] = $v;
                    ($v['system'] == 2) && $data['ios'][] = $v;
                }

                $tags       = get_tags($down[0]['id'],'Down');

                if($tags){
                    //获取标签数据
                    $tags   = implode(',',array_column($tags,'id'));
                    $limit  = empty($data['adr'])?5:4;
                    $adr    = M('TagsMap')->alias('a')->field('b.id,b.title,b.smallimg,d.tid')->join('__DOWN__ b ON a.did = b.id')->join('__DOWN_DMAIN__ c ON a.did = c.id')->join('__PRODUCT_TAGS_MAP__ d ON a.did = d.did')
                        ->where("a.tid IN($tags) AND a.type = 'down' ".(empty($data['adr'])?'':'AND b.id !='.$data['adr'][0]['id'])." AND b.status = 1 AND c.system = 1 AND c.network = 2 AND d.type = 'down'")->order('b.update_time DESC')->group('d.tid')->limit($limit)->select();
                    $data['adr'] = array_filter(array_merge((array)$data['adr'],(array)$adr));

                    $limit  = empty($data['ios'])?5:4;
                    $ios    = M('TagsMap')->alias('a')->field('b.id,b.title,b.smallimg')->join('__DOWN__ b ON a.did = b.id')->join('__DOWN_DMAIN__ c ON a.did = c.id')->join('__PRODUCT_TAGS_MAP__ d ON a.did = d.did')
                        ->where("a.tid IN($tags) AND a.type = 'down' ".(empty($data['ios'])?'':'AND b.id !='.$data['ios'][0]['id'])." AND b.status = 1 AND c.system = 2 AND c.network = 2 AND d.type = 'down'")->order('b.update_time DESC')->group('d.tid')->limit($limit)->select();
                    $data['ios'] = array_filter(array_merge((array)$data['ios'],(array)$ios));


                    //如果标签数据不足5条用分类数据代替
                    $row = (int)(count($data['adr']));
                    if($row<5){
                        $ids = implode(',',array_column($data['adr'],'id'));
                        empty($ids)&& $ids = 0;
                        $adr = $this->pri_down('a.id NOT IN('.$ids.') AND a.category_id = '.$down[0]['category_id'].' AND a.status = 1 AND b.system = 1',5-$row);
                        $data['adr'] = array_filter(array_merge((array)$data['adr'],(array)$adr));
                    }

                    $row = (int)(count($data['ios']));
                    if($row<5){
                        $ids = implode(',',array_column($data['ios'],'id'));
                        empty($ids)&& $ids = 0;
                        $ios = $this->pri_down('a.id NOT IN('.$ids.') AND a.category_id = '.$down[0]['category_id'].' AND a.status = 1 AND b.system = 2',5-$row);
                        $data['ios'] = array_filter(array_merge((array)$data['ios'],(array)$ios));
                    }

                }else{
                    //如果数据不足5条用分类数据代替
                    $row = (int)(count($data['adr']));
                    if($row<5){
                        $ids = implode(',',array_column($data['adr'],'id'));
                        empty($ids)&& $ids = 0;
                        $adr = $this->pri_down('a.id NOT IN('.$ids.') AND a.category_id = '.$down[0]['category_id'].' AND a.status = 1 AND b.system = 1',5-$row);
                        $data['adr'] = array_filter(array_merge((array)$data['adr'],(array)$adr));
                    }


                    $row = (int)(count($data['ios']));
                    if($row<5){
                        $ids = implode(',',array_column($data['ios'],'id'));
                        empty($ids)&& $ids = 0;
                        $ios = $this->pri_down('a.id NOT IN('.$ids.') AND a.category_id = '.$down[0]['category_id'].' AND a.status = 1 AND b.system = 2',5-$row);
                        $data['ios'] = array_filter(array_merge((array)$data['ios'],(array)$ios));
                    }
                }

            }else{
                $data['adr'] = $this->pri_down('a.status = 1 AND b.system = 1',5);
                $data['ios'] = $this->pri_down('a.status = 1 AND b.system = 2',5);
            }

        }else{
            $data['adr'] = $this->pri_down('a.status = 1 AND b.system = 1',5);
            $data['ios'] = $this->pri_down('a.status = 1 AND b.system = 2',5);
        }
        //查找数据的标签
        foreach($data as $k=>$v){
            foreach($v as $x=>$y){
                $tag = M('TagsMap')->alias('a')->join('__TAGS__ b ON a.tid = b.id')->where('a.did = '.$y['id'].' AND a.type = "down"')->getField('title',true);
                $data[$k][$x]['tag'] = implode(',',$tag);
            }
        }

        //热门专区
        $maxid = M('Batch')->field('id')->where('pid = 0 AND interface = 0 AND enabled = 1')->order('id DESC')->limit(3,1)->buildSql();

        $batch = M('Batch')->field('id,title,url_token,icon')->where("pid = 0 AND interface =0 AND enabled = 1 AND (id > (($maxid - 67)*rand() + 67))")->limit(3)->select();

        $this->assign('batch',$batch);
        $this->assign('data',$data);
        $this->display('Widget/relateDown');
    }


    /**
     * 作者:肖书成
     * 描述:获取下载的数据的私用方法（专为relateDown方法写的）
     */
    private function pri_down($where = 'a.status = 1',$row = 5){
        return M('Down')->alias('a')->field('a.id,a.title,a.smallimg')->join('__DOWN_DMAIN__ b ON a.id = b.id')->join('__PRODUCT_TAGS_MAP__ c ON a.id = c.did')
            ->where($where.' AND c.type = "down"')->order('a.view DESC')->group('c.tid')->limit($row)->select();
    }

    /**
     * 作者:肖书成
     * 描述:相关攻略文章和相关礼包
     */
    public function relateGuide($id,$productID){
        if(!empty($productID)){
            //礼包
            $package = M('ProductTagsMap')->alias('a')->field('b.id,b.title')->join('__PACKAGE__ b ON a.did = b.id')->where('a.tid = '.$productID.' AND a.type ="package" AND b.status = 1 AND b.category_id NOT IN(3,5)')->order('b.id DESC')->limit(8)->select();
            $row = (int)count($package);
            if($row<8){
                $ids = implode(',',array_column($package,'id'));
                $where = "status = 1 AND category_id = 1". (empty($ids)?'':" AND id NOT IN($ids)");
                $packageNew = M('Package')->field('id,title')->where($where)->order('id DESC')->limit(8-$row)->select();
                $package = array_filter(array_merge((array)$package,(array)$packageNew));
            }

            //攻略
            $guide = M('ProductTagsMap')->alias('a')->field('b.id,b.title,b.update_time')->join('__DOCUMENT__ b ON a.did = b.id')->where('a.tid = '.$productID.' AND a.type ="document" AND b.id != '.$id.' AND b.status = 1 AND b.category_rootid = 85')->order('b.id DESC')->limit(8)->select();
            $row = (int)count($guide);

            if($row<8){
                $ids        = implode(',',array_column($guide,'id'));
                $where      = 'a.tid = '.$productID.' AND a.type ="document" AND b.id != '.$id.' AND b.status = 1' . (empty($ids)?'':" AND b.id NOT IN($ids)");
                $guideNew   = M('ProductTagsMap')->alias('a')->field('b.id,b.title,b.update_time')->join('__DOCUMENT__ b ON a.did = b.id')->where($where)->order('b.id DESC')->limit(8-$row)->select();
                $guide      = array_filter(array_merge((array)$guide,(array)$guideNew));
                $row = (int)count($guide);
                if($row<8){
                    $ids        = implode(',',array_column($guide,'id'));
                    $where      = "status = 1 AND id != $id AND category_rootid = 85". (empty($ids)?'':" AND id NOT IN($ids)");
                    $guideNew   = M('Document')->field('id,title,update_time')->where($where)->order('id DESC')->limit(8-$row)->select();
                    $guide      = array_filter(array_merge((array)$guide,(array)$guideNew));
                }
            }
        }else{

            //攻略
            $guide      = M('Document')->field('id,title,update_time')->where('id != '.$id.' AND status = 1 AND category_rootid = 85')->order('id DESC')->limit(8)->select();

            //礼包
            $package    = M('Package')->field('id,title')->where('status = 1 AND category_id = 1')->order('id DESC')->limit(8)->select();
        }

        $this->assign(array(
            'guide'     => $guide,
            'package'   => $package,
        ));

        $this->display('Widget/relateGuide');

    }
	

}
