<?php
// +----------------------------------------------------------------------
// |  数据中心分类类
// +----------------------------------------------------------------------
// |  Author: 肖书成
// |  Time  : 2015-11-20
// +----------------------------------------------------------------------

namespace IAPI\Controller;

class ClassController extends DataBaseController{

    /**
     * 描述:获取所有的分类模型
     *
     */
    public function get(){
        $m  =   I('m');
        $model  =   array('document'=>'Category','down'=>'DownCategory','package'=>'PackageCategory','gallery'=>'GalleryCategory');


        //查询是否存在 分类表
        $models =   unserialize(S('modelsCate'));
        if(!$models){
            $models  =   M('Model')->where('status = 1 AND name = "'.implode('" OR name = "',array_keys($model)).'"')->getField('name',true);
            S('modelsCate',serialize($models),1200);
        }

        //查询是否有缓存 有则返回缓存数据
        if($m){
            //有参数则验证参数
            $m  =   strtolower($m);
            if(!in_array($m,$models)){
                $status['error']    =   '参数错误';
                $this->ajaxReturn($status,$this->returnType);
            }

            $result = S($model[$m].'jf');
            if($result) $this->ajaxReturn($result,$this->returnType,false);
        }else{
            $result = S('allCate');
            if($result) $this->ajaxReturn($result,$this->returnType,false);
        }


        //查询各模块的分类
        $cate   =   array();
        foreach($models as $k=>$v){
            $cate[$k]['m']      =   $v;
            $cate[$k]['class']  =   $this->selctClassTree($model[$v]);
            if(!empty($m) && $m == $v)
                $result = $cate[$k];
        }

        //存入缓存
        foreach($cate as $k=>$v){
            S($model[$v['m']].'jf',json_encode($v),1200);
        }
        S('allCate',json_encode($cate),1200);

        //结果返回
        $result?'':$result = $cate;
        $this->ajaxReturn($result,$this->returnType);
    }

    /**
     * 描述:私有方法，根据分类表获取分类树
     * @param $table
     * @return array
     */
    private function selctClassTree($table){
        $cate       =   M($table)->field('id,name,title,pid,model,allow_publish')->where('status = 1')->order('pid ASC,sort ASC')->select();
//        $cate       =   list_to_tree($cate,'id','pid','child',0);
        $cate       =   $this->array_tree($cate,$name = 'child', $pid = 0);

        foreach($cate as $k=>$v){
            if($v['pid'] != 0){
                unset($cate[$k]);
            }
        }

        return $cate;
    }



}