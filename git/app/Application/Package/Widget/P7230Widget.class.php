<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Administrator
 * Date: 14-11-7
 * Time: 上午10:30
 * To change this template use File | Settings | File Templates.
 */

namespace Package\Widget;

use Package\Controller\BaseController;

class P7230Widget extends BaseController{

    /**
     * 礼包首页、列表页的左边 新手开测、礼包排行。
     */
    public function packageLeft(){
        $this->display('Widget/packageLeft');
    }

    /**
     * 礼包首页，列表页的 编辑精选推荐位
     */
    public function bjjx(){
        $this->display('Widget/bjjx');
    }

    /**
     * 礼包详情页  礼包排行
     */
    public function packageRank(){
        $this->display('Widget/packageRank');
    }

    /**
     * 作者:肖书成
     * 描述:礼包详情页排行 新规则
     */
    public function packageRankNew($id){
        //获取产品标签
        $productID = get_tags($id,'Package','product');
        if(!empty($productID)){
            $productID  = $productID[0]['id'];

            //获取下载数据
            $down       = M('ProductTagsMap')->alias('a')->field('b.id,b.title,b.category_id,b.smallimg,c.system')->join('__DOWN__ b ON a.did = b.id')->join('__DOWN_DMAIN__ c ON a.did = c.id')
                ->where("a.tid = $productID AND a.type = 'down' AND b.status = 1")->order('b.id DESC')->group('c.system')->select();

            if($down){
                $data = array();
                foreach($down as $k=>$v){
                    ($v['system'] == 1) && $data['adr'][] = $v;
                    ($v['system'] == 2) && $data['ios'][] = $v;
                }

                $tags       = get_tags($down[0]['id'],'Down');
                if($tags){
                    //获取标签数据
                    $tags   = implode(',',array_column($tags,'id'));
                    $limit  = empty($data['adr'])?10:9;

                    $adr    = M('TagsMap')->alias('a')->field('b.id,b.title,b.smallimg')->join('__DOWN__ b ON a.did = b.id')->join('__DOWN_DMAIN__ c ON a.did = c.id')->join('__PRODUCT_TAGS_MAP__ d ON a.did = d.did')
                        ->where("a.tid IN($tags) AND a.type = 'down' ".(empty($data['adr'])?'':'AND b.id !='.$data['adr'][0]['id'])." AND b.status = 1 AND c.system = 1 AND c.network = 2 AND d.type = 'down'")->order('b.update_time DESC')->group('d.tid')->limit($limit)->select();
                    $data['adr'] = array_filter(array_merge((array)$data['adr'],(array)$adr));

                    $limit  = empty($data['ios'])?10:9;
                    $ios    = M('TagsMap')->alias('a')->field('b.id,b.title,b.smallimg')->join('__DOWN__ b ON a.did = b.id')->join('__DOWN_DMAIN__ c ON a.did = c.id')->join('__PRODUCT_TAGS_MAP__ d ON a.did = d.did')
                        ->where("a.tid IN($tags) AND a.type = 'down' ".(empty($data['ios'])?'':'AND b.id !='.$data['ios'][0]['id'])." AND b.status = 1 AND c.system = 2 AND c.network = 2  AND d.type = 'down'")->order('b.update_time DESC')->group('d.tid')->limit($limit)->select();
                    $data['ios'] = array_filter(array_merge((array)$data['ios'],(array)$ios));

                    //如果标签数据不足10条用分类数据代替
                    $row = (int)(count($data['adr']));
                    if($row<10){
                        $ids = implode(',',array_column($data['adr'],'id'));
                        $adr = M('Down')->alias('a')->field('a.id,a.title,a.smallimg')->join('__DOWN_DMAIN__ b ON a.id = b.id')
                            ->where('a.id NOT IN('.$ids.') AND a.category_id = '.$down[0]['category_id'].' AND a.status = 1 AND b.system = 1 AND b.network = 2')->limit(10-$row)->select();
                        $data['adr'] = array_filter(array_merge((array)$data['adr'],(array)$adr));
                    }


                    $row = (int)(count($data['ios']));
                    if($row<10){
                        $ids = implode(',',array_column($data['ios'],'id'));
                        $ios = M('Down')->alias('a')->field('a.id,a.title,a.smallimg')->join('__DOWN_DMAIN__ b ON a.id = b.id')
                            ->where('a.id NOT IN('.$ids.') AND a.category_id = '.$down[0]['category_id'].' AND a.status = 1 AND b.system = 2 AND b.network = 2')->limit(10-$row)->select();
                        $data['ios'] = array_filter(array_merge((array)$data['ios'],(array)$ios));
                    }


                }else{
                    //如果数据不足10条用分类数据代替
                    $row = (int)(count($data['adr']));
                    if($row<10){
                        $ids = implode(',',array_column($data['adr'],'id'));
                        $adr = M('Down')->alias('a')->field('a.id,a.title,a.smallimg')->join('__DOWN_DMAIN__ b ON a.id = b.id')
                            ->where('a.id NOT IN('.$ids.') AND a.category_id = '.$down[0]['category_id'].' AND a.status = 1 AND b.system = 1 AND b.network = 2')->limit(10-$row)->select();
                        $data['adr'] = array_filter(array_merge((array)$data['adr'],(array)$adr));
                    }


                    $row = (int)(count($data['ios']));
                    if($row<10){
                        $ids = implode(',',array_column($data['ios'],'id'));
                        $ios = M('Down')->alias('a')->field('a.id,a.title,a.smallimg')->join('__DOWN_DMAIN__ b ON a.id = b.id')
                            ->where('a.id NOT IN('.$ids.') AND a.category_id = '.$down[0]['category_id'].' AND a.status = 1 AND b.system = 2 AND b.network = 2')->limit(10-$row)->select();
                        $data['ios'] = array_filter(array_merge((array)$data['ios'],(array)$ios));
                    }
                }
                $this->assign('data',$data);
            }
        }
        $this->display('Widget/packageRankNew');
    }

