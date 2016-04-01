<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Widget;
use Think\Controller;

/**
 * 页面widget
 * 
 */

class P7230Widget extends Controller{

    public function everydayRecommend(){
        $this->display(T('Home@7230/Widget/Recommended'));
            // $this->display('Home@7230/Widget/Recommended');
    }


      /**
      * SEO
      * @param  string $type 类型
      * @param  string $module 模块名
      * @param  integer $cid or $id
      *@return array
      */
    public function SEO($type, $module = null, $id = null){
        $seo = array();
        switch ($type) {
            case 'index':
                $seo['title'] =C('WEB_SITE_TITLE');
                $seo['keywords'] = C('WEB_SITE_KEYWORD');
                $seo['description'] =C('WEB_SITE_DESCRIPTION');
                return $seo;
                break;
            case 'moduleindex':
                if(empty($module)) return;
                $module = strtoupper($module);
                $seo['title'] =C(''.$module.'_DEFAULT_TITLE');
                $seo['keywords'] = C(''.$module.'_DEFAULT_KEY');
                $seo['description'] =C(''.$module.'_DEFAULT_DESCRIPTION');
                return $seo;
                break;
	case 'special':
                $id=empty($id)?'1':$id;
	   $cate=D('StaticPage')->where("id='$id'")->find();
                $seo['title'] =empty($cate['title'])?$cate['title']:$cate['title'];
                $seo['keywords'] = empty($cate['keywords'])?'':$cate['keywords'];
                $seo['description'] =empty($cate['description'])?'':$cate['description'];
                return $seo;
                break;
            case 'category':
                $id=empty($id)?'1':$id;
                if(empty($module)) return;
                $module = strtoupper($module);
                $cate_name = array(
                    'DOCUMENT' => 'Category',
                    'PACKAGE' => 'PackageCategory',
                    'DOWN' => 'DownCategory'
                );
                $cate=D($cate_name[$module])->where("id='$id'")->find();
                $seo['title'] =empty($cate['meta_title'])?$cate['title']:$cate['meta_title'];
                $seo['keywords'] = empty($cate['keywords'])?C(''.$module.'_DEFAULT_KEY'):$cate['keywords'];
                $seo['description'] =empty($cate['description'])?C(''.$module.'_DEFAULT_KEY'):$cate['description'];
                return $seo;
                break;
            case 'detail':
                $id=empty($id)?'1':$id;
                if(empty($module)) return;
                $module = ucfirst(strtolower($module));                
                $detail = D($module)->detail($id);

                //标题
                if($module=='Down'){
                    //下载的规则 
                    //1、seotitle+版本号
                    //2、副标题|主标题+下载+版本号
                    if(!empty($detail['seo_title'])){
                        $title = $detail['seo_title'] . $detail['version']; 
                    }else{
                        $title = $detail['sub_title'] .'|'. $detail['title'] . '下载' . $detail['version']; 
                    }
                }else{
                    //其他的规则 
                    //1、seo title
                    //2、主标题
                    if(!empty($detail['seo_title'])){
                        $title = $detail['seo_title']; 
                    }else{
                        $title = $detail['title']; 
                    }
                }
                //标题需要加前后缀
                if(C('SEO_STRING')){
                    $t = array();                
                    $t[abs((int)C('SEO_PRE_SUF') - 1)] = $title; 
                    $t[(int)C('SEO_PRE_SUF')] = C('SEO_STRING');
                    ksort($t);
                    $seo['title'] = implode(' - ', $t);                      
                }else{
                    $seo['title'] = $title; 
                }

                //关键词
                if(empty($detail['seo_keywords'])){
                    if($module=='Document'){
                        //文章 标签
                        $tag_ids = M('TagsMap')->where('did='.$id.' AND type="document"')->getField('tid', true);
                        if(empty($tag_ids)){
                            $seo['keywords'] = $detail['title']; 
                        }else{
                            $tags = M('Tags')->where(array('id'=>array('in', $tag_ids)))->getField('title', true);
                            $seo['keywords'] = empty($tags) ? $detail['title'] : implode(',', $tags);                            
                        }
                    }else{
                        //其他 主标题+副标题
                        $seo['keywords'] = empty($detail['sub_title']) 
                                                        ? $detail['title'] 
                                                        : $detail['title'] . ',' . $detail['sub_title'];
                    }
                }else{
                    $seo['keywords'] = $detail['seo_keywords'];
                }

                //描述分模块处理
                if(empty($detail['seo_description'])){
                    $des = array('Document'=>'description','Down'=>'conductor','Package'=>'content');
                    if(empty($detail[$des[$module]])){
                        $seo['description'] = substr(str_replace('"', '', str_replace(PHP_EOL, '', strip_tags($detail['content']))),0,500);
                    }else{
                        $seo['description'] = str_replace('"', '', str_replace(PHP_EOL, '', strip_tags($detail[$des[$module]])));
                    }
                }else{
                    $seo['description'] = strip_tags($detail['seo_description']);
                }

                return $seo;
                break;
        }
    }


