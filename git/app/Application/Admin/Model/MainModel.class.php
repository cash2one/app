<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Admin\Model;
use Think\Model;

/**
 * 编辑工作量统计模型
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */

class MainModel extends Model {

    protected $tableName = 'down';

    /**
     * 下载统计
     * @param  integer $uid 用户ID
     * @param int $typeid 0,新增。1，修改
     * @param int $starttime,开始时间. $endtime，结束时间
     * @return int
     */
    public function getDownCount( $uid, $typeid=0, $starttime, $endtime)
    {
        if(empty($uid))
        {
            return false;
        }

        if(empty($typeid)){
            $map['uid'] = $uid;
            $map['create_time'] = array('between',array($starttime, $endtime));
        }else{
            $map['edit_id'] = $uid;
            $map['update_time'] = array('between',array($starttime, $endtime));
        }
        if($typeid==1){
            $map['create_time']=array('lt',$starttime);
        }

        $result = M('Down')->where($map)->count();//执行
        if($result){
            return $result;
        }else{
            return 0;
        }


    }

    /**
     * 文章统计
     * @param  integer $uid 用户ID
     * @param $d 时间
     * @param int $typeid 0,新增。1，修改
     * @param int $starttime,开始时间. $endtime，结束时间
     * @return int
     */
    public function getDocumentCount( $uid, $typeid=0, $starttime, $endtime)
    {
        if(empty($uid))
        {
            return false;
        }

        if(empty($typeid)){
            $map['uid'] = $uid;
            if(!empty($starttime) && !empty($endtime)) $map['create_time'] = array('between',array($starttime, $endtime));
        }else{
            $map['edit_id'] = $uid;
            if(!empty($starttime) && !empty($endtime)) $map['update_time'] = array('between',array($starttime, $endtime));
        }
        if($typeid==1){
            $map['create_time']=array('lt',$starttime);
        }

        $result = M('document')->where($map)->count('id');//执行
        if($result){
            return $result;
        }else{
            return 0;
        }

    }

    /**
     * 描述：总点击量
     * @param $uid
     * @param string $model （表示文章、下载、礼包模型，默认为文章模型）
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function getViewSum($uid,$model='document',$starttime,$endtime)
    {
        if(empty($uid))
        {
            return false;
        }
        $fields =  M($model)->getDbFields();
        if(in_array('view',$fields)) //判断字段是否存在
        {
            $map['uid'] = $uid;
            if(!empty($starttime) && !empty($endtime))  $map['create_time'] = array('between',array($starttime, $endtime));
            $result = M($model)->where($map)->sum('view');//执行
        }
        if($result){
            return $result;
        }else{
            return 0;
        }
    }

    /**
     * 礼包统计
     * @param  integer $uid 用户ID
     * $param int $d 时间
     * @param int $catid 礼包分类id
     * @param int $starttime,开始时间. $endtime，结束时间
     * @return int
     */
     public function getPackageCount($uid, $catid='', $starttime, $endtime)
     {
         if(empty($uid))
         {
             return false;
         }
         $map['uid'] = $uid;
         if(!empty($catid))
         {
            $map['category_id'] = array('in', $catid);
         }
         $map['create_time'] = array('between',array($starttime, $endtime));
         //礼包统计
         $result = M('package')->where($map)->count();
         if($result){
             return $result;
         }else{
             return 0;
         }
     }

    /**
     * 专题统计
     * @param  integer $uid 用户ID
     * @param int $starttime,开始时间. $endtime，结束时间
     * @return int
     */
    public function getFeatureCount($uid, $starttime, $endtime)
    {
        if(empty($uid))
        {
            return false;
        }
        $map['uid'] = $uid;
        $map['update_time'] = array('between',array($starttime, $endtime));
        $result = M('feature')->where($map)->count();
        if($result){
            return $result;
        }else{
            return 0;
        }
    }

    /**
     * 专区统计
     * @param  integer $uid 用户ID
     * @return int
     */
    public function getBatchCount($uid, $starttime, $endtime)
    {
        if(empty($uid))
        {
            return false;
        }
        $map['uid'] = $uid;
        $map['update_time'] = array('between',array($starttime, $endtime));
        $result = M('batch')->where($map)->count();
        if($result){
            return $result;
        }else{
            return 0;
        }
    }

    /**
     * k页面统计
     * @param  integer $uid 用户ID
     * @return int
     */
    public function getSpecialCount($uid, $starttime, $endtime)
    {
        if(empty($uid))
        {
            return false;
        }
        $map['uid'] = $uid;
        $map['update_time'] = array('between',array($starttime, $endtime));
        $result = M('special')->where($map)->count();
        if($result){
            return $result;
        }else{
            return 0;
        }
    }

    /**
     * 厂商统计
     * @param  integer $uid 用户ID
     * @return int
     */
    public function getCompanyCount($uid, $starttime, $endtime)
    {
        if(empty($uid))
        {
            return false;
        }
        $map['uid'] = $uid;
        $map['create_time'] = array('between',array($starttime, $endtime));
        //厂商统计
        $result = M('company')->where($map)->count();
        if($result){
            return $result;
        }else{
            return 0;
        }
    }

    /**
     *
     * @param  integer $table 查询表名
     * @return int
     */
    public function getDateCompare($table,$uid)
    {
        if(empty($table))
        {
            return false;
        }
        $map['uid'] = $uid;
        //比较时间
        $result = M()->table($table)->field('create_time, update_time')->where($map)->select();
        foreach($result as $row){
            if($row['update_time']>$row['create_time']){
                return true;
            }else{
                return false;
            }
        }

    }

    /**
     * 获取分类信息
     * @param  array $title  标题
     * @param  boolean $field 查询字段
     * @return array
     */
    public function getCateID($title=array(), $field = true){
        if(!empty($title)){
            $map['status'] = 1;
            $res = M('package_category')->field($field)->where($map)->select();
            $data = array();
            foreach($res as $key=>$value){
                if( in_array($value['title'], $title) ){
                    $data['a1'][] = $value['id'];
                }else{
                    $data['a2'][] = $value['id'];
                }
            }
            return $data;
        }else{
            return false;
        }
    }

}