    /**
     * 礼包详情页的 安卓 IOS 攻略 官网 等附加信息
     * @param $id
     */
    public function official($id){

        //获取 安卓 IOS 官网 附加信息
        $append = array();
        $android = array();
        $ios = array();
        $downIds = get_base_by_tag($id,'Package','Down','product',false);
        foreach($downIds as $k=>$v){
            if($v['system']=='1'){
                $android[] = $v['id'];
            }
            if($v['system']=='2'){
                $ios[] = $v['id'];
            }
        }
        $append['androidDown'] = $android[0]?$android[0]:false; //安卓
        $append['IOSDown'] = $ios[0]?$ios[0]:false; //IOS
        $append['author_url']  = $downIds[0]['author_url']; //官网
        if($append['author_url'] && substr($append['author_url'],0,7)!='http://'){
            $append['author_url'] = 'http://'.$append['author_url'];
        }


        //获取攻略
        $documentIds = get_base_by_tag($id,'Package','document','product',false);
        $strategy = array();
        $strategyId = array('89','92','95');
        foreach($documentIds as $k=>$v){
                if(in_array($v['category_id'],$strategyId)){
                    $strategy[] = $v['category_id'];
                }
        }
        $append['strategy']=$strategy[0]?$strategy[0]:false; //攻略

        //选择模板
        $this->assign('append',$append);
        $this->display('Widget/official');
    }

    /**
     * 获取礼包详情页的相关礼包
     * @param $id
     */
    public function otherPackage($id){
        //获取产品标签
        $tid = get_tags($id,'Package','product');
        if(is_array($tid)){
            $tid = $tid[0]['id'];
        }

        //根据产品标签查找相关礼包
        $package = array();
        if(!empty($tid)){
            $packageId = M('productTagsMap')->where('tid = "'.$tid.'" AND type = "package"' )->getField('did',true);
            unset($packageId[array_search($id,$packageId)]);
            if(!empty($packageId)){
                $packageId = implode(',',$packageId);
                $package = M('package')->where("id IN($packageId) AND id != $id AND category_id IN(1,2,4)")->select();
            }
        }
        //选择模板
        $this->assign("empty",'<li style="display: block; color: #666; margin-top:30px;  text-align: center; line-height: 30px; height: 30px; width: 840px">暂无其他相关礼包！</li>');
        $this->assign('package',$package?$package:false);
        $this->display('Widget/otherPackage');
    }


    //给礼包详情页用，传入下载id，提供下载数据
//    private function get_Down($id){
//        //下载
//        $address = M('downAddress')->where('did = '.$id)->find();
//        if(empty($address)){
//            return false;
//        }
//        if(substr($address['url'],0,1)=='/'){
//            $address['url']=substr($address['url'],1);
//        }
//        $down = M('presetSite')->find($address['site_id'])['site_url'];
//        if(substr($down,0,7)!="http://" && substr($down,0,8)!="https://"){
//            $down = "http://".$down;
//        }
//        if(substr($down,-1,1)!='/'){
//            $down = $down.'/';
//        }
//        return $down;
//    }


    /**
     * 推荐编辑精选列表页
     *@return void
     */
    public function bjjxLists(){
        //list(列表)   Page（分页）
        $this->listPage('a.position & 1','27');
        $this->assign('info',array('title'=>'编辑精选'));

        //模板选择
        $this->display('Category/index');
    }

    /**
     * 礼包类型排行列表页
     */
    public function packageLists(){
        $where = I('condition');
        $seo = I('seo');

        //list(列表)   Page（分页）
        $this->listPage('a.category_id = 1 AND p.conditions & '.$where,$seo);
        $this->assign('info',array('title'=>I('title')?I('title'):''));

        //模板选择
        $this->display('Category/index');
    }