    /**
    * content字段图片和内置插入标签处理
    * @param string $content 内容
    * @return string 处理过后的内容
    */
    function contentProcess($content){
        //图片暂时兼容处理
      $content = preg_replace('/src\=\"(\/up\/.+?)/i', 'src="'. C('PIC_HOST') .'$1', $content);
      $content = preg_replace('/src\=\"(up\/.+?)/i', 'src="'. C('PIC_HOST') .'/$1', $content);
      $content = preg_replace('/src\=\"(\/Uploads\/.+?)/i', 'src="'. C('PIC_HOST') .'$1', $content);
      $content = preg_replace('/src\=\"(Uploads\/.+?)/i', 'src="'. C('PIC_HOST') .'/$1', $content);
      $content = preg_replace('/src\=\"http:\/\/(www.)??7230.com\/(up\/.+?)/i', 'src="'. C('PIC_HOST') .'/$2', $content);
        //内置标签处理
      echo $content;
  }
  
     /**
    * content Tag链接地址处理
    * @param string $name name
    * @return string 返回地址
    */
 function tagsUrl($name){
	echo "/tag/".$name;
		
	}
    /**
     * 生成头部
    * @param string $content 内容
    * @return string 处理过后的内容
    */
    function head(){
      $this->display(T('Home@7230/Public/header'));
  }
   /**
    * 生成友情链接
    * @param string $content 内容
    * @return string 处理过后的内容
    */
    function footIndex(){
    $links=D('Link')->where("status=1 AND `group`=1")->order('sort desc')->limit('0,500')->select();
	 $this->assign("flink",$links);
     $this->display(T('Home@7230/Public/footerIndex'));
    }

    /**
    * 生成尾部
    * @param string $content 内容
    * @return string 处理过后的内容
    */
    function foot(){
      $this->display(T('Home@7230/Public/footer'));
    }

    /**
     * 排行页
     */
    public function rank(){

      $yearDownList=M('Down')->alias('a')->field('a.id,a.smallimg,a.title,a.abet')->join('__DOWN_ADDRESS__ b ON a.id = b.did ')->limit(30)->order('hits desc')->select();
      $this->assign('lists',$yearDownList);

      //SEO
      $this->assign("SEO",WidgetSEO(array('special',null,'9')));

      $this->display('Widget/phpd');
  }
	  /**
     * 媒体库
     */
   public function media(){
	$this->assign("SEO",WidgetSEO(array('special',null,'10')));
    $this->display('Widget/media');
}
	  /**
     * Banner
     */
   public function banner(){
	 $arrH=array();//首页横图
	 $arrW=array();//首页竖图
	 $docH= M('Document')->field('id,title,description,cover_id,level,update_time')->where("home_position='1' OR home_position='3'")->order('update_time desc')->limit(4)->select();
	 foreach ($docH as $k => $v) {$docH[$k]['type'] = 'document';}
     $docW= M('Document')->field('id,title,description,vertical_pic,level,update_time')->where("home_position='2' OR home_position='3'")->order('update_time desc')->limit(2)->select();
	 foreach ($docW as $k => $v) {$docW[$k]['type'] = 'document';}
	 
	 
	 $downH= M('Down')->field('id,title,description,cover_id,level,update_time')->where("home_position='1' OR home_position='3'")->order('update_time desc')->limit(4)->select();
	 foreach ($downH as $k => $v) {$downH[$k]['type'] = 'down';}
	 
	 $downW= M('Down')->field('id,title,description,vertical_pic,level,update_time')->where("home_position='2' OR home_position='3'")->order('update_time desc')->limit(2)->select();
	 foreach ($downW as $k => $v) {$downW[$k]['type'] = 'down';}
	   
	 $packageH= M('Package')->field('id,title,description,cover_id,level,update_time')->where("home_position='1' OR home_position='3'")->order('update_time desc')->limit(4)->select();
	 foreach ($packageH as $k => $v) {
         $packageH[$k]['type'] = 'package';
       }
	 $packageW= M('Package')->field('id,title,description,vertical_pic,level,update_time')->where("home_position='2' OR home_position='3'")->order('update_time desc')->limit(2)->select();
	 foreach ($packageW as $k => $v) {
         $packageW[$k]['type'] = 'package';
       }
	   
	 $arrH=array_merge((array)$docH,(array)$downH,(array)$packageH);
	 $arrW=array_merge((array)$docW,(array)$downW,(array)$packageW);
	
	 $arrH=array_sort($arrH,'update_time',SORT_DESC);//首页横图
	 $arrW=array_sort($arrW,'update_time',SORT_DESC);//首页竖图
	 $this->assign('bannerH',$arrH);//首页横图
	 $this->assign('bannerW',$arrW);//首页竖图
     $this->display('Widget/banner');
  }


