<?php

namespace Down\Widget;

use Down\Controller\BaseController;

class P7230mobileWidget extends BaseController{
    //下载详情页 专区、礼包、攻略
    public function DownDetailPackage($id){
        $tid = M('productTagsMap')->where('type = "down" AND did = '.$id)->getField('tid');
        $result = array();
        if($tid){
            $result['batch'] = M('productTagsMap')->where('type = "batch" AND tid = '.$tid)->getField('did');
            $result['package'] = M('productTagsMap')->alias('a')->join('__PACKAGE__ b ON a.did = b.id')->where('a.type = "package" AND a.tid = '.$tid.' AND b.category_id IN(1,2,4)')->getField('did');
            $result['document'] = M('document')->alias('a')->join('__PRODUCT_TAGS_MAP__ b ON a.id = b.did')->where('a.category_id IN(95,92,89) AND b.type = "document" AND b.tid = '.$tid)->getField('did');
        }
        $this->assign('result',$result);
        $this->display('Widget/DownDetailPackage');
    }


    /**
     * 排行页
     * @return void
     */
    public function top(){
        //获取标签
        $tags = M('Tags')->where('category=1 AND status=1')->field('title,id,name')->select();
        foreach ($tags as &$value) {
            $value['num_count'] = M('TagsMap')->where('tid='. $value['id'] .' AND type="down"')->count();
            $value['num_count'] = empty($value['num_count']) ? 0 : $value['num_count'];
        }
        $this->assign('tags', $tags);
        //获取分类
        $categorys = M('DownCategory')->where('rootid=1 AND status=1 AND id!=1')->field('title,id,name')->select();
        foreach ($categorys as &$value) {
            $value['num_count'] = M('Down')->where('category_id='. $value['id'] .' AND status=1')->count();
            $value['num_count'] = empty($value['num_count']) ? 0 : $value['num_count'];
        }
        $this->assign('categorys', $categorys);
        //模板
        $this->assign("SEO",WidgetSEO(array('index')));
        $this->display('Top/index');       
    }

    /***************************************第二版详情页********************************************/
    /**
     * 作者:肖书成
     * 描述:下载详情页 父级页面
     * @param $info array 详细信息
     * @param $SEO  array SEO
     */
    public function Pindex($info,$SEO){
        $this->assign(array(
            'info'  => $info,
            'SEO'   => $SEO,
            'url'   =>  C('MOBILE_STATIC_URL')
            ));

        $this->display('Detail/Pindex');
    }

    /**
     * 作者:肖书成
     * 描述:下载详情页 子级页面
     * @param $info array 详细信息
     * @param $SEO  array SEO
     */
    public function Cindex($info,$SEO){
        $this->assign(array(
            'info'  => $info,
            'SEO'   => $SEO,
            'url'   =>  C('MOBILE_STATIC_URL')
        ));
        $this->display('Detail/Cindex');
    }

    /**
     * 作者:肖书成
     * 描述:排行列表
     * @param $info
     */
    public function ph($info){
        $arr    =   explode(',',$info['soft_id']);

        $data   =   M('Down')->alias('a')->field('a.id,a.title,a.description,a.smallimg,a.view,b.size,c.title cate')->join('__DOWN_DMAIN__ b ON a.id = b.id')
                    ->join('__DOWN_CATEGORY__ c ON a.category_id = c.id')
                    ->where('a.id IN('.$info['soft_id'].') AND a.status = 1 AND a.pid = 0')->select();

        //根据填写ID来排序
        $lists   =   array();
        foreach($arr as $k=>$v){
            foreach($data as $k1=>$v1){
                if($v1['id'] === $v){
                    $lists[] =   $v1;
                }
            }
        }

        $this->assign('lists',$lists);
        $this->display('Widget/phList');
    }

    /**
     * 作者:肖书成
     * 描述:更多手游排行
     * @param $id
     */
    public function morePh($id){
        if(is_array($id)){
            $id =   implode(',',$id);
        }

        $lists  =   M('Down')->field('id,title')->where('category_id = 38 AND id NOT IN('.$id.') AND status = 1 AND pid = 0')->select();

        $this->assign('lists',$lists);
        $this->display(T('Down@7230mobile/Widget/morePh'));
    }

    /**
     * 作者:肖书成
     * 描述:下载详情页（第二版）的Widget 相关礼包、猜你喜欢、猜你喜欢玩、相关攻略资讯
     */
    public function downFoot($info){
        //猜你喜欢（标签数据 最新前8条）
        $tagDown    =   array();
        foreach($info['tags'] as $k=>$v){
            $tagDown[]  =   M('TagsMap')->alias('a')->field('b.id,b.title,b.smallimg,c.size')->join('__DOWN__ b ON a.did = b.id')
                            ->join('__DOWN_DMAIN__ c ON a.did = c.id')->join('__PRODUCT_TAGS_MAP__ d ON a.did = d.did')
                            ->where('a.tid = '.$v['id'].' AND b.status = 1 AND b.pid = 0 AND b.id != '.$info['id'] .' AND c.size != 0 AND d.type="down"')
                            ->order('b.update_time DESC')->group('d.tid')->limit(8)->select();
        }

        //猜你喜欢玩（分类数据）【添加了随机】
        $count      =   M('Down')->alias('a')->join('__DOWN_DMAIN__ b ON a.id = b.id')
                        ->where('a.category_id = '.$info['category_id'] .' AND a.id != '.$info['id'].'  AND a.status = 1 AND a.pid = 0 AND b.size !="0"')->count('a.id');

        if((int)$count<=4){
            $star   =   0;
        }else{
            $star   =   rand(0,(int)$count - 4);
        }
        $cateDown   =   M('Down')->alias('a')->field('a.id,a.title,a.smallimg,b.size')->join('__DOWN_DMAIN__ b ON a.id = b.id')
                        ->where('a.category_id = '.$info['category_id'] .' AND a.id != '.$info['id'].'  AND a.status = 1 AND a.pid = 0 AND b.size !="0"')
                        ->limit($star,4)->select();

        $this->assign(array(
            'tagDown'   =>  $tagDown,
            'cateDown'  =>  $cateDown,
            'info'      =>  $info,
        ));

        $this->display('Widget/downFoot');
    }