    /**
     * 分页列表数据
     */
    function listPage($wheres,$seo="0",$order='a.id DESC'){
        //根据传入static_page表ID查找数据
        $page_id = I('page_id');
        $page_info = get_staticpage($page_id);

        //分页获取数据
        $row = 15;
        $count  = M('package')->alias('a')->join('__PACKAGE_PMAIN__ p ON a.id = p.id')->where('status = 1 AND pid = 0 AND '.$wheres)->order($order)->count();// 查询满足要求的总记录数

        //是否返回总页数
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

        $lists = M('package')->alias('a')->join('__PACKAGE_PMAIN__ p ON a.id = p.id')->where('status = 1 AND pid = 0 AND '.$wheres)->order($order)->page($p, $row)->select();
        $this->assign('lists',$lists);// 赋值数据集

        $path = $page_info['path'] . getStaticExt();

        $Page = new \Think\Page($count, $row, '', false, $path);// 实例化分页类 指定路径规则
        $Page->setConfig('prev','上一页');
        $Page->setConfig('next','下一页');
        $Page->setConfig('first','首页');
        $Page->setConfig('last','尾页');
        $show       = $Page->show();// 分页显示输出
        $this->assign('page',$show);// 赋值分页输出

        //SEO
        $this->assign("SEO",WidgetSEO(array('special',null,$seo)));
    }

    /***
     * 开服开测表
     * @return bool
     */
    //开测开服表
    public function testSever(){
        $cate = I('cate');
        $cates = array('3','5');
        if(!in_array($cate,$cates)){
            return false;
        }

        //调取开服开测列表数据
        $listPackage = $this->listServer($cate,20);

        //安卓列表
        $adrPackage  = $this->listServer($cate,20,'a.id DESC','c.conditions & 1');
        //IOS列表
        $iosPackage  = $this->listServer($cate,20,'a.id DESC','c.conditions & 2');

        $this->assign(array(
            'empty'=>'<li style="display: block; color: #666; padding: 20px 0px; text-align: center; line-height: 30px; height: 30px; width: 1200px">暂无数据！</li>',
            'list'=>$listPackage,
            'android'=>$adrPackage,
            'IOS'=>$iosPackage,
            'cate'=>$cate,
        ));

        //SEO
        $seo = I('SEO');
        $this->assign("SEO",WidgetSEO(array('special',null,$seo)));

        //模板选择
        $info = M('packageCategory')->where('id = '.$cate)->find();//分类详细信息
        $this->assign('info',$info);
        $temp = $info['template_index']
            ? $info['template_index']
            : ($info['template_lists'] ? $info['template_lists'] : 'testSever');
        $this->display('Category/'.$temp);
    }

    /***
     * 开测开服排行数据
     */
    public function testServerRank(){
        $test   = $this->listServer(5,5,'a.abet DESC,a.create_time DESC');
        $server = $this->listServer(3,5,'a.abet DESC,a.create_time DESC');
        $this->assign(array(
            'test'=>$test,
            'server'=>$server
        ));
        $this->display('Widget/testServerRank');
    }

    //此方法仅供 开测开服表 内部调用
    private function listServer($cate,$limit="",$order = "a.id DESC",$where = 1){
        $listPackage = M('package')->alias('a')->field('a.id,a.title,a.cover_id,b.tid,c.*')->join('__PRODUCT_TAGS_MAP__ b ON a.id = b.did')->join('__PACKAGE_PARTICLE__ c ON a.id = c.id')->where('b.type = "package" AND a.category_id = '.$cate.' AND '.$where)->limit($limit)->order($order)->select();

        foreach($listPackage as $k=>&$v){
            if(!empty($v['tid'])){
                $down =  M('Down')->alias('a')->field('a.id game_id,a.title game_title,b.system,b.picture_score,b.music_score,b.feature_score,b.run_score,c.name company')->join('__DOWN_DMAIN__ b ON a.id = b.id','LEFT')->join('__COMPANY__ c ON b.company_id = c.id','LEFT')->join('__PRODUCT_TAGS_MAP__ d ON a.id = d.did','LEFT')->where('d.tid = '.$v['tid'].' AND d.type = "down"')->select();
                if(!empty($down)){
                    foreach($down as $k1=>&$v1){
                        $v1['score'] = round(($v1['picture_score'] + $v1['music_score'] + $v1['feature_score'] + $v1['run_score'])*5/10,1);
                        if($v1['system']=="1" && empty($v['androidId'])){
                            $v['androidId']= $v1['game_id'];
                        }

                        if($v1['system']=="2" && empty($v['IOS'])){
                            $v['IOS'] = $v1['game_id'];
                        }
                    }
                    unset($v1);
                    $v = array_merge($v,$down[0]);
                };

                //根据产品标签查找最新的礼包
                $package = M('package')->alias('a')->field('a.id')->join('__PRODUCT_TAGS_MAP__ b ON a.id = b.did')->where('a.category_id IN(1,2,4) AND b.tid = '.$v['tid'].' AND b.type = "package"')->order('a.create_time DESC')->select();
                $v['packageId'] = $package[0]['id'];
            }
        }
        unset($v);
        return $listPackage;
    }
    /***
     * 开测开服列表
     */
    public function testServerList(){
        $test   = $this->listServer(5,10,'a.create_time DESC');
        $server = $this->listServer(3,10,'a.create_time DESC');
        $this->assign(array(
            'test'=>$test,
            'server'=>$server
        ));
        $this->display(T('Package@7230/Widget/testserverlist'));
    }

}