    //专题
    public function ztpd(){
        //专区
        $zqList = M('batch')->where('pid = 0 AND interface = 0')->order('id desc')->limit(5)->select();

        //合集
        $ztList = M('feature')->where('interface = 0')->order('id desc')->limit(5)->select();

        //厂商
        $companyList  = M('company')->order('id desc')->limit(5)->select();

        foreach($companyList as $k=>&$v){
            if(empty($v['path']))
                continue;

            if(substr($v['path'],0,1) != '/'){
                $v['path'] = '/'.$v['path'];
            }

            if(substr($v['path'],-1,1) != '/'){
                $v['path'] = $v['path'].'/';
            }
        }
        unset($v);

        //机型
        $jx = M('featureCategory')->where('pid = 1')->select();
        foreach($jx as $k=>&$v){
            $v['game'] = M('feature')->where('category_id ='.$v['id'])->select();
        }



        $this->assign(array(
            'zqList'=>$zqList,
            'ztList'=>$ztList,
            'companyList'=>$companyList,
            'jx'=>$jx,
        ));

        $this->display('Widget/ztpd');
    }


    //专区
    public function zq(){

        //热门专区
        $hotArea = M('batch')->where('pid = 0 AND interface = 0')->order('abet desc')->limit(3)->select();
        $this->assign('hotArea',$hotArea);

        //专区列表数据
        $this->lists(17,'batch','pid = 0 AND interface = 0','id desc',12);

        //SEO

        //模板选择
        $this->display('Widget/zqlb');
    }

    public function zqRight(){
        //今日推荐
        $today = M('Down')->where('position & 1')->order('update_time desc')->find();
        if(!empty($today)){
        $today['score'] = get_score($today['id'],true);
        $today['batch'] = get_base_by_tag($today['id'],'Down','Batch','product')['url_token'];
        $today['tags']  = implode(',',array_column(get_tags($today['id'],'Down'),'title'));
        }

        //排行榜
        $rankGame = M('ProductTagsMap')->alias('a')->field('b.id,b.title,b.smallimg')->join('__DOWN__ b ON a.did = b.id')->where('b.status = 1 AND b.category_rootid = 1 AND (b.position > 0 || b.home_position > 0)')->group('a.tid')->limit(10)->order('b.update_time DESC')->select();
        foreach($rankGame as $k=>$v){
            $rankGame[$k]['score'] = get_score($v['id'],true);
            $rankGame[$k]['tags']  = implode(',',array_column(get_tags($v['id'],'down'),'title'));
        }

        //最新
        $newGame = M('ProductTagsMap')->alias('a')->field('b.id,b.title,b.smallimg')->join('__DOWN__ b ON a.did = b.id')->where('b.status = 1 AND b.category_rootid = 1')->group('a.tid')->limit(10)->order('b.update_time DESC')->select();
        foreach($newGame as $k=>$v){
            $newGame[$k]['score'] = get_score($v['id'],true);
            $newGame[$k]['tags']  = implode(',',array_column(get_tags($v['id'],'down'),'title'));
        }

        $this->assign(array(
            'today'=>$today,
            'rankGame'=>$rankGame,
            'newGame'=>$newGame,
        ));

        $this->display('Widget/zqRight');
    }



    //游戏合集
    public function hj(){
        //合集列表数据
        $this->lists(18,'special','pid = 0 AND interface = 0');
        $this->assign('pageTitle','合集');
        //模板选择
        $this->display('Widget/hj');
    }




    //专题 机型
    public function feature(){
        $type = M('featureCategory')->where('pid = 1')->select();
        foreach($type as $k=>$v){
            $type[$k]['feature'] = M('feature')->where('category_id = '.$v['id'])->select();
        }
        $this->assign('type',$type);//机型的列表数据


        //SEO
        $this->assign("SEO",WidgetSEO(array('special',null,14)));

        $this->display('Widget/feature');
    }