    /**
     * 作者肖书成
     * 描述:下载详情页的子下载。
     */
    public function childDown($pid,$id){
        $data = M('Down')->field('id,title')->where('pid = '.$pid.' AND status = 1 AND id !='.$id)->select();
        if(empty($data)){
            return ;
        }

        $this->assign('data',$data);
        $this->display('Widget/childDown');
    }


    /**
     * 作者:肖书成
     * 时间:2015/10/27
     * 描述:相关礼包（2条） 下载,文章,礼包
     */
    public function relatePackage($tid,$where = 1){
        $data           =   array();
        if(!empty($tid)){
            $data       =   $this->get_product_data($tid,'package','b.status = 1 AND b.category_id IN(1,2,4) AND '.$where,'c.content',2,'__PACKAGE_PMAIN__ c ON b.id = c.id');
            $num        =   count($data);
            if($num     ==  0){
                $data   =   M('Package')->alias('a')->field('a.id,a.title,b.content')->join('__PACKAGE_PMAIN__ b ON a.id = b.id')->where('a.status = 1 AND a.category_id IN(1,2,4) AND '.$where)->order('a.update_time DESC')->limit(2)->select();
            }elseif($num==  1){
                $nid    =   $data[0]['id'];
                $data1  =   M('Package')->alias('a')->field('a.id,a.title,b.content')->join('__PACKAGE_PMAIN__ b ON a.id = b.id')->where('a.status = 1 AND a.category_id IN(1,2,4) AND a.id !='.$nid .' AND '.$where)->order('a.update_time DESC')->limit(1)->select();
                $data   =   array_merge($data,$data1);
            }
        }else{
            $data   =   M('Package')->alias('a')->field('a.id,a.title,b.content')->join('__PACKAGE_PMAIN__ b ON a.id = b.id')->where('a.status = 1 AND a.category_id IN(1,2,4) AND '.$where)->order('a.update_time DESC')->limit(2)->select();
        }

        $this->assign('data',$data);
        $this->display(T('Down@7230mobile/Widget/relatePackage'));
    }

    /**
     * 作者:肖书成
     * 时间:2015/10/27
     * 描述:相关攻略资讯(5条)
     *
     */
    public function relateArticle($tid,$where = 1){
        $data           =   array();
        if(!empty($tid)){
            $data       =   $this->get_product_data($tid,'document','b.status = 1 AND b.category_id IN(87,89,90,92,94,95) AND b.pid = 0 AND '.$where,'b.update_time',5);
            $num        =   count($data);

            if($num     ==  0){
                $data   =   $this->get_product_data($tid,'document','b.status = 1 AND b.pid = 0 AND '.$where,'b.update_time',5);
            }elseif($num<5){
                $nid    =   implode(',',array_column($data,'id'));
                $data1  =   M('Document')->alias('b')->field('id,title,update_time')->where('status = 1 AND category_id IN(87,89,90,92,94,95) AND pid = 0 AND id NOT IN('.$nid .') AND '.$where)->order('update_time DESC')->limit(5-$num)->select();
                $data   =   array_merge($data,$data1);
            }
        }
        $num            =   count($data);
        if($num<5){
            $data       =   M('Document')->alias('b')->field('id,title,update_time')->where('b.status = 1 AND b.category_id IN(87,89,90,92,94,95) AND b.pid = 0 AND '.$where)->order('update_time DESC')->limit(5-$num)->select();
        }


        $this->assign('data',$data);
        $this->display(T('Down@7230mobile/Widget/relateArticle'));
    }

    /**
     * 作者:肖书成
     * 时间:2015/10/27
     * 描述:根据(产品)标签获取数据的方法(秒杀90%以上根据(产品)标签获取的数据)
     * @param int $tid
     * @param string $type
     * @param int $where
     * @param string $field
     * @param int $row
     * @param bool $join
     * @param string $table
     * @param string $order
     * @return array $list
     */
    private function get_product_data($tid,$type,$where = 1,$field='b.create_time',$row = 8,$join = false,$table = 'ProductTagsMap',$order = 'b.update_time DESC'){
        if(empty($tid)) return false;
        $b = '__'.strtoupper($type).'__';

        $field = $field?'b.id,b.title,'.$field:'max(b.id) max,min(b.id) min,count(b.id) count';

        $model = M($table)->alias('a')->field($field)->join("$b b ON a.did = b.id");

        if($join) $model->join($join);

        $tWhere = is_numeric($tid)?"a.tid = $tid":"a.tid IN($tid)";

        $list = $model->where("$tWhere AND a.type = '$type' AND b.status = 1 AND $where")->order($order)->group('b.id')->limit($row)->select();

        return $list;
    }
}