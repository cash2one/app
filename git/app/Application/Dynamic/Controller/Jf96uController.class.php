<?php
// +----------------------------------------------------------------------
// | 描述:96u手游网动态数据调用控制器
// +----------------------------------------------------------------------
// | Author: 谭坚 <tanjian691406429@163.com>
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------
// | Date: 15-11-10 下午4:58    Version:1.0.0 
// +----------------------------------------------------------------------


namespace Dynamic\Controller;



class Jf96uController extends BaseController{


    /**
     * 描述：提交评论动态接口
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function API_commentSub(){
        $this->API_init();
        $callback = I('callback');
        $id = I('id');
        $model = I('model');
        if(!is_numeric($id) || empty($model)) return;
        $m = M('Comment');
        $data['message'] = strip_tags(I('message'));
        $data['document_id'] = intval($id);
        $data['type'] = strip_tags($model);
        $data['uname'] = strip_tags(I('uname'));
        $data['add_time'] = time();
        $data['enable'] = C('COMMENT_AUDIT') ? C('COMMENT_AUDIT') : 0;
        $rs = $m->add($data);
        echo $callback ? $callback.'('.json_encode($rs).');' : json_encode($rs);
    }

    /**
     * 描述：获取评论列表
     * @param $id
     * @param $model
     * @param string $p
     * @param string $row
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function API_comment($id, $model,$p='1',$row='10'){
        $this->API_init();
        $callback = I('callback');
        if(!is_numeric($id) || empty($model)) return;
        $start = ($p-1)*$row;
        $m = M('Comment');
        $map =array();
        $map['document_id'] = intval($id);
        $map['enabled'] = 1;
        $map['type'] = strip_tags($model);
        $rs = $m->where($map)->field('id,uname,message,add_time')->order('add_time desc')->limit($start,$row)->select();
        echo $callback ? $callback.'('.json_encode($rs).');' : json_encode($rs);
    }


    /**
     * 作者:肖书成
     * 描述:标签页获取数据（专门为 手机专题、专区而写的）
     * 时间:2015-12-14
     */
    public function API_tags(){
        //接受参数
        $key        =       I('key');
        $star       =       I('star');


        //验证参数
        if(!is_numeric($key) || !is_numeric($star) || (int)$key<=0 || (int)$star<0){
            $this->API_false();
        }


        //查找标签数据
        $data   =   M('ProductTagsMap')->alias('a')->field('b.id,b.title,b.description,b.smallimg,b.model_id,b.category_id,b.update_time')->join('__DOWN__ b ON a.did = b.id')
            ->where("a.tid = ".$key." AND a.type = 'down'")->order('b.id DESC')->limit($star,12)->select();
        if(empty($data)){
            $this->API_result(null);exit;
        }

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
        $cate   =   S('96uDownCate');
        if(!$cate){
            $cate = M('DownCategory')->where('status = 1')->getField('id,title');
            if($cate){
                S('96uDownCate',serialize($cate),3600);
            }
        }else{
            $cate   =   unserialize($cate);
        }


        //合并附属模型的数据
        $data   =   array();
        foreach($arr as $k=>$v){
            foreach($v as $k1=>$v1){
                if($arr_data[$k][$v1['id']]){
                    $v1['cate'] =   $cate[$v1['category_id']];
                    $v1['url']  =   staticUrl('detail',$v1['id'],'Down');
                    $v1['img']  =   get_cover($v1['smallimg'],'path');
                    $v1['update_time']  =   date('Y-m-d',$v1['update_time']);
                    $arr_data[$k][$v1['id']]['size']    =   format_size($arr_data[$k][$v1['id']]['size']);
                    $arr_data[$k][$v1['id']]['version'] =   $arr_data[$k][$v1['id']]['version']?$arr_data[$k][$v1['id']]['version']:'1.0';
                    $data[] =   array_merge($v1,$arr_data[$k][$v1['id']]);
                }
            }
        }

        //结果处理
        $this->API_result($data);
    }


    /**
     * 作者:ganweili
     * 时间:2016/1/13
     * 描述:点击统计
     */
    public  function  hits (){
        C('SHOW_PAGE_TRACE', false);
        /*
        header('Access-Control-Allow-Origin:http://m.96u.com');
        header('Access-Control-Allow-Origin:http://www.96u.com');
        header('Access-Control-Allow-Origin:http://dynamic.96u.com');
        */
        header('Access-Control-Allow-Origin:*');
        $id=I('id');
        $uid=I('uid');
        $type=I('type');
        if($type=='Down' or  $type=='Document'  ){
            $ud=M($type)->where(" id='$id' and uid = '$uid'")->setInc('view',1);
            echo $ud;
        }else{
            echo "参数不对";
        }


    }
} 