    //最热游戏
    public function hotGame(){
        $ids = M('downCategory')->field('id')->where('pid=1')->select();
        $ids = implode(',',array_column($ids,'id'));
        $lists = M('down')->field('id,smallimg,title')->where('category_id IN ('.$ids.')')->order('abet desc')->limit(10)->select();
        foreach($lists as $k=>$v){
            $lists[$k]['tags']  = implode(',',array_column(get_tags($v['id'],'Down'),'title'));
            $lists[$k]['score'] = get_score($v['id'],true);
        }
        $this->assign('lists',$lists);
        $this->display('Widget/hotGame');
    }

    //热门机型下载
    public function hotType(){
        $brandIds = M('featureCategory')->field('id')->where('pid = 1')->select();
        $brandIds = implode(',',array_column($brandIds,'id'));
        $type = M('featureCategory')->field('title,path')->where("pid IN($brandIds)")->limit(10)->select();
        $this->assign('hotType',$type);
        $this->display('Widget/hotType');
    }

    /***
     * 面包屑导航
     */
    public function Breadcrumb($type ,$id ,$model){
        if(!is_numeric($id) || $id < 1){
            return false;
        }
        $model   = ucfirst(strtolower($model));

        $category = "__CATEGORY__";

        switch($model){
            case 'Document': break;
            case 'Down': $category = '__DOWN_CATEGORY__' ;break;
            case 'Package': $category = '__PACKAGE_CATEGORY__' ;break;
            default: return false;
        }

        $result = "<a href=".staticUrl('index').">首页</a>->";

        switch($type){
            case 'lists':
                switch($model){
                    case 'Document': $category="downCategory"; break;
                    case 'Package':  $category='packageCategory';break;
                    default: return false;
                }
                $result .= M($category)->where('id = '.$id)->getField('title');
                break;

            case 'detail':
                if($model == 'Down'){
                    $arr = M($model)->field('title,category_rootid')->where('id = '.$id)->find();
                    $arr['cateName'] = M('downCategory')->where('id = '.$arr['category_rootid'])->getField('title');

                    if($arr['cateName']=="游戏"){
                        return $result .'<a href="'.C('STATIC_URL').'/yx/" >游戏</a>->'.$arr['title'];
                    }elseif($arr['cateName']=="软件"){
                        return $result .'<a href="'.C('STATIC_URL').'/rj/" >软件</a>->'.$arr['title'];
                    }elseif($arr['cateName']=="壁纸"){
                        return $result .'<a href="'.C('STATIC_URL').'/pic/" >图片库</a>->'.$arr['title'];
                    }elseif($arr['cateName']=="铃声"){
                        return $result .'<a href="'.getWidgetPath('10').'" >媒体库</a>->'.$arr['title'];
                    }
                }

                $arr = M($model)->alias('a')->field('a.title,b.title name,b.id')->join($category.' b ON a.category_id = b.id')->where('a.id = '.$id)->find();
                $result .='<a href='.staticUrl('lists',$arr['id'],$model).'>'.$arr['name'].'</a>->'.$arr['title'];
                break;
            default: return false;
        }
        return $result;
    }


    //手游周报
    public function weekNews(){
        $this->lists(20,'batch','category_id = 11 AND pid = 0');
        $this->assign('pageTitle','手游周报');
        //模板选择
        $this->display('Widget/hj');
    }

    //手游月报
    public function monthNews(){
        $this->lists(19,'batch','category_id = 10 AND pid = 0');
        $this->assign('pageTitle','手游月报');
        //模板选择
        $this->display('Widget/hj');
    }


 


    //此方法仅供 手游周报、手游月报、合集、专区 所用！！
    private function lists($WidgetSEO,$table,$where = 1,$order = 'id desc',$row = 20){
        //根据传入static_page表ID查找数据
        $page_id = I('page_id');
        $page_info = get_staticpage($page_id);

        //分页获取数据
        $count  = M($table)->where('1 AND '.$where)->count();// 查询满足要求的总记录数

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

        $lists = M($table)->where('1 AND '.$where)->order($order)->page($p, $row)->select();
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
        $this->assign("SEO",WidgetSEO(array('special',null,$WidgetSEO)));
    }
	
	
	

	
	
