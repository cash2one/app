<?php
// +----------------------------------------------------------------------
// |  数据中心标签类
// +----------------------------------------------------------------------
// |  Author: 肖书成
// |  Time  : 2015-11-21
// +----------------------------------------------------------------------

namespace IAPI\Controller;

class TagsController extends DataBaseController{
    /**
     * 描述:不传参数则获取所有的标签
     * GET  @tree 代表获取带分类树的标签
     * GET  @model_id 代表获取绑定当前模型的标签
     * 时间:2015-11-21
     */
    public function get(){
        //参数接收
        $tree       =   I('tree');
        $model_id   =   I('model_id');

        //验证参数
        if($model_id && !is_numeric($model_id)){
            $status['error'] = '参数错误';
            $this->ajaxReturn($status,$this->returnType);
        }

        //获取所有标签分类的ID 创造条件
        if($model_id){
            //有则返回缓存
            $result     =   (!$tree?S('IAPI_tags'.$model_id):S('IAPI_tags_tree'.$model_id));
            if($result) $this->ajaxReturn($result,$this->returnType,false);

            //根据模型id 获取绑定标签分类
            $tagCate    =   M('Model')->where('id = '.$model_id)->getField('tags_category');
            if(empty($tagCate))
                $this->ajaxReturn(null,$this->returnType);

            //条件拼接
            $where      =   'status = 1 AND (category = '.str_replace(',',' OR category = ',$tagCate).')';
        }else{
            //有缓存则返回缓存
            $model_id   =   0;
            $result     =   (!$tree?S('IAPI_tags'.$model_id):S('IAPI_tags_tree'.$model_id));
            if($result) $this->ajaxReturn($result,$this->returnType,false);

            //根据模型id 获取标签分类
            $tagCate    =   M('TagsCategory')->where('status = 1')->getField('id',true);
            if(empty($tagCate))
                $this->ajaxReturn(null,$this->returnType);

            //条件拼接
            $where      =   'status = 1 AND (category = '.implode(' OR category = ',$tagCate).')';
        }

        //根据标签分类ID来查询标签数据
        $tags['model_id']   =   $model_id;
        $tags['tags']       =   M('Tags')->field('id,category,name,title,pid')->where($where)->order('id DESC')->select();

        //数据缓存并输出
        if(!$tree){
            $arr        =   json_encode($tags);
            S('IAPI_tags'.$model_id,$arr,1200);
            $this->ajaxReturn($arr,$this->returnType,false);
        }else{
            //        $arr_tree   =   list_to_tree($tags,'id','pid','child',0);
            $tags['tags']   =   $this->array_tree($tags['tags']);
            $arr_tree       =   json_encode($tags);
            S('IAPI_tags_tree'.$model_id,$arr_tree,1200);
            $this->ajaxReturn($arr_tree,$this->returnType,false);
        }
    }
}