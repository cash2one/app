<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/4/16
 * Time: 16:57
 */

namespace Down\Widget;
use Common\Controller\WidgetController;

class dongdongWidget extends WidgetController{

    /**  网游、单机、软件汇集首页  **/
    public function networkGame(){
        //接收参数
        $topCate =  I('cate');
        $seo     =  I('seo');
        if(!is_numeric($seo));
        if(!in_array($topCate,array(1,12,116))) return false;


        //子分类分类
        $cate   =   M('DownCategory')->field('id,title')->where('pid = '.$topCate.' AND status = 1 AND display = 1')->order('sort ASC,id ASC')->select();
        $cates  =   implode(',',array_column($cate,'id'));


        //推荐
        $recommend  = $this->getdown('category_id IN ('.$cates.') AND position & 8','update_time DESC',22);

        //热门
        $hot        = $this->getdown('category_id IN ('.$cates.') AND position & 16','update_time DESC',22);

        //最新
        $new        = $this->getdown('category_id IN ('.$cates.')','id DESC',12);


        //分类框
        foreach($cate as $k=>$v){
            $cate[$k]['data'] = M('Down')->field('id,title,smallimg')->where('status = 1 AND category_id = '.$v['id'])->limit('10')->order('id DESC')->select();
            $cate[$k]['sort'] = $k;
            if(empty($cate[$k]['data'])){
                unset($cate[$k]);
            }
        }
        sort($cate,'sort');

        $this->assign(array(
            'recommend'     =>      $recommend, //推荐
            'hot'           =>      $hot,       //热门
            'new'           =>      $new,       //最新游戏
            'topCate'       =>      $topCate,   //顶级分类
            'cates'         =>      $cates,     //全部分类
            'cate'          =>      $cate,      //网游总分类
            'SEO'           =>      WidgetSEO(array('special',null,$seo))//seo
        ));
        $this->display(T('Down@dongdong/Category/networkGame'));
    }


    //频道幻灯片区域
    public function todaySlide($cate,$arr = ''){
        $cate = $cate?$cate:I('cate');
        $order = array('index'=>'abet DESC','all'=>'id DESC','1'=>'update_time DESC','116'=>'update_time DESC');
        $where = array('index'=>'1','all'=>'1','1'=>'category_id IN ('.$arr.')','116'=>'category_id IN ('.$arr.')');
        $Swhere= array('index'=>'position & 1','all'=>'position & 512','1'=>'position & 1024 AND category_id IN ('.$arr.')','116'=>'position & 2048 AND category_id IN ('.$arr.')');

        //新游戏(今日推荐，现在是用每日推荐来推荐)
        $today      = $this->getdown('position & 128 AND '.$where[$cate],$order[$cate],3);

        //避免幻灯片和新游戏数据重复
        $ids        = implode(',',array_column($today,'id'));
        if(empty($ids)) $ids = 1;

        //首页头条(幻灯片)
        $slide      = $this->getdown('id NOT IN('.$ids.') AND '.$Swhere[$cate],'update_time DESC',4,'description');

        $this->assign(array(
            'today' =>  $today,
            'slide' =>  $slide,
        ));

        $this->display(T('Down@dongdong/Widget/todaySlide'));
    }

    //游戏详情页，下面的相关、最新、最热游戏
    public function detailGame($id,$cate){
        //同类游戏
        $cateGame = $this->getRankDown(false,'id !='.$id.' AND category_id = '.$cate,8);
        if(count($cateGame)<8) $cateGame = $this->appendDownData(false,'id !='.$id,$cateGame,8);

        //最近更新
        $newGame    = $this->getdown('id !='.$id.' AND category_id = '.$cate);
        if(count($newGame)<8) $newGame = $this->appendDownData(false,'id !='.$id,$newGame,8);

        //热门游戏
        $hotGame    = $this->getdown('id !='.$id.' AND category_rootid != 12','abet DESC');

        //热门软件
        $hotSoft    = $this->getdown('id !='.$id.' AND category_rootid = 12','abet DESC');

        //模板赋值
        $this->assign(array(
            'cateGame'    =>  $cateGame,
            'newGame'       =>  $newGame,
            'hotGame'       =>  $hotGame,
            'hotSoft'       =>  $hotSoft,
            'cate'          =>  $cate,
        ));

        //模板选择
        $this->display('Widget/detailGame');
    }

    //游戏详情页的右边相关推荐
    public function detailRight($id,$tags){
        $game = $this->relateGame($id,array_column($tags,'id'),10,'id DESC','id ASC','view,hits_today,hits_week,hits_month');
        foreach($game as $k=>&$v){
            $v['hits'] = ($v['view']>20)?$v['view']:(10+rand(1,20));
        }unset($v);

        $this->assign('game',$game);
        $this->display('Widget/detailRight');
    }