	   public function relateArticle($id){
	    $array=array();
		$pcount=0;//产品标签匹配出的相关文章数目
		$count=0;//标签匹配出的相关文章数目
		$p=get_base_by_tag($id,'Document','Document','product',false);
		$cid=M('Document')->where("id='$id'")->getField('category_id');
		
		$newArray =array();
		$newArray=$p;
		foreach($newArray as $key=>$value){    
		if($value['id']==$id&&$value['category_id']!=$cid){       //删除值为$v的项        
		   unset($newArray[$key]);    //unset()函数做删除操作
		   }
		}
		$count=count($newArray);
		if($count<8){
			foreach($p as $key=>$value){    
		    if($value['id']==$id){       //删除值为$v的项        
		    unset($p[$key]);    //unset()函数做删除操作
		   }
		}
			$array=$p;
		}else{
			$array=$newArray;
		}
	     //
		 //产品标签  同分类
        //产品标签  非该分类
        //8
		
	    $this->assign("relateArticle",array_slice($array,0,8));
		$this->display(T('Document@7230/Widget/relateArticle'));
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
     * 开测开服排行数据
     */
    public function testServerList(){
        $test   = $this->listServer(5,14,'a.level DESC,a.create_time DESC');
        $server = $this->listServer(3,14,'a.level DESC,a.create_time DESC');
        $this->assign(array(
            'test'=>$test,
            'server'=>$server
        ));
        $this->display(T('Home@7230/Widget/testserverlist'));
    }

    /**
     * Author: x i a o
     * 描  述: 标签归档
     */
    public function tagAll(){
        $data = M('Tags')->field('name,title')->where('status = 1 AND category IN(1,7)')->select();
        $lists = array(
            1=>array(),
            2=>array(),
            3=>array(),
            4=>array(),
            5=>array(),
            6=>array(),
            7=>array(),
            8=>array(),
            9=>array(),
            'A'=>array(),
            'B'=>array(),
            'C'=>array(),
            'D'=>array(),
            'E'=>array(),
            'F'=>array(),
            'G'=>array(),
            'H'=>array(),
            'I'=>array(),
            'J'=>array(),
            'K'=>array(),
            'L'=>array(),
            'M'=>array(),
            'N'=>array(),
            'O'=>array(),
            'P'=>array(),
            'Q'=>array(),
            'R'=>array(),
            'S'=>array(),
            'T'=>array(),
            'U'=>array(),
            'V'=>array(),
            'W'=>array(),
            'X'=>array(),
            'Y'=>array(),
            'Z'=>array(),
        );
        $number    = array_keys($lists);
        $pingyin = new \OT\PinYin();
        foreach($data as $k => $v){
            $a = ucwords(substr($pingyin->getFirstPY($v['title']),0,1));

            switch($a){
                case 1:$lists[1][] = $v;break;
                case 2:$lists[2][] = $v;break;
                case 3:$lists[3][] = $v;break;
                case 4:$lists[4][] = $v;break;
                case 5:$lists[5][] = $v;break;
                case 6:$lists[6][] = $v;break;
                case 7:$lists[7][] = $v;break;
                case 8:$lists[8][] = $v;break;
                case 9:$lists[9][] = $v;break;
                case 'A':$lists['A'][] = $v;break;
                case 'B':$lists['B'][] = $v;break;
                case 'C':$lists['C'][] = $v;break;
                case 'D':$lists['D'][] = $v;break;
                case 'E':$lists['E'][] = $v;break;
                case 'F':$lists['F'][] = $v;break;
                case 'G':$lists['G'][] = $v;break;
                case 'H':$lists['H'][] = $v;break;
                case 'I':$lists['I'][] = $v;break;
                case 'J':$lists['J'][] = $v;break;
                case 'K':$lists['K'][] = $v;break;
                case 'L':$lists['L'][] = $v;break;
                case 'M':$lists['M'][] = $v;break;
                case 'N':$lists['N'][] = $v;break;
                case 'O':$lists['O'][] = $v;break;
                case 'P':$lists['P'][] = $v;break;
                case 'Q':$lists['Q'][] = $v;break;
                case 'R':$lists['R'][] = $v;break;
                case 'S':$lists['S'][] = $v;break;
                case 'T':$lists['T'][] = $v;break;
                case 'U':$lists['U'][] = $v;break;
                case 'V':$lists['V'][] = $v;break;
                case 'W':$lists['W'][] = $v;break;
                case 'X':$lists['X'][] = $v;break;
                case 'Y':$lists['Y'][] = $v;break;
                case 'Z':$lists['Z'][] = $v;break;
                default:break;
            }
        }
        $lists = array_filter($lists);
        $num = array_keys($lists);
        $this->assign(array(
            'SEO'    => WidgetSEO(array('special',null,60)),
            'number' => $number,
            'num'    => $num,
            'lists'  => $lists
        ));
        $this->display('Widget/tagAll');
    }





}

