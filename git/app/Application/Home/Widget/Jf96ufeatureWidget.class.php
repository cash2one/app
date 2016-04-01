<?php
/**
 * 作者:肖书成
 * 描述:96u专题 Widget 类
 * Date: 2015-12-11
 * Time: 11:58
 */

namespace Home\Widget;

use Home\Controller\BatchController;
class jf96ufeatureWidget extends BatchController{
    /**
     * 描述:专题空操作（必须要这个方法，为了融合 image 和 html 方法）
     * 时间:2015-12-15
     */
    public function __call($method, $args){
        return;
    }


    /**
     * 描述:专题专区的头部 HTML 部分
     * 时间:2015-12-15
     */
    public function head(){
        $this->display(T('Home@jf96u/FeatureWidget/head'));
    }


    /**
     * 描述:专题专区的头部 body 部分
     * 时间:2015-12-15
     */
    public function body(){
        $this->display(T('Home@jf96u/FeatureWidget/body'));
    }


    /**
     * 描述:专题专区的头部 foot 部分
     * 时间:2015-12-15
     */
    public function foot(){
        $this->display(T('Home@jf96u/FeatureWidget/foot'));
    }


    /**
     * 描述:为手机版的专区专题 提供的标签不足20条，自动填充至20条
     * 时间:2015-12-15
     * @param $id
     * @param $num
     * @param string $type
     */
    public function get_tag($id,$num,$type='feature',$is_M = true){
        $num    =   (int)$num;
        $is_M === true?$number = 10:$number = 12;

        if($num>=0&&$num<$number){
            //查找标签ID
            $tid    =   M('ProductTagsMap')->field('tid')->where("did = $id and type = '$type'")->select();
            if(empty($tid)){ return false;}

            //查找标签数据
            $data   =   M('ProductTagsMap')->alias('a')->field('b.id,b.title,b.category_id,b.description,b.smallimg,b.model_id')->join('__DOWN__ b ON a.did = b.id')
                        ->join('__DOWN_CATEGORY__ c ON b.category_id = c.id')->where("a.tid = ".$tid[0]['tid']." AND a.type = 'down'")
                        ->order('b.id DESC')->limit($number - $num)->select();
            if(empty($data)){return false;}

            //根据标签数据查找相应附属模型
            $model_id   =   implode(',',array_unique(array_column($data,'model_id')));
            $model      =   M('Model')->where("id IN($model_id) AND name != 'paihang'")->getField('id,name');

            $data_id    =   array();
            $arr        =   array();
            foreach($data as $k=>$v){
                foreach($model as $k1=>$v1){
                    if($k1 == $v['model_id']){
                        $data_id[$v1][] =   $v['id'];
                        $arr[$v1][] =   $v;
                        continue;
                    }
                }
            }

            //根据标签数据查找相应附属模型数据
            $arr_data   =   array();
            foreach($data_id as $k=>$v){
                $arr_data[$k]  =   M('Down'.ucfirst($k))->where('id IN('.implode(',',$v).')')->getField('id,version,size');
            }

            //查找下载分类
            $cate = M('DownCategory')->where('status = 1')->getField('id,title');


            //合并附属模型的数据
            $data   =   array();
            foreach($arr as $k=>$v){
                foreach($v as $k1=>$v1){
                    if($arr_data[$k][$v1['id']]){
                        $v1['cate'] =   $cate[$v1['category_id']];
                        $data[] =   array_merge($v1,$arr_data[$k][$v1['id']]);
                    }
                }
            }

            $is_M === true ? $is_M = 'Mmore':$is_M = 'Pmore';

            $this->assign('lists',$data);
            $this->display(T('Home@jf96u/FeatureWidget/'.$is_M));
        }
    }
} 