    //获取下载游戏
    private function getdown($where = 1,$order = 'id DESC',$row = 8,$field=false){
        return M('down')->field('id,title,cover_id,smallimg'.($field?','.$field:''))->where('status = 1 AND '.$where)->order($order)->limit($row)->select();
    }

    public function cateTree(){
        //查找所有的父分类
        $tree    =   M('DownCategory')->field('id,title')->where('status = 1 AND display = 1 AND pid = 0')->order('sort ASC,id ASC')->select();
        if(empty($tree)){ return;}

        //根据父分类查找子分类，并处理掉没有下载数据的子分类
        foreach($tree as $k=>$v){
            $lists = M('DownCategory')->field('id,title,pid')->where('status = 1 AND display = 1 AND pid = '.$v['id'])->order('sort ASC,id ASC')->select();
            foreach($lists as $k1=>$v1){
                $count = M('Down')->where('status = 1 AND category_id = '.$v1['id'])->count();
                if($count<1){
                    unset($lists[$k1]);
                }
            }

            $tree[$k]['child'] = $lists;
        }

        //页面赋值
        $this->assign('tree',$tree);

        //模板选择
        $this->display('Widget/cateTree');
    }


    /**最新首发**/
    public function start(){
        //根据传入static_page表ID查找数据
        $page_id    = I('page_id');
        $page_info  = get_staticpage($page_id);
        $cate       = I('cate');
        $seo        = I('seo');
        $row        = I('row')?I('row'):54;


        //条件
        $where = array('all'=>1,'game'=>'category_rootid != 12','soft'=>'category_rootid = 12');
        if(empty($where[$cate])) return false;

        //分页获取数据
        $count  = M('Down')->where('status = 1 AND '.$where[$cate])->count();// 查询满足要求的总记录数



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

        $lists = M('Down')->alias('a')->field('a.id,a.title,a.smallimg,b.title cate')->join('__DOWN_CATEGORY__ b ON a.category_id = b.id')->where('a.status = 1 AND '.$where[$cate])->order('a.id DESC')->page($p, $row)->select();
        $this->assign('lists',$lists);// 赋值数据集

        //相关推荐
        $relate = $this->getRankDown('view',$where[$cate],10);

        foreach($relate as $k=>&$v){
            $v['hits'] = ($v['view']>20)?$v['view']:(10+rand(1,20));
        }unset($v);
        $this->assign('relate',$relate);

        //分类
        $this->assign('cate'  ,$cate);
        $cateName = array('all'=>'最新首发','game'=>'游戏首发','soft'=>'软件首发');
        $this->assign('cateName',$cateName[$cate]);

        $path = $page_info['path'] . getStaticExt();

        $Page = new \Think\Page($count, $row, '', false, $path);// 实例化分页类 指定路径规则
        $Page->firstPath = getWidgetPath($seo);
        $Page->setConfig('prev','上一页');
        $Page->setConfig('next','下一页');
        $Page->setConfig('first','首页');
        $Page->setConfig('last','尾页');

        $show       = $Page->show();// 分页显示输出
        $this->assign('page',$show);// 赋值分页输出

        //SEO
        $this->assign("SEO",WidgetSEO(array('special',null,$seo)));

        //模板选择
        $this->display('Widget/start');
    }

    //导航二级菜单栏
    public function childMenu($cate){
        if(!in_array($cate,array(1,12,116))){ return false;}
        $lists = M('DownCategory')->field('id,title')->where('pid = '.$cate)->select();
        foreach($lists as $k=>$v){
            if(M('Down')->where('status = 1 AND category_id = '.$v['id'])->count()<1){
                unset($lists[$k]);
            };
        }

        $this->assign('lists',$lists);
        $this->display(T('Down@dongdong/Widget/childMenu'));
    }

    //全站的首尾部
    public function inclusive(){
        $cate = I('cate');
        if(!in_array($cate,array('header','foot'))) return false;
        if($cate == 'foot'){
            $page_id = (int)I('page_id');
            $page_info = get_staticpage($page_id);

            if(!empty($page_info['description'])){
                $data = array_filter(explode('|',str_replace(PHP_EOL,'|',$page_info['description'])));
                $this->assign('data',$data);
            }
        }
        $this->display(T('Home@dongdong/Public/'.$cate));
    }
	
	
	
	//东东详情面包屑0
	public function dongdongCrumb($id){
		  $info = M("Down")->alias("__DOWN")->where("__DOWN.id='$id'")->join("INNER JOIN __DOWN_CATEGORY__ ON __DOWN.category_id = __DOWN_CATEGORY__.id")->field("*")->find();
		  echo "<a href=\"".C('STATIC_URL')."\">首页</a> > <a href=\"".staticUrl('lists',$info['category_id'], 'Down')."\">".$info['title']."</a> >";
	}
	
	
	
	
	
	
	
	
}